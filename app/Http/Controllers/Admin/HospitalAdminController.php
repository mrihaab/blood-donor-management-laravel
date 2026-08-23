<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use Illuminate\Http\Request;

class HospitalAdminController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Hospital::class);

        $query = Hospital::withCount(['bloodRequests', 'patients']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('license_number', 'like', "%{$search}%");
            });
        }

        $hospitals = $query->latest()->paginate(15)->withQueryString();

        return view('admin.hospitals.index', compact('hospitals'));
    }

    public function show(Hospital $hospital)
    {
        $this->authorize('view', $hospital);

        $hospital->load(['patients', 'bloodRequests' => function ($q) {
            $q->latest()->take(10);
        }]);

        return view('admin.hospitals.show', compact('hospital'));
    }
}
