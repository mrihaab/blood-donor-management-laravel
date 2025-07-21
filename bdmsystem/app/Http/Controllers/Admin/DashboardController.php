<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Donor;
use App\Models\BloodRequest;
use App\Models\BloodInventory;
use App\Models\Donation;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\SystemSetting;
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

        // Blood Inventory with Low Stock Alerts
        $bloodInventory = BloodInventory::with('bloodGroup')
            ->get()
            ->map(function ($inventory) {
                return [
                    'blood_group' => $inventory->bloodGroup->name,
                    'units_available' => $inventory->units_available,
                    'units_requested' => $inventory->units_requested,
                    'last_updated' => $inventory->updated_at->format('M d, Y')
                ];
            });

        $lowStockThreshold = SystemSetting::get('low_stock_threshold', 10);
        $lowStockAlerts = BloodInventory::with('bloodGroup')
            ->where('units_available', '<', $lowStockThreshold)
            ->get()
            ->map(function ($inventory) use ($lowStockThreshold) {
                return [
                    'blood_group' => $inventory->bloodGroup->name,
                    'units_available' => $inventory->units_available,
                    'threshold' => $lowStockThreshold
                ];
            });

        // Monthly Donation Chart (Last 6 months)
        $donationsChart = DB::table('donations')
            ->select(
                DB::raw("DATE_FORMAT(donation_date, '%Y-%m') as month"),
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

        // Recent Activities
        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'message' => $activity->message,
                    'user_name' => $activity->user->name ?? 'System',
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

        // Top Donors (by donation count)
        $topDonors = DB::table('donations')
            ->join('donors', 'donations.donor_id', '=', 'donors.id')
            ->join('users', 'donors.user_id', '=', 'users.id')
            ->select('users.name', DB::raw('COUNT(*) as donation_count'))
            ->groupBy('users.id', 'users.name')
            ->orderBy('donation_count', 'desc')
            ->limit(5)
            ->get();

        // Quick Actions Data
        $quickActions = [
            'pending_requests' => $pendingRequests,
            'low_stock_count' => $lowStockAlerts->count(),
            'pending_appointments' => DB::table('appointments')
                ->where('status', 'scheduled')
                ->whereDate('appointment_date', '>=', now())
                ->count(),
            'notifications_pending' => DB::table('notifications')
                ->where('status', 'pending')
                ->count()
        ];

        return view('admin.dashboard', compact(
            'totalDonors',
            'activeDonors', 
            'totalRequests',
            'pendingRequests',
            'approvedRequests',
            'totalDonations',
            'totalAdmins',
            'bloodInventory',
            'lowStockAlerts',
            'lowStockThreshold',
            'donationsChart',
            'bloodGroupStats',
            'recentActivities',
            'thisMonthStats',
            'topDonors',
            'quickActions'
        ));
    }
}
