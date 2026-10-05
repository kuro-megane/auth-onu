<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ONU Family OTP認証">
    <title>OTP認証 | ONU</title>
    <link rel="stylesheet" href="{{ asset('css/otp.css') }}">
</head>
<body class="otp-page">
    <main class="otp-main">
        <section class="otp-card" aria-labelledby="otp-title">
            <h1 id="otp-title" class="otp-title">OTP認証</h1>
            <p class="otp-description">メールでお送りしたワンタイムパスワードを入力してください。</p>

            <form class="otp-form" method="POST" action="{{ route('otp.verify') }}">
                @csrf

                @error('otp')
                    <p class="otp-error" role="alert">{{ $message }}</p>
                @enderror

                <label class="otp-label" for="otp">ワンタイムパスワード</label>
                <input id="otp" class="otp-input" type="text" name="otp" maxlength="8"
                    autocomplete="one-time-code" required autofocus>
                <button class="otp-button" type="submit">認証</button>
            </form>
        </section>
    </main>
</body>
</html>
