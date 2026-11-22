<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordChangeControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 未認証ユーザーはパスワード変更画面と更新処理を利用できないことを検証する。
     */
    public function test_password_change_routes_require_authentication(): void
    {
        $this->get(route('password.change.edit'))->assertRedirect(route('login'));
        $this->patch(route('password.change.update'), [])->assertRedirect(route('login'));
    }

    /**
     * パスワード変更画面に現在、新規、確認用のパスワード入力欄が表示されることを検証する。
     */
    public function test_edit_displays_password_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('password.change.edit'));

        $response->assertOk()
            ->assertViewIs('account.password')
            ->assertSee('name="current_password"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="password_confirmation"', false);
    }

    /**
     * 正しい現在のパスワードで変更すると新しいパスワードが保存され、ログアウトすることを検証する。
     */
    public function test_update_changes_password_and_logs_user_out(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('CurrentPassword1!'),
        ]);

        $response = $this->actingAs($user)->patch(route('password.change.update'), [
            'current_password' => 'CurrentPassword1!',
            'password' => 'NewPassword2#',
            'password_confirmation' => 'NewPassword2#',
        ]);

        $response->assertRedirect(route('login'))
            ->assertSessionHas('status', 'パスワードを変更しました。新しいパスワードでログインしてください。');
        $this->assertTrue(Hash::check('NewPassword2#', $user->fresh()->password));
        $this->assertGuest();
    }

    /**
     * 現在のパスワードが未入力または誤っている場合は変更を拒否することを検証する。
     */
    public function test_update_rejects_missing_or_incorrect_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('CurrentPassword1!'),
        ]);

        $this->actingAs($user)
            ->from(route('password.change.edit'))
            ->patch(route('password.change.update'), [
                'password' => 'NewPassword2#',
                'password_confirmation' => 'NewPassword2#',
            ])
            ->assertRedirect(route('password.change.edit'))
            ->assertSessionHasErrors([
                'current_password' => '現在のパスワードを入力してください。',
            ]);

        $this->actingAs($user)
            ->from(route('password.change.edit'))
            ->patch(route('password.change.update'), [
                'current_password' => 'WrongPassword1!',
                'password' => 'NewPassword2#',
                'password_confirmation' => 'NewPassword2#',
            ])
            ->assertRedirect(route('password.change.edit'))
            ->assertSessionHasErrors([
                'current_password' => '現在のパスワードが正しくありません。',
            ]);

        $this->assertTrue(Hash::check('CurrentPassword1!', $user->fresh()->password));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * 新しいパスワードが確認欄と不一致または強度不足の場合は変更を拒否することを検証する。
     */
    public function test_update_rejects_unconfirmed_or_weak_new_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('CurrentPassword1!'),
        ]);

        $this->actingAs($user)
            ->from(route('password.change.edit'))
            ->patch(route('password.change.update'), [
                'current_password' => 'CurrentPassword1!',
                'password' => 'NewPassword2#',
                'password_confirmation' => 'DifferentPassword3$',
            ])
            ->assertRedirect(route('password.change.edit'))
            ->assertSessionHasErrors([
                'password' => '新しいパスワードと新しいパスワード（確認）が一致しません。',
            ]);

        $this->actingAs($user)
            ->from(route('password.change.edit'))
            ->patch(route('password.change.update'), [
                'current_password' => 'CurrentPassword1!',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertRedirect(route('password.change.edit'))
            ->assertSessionHasErrors([
                'password' => '新しいパスワードには大文字と小文字を含めてください。',
            ]);

        $this->assertTrue(Hash::check('CurrentPassword1!', $user->fresh()->password));
    }

    /**
     * 新しいパスワードの必須、文字数、数字、記号の各エラーが日本語で表示されることを検証する。
     */
    public function test_update_returns_japanese_password_requirement_messages(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('CurrentPassword1!'),
        ]);

        $invalidPasswords = [
            '' => '新しいパスワードを入力してください。',
            'Aa1!' => '新しいパスワードは8文字以上で入力してください。',
            str_repeat('Aa1!', 9) => '新しいパスワードは32文字以下で入力してください。',
            'Password!' => '新しいパスワードには数字を含めてください。',
            'Password1' => '新しいパスワードには記号を含めてください。',
        ];

        foreach ($invalidPasswords as $password => $message) {
            $this->actingAs($user)
                ->from(route('password.change.edit'))
                ->patch(route('password.change.update'), [
                    'current_password' => 'CurrentPassword1!',
                    'password' => $password,
                    'password_confirmation' => $password,
                ])
                ->assertRedirect(route('password.change.edit'))
                ->assertSessionHasErrors(['password' => $message]);
        }

        $this->assertTrue(Hash::check('CurrentPassword1!', $user->fresh()->password));
    }
}
