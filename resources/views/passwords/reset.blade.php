<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ONU Family 新しいパスワードの設定">
    <title>新しいパスワードの設定 | ONU</title>
    <link rel="stylesheet" href="{{ asset('css/password-reset.css') }}">
</head>
<body class="password-page">
    <main class="password-main">
        <section class="password-card" aria-labelledby="password-title">
            <h1 id="password-title" class="password-title">新しいパスワードの設定</h1>
            <p class="password-description">8～32文字で、大文字・小文字・数字・記号をそれぞれ含めてください。</p>

            <form class="password-form" method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="password-field">
                    <label class="password-label" for="email">メールアドレス</label>
                    <input id="email" class="password-input" type="email" name="email"
                        value="{{ old('email', $email) }}" autocomplete="email" maxlength="255" required>
                    @error('email')
                        <p class="password-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="password-field">
                    <label class="password-label" for="password">新しいパスワード</label>
                    <input id="password" class="password-input" type="password" name="password"
                        autocomplete="new-password" maxlength="32" required autofocus>
                    @error('password')
                        <p class="password-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="password-field">
                    <label class="password-label" for="password_confirmation">新しいパスワード（確認）</label>
                    <input id="password_confirmation" class="password-input" type="password"
                        name="password_confirmation" autocomplete="new-password" maxlength="32" required>
                </div>

                <button class="password-button" type="submit">パスワードを変更</button>
                <a class="password-link" href="{{ route('password.request') }}">リセットメールを再送する</a>
            </form>
        </section>
    </main>
</body>
</html>
