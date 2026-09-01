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

    public function index()
    {
        $requests = BloodRequest::with(['user', 'approver'])
            ->latest()
            ->paginate(15);

        $bloodRequests = $requests;
            
        return view('admin.blood-requests.index', compact('requests', 'bloodRequests'));
    }

    public function approve(ApproveBloodRequest $request, $id)
    {
        $bloodRequest = BloodRequest::findOrFail($id);
        $this->bloodRequestService->approveRequest($bloodRequest, auth()->user(), $request->input('admin_notes'));

        return back()->with('success', 'Blood request approved successfully.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
        $bloodRequest = BloodRequest::findOrFail($id);
        $this->bloodRequestService->rejectRequest($bloodRequest, auth()->user(), $request->input('reason'));

        return back()->with('success', 'Blood request rejected.');
    }

    public function fulfill(FulfillBloodRequest $request, $id)
    {
        $bloodRequest = BloodRequest::findOrFail($id);
        $this->bloodRequestService->dispenseRequest($bloodRequest, auth()->user());

        return back()->with('success', 'Blood request fulfilled.');
    }

    public function dispenseBlood(Request $request, $id)
    {
        $bloodRequest = BloodRequest::findOrFail($id);
        $this->bloodRequestService->dispenseRequest($bloodRequest, auth()->user());

        return back()->with('success', 'Blood dispensed successfully for request.');
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

        return back()->with('success', "Notified {$count} matching donors.");
    }
}
