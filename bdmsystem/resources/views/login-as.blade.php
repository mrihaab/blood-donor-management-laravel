<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Donor Management System - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
        }
        
        .role-card {
            border: 2px solid transparent;
            border-radius: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
            background: #f8f9fa;
            position: relative;
            overflow: hidden;
        }
        
        .role-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .role-card.selected {
            border-color: #007bff;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .role-card.admin.selected {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .role-card.donor.selected {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        }
        
        .role-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.8;
        }
        
        .role-card.selected .role-icon {
            opacity: 1;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .header-section {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .system-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .system-subtitle {
            color: #7f8c8d;
            font-size: 1.1rem;
        }
        
        .quick-login {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
        }
        
        .quick-login-btn {
            border: 1px solid #dee2e6;
            background: white;
            border-radius: 8px;
            padding: 8px 15px;
            font-size: 14px;
            margin: 2px;
            transition: all 0.2s ease;
        }
        
        .quick-login-btn:hover {
            background: #e9ecef;
            border-color: #adb5bd;
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="login-card">
                        <div class="row g-0">
                            <!-- Left Side - Branding -->
                            <div class="col-lg-5 d-flex align-items-center justify-content-center p-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                <div class="text-center">
                                    <div class="mb-4">
                                        <i class="fas fa-heartbeat" style="font-size: 4rem; opacity: 0.9;"></i>
                                        <i class="fas fa-tint ms-2" style="font-size: 3rem; opacity: 0.8;"></i>
                                    </div>
                                    <h2 class="fw-bold mb-3">Blood Donor Management System</h2>
                                    <p class="lead opacity-90">Saving lives through organized blood donation</p>
                                    <div class="mt-4">
                                        <div class="d-flex justify-content-center align-items-center mb-2">
                                            <i class="fas fa-check-circle me-2"></i>
                                            <span>Secure & Reliable</span>
                                        </div>
                                        <div class="d-flex justify-content-center align-items-center mb-2">
                                            <i class="fas fa-users me-2"></i>
                                            <span>Community Driven</span>
                                        </div>
                                        <div class="d-flex justify-content-center align-items-center">
                                            <i class="fas fa-heart me-2 pulse"></i>
                                            <span>Life Saving</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Right Side - Login Form -->
                            <div class="col-lg-7 p-5">
                                <div class="header-section">
                                    <h3 class="system-title">🩸 Welcome</h3>
                                    <p class="system-subtitle">Choose your role to access the system</p>
                                </div>
                                
                                @if ($errors->any())
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        {{ $errors->first() }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif
                                
                                <form method="POST" action="{{ route('login.as') }}" id="loginForm">
                                    @csrf
                                    
                                    <!-- Role Selection -->
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold mb-3">
                                            <i class="fas fa-user-tag me-2"></i>Select Your Role
                                        </label>
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <div class="role-card admin text-center p-4" data-role="admin">
                                                    <i class="fas fa-user-shield role-icon text-primary"></i>
                                                    <h5 class="fw-bold">Admin</h5>
                                                    <p class="small mb-0">System Administrator</p>
                                                    <small class="text-muted">Manage donors, inventory & requests</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="role-card donor text-center p-4" data-role="donor">
                                                    <i class="fas fa-heart role-icon text-danger"></i>
                                                    <h5 class="fw-bold">Donor</h5>
                                                    <p class="small mb-0">Blood Donor</p>
                                                    <small class="text-muted">Schedule appointments & donate</small>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="role" id="selectedRole" required>
                                    </div>
                                    
                                    <!-- Email Input -->
                                    <div class="mb-4">
                                        <label for="email" class="form-label fw-semibold">
                                            <i class="fas fa-envelope me-2"></i>Email Address
                                        </label>
                                        <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email address" required value="{{ old('email') }}">
                                    </div>
                                    
                                    <!-- Submit Button -->
                                    <div class="d-grid mb-4">
                                        <button type="submit" class="btn btn-login btn-lg" id="loginBtn" disabled>
                                            <i class="fas fa-sign-in-alt me-2"></i>Access System
                                        </button>
                                    </div>
                                </form>
                                
                                <!-- Quick Login Section -->
                                <div class="quick-login">
                                    <h6 class="fw-semibold mb-3">
                                        <i class="fas fa-bolt me-2 text-warning"></i>Quick Login (Demo Accounts)
                                    </h6>
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <button type="button" class="btn quick-login-btn w-100" onclick="quickLogin('admin', 'admin@example.com')">
                                                <i class="fas fa-user-shield me-2 text-primary"></i>Admin Demo
                                            </button>
                                        </div>
                                        <div class="col-12">
                                            <button type="button" class="btn quick-login-btn w-100" onclick="quickLogin('donor', 'donor@example.com')">
                                                <i class="fas fa-heart me-2 text-danger"></i>Donor Demo
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Role selection functionality
        document.querySelectorAll('.role-card').forEach(card => {
            card.addEventListener('click', function() {
                // Remove selected class from all cards
                document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
                
                // Add selected class to clicked card
                this.classList.add('selected');
                
                // Set the role value
                const role = this.dataset.role;
                document.getElementById('selectedRole').value = role;
                
                // Enable login button
                document.getElementById('loginBtn').disabled = false;
                
                // Update login button text based on role
                const btnText = role === 'admin' ? 'Access Admin Panel' : 'Access Donor Portal';
                document.getElementById('loginBtn').innerHTML = `<i class="fas fa-sign-in-alt me-2"></i>${btnText}`;
            });
        });
        
        // Quick login functionality
        function quickLogin(role, email) {
            // Select the role
            document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
            document.querySelector(`[data-role="${role}"]`).classList.add('selected');
            
            // Set form values
            document.getElementById('selectedRole').value = role;
            document.getElementById('email').value = email;
            
            // Enable and update button
            const btnText = role === 'admin' ? 'Access Admin Panel' : 'Access Donor Portal';
            document.getElementById('loginBtn').innerHTML = `<i class="fas fa-sign-in-alt me-2"></i>${btnText}`;
            document.getElementById('loginBtn').disabled = false;
            
            // Auto submit after a short delay for better UX
            setTimeout(() => {
                document.getElementById('loginForm').submit();
            }, 500);
        }
        
        // Form validation
        document.getElementById('email').addEventListener('input', function() {
            const hasRole = document.getElementById('selectedRole').value;
            const hasEmail = this.value.length > 0;
            document.getElementById('loginBtn').disabled = !(hasRole && hasEmail);
        });
    </script>
</body>
</html>
