<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Restaurant Franchise Supply Platform</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Loading Overlay Styles -->
    <link rel="stylesheet" href="{{ asset('css/loading-overlay.css') }}">
    
    <style>
        body {
            background-color: #f8f9fc;
        }
        .login-container {
            margin-top: 10vh;
        }
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .card-header {
            background-color: #4e73df;
            color: white;
            text-align: center;
            padding: 1.5rem;
            border-radius: 1rem 1rem 0 0;
        }
        .btn-login {
            background-color: #4e73df;
            border-color: #4e73df;
            color: white;
            font-weight: bold;
        }
        .btn-login:hover {
            background-color: #2e59d9;
            border-color: #2e59d9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center login-container">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h4 class="m-0 font-weight-bold">Restaurant Franchise Supply Platform</h4>
                    </div>
                    <div class="card-body p-5">
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block">
                                <div class="text-center">
                                <img src="{{ asset('images/myLogo.png') }}" 
                                alt="Login Image" class="img-fluid rounded">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="p-4">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                                    </div>
                                    
                                    @if($errors->any())
                                        <div class="alert alert-danger">
                                            <ul class="mb-0">
                                                @foreach($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    
                                    <form class="user" method="POST" action="{{ url('/login') }}">
                                        @csrf
                                        <div class="form-group mb-3">
                                            <label for="email">Email Address</label>
                                            <input type="email" class="form-control" 
                                                id="email" name="email" 
                                                placeholder="Enter Email Address..." 
                                                value="{{ old('email') }}" required>
                                        </div>
                                        <div class="form-group mb-4">
                                            <label for="password">Password</label>
                                            <input type="password" class="form-control" 
                                                id="password" name="password" 
                                                placeholder="Password" required>
                                        </div>
                                        <button type="submit" class="btn btn-login btn-block w-100 mb-3">
                                            Login
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Loading Overlay Script -->
    <script src="{{ asset('js/loading-overlay.js') }}"></script>
</body>
</html>