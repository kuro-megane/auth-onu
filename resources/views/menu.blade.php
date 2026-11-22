<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メニュー | ONU</title>
    <link rel="stylesheet" href="{{ asset('css/menu.css') }}">
</head>
<body class="menu-page">
    @if (session('status'))
        <div class="toast" role="status" aria-live="polite">{{ session('status') }}</div>
    @endif

    <main class="menu-main">
        <section class="menu-card" aria-labelledby="menu-title">
            <h1 id="menu-title" class="menu-title">メニュー</h1>

            @if ($familyApps !== [])
                <section class="app-list" aria-labelledby="app-list-title">
                    <h2 id="app-list-title" class="app-list-title">アプリ一覧</h2>
                    <div class="app-links">
                        @foreach ($familyApps as $app)
                            <a class="app-link app-link--{{ $app['theme'] }}" href="{{ $app['url'] }}">
                                <img class="app-link-icon" src="{{ asset($app['icon']) }}" alt="">
                                <span>{{ $app['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="account-list" aria-labelledby="account-list-title">
                <h2 id="account-list-title" class="account-list-title">アカウント</h2>
                <div class="account-links">
                    <a class="account-link" href="{{ route('account.edit') }}">アカウント編集</a>
                    <a class="account-link" href="{{ route('password.change.edit') }}">パスワード変更</a>
                </div>
            </section>

            <form class="logout-form" method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="logout-button" type="submit">ログアウト</button>
            </form>
        </section>
    </main>
</body>
</html>
