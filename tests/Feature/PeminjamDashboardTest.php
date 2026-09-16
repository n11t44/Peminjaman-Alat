<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeminjamDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_peminjam_dashboard_page_loads(): void
    {
        $user = User::factory()->create([
            'role' => 'peminjam',
        ]);

        $response = $this->actingAs($user)
            ->get(route('peminjam.dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard Peminjam');
        $response->assertViewHas(['totalPeminjaman', 'menunggu', 'sedangDipinjam', 'selesai']);
    }
}
