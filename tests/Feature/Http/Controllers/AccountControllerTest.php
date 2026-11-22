<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 未認証ユーザーはアカウント編集画面と更新処理を利用できないことを検証する。
     */
    public function test_account_routes_require_authentication(): void
    {
        $this->get(route('account.edit'))->assertRedirect(route('login'));
        $this->patch(route('account.update'), [])->assertRedirect(route('login'));
    }

    /**
     * アカウント編集画面に認証ユーザーの現在の情報が表示されることを検証する。
     */
    public function test_edit_displays_authenticated_user_details(): void
    {
        $user = User::factory()->create([
            'name' => '変更前の名前',
            'email' => 'before@example.com',
            'login_id' => 'before-login-id',
        ]);

        $response = $this->actingAs($user)->get(route('account.edit'));

        $response->assertOk()
            ->assertViewIs('account.edit')
            ->assertViewHas('user', $user)
            ->assertSee('value="変更前の名前"', false)
            ->assertSee('value="before@example.com"', false)
            ->assertSee('value="before-login-id"', false);
    }

    /**
     * 有効な入力により認証ユーザーの情報が更新され、完了トースト付きのメニューへ遷移することを検証する。
     */
    public function test_update_changes_authenticated_user_details(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch(route('account.update'), [
            'name' => '変更後の名前',
            'email' => 'after@example.com',
            'login_id' => 'after-login-id',
        ]);

        $response->assertRedirect(route('menu'))
            ->assertSessionHas('status', 'アカウント情報を更新しました。');

        $this->actingAs($user)
            ->withSession(['status' => 'アカウント情報を更新しました。'])
            ->get(route('menu'))
            ->assertOk()
            ->assertSee('class="toast"', false)
            ->assertSeeText('アカウント情報を更新しました。');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => '変更後の名前',
            'email' => 'after@example.com',
            'login_id' => 'after-login-id',
        ]);
        $this->assertAuthenticatedAs($user);
    }

    /**
     * 現在のメールアドレスとログインIDは重複扱いにならず更新できることを検証する。
     */
    public function test_update_accepts_current_email_and_login_id(): void
    {
        $user = User::factory()->create([
            'email' => 'current@example.com',
            'login_id' => 'current-login-id',
        ]);

        $response = $this->actingAs($user)->patch(route('account.update'), [
            'name' => '名前のみ変更',
            'email' => $user->email,
            'login_id' => $user->login_id,
        ]);

        $response->assertRedirect(route('menu'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => '名前のみ変更',
        ]);
    }

    /**
     * 必須項目の欠落と他ユーザーが使用中のメールアドレスおよびログインIDを拒否することを検証する。
     */
    public function test_update_rejects_invalid_or_duplicate_details(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create([
            'email' => 'used@example.com',
            'login_id' => 'used-login-id',
        ]);

        $response = $this->actingAs($user)
            ->from(route('account.edit'))
            ->patch(route('account.update'), [
                'name' => '',
                'email' => $otherUser->email,
                'login_id' => $otherUser->login_id,
            ]);

        $response->assertRedirect(route('account.edit'))
            ->assertSessionHasErrors([
                'name' => '名前は必須です。',
                'email' => 'メールアドレスはすでに使用されています。',
                'login_id' => 'ログインIDはすでに使用されています。',
            ]);
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'email' => $otherUser->email,
        ]);
    }
}
