@extends('layouts.app')

@section('title', 'Kebijakan Privasi – sebatam.com')

@section('content')
<div class="container mx-auto px-4 py-16 max-w-4xl">
  <div class="bg-white rounded-lg shadow-md p-10">
    <header class="mb-8">
      <h1 class="text-2xl font-bold text-gray-800 mb-1">Kebijakan Privasi sebatam.com</h1>
      <p class="text-sm text-gray-500">Terakhir Diperbarui: 18 Februari 2026</p>
    </header>

    <section class="prose max-w-none text-gray-700">
      <p>Di {{ config('app.name') }}, kami sangat menghargai privasi Anda. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi pribadi Anda saat Anda menggunakan layanan direktori kami.</p>

      <h3>1. Informasi yang Kami Kumpulkan</h3>
      <p>Kami mengumpulkan informasi yang Anda berikan secara sukarela saat mendaftarkan usaha atau profil profesional, termasuk namun tidak terbatas pada:</p>
      <ul>
        <li><strong>Informasi Identitas:</strong> Nama lengkap, nama usaha, dan deskripsi profesional.</li>
        <li><strong>Informasi Kontak:</strong> Alamat email, nomor telepon/WhatsApp, dan alamat fisik usaha.</li>
        <li><strong>Konten Visual:</strong> Foto profil, logo usaha, dan foto galeri produk/layanan.</li>
        <li><strong>Data Teknis:</strong> Alamat IP, jenis perangkat, dan aktivitas penggunaan situs melalui log server (untuk keamanan dan optimasi sistem).</li>
      </ul>

      <h3>2. Penggunaan Informasi</h3>
      <p>Kami menggunakan informasi Anda untuk:</p>
      <ul>
        <li>Menampilkan profil usaha/profesional Anda di direktori publik agar dapat ditemukan oleh calon pelanggan.</li>
        <li>Menampilkan iklan baris yang Anda posting di direktori publik agar dapat ditemukan oleh calon pelanggan atau dibaca oleh khalayak umum.</li>
        <li>Menghubungi Anda terkait pembaruan layanan, verifikasi akun, atau informasi penting lainnya.</li>
        <li>Meningkatkan performa dan keamanan situs web kami.</li>
        <li>Menganalisis tren penggunaan untuk pengembangan fitur baru.</li>
      </ul>

      <h3>3. Keamanan Data</h3>
      <p>Kami mengelola data Anda pada infrastruktur server pribadi (VPS) dengan standar keamanan yang kami upayakan sebaik mungkin (seperti penggunaan SSL/HTTPS). Namun, perlu diingat bahwa tidak ada metode transmisi data melalui internet yang 100% aman. Kami menyarankan Anda untuk menjaga kerahasiaan kredensial akun Anda.</p>

      <h3>4. Berbagi Informasi dengan Pihak Ketiga</h3>
      <p><strong>Tampilan Publik:</strong> Informasi usaha dan iklan baris yang Anda masukkan dalam listing akan tersedia secara publik di situs web kami.</p>
      <p><strong>Pihak Ketiga:</strong> Kami tidak menjual atau menyewakan data pribadi Anda kepada pihak luar. Kami hanya akan membagikan data jika diwajibkan oleh hukum atau untuk keperluan teknis penyedia layanan (seperti penyedia hosting/VPS) demi kelancaran operasional situs.</p>

      <h3>5. Hak Anda atas Data</h3>
      <p>Anda memiliki hak untuk:</p>
      <ul>
        <li>Mengakses dan memperbarui informasi profil Anda kapan saja melalui dashboard pengguna.</li>
        <li>Meminta penghapusan akun dan data terkait dari sistem kami dengan menghubungi tim admin kami.</li>
      </ul>

      <h3>6. Cookie</h3>
      <p>Kami menggunakan cookie untuk meningkatkan pengalaman pengguna, seperti mengingat preferensi bahasa atau status login Anda. Anda dapat mengatur browser Anda untuk menolak cookie, namun hal ini mungkin mempengaruhi fungsi beberapa fitur di situs kami.</p>

      <h3>7. Perubahan Kebijakan</h3>
      <p>Kami dapat memperbarui Kebijakan Privasi ini dari waktu ke waktu. Setiap perubahan akan diberitahukan melalui halaman ini dengan memperbarui tanggal "Terakhir Diperbarui".</p>

      <h3>8. Hubungi Kami</h3>
      <p>Jika ada pertanyaan mengenai kebijakan privasi ini, Anda dapat menghubungi kami di:</p>
      <ul class="list-unstyled">
        <li><i class="bi bi-envelope me-2 text-primary"></i>Email: <a href="mailto:admin@sebatam.com" class="text-blue-600 hover:underline">admin@sebatam.com</a></li>
        <li><i class="bi bi-geo-alt me-2 text-primary"></i>Lokasi: Batam, Kepulauan Riau.</li>
      </ul>

      <div class="mt-4 text-gray-600 text-sm">
        Dokumen ini masih dapat diperbarui.
      </div>
    </section>
  </div>
</div>
@endsection
