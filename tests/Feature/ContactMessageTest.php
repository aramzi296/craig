<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\ContactMessage;

class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create standard user
        $this->user = User::create([
            'name' => 'John Doe',
            'whatsapp' => '6281234567890',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]);

        // Create admin user
        $this->admin = User::create([
            'name' => 'Admin User',
            'whatsapp' => '6289999999999',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);
    }

    /** @test */
    public function visitors_can_submit_contact_form_successfully()
    {
        $response = $this->post(route('contact.send'), [
            'name' => 'Ady Batam',
            'whatsapp' => '0812-3456-7890',
            'message' => 'Halo Admin, saya ingin menanyakan tentang slot premium.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Ady Batam',
            'whatsapp' => '6281234567890', // normalized whatsapp
            'message' => 'Halo Admin, saya ingin menanyakan tentang slot premium.',
            'status' => 'unread',
        ]);
    }

    /** @test */
    public function contact_form_requires_validation()
    {
        $response = $this->post(route('contact.send'), [
            'name' => '',
            'whatsapp' => '',
            'message' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'whatsapp', 'message']);
        $this->assertDatabaseEmpty('contact_messages');
    }

    /** @test */
    public function non_admins_cannot_access_contact_management()
    {
        // Guest cannot access
        $response = $this->get(route('admin.contacts'));
        $response->assertRedirect('/login');

        // Normal user is redirected back with error
        $response = $this->actingAs($this->user)->get(route('admin.contacts'));
        $response->assertRedirect('/');
        $response->assertSessionHas('error', 'Anda tidak memiliki akses ke halaman ini.');
    }

    /** @test */
    public function admin_can_manage_contact_messages()
    {
        // Create a contact message
        $message = ContactMessage::create([
            'name' => 'Tomi',
            'whatsapp' => '6281234567890',
            'message' => 'Pertanyaan umum.',
            'status' => 'unread',
        ]);

        // Admin views inbox
        $response = $this->actingAs($this->admin)->get(route('admin.contacts'));
        $response->assertOk();
        $response->assertSee('Pertanyaan umum.');

        // Admin marks as read
        $response = $this->actingAs($this->admin)->post(route('admin.contacts.read', $message->id));
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals('read', $message->fresh()->status);

        // Admin deletes the message
        $response = $this->actingAs($this->admin)->delete(route('admin.contacts.destroy', $message->id));
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('contact_messages', ['id' => $message->id]);
    }
}
