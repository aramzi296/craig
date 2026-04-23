<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'max_category' => ['value' => '3', 'description' => 'jumlah kategori untuk sebuah listing'],
            'huruf_fitur' => ['value' => '40', 'description' => 'jumlah huruf untuk sebuah fitur'],
            'huruf_deskripsi_iklan' => ['value' => '500', 'description' => 'jumlah huruf untuk deskripsi iklan'],
            'huruf_deskripsi_iklan_premium' => ['value' => '5000', 'description' => 'jumlah huruf untuk deskripsi iklan premium'],
            'max_foto_iklan' => ['value' => '4', 'description' => 'jumlah foto untuk sebuah listing'],
            'max_foto_iklan_premium' => ['value' => '12', 'description' => 'jumlah foto untuk sebuah listing premium'],
            'max_karakter_komentar' => ['value' => '250', 'description' => 'jumlah karakter untuk sebuah komentar'],
            'expire_iklan' => ['value' => '30', 'description' => 'default expire iklan dalam hari'],
            'expire_iklan_premium' => ['value' => '30', 'description' => 'default expire iklan premium dalam hari'],
            'expire_iklan_kuota_baru' => ['value' => '30', 'description' => 'default expire iklan kuota baru dalam hari'],
            'link_website' => ['value' => '1', 'description' => 'link website untuk listing gratis (1=ya, 0=tidak)'],
            'link_website_premium' => ['value' => '1', 'description' => 'link website untuk listing premium (1=ya, 0=tidak)'],
            'jumlah_iklan_user_default' => ['value' => '1', 'description' => 'jumlah iklan untuk user default'],
            'is_maintenance' => ['value' => '0', 'description' => 'Mode perbaikan (1=aktif, 0=tidak)'],
            'maintenance_message' => ['value' => 'Mohon maaf, sistem sedang dalam perbaikan rutin untuk meningkatkan layanan kami. Silakan kembali lagi nanti.', 'description' => 'Pesan yang ditampilkan saat mode perbaikan aktif'],
        ];

        foreach ($settings as $key => $data) {
            \App\Models\Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $data['value'], 'description' => $data['description']]
            );
        }
    }
}
