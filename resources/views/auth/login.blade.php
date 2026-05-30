<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Gestion Équipements IT</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="{{ asset(path: 'css/app.css') }}" rel="stylesheet">


</head>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <div class="user-icon-circle">
                <i class="fas fa-user"></i>
            </div>

            <div class="auth-title">
                <h1>Connexion</h1>
                <p>Gestion Équipements IT</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>
                        @foreach($errors->all() as $error)
                            {{ $error }}
                        @endforeach
                    </span>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf

                <div class="form-group">
                    <div class="input-wrapper">
                        <div class="input-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <input
                            type="email"
                            name="email"
                            class="form-input"
                            placeholder="Email ID"
                            value="{{ old('email') }}"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <!-- Password Field -->
<div class="form-group">
    <label for="password" class="form-label">Mot de passe</label>
    <div class="input-wrapper">
        <div class="input-icon">
            <i class="fas fa-lock"></i>
        </div>
        <input
            type="password"
            class="form-input @error('password') is-invalid @enderror"
            id="password"
            name="password"
            placeholder="Entrez votre mot de passe"
            required
        >
        <!-- Eye Icon to Toggle Password -->
        <i class="fas fa-eye toggle-password" id="togglePassword" onclick="togglePasswordVisibility()"></i>
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="{{ route('forgot-password') }}" class="forgot-password">Forgot Password?</a>
                </div>

                <button type="submit" class="btn-auth">
                    Login
                </button>
            </form>

          
        </div>
    </div>

    <script>
function togglePasswordVisibility() {
    const passwordField = document.getElementById('password');
    const toggleIcon = document.getElementById('togglePassword');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}
</script>
</body>
</html>
