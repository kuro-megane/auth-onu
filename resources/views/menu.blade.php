<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メニュー | ONU</title>
    <link rel="stylesheet" href="{{ asset('css/menu.css') }}">
</head>
<body class="menu-page">
    <main class="menu-main">
        <section class="menu-card" aria-labelledby="menu-title">
            <h1 id="menu-title" class="menu-title">メニュー</h1>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="logout-button" type="submit">ログアウト</button>
            </form>
        </section>
    </main>
</body>
</html>
