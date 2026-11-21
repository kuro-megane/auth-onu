<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ONU Family パスワード再設定">
    <title>パスワード再設定 | ONU</title>
    <link rel="stylesheet" href="{{ asset('css/password-reset.css') }}">
</head>
<body class="password-page">
    <main class="password-main">
        <section class="password-card" aria-labelledby="password-title">
            <h1 id="password-title" class="password-title">パスワード再設定</h1>
            <p class="password-description">登録済みのメールアドレスを入力してください。</p>

            @if (session('status'))
                <p class="password-status" role="status">{{ session('status') }}</p>
            @endif

            <form class="password-form" method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="password-field">
                    <label class="password-label" for="email">メールアドレス</label>
                    <input id="email" class="password-input" type="email" name="email" value="{{ old('email') }}"
                        autocomplete="email" maxlength="255" required autofocus>
                    @error('email')
                        <p class="password-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>
                <button class="password-button" type="submit">リセット用メールを送信</button>
                <a class="password-link" href="{{ route('login') }}">ログイン画面へ戻る</a>
            </form>
        </section>
    </main>
</body>
</html>
