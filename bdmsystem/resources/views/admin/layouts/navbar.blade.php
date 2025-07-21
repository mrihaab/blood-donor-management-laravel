<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="{{ route('admin.dashboard') }}">🩸 BDMS Admin</a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">📊 Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('admin.donors.*') ? 'active' : '' }}" href="{{ route('admin.donors.index') }}">👥 Manage Donors</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('admin.appointments.*') ? 'active' : '' }}" href="{{ route('admin.appointments.index') }}">📅 Appointments</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}" href="{{ route('admin.inventory.index') }}">🩸 Blood Inventory</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('admin.donations.*') ? 'active' : '' }}" href="{{ route('admin.donations.index') }}">💉 Donations</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('admin.blood_requests.*') ? 'active' : '' }}" href="{{ route('admin.blood_requests.index') }}">🆘 Blood Requests</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">📊 Reports</a>
        </li>
      </ul>
      
      <ul class="navbar-nav">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
            👨‍💼 {{ auth()->user()->name }}
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
