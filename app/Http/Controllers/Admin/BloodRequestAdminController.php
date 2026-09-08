<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveBloodRequest;
use App\Http\Requests\Admin\FulfillBloodRequest;
use App\Models\BloodInventory;
use App\Models\BloodRequest;
use App\Models\User;
use App\Services\BloodInventoryService;
use App\Services\BloodRequestService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class BloodRequestAdminController extends Controller
{
    protected BloodRequestService $bloodRequestService;
    protected BloodInventoryService $inventoryService;
    protected NotificationService $notificationService;

    public function __construct(
        BloodRequestService $bloodRequestService,
        BloodInventoryService $inventoryService,
        NotificationService $notificationService
    ) {
        $this->bloodRequestService = $bloodRequestService;
        $this->inventoryService = $inventoryService;
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $query = BloodRequest::with(['user', 'approver', 'hospitalEntity', 'bloodGroup']);

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
            $bgObj = $req->blood_group_id ? null : \App\Models\BloodGroup::where('name', $req->blood_group)->first();
            $bgId = $req->blood_group_id ?? ($bgObj ? $bgObj->id : null);

            $matchingStockCount = \App\Models\BloodUnit::where(function($q) use ($bgId, $req) {
                    if ($bgId) $q->where('blood_group_id', $bgId);
                    $q->orWhere('blood_group', $req->blood_group);
                })
                ->where('status', 'available')
                ->where('expiry_date', '>=', now()->format('Y-m-d'))
                ->count();

            $oNegGroup = \App\Models\BloodGroup::where('name', 'O-')->first();
            $oNegCount = \App\Models\BloodUnit::where(function($q) use ($oNegGroup) {
                    if ($oNegGroup) $q->where('blood_group_id', $oNegGroup->id);
                    $q->orWhere('blood_group', 'O-');
                })
                ->where('status', 'available')
                ->where('expiry_date', '>=', now()->format('Y-m-d'))
                ->count();

            $req->matching_stock_count = $matchingStockCount;
            $req->o_neg_stock_count = $oNegCount;
            $req->has_enough_stock = $matchingStockCount >= $req->units_needed;
            return $req;
        });

        $bloodRequests = $requests;
        $bloodGroups = \App\Models\BloodGroup::all();
        $hospitals = \App\Models\Hospital::all();

        return view('admin.blood-requests.index', compact('requests', 'bloodRequests', 'bloodGroups', 'hospitals'));
    }

    public function approve(ApproveBloodRequest $request, $id)
    {
        $bloodRequest = BloodRequest::findOrFail($id);

        try {
            $this->bloodRequestService->approveRequest($bloodRequest, auth()->user(), $request->input('admin_notes'));
            return back()->with('success', 'Blood request approved successfully and stock allocated via FEFO.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Unable to approve requisition: ' . $e->getMessage());
        }
    }

    public function instantDispense($id)
    {
        $bloodRequest = BloodRequest::findOrFail($id);

        try {
            if ($bloodRequest->status === 'pending') {
                $this->bloodRequestService->approveRequest($bloodRequest, auth()->user(), 'Instant 1-Click Bank Inventory Dispense');
            }

            $this->bloodRequestService->dispenseRequest($bloodRequest, auth()->user());

            return back()->with('success', "⚡ INSTANT DISPENSE SUCCESSFUL! Stock units auto-allocated via FEFO and issued directly to {$bloodRequest->hospital} without disturbing donors.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Unable to execute instant dispense: ' . $e->getMessage());
        }
    }

    public function dispenseUniversalFallback($id)
    {
        $bloodRequest = BloodRequest::findOrFail($id);

        try {
            $this->bloodRequestService->dispenseUniversalFallbackRequest($bloodRequest, auth()->user());

            return back()->with('success', "🩸 UNIVERSAL O- DISPENSE SUCCESSFUL! O- Negative bag allocated via FEFO and issued to {$bloodRequest->hospital} for Emergency Request #REQ-{$bloodRequest->id}.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Unable to execute Universal O- Fallback dispense: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
        $bloodRequest = BloodRequest::findOrFail($id);
        
        try {
            $this->bloodRequestService->rejectRequest($bloodRequest, auth()->user(), $request->input('reason'));
            return back()->with('success', 'Blood request rejected.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Unable to reject requisition: ' . $e->getMessage());
        }
    }

    public function fulfill(FulfillBloodRequest $request, $id)
    {
        $bloodRequest = BloodRequest::findOrFail($id);
        
        try {
            $this->bloodRequestService->dispenseRequest($bloodRequest, auth()->user());
            return back()->with('success', 'Blood request fulfilled.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Unable to fulfill requisition: ' . $e->getMessage());
        }
    }

    public function dispenseBlood(Request $request, $id)
    {
        $bloodRequest = BloodRequest::findOrFail($id);
        
        try {
            $this->bloodRequestService->dispenseRequest($bloodRequest, auth()->user());
            return back()->with('success', 'Blood dispensed successfully for request.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Unable to dispense blood: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $bloodRequest = BloodRequest::findOrFail($id);
        $reqId = $bloodRequest->id;
        $bloodRequest->delete();

        activity()
            ->causedBy(auth()->user())
            ->log("Deleted emergency blood request #REQ-{$reqId}");

        return back()->with('success', "Blood request #REQ-{$reqId} deleted successfully.");
    }

    public function notifyDonors($id)
    {
        $bloodRequest = BloodRequest::findOrFail($id);
        $count = $this->notificationService->sendEmergencyBroadcast($bloodRequest);

        return back()->with('success', "🚨 Omnichannel Emergency Alert dispatched to {$count} nearby eligible donors in {$bloodRequest->city} via In-App Notification, Emergency Email, and WhatsApp/SMS gateway!");
    }

    public function bulkTriage(Request $request)
    {
        $query = BloodRequest::where('status', 'pending')
            ->with(['hospitalEntity', 'patient', 'bloodGroup'])
            ->orderByRaw("CASE WHEN urgency_level = 'emergency' THEN 0 WHEN urgency_level = 'urgent' THEN 1 ELSE 2 END")
            ->latest();

        $pendingRequests = $query->get();

        $triagedRequests = $pendingRequests->map(function ($req) {
            $compat = $this->bloodRequestService->getRecommendedCompatibleUnit($req);
            $req->recommended_compat = $compat;
            $req->has_compatible_stock = $compat !== null;
            return $req;
        });

        $readyToDispenseCount = $triagedRequests->where('has_compatible_stock', true)->count();
        $shortageCount = $triagedRequests->where('has_compatible_stock', false)->count();

        return view('admin.blood-requests.bulk-triage', compact('triagedRequests', 'readyToDispenseCount', 'shortageCount'));
    }

    public function bulkDispense(Request $request)
    {
        $request->validate([
            'request_ids' => 'required|array|min:1',
            'request_ids.*' => 'exists:blood_requests,id',
        ]);

        $requestIds = $request->input('request_ids');
        $successfulCount = 0;
        $failedCount = 0;

        \Illuminate\Support\Facades\DB::transaction(function () use ($requestIds, &$successfulCount, &$failedCount) {
            foreach ($requestIds as $id) {
                $bloodRequest = BloodRequest::lockForUpdate()->find($id);
                if (!$bloodRequest || $bloodRequest->status !== 'pending') {
                    $failedCount++;
                    continue;
                }

                try {
                    $this->bloodRequestService->approveRequest($bloodRequest, auth()->user(), 'Batch Bulk AI Triage Auto-Allocation');
                    $this->bloodRequestService->dispenseRequest($bloodRequest, auth()->user());
                    $successfulCount++;
                } catch (\Throwable $e) {
                    $failedCount++;
                }
            }
        });

        if ($successfulCount > 0) {
            return redirect()->route('admin.blood_requests.bulk_triage')
                ->with('success', "⚡ BATCH DISPENSE SUCCESSFUL! {$successfulCount} Blood Bag(s) allocated via FEFO and issued to respective hospital wards in a single transaction.");
        }

        return redirect()->route('admin.blood_requests.bulk_triage')
            ->with('error', "Unable to dispense batch. {$failedCount} request(s) could not be fulfilled due to insufficient stock.");
    }

    public function reportReaction(Request $request, $id)
    {
        $bloodRequest = BloodRequest::findOrFail($id);
        $reasonNotes = "⚠️ TRANSFUSION REACTION INCIDENT REPORTED: " . ($request->input('notes', 'Adverse patient reaction during blood administration. Transfusion halted immediately.'));

        $bloodRequest->update([
            'reason' => ($bloodRequest->reason ? $bloodRequest->reason . " | " : "") . $reasonNotes,
        ]);

        if ($bloodRequest->donor_id) {
            BloodUnit::where('donor_id', $bloodRequest->donor_id)
                ->where('status', 'available')
                ->update([
                    'status' => 'quarantined',
                    'temperature_status' => 'quarantined_adverse_reaction',
                ]);
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($bloodRequest)
            ->log("Adverse transfusion reaction reported for Request #REQ-{$id}. Same-donor units quarantined.");

        return back()->with('success', "🚨 TRANSFUSION REACTION LOGGED! Transfusion halted and all matching donor units in inventory quarantined for lab antibody re-testing.");
    }
}
