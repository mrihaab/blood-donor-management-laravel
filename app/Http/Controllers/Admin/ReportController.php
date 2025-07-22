<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\BloodRequest;
use App\Models\BloodInventory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function donorReport(Request $request)
    {
        $query = User::where('role', 'donor')
            ->with(['donor.bloodGroup']);

        // Apply filters
        if ($request->filled('blood_group')) {
            $query->whereHas('donor.bloodGroup', function($q) use ($request) {
                $q->where('name', $request->blood_group);
            });
        }

        if ($request->filled('city')) {
            $query->whereHas('donor', function($q) use ($request) {
                $q->where('city', $request->city);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
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
        $query = Donation::with(['donor.user', 'bloodGroup']);

        // Apply filters
        if ($request->filled('start_date')) {
            $query->where('donation_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('donation_date', '<=', $request->end_date);
        }

        if ($request->filled('blood_group')) {
            $query->whereHas('bloodGroup', function($q) use ($request) {
                $q->where('name', $request->blood_group);
            });
        }

        $donations = $query->orderBy('donation_date', 'desc')->get();

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
        $inventory = BloodInventory::with('bloodGroup')
            ->orderBy('units_available', 'asc')
            ->get();

        $lowStockThreshold = \App\Models\SystemSetting::get('low_stock_threshold', 10);
        $lowStockItems = $inventory->where('units_available', '<', $lowStockThreshold);

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.inventory-pdf', compact('inventory', 'lowStockItems'));
            return $pdf->download('inventory-report.pdf');
        }

        return view('admin.reports.inventory', compact('inventory', 'lowStockItems', 'lowStockThreshold'));
    }

    public function monthlyStats(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $startDate = now()->setYear($year)->setMonth($month)->startOfMonth();
        $endDate = now()->setYear($year)->setMonth($month)->endOfMonth();

        $stats = [
            'donations' => Donation::whereBetween('donation_date', [$startDate, $endDate])->count(),
            'blood_requests' => BloodRequest::whereBetween('created_at', [$startDate, $endDate])->count(),
            'new_donors' => User::where('role', 'donor')
                ->whereBetween('created_at', [$startDate, $endDate])->count(),
            'approved_requests' => BloodRequest::where('status', 'approved')
                ->whereBetween('updated_at', [$startDate, $endDate])->count(),
        ];

        // Blood group wise donations
        $bloodGroupStats = DB::table('donations')
            ->join('blood_groups', 'donations.blood_group_id', '=', 'blood_groups.id')
            ->select('blood_groups.name', DB::raw('count(*) as total'))
            ->whereBetween('donations.donation_date', [$startDate, $endDate])
            ->groupBy('blood_groups.name')
            ->get();

        // Daily donations for the month
        $dailyDonations = DB::table('donations')
            ->select(DB::raw('DATE(donation_date) as date'), DB::raw('count(*) as total'))
            ->whereBetween('donation_date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.monthly-stats-pdf', compact(
                'stats', 'bloodGroupStats', 'dailyDonations', 'month', 'year'
            ));
            return $pdf->download("monthly-stats-{$year}-{$month}.pdf");
        }

        return view('admin.reports.monthly-stats', compact(
            'stats', 'bloodGroupStats', 'dailyDonations', 'month', 'year'
        ));
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
            fputcsv($file, ['Name', 'Email', 'Phone', 'Blood Group', 'City', 'Status', 'Registered Date']);

            foreach ($donors as $donor) {
                fputcsv($file, [
                    $donor->name,
                    $donor->email,
                    $donor->donor->contact_number ?? '',
                    $donor->donor->bloodGroup->name ?? '',
                    $donor->donor->city ?? '',
                    $donor->status,
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
            fputcsv($file, ['Donor Name', 'Blood Group', 'Units', 'Donation Date', 'Status']);

            foreach ($donations as $donation) {
                fputcsv($file, [
                    $donation->donor->user->name,
                    $donation->bloodGroup->name,
                    $donation->units,
                    $donation->donation_date,
                    $donation->status
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
