<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ULC System</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f6f9;
        }

        .login-page {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 15px;
        }

        .login-box {
            width: 100%;
            max-width: 400px;
        }

        .card {
            border-top: 5px solid #FF5F00;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        .login-logo a {
            color: #FF5F00 !important;
            font-weight: 700;
            font-size: 26px;
        }

        .btn-primary {
            background: #FF5F00 !important;
            border-color: #FF5F00 !important;
        }

        .btn-primary:hover {
            background: #e65600 !important;
        }

        .input-group-text {
            color: #FF5F00;
        }

        .forgot-link {
            color: #666;
            font-size: 14px;
        }

        .forgot-link:hover {
            color: #FF5F00;
        }

        @media (max-width:576px) {
            .login-logo a {
                font-size: 22px;
            }

            .login-box-msg {
                font-size: 14px;
            }
        }
    </style>
</head>

<body class="hold-transition login-page">

    <div class="login-box">

        <div class="card">

            <div class="card-body login-card-body">

                <div class="login-logo">
                    <a href="#"><i class="fas fa-database mr-2"></i><b>ULC</b> System</a>
                </div>

                <p class="login-box-msg">Sign in to start your session</p>

                <form action="{{ route('auth.login.request') }}" method="POST">
                    @csrf
                    <div class="input-group mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>

                    <div class="input-group mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        Sign In
                    </button>

                    <!--<div class="mt-2 text-right">-->
                    <!--    <a href="#" class="forgot-link">I forgot my password</a>-->
                    <!--</div>-->

                </form>

            </div>

        </div>

    </div>

    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
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
