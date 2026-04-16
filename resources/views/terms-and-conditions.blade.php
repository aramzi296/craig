@extends('layouts.app')

@section('title', 'Syarat & Ketentuan – sebatam.com')

@section('content')
<div class="container mx-auto px-4 py-16 max-w-4xl">
  <div class="bg-white rounded-lg shadow-md p-10">
    <header class="mb-8">
      <h1 class="text-3xl font-bold text-gray-800 mb-1">Syarat & Ketentuan sebatam.com</h1>
      <p class="text-sm text-gray-500">Terakhir Diperbarui: 30 Maret 2026</p>
    </header>

    <section class="prose max-w-none text-gray-700">
      <p>Selamat datang di {{ config('app.name') }}. Sebelum Anda mulai menggunakan layanan kami untuk memposting sesuatu di platform ini, mohon luangkan waktu sejenak untuk membaca Syarat & Ketentuan berikut.</p>

      <h2>1. Penerimaan Ketentuan</h2>
      <p>Dengan mengakses atau menggunakan situs web {{ config('app.name') }}, Anda menyatakan bahwa Anda telah membaca, memahami, dan setuju untuk terikat oleh Syarat & Ketentuan ini. Jika Anda tidak setuju, mohon untuk tidak melanjutkan penggunaan layanan kami.</p>

      <h2>2. Akun Pengguna & Keamanan</h2>
      <ul>
        <li>Anda wajib menggunakan data yang valid saat mendaftar, terutama nomor WhatsApp untuk proses verifikasi.</li>
        <li>Anda bertanggung jawab penuh atas kerahasiaan kata sandi dan semua aktivitas yang terjadi di bawah akun Anda.</li>
        <li>Kami berhak menonaktifkan akun yang terindikasi melakukan penipuan, spamming, atau pelanggaran hukum lainnya.</li>
      </ul>

      <h2>3. Aturan Publikasi Listing Usaha</h2>
      <p>Semua konten yang Anda unggah harus memenuhi kriteria berikut:</p>
      <ul>
        <li><strong>Akurasi Data:</strong> Informasi usaha harus akurat dan tidak menyesatkan.</li>
        <li><strong>Konten Terlarang:</strong> Dilarang memposting konten pornografi, perjudian, narkoba, senjata api, ujaran kebencian, atau barang/layanan yang melanggar hukum Republik Indonesia.</li>
        <li><strong>Hak Atas Foto:</strong> Pastikan Anda memiliki hak atau izin untuk menggunakan foto-foto yang diunggah.</li>
        <li><strong>Duplikasi:</strong> Dilarang membuat postingan ganda (spam) untuk item atau layanan yang sama dalam waktu singkat.</li>
      </ul>

      <h2>4. Moderasi & Verifikasi</h2>
      <ul>
        <li><strong>Verifikasi WhatsApp:</strong> Setiap pembuat listing wajib melakukan verifikasi melalui kode yang dikirim via WhatsApp demi keamanan komunitas.</li>
        <li><strong>Hak Admin:</strong> Tim {{ config('app.name') }} berhak mengedit, menangguhkan, atau menghapus listing tanpa pemberitahuan jika ditemukan adanya ketidaksesuaian dengan standar kami.</li>
      </ul>

      <h2>5. Batasan Tanggung Jawab (Disclaimer)</h2>
      <p>Harap dipahami bahwa:</p>
      <ul>
        <li>Kami <strong>tidak bertanggung jawab</strong> atas transaksi atau interaksi yang terjadi antara pengguna dan penyedia usaha yang berakhir dengan kerugian atau ketidakpuasan pihak manapun.</li>
      </ul>

      <h2>6. Perubahan Layanan</h2>
      <p>Kami berhak mengubah atau menghentikan layanan kapan saja dan dapat memperbarui Syarat & Ketentuan ini sewaktu-waktu. Anda disarankan untuk memeriksa halaman ini secara berkala.</p>

      <h2>7. Hubungi Kami</h2>
      <p>Jika ada pertanyaan atau laporan terkait konten di platform ini, silakan hubungi kami melalui:<br>
        Email: <a href="mailto:admin@sebatam.com" class="text-blue-600 hover:underline">admin@sebatam.com</a><br>
        Lokasi: Batam, Kepulauan Riau.
      </p>

      <div class="mt-10 p-4 rounded-lg bg-gray-50 text-center text-gray-600">
        Terima kasih telah bersama membangun ekosistem digital yang sehat di Kota Batam.
      </div>
    </section>
  </div>
</div>
@endsection
