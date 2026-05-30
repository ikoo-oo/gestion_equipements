<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - Gestion Équipements IT</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <div class="user-icon-circle">
                <i class="fas fa-envelope"></i>
            </div>

            <div class="auth-title">
                <h1>Mot de passe oublié</h1>
                <p>Réinitialisation de mot de passe</p>
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

            <form method="POST" action="{{ route('forgot-password.post') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label" style="color: rgba(30, 58, 95, 0.9); margin-bottom: 12px; display: block;">
                        <i class="fas fa-info-circle"></i> Entrez votre adresse email pour recevoir un lien de réinitialisation
                    </label>
                    <div class="input-wrapper">
                        <div class="input-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <input
                            type="email"
                            name="email"
                            class="form-input"
                            placeholder="Votre email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <button type="submit" class="btn-auth">
                    Envoyer le lien
                </button>
            </form>

            <div class="toggle-link" style="margin-top: 20px;">
                <a href="{{ route('login') }}">
                    <i class="fas fa-arrow-left"></i> Retour à la connexion
                </a>
            </div>
        </div>
    </div>
</body>
</html>
