<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ListingType;

class UpdateListingTypeKeteranganSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'jual-beli' => 'Jika anda mau menjual atau membeli produk/barang. Baik baru atau bekas. Contoh: mobil, motor, HP, laptop, sepeda, furniture, pakaian, dll.',
            'proyek' => 'Jika Anda mau menawarkan proyek atau pekerjaan kepada pihak lain untuk mengerjakan proyek atau pekerjaan tersebut. Contoh: mencari pemborong bangunan, mencari tukang las, mencari tukang ledeng, mencari tukang cat, dll.',
            'jasa' => 'Jika anda mau menawarkan jasa Anda. Contoh: service AC, jasa anggutan puing, tukang, jasa desain grafis, jasa pembuatan website, jasa perbaikan elektronik, jasa kebersihan, dll.',
            'direktori' => 'Jika Anda mau informasi usaha Anda dapat ditemukan oleh warga Batam.',
            'cari-kerja' => 'Jika Anda mau mencari pekerjaan dan menawarkan profil Anda kepada warga Batam.',
            'lowongan' => 'Jika Anda mau memasang informasi lowongan kerja.',
            'agenda' => 'Jika Anda mau memberitahukan agenda kegiatan (gratis atau berbayar). Contoh: kegiatan seminar, pelatihan, workshop, dll.',
            'promo' => 'Jika Anda mau menawarkan promo: diskon, bonus, cashback, doorprice, luckydraw, kupon, dll.',
            'pengumuman' => 'Jika Anda mau memberikan pengumuman publik lainnya kepada warga Batam lainnya. Contoh: berita kehilangan barang/hewan peliharaan, dll.',
            'lainnya' => 'Untuk menampung informasi yang belum masuk dalam kategori di atas.'
        ];

        foreach ($data as $slug => $ket) {
            ListingType::where('slug', $slug)->update(['keterangan' => $ket]);
        }
    }
}
