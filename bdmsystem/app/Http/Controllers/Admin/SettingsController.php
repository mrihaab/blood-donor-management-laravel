<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\BloodGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $generalSettings = SystemSetting::byGroup('general')->get();
        $inventorySettings = SystemSetting::byGroup('inventory')->get();
        $donationSettings = SystemSetting::byGroup('donation')->get();
        $notificationSettings = SystemSetting::byGroup('notifications')->get();
        $locationSettings = SystemSetting::byGroup('locations')->get();
        $bloodGroups = BloodGroup::all();
        
        return view('admin.settings.index', compact(
            'generalSettings',
            'inventorySettings', 
            'donationSettings',
            'notificationSettings',
            'locationSettings',
            'bloodGroups'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'organization_name' => 'required|string|max:255',
            'organization_email' => 'required|email',
            'organization_phone' => 'required|string|max:20',
            'organization_address' => 'required|string',
            'low_stock_threshold' => 'required|integer|min:1',
            'donation_interval_days' => 'required|integer|min:1',
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'cities' => 'array',
            'cities.*' => 'string|max:100'
        ]);

        // Update general settings
        SystemSetting::set('organization_name', $request->organization_name);
        SystemSetting::set('organization_email', $request->organization_email);
        SystemSetting::set('organization_phone', $request->organization_phone);
        SystemSetting::set('organization_address', $request->organization_address);
        
        // Update inventory settings
        SystemSetting::set('low_stock_threshold', $request->low_stock_threshold, 'integer');
        
        // Update donation settings
        SystemSetting::set('donation_interval_days', $request->donation_interval_days, 'integer');
        
        // Update notification settings
        SystemSetting::set('email_notifications', $request->has('email_notifications'), 'boolean');
        SystemSetting::set('sms_notifications', $request->has('sms_notifications'), 'boolean');
        
        // Update cities
        if ($request->has('cities')) {
            SystemSetting::set('cities', $request->cities, 'json');
        }

        // Handle logo upload
        if ($request->hasFile('organization_logo')) {
            $logoPath = $request->file('organization_logo')->store('logos', 'public');
            SystemSetting::set('organization_logo', $logoPath);
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully!');
    }

    public function manageBloodGroups()
    {
        $bloodGroups = BloodGroup::all();
        return view('admin.settings.blood-groups', compact('bloodGroups'));
    }

    public function storeBloodGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:10|unique:blood_groups,name',
            'description' => 'nullable|string|max:255'
        ]);

        BloodGroup::create($request->only(['name', 'description']));

        return redirect()->route('admin.settings.blood-groups')
            ->with('success', 'Blood group added successfully!');
    }

    public function updateBloodGroup(Request $request, BloodGroup $bloodGroup)
    {
        $request->validate([
            'name' => 'required|string|max:10|unique:blood_groups,name,' . $bloodGroup->id,
            'description' => 'nullable|string|max:255'
        ]);

        $bloodGroup->update($request->only(['name', 'description']));

        return redirect()->route('admin.settings.blood-groups')
            ->with('success', 'Blood group updated successfully!');
    }

    public function destroyBloodGroup(BloodGroup $bloodGroup)
    {
        // Check if blood group is in use
        $inUse = \App\Models\Donor::where('blood_group_id', $bloodGroup->id)->exists() ||
                 \App\Models\BloodRequest::where('blood_group_id', $bloodGroup->id)->exists();
        
        if ($inUse) {
            return redirect()->route('admin.settings.blood-groups')
                ->with('error', 'Cannot delete blood group as it is in use.');
        }

        $bloodGroup->delete();

        return redirect()->route('admin.settings.blood-groups')
            ->with('success', 'Blood group deleted successfully!');
    }

    public function manageCities()
    {
        $cities = SystemSetting::get('cities', []);
        return view('admin.settings.cities', compact('cities'));
    }

    public function updateCities(Request $request)
    {
        $request->validate([
            'cities' => 'required|array|min:1',
            'cities.*' => 'required|string|max:100'
        ]);

        SystemSetting::set('cities', $request->cities, 'json');

        return redirect()->route('admin.settings.cities')
            ->with('success', 'Cities updated successfully!');
    }
}
