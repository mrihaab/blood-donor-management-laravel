<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\BloodGroup;
use App\Models\BloodRequest;
use App\Models\BloodUnit;
use App\Models\Donor;
use App\Models\User;
use App\Services\BloodInventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    protected BloodInventoryService $inventoryService;

    public function __construct(BloodInventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index()
    {
        return view('admin.reports.index');
    }

    public function donorReport(Request $request)
    {
        $query = Donor::with(['user', 'bloodGroup', 'appointments']);

        if ($request->filled('blood_group')) {
            $query->whereHas('bloodGroup', function($q) use ($request) {
                $q->where('name', $request->blood_group);
            });
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        $donors = $query->get();

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.donors-pdf', compact('donors'));
            return $pdf->download('donors-report.pdf');
        }

        if ($request->format === 'csv') {
            return $this->exportDonorsCsv($donors);
        }

        return view('admin.reports.donors', compact('donors'));
    }

    public function donationReport(Request $request)
    {
        $query = BloodUnit::with(['donor.user', 'bloodGroup', 'component'])
            ->latest();

        if ($request->filled('start_date')) {
            $query->whereDate('collection_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('collection_date', '<=', $request->end_date);
        }

        if ($request->filled('blood_group')) {
            $query->whereHas('bloodGroup', function($q) use ($request) {
                $q->where('name', $request->blood_group);
            });
        }

        $donations = $query->get();

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.donations-pdf', compact('donations'));
            return $pdf->download('donations-report.pdf');
        }

        if ($request->format === 'csv') {
            return $this->exportDonationsCsv($donations);
        }

        return view('admin.reports.donations', compact('donations'));
    }

    public function inventoryReport(Request $request)
    {
        $inventory = $this->inventoryService->getGroupedInventoryStock();
        $totalAvailable = BloodUnit::where('status', 'available')->count();
        $totalDispensed = BloodUnit::where('status', 'dispensed')->count();

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.inventory-pdf', compact('inventory', 'totalAvailable', 'totalDispensed'));
            return $pdf->download('inventory-report.pdf');
        }

        return view('admin.reports.inventory', compact('inventory', 'totalAvailable', 'totalDispensed'));
    }

    public function monthlyStats(Request $request)
    {
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        $startDate = now()->setYear($year)->setMonth($month)->startOfMonth();
        $endDate = now()->setYear($year)->setMonth($month)->endOfMonth();

        $stats = [
            'donations' => BloodUnit::whereBetween('created_at', [$startDate, $endDate])->count(),
            'blood_requests' => BloodRequest::whereBetween('created_at', [$startDate, $endDate])->count(),
            'new_donors' => User::where('role', 'donor')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'approved_requests' => BloodRequest::where('status', 'approved')->whereBetween('updated_at', [$startDate, $endDate])->count(),
            'dispensed_units' => BloodUnit::where('status', 'dispensed')->whereBetween('updated_at', [$startDate, $endDate])->count(),
        ];

        $bloodGroupStats = DB::table('blood_units')
            ->join('blood_groups', 'blood_units.blood_group_id', '=', 'blood_groups.id')
            ->select('blood_groups.name', DB::raw('count(*) as total'))
            ->whereBetween('blood_units.created_at', [$startDate, $endDate])
            ->groupBy('blood_groups.name')
            ->get();

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.monthly-stats-pdf', compact('stats', 'bloodGroupStats', 'month', 'year'));
            return $pdf->download("monthly-stats-{$year}-{$month}.pdf");
        }

        return view('admin.reports.monthly-stats', compact('stats', 'bloodGroupStats', 'month', 'year'));
    }

    private function exportDonorsCsv($donors)
    {
        $filename = 'donors-report-' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($donors) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Email', 'Phone', 'Blood Group', 'City', 'Status', 'Registered Date']);

            foreach ($donors as $donor) {
                fputcsv($file, [
                    $donor->id,
                    $donor->user->name ?? 'N/A',
                    $donor->user->email ?? 'N/A',
                    $donor->contact_number ?? '',
                    $donor->bloodGroup->name ?? '',
                    $donor->city ?? '',
                    $donor->user->status ?? 'active',
                    $donor->created_at->format('Y-m-d')
                ]);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function exportDonationsCsv($donations)
    {
        $filename = 'donations-report-' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($donations) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Unit Serial', 'Donor Name', 'Blood Group', 'Component', 'Collection Date', 'Expiry Date', 'Status']);

            foreach ($donations as $unit) {
                fputcsv($file, [
                    $unit->unit_number,
                    $unit->donor->user->name ?? 'Direct Admin Intake',
                    $unit->bloodGroup->name ?? 'N/A',
                    $unit->component->name ?? 'Whole Blood',
                    $unit->collection_date,
                    $unit->expiry_date,
                    $unit->status
                ]);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
