<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Donor;
use App\Models\BloodGroup;
use App\Models\BloodRequest;
use App\Models\BloodUnit;
use App\Models\Donation;
use App\Models\User;
use App\Models\SystemSetting;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Basic Statistics
        $totalDonors = User::where('role', 'donor')->count();
        $activeDonors = User::where('role', 'donor')->where('status', 'active')->count();
        $totalRequests = BloodRequest::count();
        $pendingRequests = BloodRequest::where('status', 'pending')->count();
        $approvedRequests = BloodRequest::where('status', 'approved')->count();
        $totalDonations = Donation::count();
        $totalAdmins = User::where('role', 'admin')->count();

        // Single Source of Truth: Physical BloodUnit bags count
        $totalAvailableUnits = BloodUnit::where('status', 'available')
            ->where('expiry_date', '>=', now()->format('Y-m-d'))
            ->count();

        // Optimized GROUP BY queries to avoid N+1 queries per blood group
        $availableGroupCounts = BloodUnit::select('blood_group_id', DB::raw('COUNT(*) as total'))
            ->where('status', 'available')
            ->where('expiry_date', '>=', now()->format('Y-m-d'))
            ->groupBy('blood_group_id')
            ->pluck('total', 'blood_group_id');

        $reservedGroupCounts = BloodUnit::select('blood_group_id', DB::raw('COUNT(*) as total'))
            ->whereIn('status', ['reserved', 'allocated'])
            ->groupBy('blood_group_id')
            ->pluck('total', 'blood_group_id');

        // Map stock across all BloodGroups using grouped query results
        $bloodGroups = BloodGroup::all();
        $bloodInventory = $bloodGroups->map(function ($group) use ($availableGroupCounts, $reservedGroupCounts) {
            return [
                'blood_group_id'  => $group->id,
                'blood_group'     => $group->name,
                'units_available' => (int) ($availableGroupCounts[$group->id] ?? 0),
                'units_requested' => (int) ($reservedGroupCounts[$group->id] ?? 0),
                'last_updated'    => now()->format('M d, Y')
            ];
        });

        $lowStockThreshold = SystemSetting::get('low_stock_threshold', 10);
        $lowStockAlerts = $bloodInventory->filter(function ($item) use ($lowStockThreshold) {
            return $item['units_available'] < $lowStockThreshold;
        })->values();

        // Expiring soon (within 7 days)
        $expiringSoonCount = BloodUnit::where('status', 'available')
            ->whereBetween('expiry_date', [now()->format('Y-m-d'), now()->addDays(7)->format('Y-m-d')])
            ->count();

        // Monthly Donation Chart (Last 6 months)
        $monthQuery = DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', donation_date) as month"
            : "DATE_FORMAT(donation_date, '%Y-%m') as month";

        $donationsChart = DB::table('donations')
            ->select(
                DB::raw($monthQuery),
                DB::raw('COUNT(*) as count')
            )
            ->where('donation_date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => Carbon::createFromFormat('Y-m', $item->month)->format('M Y'),
                    'count' => $item->count
                ];
            });

        // Blood Group Distribution
        $bloodGroupStats = DB::table('donations')
            ->join('blood_groups', 'donations.blood_group_id', '=', 'blood_groups.id')
            ->select('blood_groups.name as blood_group', DB::raw('COUNT(*) as total'))
            ->groupBy('blood_groups.name')
            ->orderBy('total', 'desc')
            ->get();

        // Recent Activities from activity_log
        $recentActivities = Activity::with('causer')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'message' => $activity->description,
                    'user_name' => optional($activity->causer)->name ?? 'System',
                    'created_at' => $activity->created_at->diffForHumans()
                ];
            });

        // This Month Statistics
        $thisMonth = now()->startOfMonth();
        $thisMonthStats = [
            'donations' => Donation::where('donation_date', '>=', $thisMonth)->count(),
            'new_donors' => User::where('role', 'donor')
                ->where('created_at', '>=', $thisMonth)
                ->count(),
            'blood_requests' => BloodRequest::where('created_at', '>=', $thisMonth)->count(),
            'approved_requests' => BloodRequest::where('status', 'approved')
                ->where('updated_at', '>=', $thisMonth)
                ->count()
        ];

        // Quick Actions Data
        $quickActions = [
            'pending_requests' => $pendingRequests,
            'low_stock_count' => $lowStockAlerts->count(),
            'pending_appointments' => DB::table('appointments')
                ->where('status', 'scheduled')
                ->whereDate('appointment_date', '>=', now())
                ->count(),
        ];

        // Active Emergency Requisitions for Top Dashboard Command Banner
        $activeEmergencyRequests = BloodRequest::with(['user', 'hospitalEntity'])
            ->whereIn('status', ['pending', 'approved'])
            ->where(function($q) {
                $q->where('urgency', 'emergency')
                  ->orWhere('urgency_level', 'emergency')
                  ->orWhere('urgency_level', 'urgent');
            })
            ->latest()
            ->get()
            ->map(function($req) {
                $bg = \App\Models\BloodGroup::where('name', $req->blood_group)->first();
                $matchingStockCount = $bg ? \App\Models\BloodUnit::where('blood_group_id', $bg->id)
                    ->where('status', 'available')
                    ->where('expiry_date', '>=', now()->format('Y-m-d'))
                    ->count() : 0;

                $req->matching_stock_count = $matchingStockCount;
                $req->has_enough_stock = $matchingStockCount >= $req->units_needed;
                return $req;
            });

        return view('admin.dashboard', compact(
            'totalDonors',
            'activeDonors', 
            'totalRequests',
            'pendingRequests',
            'approvedRequests',
            'totalDonations',
            'totalAdmins',
            'totalAvailableUnits',
            'expiringSoonCount',
            'bloodInventory',
            'lowStockAlerts',
            'lowStockThreshold',
            'donationsChart',
            'bloodGroupStats',
            'recentActivities',
            'thisMonthStats',
            'quickActions',
            'activeEmergencyRequests'
        ));
    }

    public function emergencyRequests(Request $request)
    {
        $query = BloodRequest::with(['user', 'approver', 'hospitalEntity']);

        if ($request->filled('urgency')) {
            $query->where('urgency_level', $request->input('urgency'));
        }
        if ($request->filled('blood_group')) {
            $query->where('blood_group', $request->input('blood_group'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('hospital_id')) {
            $query->where('hospital_id', $request->input('hospital_id'));
        }

        $requests = $query->orderByRaw("CASE WHEN urgency_level = 'emergency' THEN 0 WHEN urgency_level = 'urgent' THEN 1 ELSE 2 END")
            ->latest()
            ->paginate(15);

        $requests->getCollection()->transform(function($req) {
            $bg = \App\Models\BloodGroup::where('name', $req->blood_group)->first();
            $matchingStockCount = $bg ? \App\Models\BloodUnit::where('blood_group_id', $bg->id)
                ->where('status', 'available')
                ->where('expiry_date', '>=', now()->format('Y-m-d'))
                ->count() : 0;

            $req->matching_stock_count = $matchingStockCount;
            $req->has_enough_stock = $matchingStockCount >= $req->units_needed;
            return $req;
        });

        $bloodRequests = $requests;
        $bloodGroups = \App\Models\BloodGroup::all();
        $hospitals = \App\Models\Hospital::all();

        return view('admin.emergency-requests.index', compact('requests', 'bloodRequests', 'bloodGroups', 'hospitals'));
    }
}
