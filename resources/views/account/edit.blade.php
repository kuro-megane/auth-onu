<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>アカウント編集 | ONU</title>
    <link rel="stylesheet" href="{{ asset('css/account.css') }}">
</head>
<body class="account-page">
    <main class="account-main">
        <section class="account-card" aria-labelledby="account-title">
            <header class="account-header">
                <h1 id="account-title" class="account-title">アカウント編集</h1>
            </header>

            @if (session('status'))
                <p class="account-status" role="status">{{ session('status') }}</p>
            @endif

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

            <form class="account-form" method="POST" action="{{ route('account.update') }}">
                @csrf
                @method('PATCH')

                <label class="account-field">
                    <span class="account-label">名前</span>
                    <input class="account-input" type="text" name="name" value="{{ old('name', $user->name) }}" required maxlength="255" autocomplete="name">
                </label>

                <label class="account-field">
                    <span class="account-label">メールアドレス</span>
                    <input class="account-input" type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255" autocomplete="email">
                </label>

                <label class="account-field">
                    <span class="account-label">ログインID</span>
                    <input class="account-input" type="text" name="login_id" value="{{ old('login_id', $user->login_id) }}" required maxlength="255" autocomplete="username">
                </label>

                <button class="account-submit" type="submit">更新</button>
                <a class="account-back" href="{{ route('menu') }}">メニューに戻る</a>
            </form>
        </section>
    </main>
</body>
</html>
