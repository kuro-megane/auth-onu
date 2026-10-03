<form method="POST" action="{{ route('login') }}">
    @csrf
    <label>ログインID</label>
    <input type="text" name="login_id" value="{{ old('login_id') }}" required autofocus>
    <label>パスワード</label>
    <input type="password" name="password" required>
    <button type="submit">ログイン</button>
</form>
