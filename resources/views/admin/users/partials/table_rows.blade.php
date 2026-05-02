@forelse($users as $user)
<tr>
    <td>
        <div class="d-flex align-items-center">
            <img src="{{ $user->avatar }}" class="rounded-circle me-2" style="width: 40px; height: 40px;">
            <div>
                <strong>{{ $user->name }}</strong>
                <br><small class="text-muted">ID: #{{ $user->id }}</small>
            </div>
        </div>
    </td>
    <td>
        <i class="fas fa-envelope"></i> {{ $user->email }}<br>
        @if($user->phone)<i class="fas fa-phone"></i> {{ $user->phone }}@endif
    </td>
    <td>
        <span class="badge bg-{{ $user->is_admin ? 'warning' : 'info' }}">
            {{ $user->is_admin ? 'Admin' : 'Customer' }}
        </span>
        @if(!$user->is_admin && $user->id != auth()->id())
            <button onclick="toggleAdmin({{ $user->id }})" class="btn btn-sm btn-link">Make Admin</button>
        @endif
    </td>
    <td>
        @if($user->email_verified_at)
            <span class="badge bg-success">Verified</span>
        @else
            <span class="badge bg-danger">Unverified</span>
            <button onclick="verifyEmail({{ $user->id }})" class="btn btn-sm btn-link">Verify Now</button>
        @endif
     </td>
    <td><span class="badge bg-primary">{{ $user->orders_count }} orders</span></td>
    <td>{{ $user->created_at->format('M d, Y') }}</td>
    <td>
        <div class="btn-group btn-group-sm">
            <button onclick="editUser({{ $user->id }})" class="btn btn-primary">
                <i class="fas fa-edit"></i>
            </button>
            <a href="{{ route('admin.users.impersonate', $user->id) }}" class="btn btn-warning" onclick="return confirm('Start impersonating this user?')">
                <i class="fas fa-mask"></i>
            </a>
            @if(!$user->is_admin && $user->id != auth()->id())
                <button onclick="deleteUser({{ $user->id }})" class="btn btn-danger">
                    <i class="fas fa-trash"></i>
                </button>
            @endif
        </div>
     </td>
</tr>
@empty
<tr>
    <td colspan="7" class="text-center py-5">
        <i class="fas fa-users fa-3x text-muted mb-3"></i>
        <p>No users found.</p>
    </td>
</tr>
@endforelse