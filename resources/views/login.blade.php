<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ONU Family ログイン">
    <title>ONU</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body class="login-page">
    <main class="login-main">
        <section class="login-card" aria-labelledby="login-title">
            <header class="login-header">
                <div class="login-logo" aria-hidden="true">O</div>
                <h1 id="login-title" class="login-title">ONU</h1>
                <p class="login-description">アカウントにログイン</p>
            </header>

            <form class="login-form" method="POST" action="{{ route('login.attempt') }}">
                @csrf

                @error('login_id')
                    <p class="login-error" role="alert">{{ $message }}</p>
                @enderror

                <div class="login-field">
                    <label class="login-label" for="login_id">ログインID</label>
                    <input id="login_id" class="login-input" type="text" name="login_id"
                        value="{{ old('login_id') }}" autocomplete="username" placeholder="ログインIDを入力"
                        required autofocus>
                </div>

                <div class="login-field">
                    <label class="login-label" for="password">パスワード</label>
                    <input id="password" class="login-input" type="password" name="password"
                        autocomplete="current-password" placeholder="パスワードを入力" required>
                </div>

                <label class="login-remember" for="remember">
                    <input id="remember" class="login-checkbox" type="checkbox" name="remember">
                    <span>次回から自動でログイン</span>
                </label>

                <button class="login-button" type="submit">ログイン</button>
                <a class="login-forgot-button" href="">パスワードを忘れた方</a>
            </form>
        </section>
    </main>
</body>
</html>
