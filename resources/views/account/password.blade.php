<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>パスワード変更 | ONU</title>
    <link rel="stylesheet" href="{{ asset('css/account.css') }}">
</head>
<body class="account-page">
    <main class="account-main">
        <section class="account-card" aria-labelledby="account-title">
            <header class="account-header">
                <h1 id="account-title" class="account-title">パスワード変更</h1>
            </header>

            @if ($errors->any())
                <div class="account-error" role="alert">
                    <p>入力内容を確認してください。</p>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="account-form" method="POST" action="{{ route('password.change.update') }}">
                @csrf
                @method('PATCH')

                <label class="account-field">
                    <span class="account-label">現在のパスワード</span>
                    <input class="account-input" type="password" name="current_password" required autocomplete="current-password">
                </label>

                <label class="account-field">
                    <span class="account-label">新しいパスワード</span>
                    <input class="account-input" type="password" name="password" required autocomplete="new-password">
                </label>

                <label class="account-field">
                    <span class="account-label">新しいパスワード（確認）</span>
                    <input class="account-input" type="password" name="password_confirmation" required autocomplete="new-password">
                </label>

                <button class="account-submit" type="submit">変更</button>
                <a class="account-back" href="{{ route('menu') }}">メニューに戻る</a>
            </form>
        </section>
    </main>
</body>
</html>
