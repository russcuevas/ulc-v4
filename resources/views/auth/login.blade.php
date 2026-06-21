<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ULC System - Sign In</title>

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #0b0b0f;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Ambient background glow effects */
        .ambient-glow-1 {
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 95, 0, 0.15) 0%, rgba(255, 95, 0, 0) 70%);
            top: -10%;
            left: -10%;
            filter: blur(80px);
            animation: pulse 12s infinite alternate;
            pointer-events: none;
        }

        .ambient-glow-2 {
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(0, 123, 255, 0.1) 0%, rgba(0, 123, 255, 0) 70%);
            bottom: -20%;
            right: -10%;
            filter: blur(100px);
            animation: pulse 15s infinite alternate-reverse;
            pointer-events: none;
        }

        @keyframes pulse {
            0% {
                transform: scale(1) translate(0, 0);
            }

            100% {
                transform: scale(1.1) translate(30px, 50px);
            }
        }

        .login-container {
            width: 100%;
            max-width: 440px;
            padding: 20px;
            z-index: 10;
        }

        .login-card {
            background: rgba(23, 23, 33, 0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .login-card:hover {
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5), 0 0 40px rgba(255, 95, 0, 0.05);
        }

        .logo-section {
            text-align: center;
            margin-bottom: 35px;
        }

        .logo-link {
            text-decoration: none;
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            font-size: 28px;
            color: #FF5F00;
            background: rgba(255, 95, 0, 0.1);
            width: 54px;
            height: 54px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            border: 1px solid rgba(255, 95, 0, 0.25);
            box-shadow: 0 0 25px rgba(255, 95, 0, 0.15);
            animation: float 4s infinite ease-in-out;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-6px);
            }
        }

        .logo-text {
            font-family: 'Outfit', sans-serif;
            font-size: 26px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.5px;
        }

        .logo-text span {
            color: #FF5F00;
        }

        .welcome-text {
            color: #9ca3af;
            font-size: 14px;
            margin-top: 6px;
            font-weight: 400;
        }

        .input-group {
            position: relative;
            margin-bottom: 24px;
        }

        .input-group label {
            display: block;
            color: #9ca3af;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            color: #6b7280;
            font-size: 16px;
            transition: color 0.3s ease;
            pointer-events: none;
        }

        .input-control {
            width: 100%;
            padding: 14px 48px 14px 48px;
            background: rgba(255, 255, 255, 0.02);
            border: 1.5px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            color: #ffffff;
            font-family: inherit;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .input-control::placeholder {
            color: #4b5563;
        }

        .input-control:focus {
            outline: none;
            border-color: #FF5F00;
            background: rgba(255, 95, 0, 0.02);
            box-shadow: 0 0 0 4px rgba(255, 95, 0, 0.12);
        }

        .input-control:focus+.input-icon,
        .input-wrapper:focus-within .input-icon {
            color: #FF5F00;
        }

        .toggle-password {
            position: absolute;
            right: 16px;
            color: #6b7280;
            cursor: pointer;
            font-size: 16px;
            transition: color 0.3s ease;
            background: none;
            border: none;
            outline: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-password:hover {
            color: #ffffff;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: #FF5F00;
            border: none;
            border-radius: 12px;
            color: #ffffff;
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 95, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 30px;
        }

        .submit-btn:hover {
            background: #e65600;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 95, 0, 0.45);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 35px 24px;
                border-radius: 20px;
            }
        }
    </style>
</head>

<body>

    <!-- Decorative backgrounds -->
    <div class="ambient-glow-1"></div>
    <div class="ambient-glow-2"></div>

    <div class="login-container">

        <div class="login-card">

            <div class="logo-section">
                <a href="#" class="logo-link">
                    <div class="logo-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <div>
                        <h1 class="logo-text">ULC <span>System</span></h1>
                        <p class="welcome-text">Sign in to start your session</p>
                    </div>
                </a>
            </div>

            <form action="{{ route('auth.login.request') }}" method="POST">
                @csrf

                <div class="input-group">
                    <label>Email Address</label>
                    <div class="input-wrapper">
                        <input type="email" name="email" class="input-control" placeholder="name@example.com"
                            required autocomplete="username">
                        <span class="input-icon">
                            <i class="fas fa-envelope"></i>
                        </span>
                    </div>
                </div>

                <div class="input-group" style="margin-bottom: 10px;">
                    <label>Password</label>
                    <div class="input-wrapper">
                        <input type="password" name="password" class="input-control" placeholder="••••••••" required
                            autocomplete="current-password">
                        <span class="input-icon">
                            <i class="fas fa-lock"></i>
                        </span>
                        <button type="button" class="toggle-password" tabindex="-1">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <span>Sign In</span>
                    <i class="fas fa-arrow-right"></i>
                </button>

            </form>

        </div>

    </div>

    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Password toggle logic
            const toggleBtn = document.querySelector('.toggle-password');
            const passwordInput = document.querySelector('input[name="password"]');
            if (toggleBtn && passwordInput) {
                toggleBtn.addEventListener('click', () => {
                    const isPassword = passwordInput.getAttribute('type') === 'password';
                    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                    toggleBtn.querySelector('i').className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
                });
            }

            // Notification logic
            const notyf = new Notyf({
                duration: 5000,
                position: {
                    x: 'right',
                    y: 'top'
                }
            });

            @if (session('success'))
                notyf.success("{{ session('success') }}");
            @endif

            @if (session('error'))
                notyf.error("{{ session('error') }}");
            @endif

            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    notyf.error("{{ $error }}");
                @endforeach
            @endif
        });
    </script>

</body>

</html>
