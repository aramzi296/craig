<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Listing;
use App\Models\ListingReport;

class ListingReportTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $admin;
    private $listing;

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

        // Create listing owner
        $owner = User::create([
            'name' => 'Owner',
            'whatsapp' => '6281111111111',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create listing
        $this->listing = Listing::create([
            'user_id' => $owner->id,
            'title' => 'Toko Sepatu Bagus',
            'slug' => 'toko-sepatu-bagus',
            'description' => 'Kami menjual sepatu impor berkualitas.',
            'price' => 150000,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function guests_can_report_listings_with_whatsapp_number()
    {
        $response = $this->post(route('listings.report', $this->listing->id), [
            'reason' => 'Penipuan',
            'description' => 'Iklan ini palsu dan mencoba menipu orang.',
            'reporter_whatsapp' => '0812-3456-7890',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('listing_reports', [
            'listing_id' => $this->listing->id,
            'user_id' => null,
            'reason' => 'Penipuan',
            'description' => 'Iklan ini palsu dan mencoba menipu orang.',
            'reporter_whatsapp' => '6281234567890', // should be normalized
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function guest_reporting_requires_whatsapp_number()
    {
        $response = $this->post(route('listings.report', $this->listing->id), [
            'reason' => 'Penipuan',
            'description' => 'Iklan ini palsu dan mencoba menipu orang.',
        ]);

        $response->assertSessionHasErrors(['reporter_whatsapp']);
        $this->assertDatabaseEmpty('listing_reports');
    }

    /** @test */
    public function authenticated_users_can_report_listings_without_providing_whatsapp()
    {
        $response = $this->actingAs($this->user)->post(route('listings.report', $this->listing->id), [
            'reason' => 'Spam / Duplikat',
            'description' => 'Ini spam berulang.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('listing_reports', [
            'listing_id' => $this->listing->id,
            'user_id' => $this->user->id,
            'reason' => 'Spam / Duplikat',
            'description' => 'Ini spam berulang.',
            'reporter_whatsapp' => '6281234567890', // user's whatsapp
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function non_admins_cannot_access_reports_management()
    {
        // Guests cannot access
        $response = $this->get(route('admin.reports'));
        $response->assertRedirect('/login');

        // Non-admin users cannot access
        $response = $this->actingAs($this->user)->get(route('admin.reports'));
        $response->assertRedirect('/');
        $response->assertSessionHas('error', 'Anda tidak memiliki akses ke halaman ini.');
    }

    /** @test */
    public function admin_can_view_reports_and_resolve_or_dismiss_them()
    {
        // Create a report
        $report = ListingReport::create([
            'listing_id' => $this->listing->id,
            'reason' => 'Konten Tidak Layak',
            'description' => 'Gambar tidak pantas.',
            'reporter_whatsapp' => '6281234567890',
            'status' => 'pending',
        ]);

        // Admin views the reports list
        $response = $this->actingAs($this->admin)->get(route('admin.reports'));
        $response->assertOk();
        $response->assertSee('Gambar tidak pantas.');

        // Admin resolves the report
        $response = $this->actingAs($this->admin)->post(route('admin.reports.resolve', $report->id));
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals('resolved', $report->fresh()->status);

        // Admin dismisses the report
        $report->update(['status' => 'pending']);
        $response = $this->actingAs($this->admin)->post(route('admin.reports.dismiss', $report->id));
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals('dismissed', $report->fresh()->status);
    }
}
