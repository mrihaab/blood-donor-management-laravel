@extends('admin.layouts.app')

@section('title', 'Notifications Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Notifications Management</h4>
                <div class="page-title-right">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createNotificationModal">
                        <i class="fas fa-plus"></i> Send Notification
                    </button>
                    <button type="button" class="btn btn-info ms-2" data-bs-toggle="modal" data-bs-target="#bulkNotificationModal">
                        <i class="fas fa-broadcast-tower"></i> Bulk Notification
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Recipients</th>
                                    <th>Status</th>
                                    <th>Sent At</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($notifications as $notification)
                                <tr>
                                    <td>{{ $notification->title }}</td>
                                    <td>
                                        <span class="badge bg-{{ $notification->type === 'email' ? 'primary' : ($notification->type === 'sms' ? 'success' : 'info') }}">
                                            {{ ucfirst($notification->type) }}
                                        </span>
                                    </td>
                                    <td>{{ count($notification->recipients) }} users</td>
                                    <td>
                                        <span class="badge bg-{{ $notification->status === 'sent' ? 'success' : ($notification->status === 'failed' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($notification->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $notification->sent_at ? $notification->sent_at->format('M d, Y H:i') : '-' }}</td>
                                    <td>{{ $notification->creator->name }}</td>
                                    <td>
                                        <a href="{{ route('admin.notifications.show', $notification) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No notifications found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $notifications->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Notification Modal -->
<div class="modal fade" id="createNotificationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('admin.notifications.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Send Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" required>
                            <option value="email">Email</option>
                            <option value="sms">SMS</option>
                            <option value="system">System</option>
                            <option value="announcement">Announcement</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Recipients</label>
                        <select name="recipients[]" class="form-select" multiple required>
                            @foreach(App\Models\User::where('role', 'donor')->get() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Hold Ctrl to select multiple recipients</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Notification</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Notification Modal -->
<div class="modal fade" id="bulkNotificationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('admin.notifications.send_bulk') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Send Bulk Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" required>
                            <option value="email">Email</option>
                            <option value="sms">SMS</option>
                            <option value="system">System</option>
                            <option value="announcement">Announcement</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Send To</label>
                        <select name="send_to" class="form-select" required>
                            <option value="all_donors">All Donors</option>
                            <option value="all_admins">All Admins</option>
                            <option value="all_users">All Users</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Bulk Notification</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
