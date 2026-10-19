<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PLTS Monitoring</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            padding: 2rem;
            text-align: center;
            color: white;
        }
        .form-control {
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            border: 1px solid #cbd5e1;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(37, 99, 235, 0.25);
            border-color: #2563eb;
        }
        .btn-primary {
            background-color: #2563eb;
            border: none;
            padding: 0.75rem;
            border-radius: 0.5rem;
            font-weight: 600;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <i class="ph-fill ph-solar-panel fs-1 mb-2"></i>
            <h3 class="fw-bold m-0">PLTS Monitoring</h3>
            <p class="text-white-50 m-0 mt-1 small">Sign in to your dashboard</p>
        </div>
        <div class="p-4 p-md-5">
            @if($errors->any())
                <div class="alert alert-danger rounded-3 small">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label text-secondary fw-semibold small">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="ph ph-user"></i></span>
                        <input type="text" name="name" class="form-control border-start-0 ps-0" placeholder="admin123" required autofocus>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label text-secondary fw-semibold small">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="ph ph-lock-key"></i></span>
                        <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 d-flex justify-content-center align-items-center gap-2 shadow-sm">
                    Sign In <i class="ph ph-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>

</body>
</html>
