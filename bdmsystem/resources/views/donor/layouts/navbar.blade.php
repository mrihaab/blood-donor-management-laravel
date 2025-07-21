<nav class="navbar navbar-expand-lg navbar-dark bg-danger">
  <div class="container-fluid">
    <a class="navbar-brand" href="{{ route('donor.dashboard') }}">🩸 Blood Donor Portal</a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('donor.dashboard') ? 'active' : '' }}" href="{{ route('donor.dashboard') }}">📊 Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('donor.profile.*') ? 'active' : '' }}" href="{{ route('donor.profile.edit') }}">👤 My Profile</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('donor.appointments.*') ? 'active' : '' }}" href="{{ route('donor.appointments.index') }}">📅 My Appointments</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('donor.blood_requests.*') ? 'active' : '' }}" href="{{ route('donor.blood_requests.index') }}">🆘 Blood Requests</a>
        </li>
      </ul>
      
      <ul class="navbar-nav">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
            🩸 {{ auth()->user()->name }}
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('login.as') }}">🔄 Switch Role</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item">🚪 Logout</button>
              </form>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>
