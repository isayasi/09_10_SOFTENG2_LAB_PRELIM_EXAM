<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiverr - Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Global Styles */
        body {
            font-family: "Arial", sans-serif;
            background: linear-gradient(135deg, #e8eaf6 0%, #d1c4e9 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Login Container */
        .login-container {
            background: linear-gradient(135deg, #f3e5f5 0%, #ede7f6 100%);
            padding: 2.5rem 3rem;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 450px;
            animation: fadeInUp 0.8s ease;
        }

        /* Header */
        .login-header h2 {
            color: #433878;
            text-align: center;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .login-header h2 i {
            margin-right: 0.5rem;
            color: #6a5acd;
        }

        .login-header p {
            text-align: center;
            color: #555;
            font-size: 0.95rem;
            margin-bottom: 2rem;
        }

        /* Alert */
        #loginAlert {
            margin-bottom: 1.5rem;
            border-radius: 12px;
            font-weight: 500;
        }

        /* Form Inputs */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 0.9rem 1.25rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #ede7f6, #f3e5f5);
        }

        .form-control:focus {
            border-color: #433878;
            box-shadow: 0 0 0 0.3rem rgba(67,56,120,0.25);
            transform: scale(1.02);
        }

        /* Labels */
        label {
            font-weight: 600;
            color: #433878;
            margin-bottom: 0.5rem;
        }

        /* Input Group Icons */
        .input-group-text {
            background: #6a5acd;
            border: none;
            color: white;
            border-radius: 12px 0 0 12px;
        }

        .input-group .input-group-append .input-group-text {
            cursor: pointer;
            border-radius: 0 12px 12px 0;
        }

        /* Button */
        .btn-login {
            width: 100%;
            background: linear-gradient(45deg, #433878, #6a5acd);
            color: white;
            font-weight: 600;
            border: none;
            border-radius: 25px;
            padding: 0.75rem;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .btn-login:hover {
            background: linear-gradient(45deg, #6a5acd, #8a79ff);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67,56,120,0.3);
        }

        /* Admin Features List */
        .admin-features {
            margin-top: 2rem;
        }

        .admin-features h5 {
            color: #433878;
            margin-bottom: 0.75rem;
            font-weight: 600;
        }

        .feature-list {
            list-style: none;
            padding-left: 0;
        }

        .feature-list li {
            margin-bottom: 0.5rem;
            color: #555;
            font-size: 0.95rem;
        }

        .feature-list li i {
            color: #6a5acd;
            margin-right: 0.5rem;
        }

        /* Debug Info */
        .debug-info {
            margin-top: 1.5rem;
            padding: 1rem;
            border-radius: 12px;
            background: #f3e5f5;
            display: none;
            color: #433878;
            font-size: 0.85rem;
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 576px) {
            .login-container {
                padding: 2rem;
                width: 90%;
            }
            
            .login-header h2 {
                font-size: 1.5rem;
            }
            
            .btn-login {
                padding: 0.65rem;
            }
        }

    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h2><i class="fas fa-crown"></i> Admin Portal</h2>
            <p>Fiverr - Admin</p>
        </div>
        
        <div class="login-body">
            <div id="loginAlert" class="alert alert-danger d-none" role="alert">
                <i class="fas fa-exclamation-circle"></i> <span id="alertMessage"></span>
            </div>
            
            <!-- Updated: post directly to user.php -->
            <form id="adminLoginForm" action="classes/User.php" method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        </div>
                        <input type="email" class="form-control" id="email" name="email" placeholder="admin@example.com" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        </div>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                        <div class="input-group-append">
                            <span class="input-group-text password-toggle" id="passwordToggle">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- updated: hidden input matches what we catch in user.php -->
                <input type="hidden" name="do_admin_login" value="1">
                
                <button type="submit" class="btn btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login as Administrator
                </button>
            </form>
            
            <div class="admin-features">
                <h5>Administrator Capabilities:</h5>
                <ul class="feature-list">
                    <li><i class="fas fa-check-circle"></i> Manage categories and subcategories</li>
                    <li><i class="fas fa-check-circle"></i> Monitor system performance</li>
                    <li><i class="fas fa-check-circle"></i> View all user activities</li>
                    <li><i class="fas fa-check-circle"></i> Resolve disputes between users</li>
                    <li><i class="fas fa-check-circle"></i> Switch to client role when needed</li>
                </ul>
            </div>
            
            <div class="debug-info" id="debugInfo">
                <strong>Debug Information:</strong><br>
                <div id="debugContent"></div>
            </div>
        </div>
        
        
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordToggle = document.getElementById('passwordToggle');
            const passwordInput = document.getElementById('password');
            
            passwordToggle.addEventListener('click', function() {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    passwordToggle.innerHTML = '<i class="fas fa-eye-slash"></i>';
                } else {
                    passwordInput.type = 'password';
                    passwordToggle.innerHTML = '<i class="fas fa-eye"></i>';
                }
            });
            
            const loginForm = document.getElementById('adminLoginForm');
            const loginAlert = document.getElementById('loginAlert');
            const alertMessage = document.getElementById('alertMessage');
            const debugInfo = document.getElementById('debugInfo');
            const debugContent = document.getElementById('debugContent');
            
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('error')) {
                showAlert(urlParams.get('error'), 'danger');
                if (urlParams.has('debug')) {
                    debugInfo.style.display = 'block';
                    debugContent.textContent = decodeURIComponent(urlParams.get('debug'));
                }
            }
            
            loginForm.addEventListener('submit', function(e) {
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                
                if (!email || !password) {
                    e.preventDefault();
                    showAlert('Please fill in all fields', 'danger');
                    return;
                }
                
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    showAlert('Please enter a valid email address', 'danger');
                    return;
                }
            });
            
            function showAlert(message, type) {
                alertMessage.textContent = message;
                loginAlert.className = `alert alert-${type}`;
                loginAlert.classList.remove('d-none');
                setTimeout(function() {
                    loginAlert.classList.add('d-none');
                }, 5000);
            }
        });
    </script>
</body>
</html>
