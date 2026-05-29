<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user1;
    private $user2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->admin = User::create([
            'name' => 'Admin User',
            'whatsapp' => '6289999999999',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        // Create test users
        $this->user1 = User::create([
            'name' => 'User One',
            'whatsapp' => '6281111111111',
            'email' => 'user1@example.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]);

        $this->user2 = User::create([
            'name' => 'User Two',
            'whatsapp' => '6282222222222',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]);
    }

    /** @test */
    public function admin_can_view_edit_user_page()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.edit', $this->user1->id));
        $response->assertOk();
        $response->assertSee('Edit Profil Pengguna');
        $response->assertSee('User One');
        $response->assertSee('user1@example.com');
        $response->assertSee('6281111111111');
    }

    /** @test */
    public function admin_can_update_user_name_email_and_whatsapp_successfully()
    {
        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $this->user1->id), [
            'name' => 'Updated User One',
            'email' => 'user1_new@example.com',
            'whatsapp' => '081234567890', // input with leading 0 to test normalization
        ]);

        $response->assertRedirect(route('admin.users'));
        $response->assertSessionHas('success');

        $this->user1 = $this->user1->fresh();
        $this->assertEquals('Updated User One', $this->user1->name);
        $this->assertEquals('user1_new@example.com', $this->user1->email);
        $this->assertEquals('6281234567890', $this->user1->whatsapp); // Normalized
    }

    /** @test */
    public function update_fails_if_email_already_registered_to_another_user()
    {
        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $this->user1->id), [
            'name' => 'User One',
            'email' => 'user2@example.com', // user2 email
            'whatsapp' => '6281111111111',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertEquals('user1@example.com', $this->user1->fresh()->email);
    }

    /** @test */
    public function update_fails_if_whatsapp_already_registered_to_another_user()
    {
        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $this->user1->id), [
            'name' => 'User One',
            'email' => 'user1@example.com',
            'whatsapp' => '0822-2222-2222', // normalizes to user2 whatsapp
        ]);

        $response->assertSessionHasErrors(['whatsapp']);
        $this->assertEquals('6281111111111', $this->user1->fresh()->whatsapp);
    }

    /** @test */
    public function update_fails_if_whatsapp_is_invalid()
    {
        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $this->user1->id), [
            'name' => 'User One',
            'email' => 'user1@example.com',
            'whatsapp' => 'invalid-number',
        ]);

        $response->assertSessionHasErrors(['whatsapp']);
        $this->assertEquals('6281111111111', $this->user1->fresh()->whatsapp);
    }

    /** @test */
    public function non_admins_cannot_access_or_update_user_profiles()
    {
        // Non-admin tries to access edit page
        $response = $this->actingAs($this->user2)->get(route('admin.users.edit', $this->user1->id));
        $response->assertRedirect('/');

        // Non-admin tries to update
        $response = $this->actingAs($this->user2)->put(route('admin.users.update', $this->user1->id), [
            'name' => 'Hacked Name',
            'email' => 'hacked@example.com',
            'whatsapp' => '6289999999999',
        ]);
        $response->assertRedirect('/');
        $this->assertNotEquals('Hacked Name', $this->user1->fresh()->name);
    }
}
