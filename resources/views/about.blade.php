@extends('layouts.app')

@section('title', 'Tentang Sebatam – ' . config('app.name', 'Sebatam'))

@section('content')
<div class="bg-gradient-to-r from-sky-600 to-blue-800 text-white py-16">
  <div class="container mx-auto text-center px-4">
    <h1 class="text-4xl font-extrabold mb-4">Tentang <span class="text-white">se<span class="text-sky-400">batam</span>.com</span></h1>
    <p class="text-lg opacity-80 max-w-xl mx-auto">Direktori usaha dan iklan baris terlengkap di Kota Batam.</p>
  </div>
</div>

<div class="container mx-auto px-4 py-16">
  <div class="max-w-3xl mx-auto bg-white shadow-md rounded-lg p-10">
    <div class="mb-10 text-center">
      <img src="{{ asset('logo.jpg') }}" alt="Logo Sebatam" class="mx-auto mb-6" style="max-height: 100px;">
      <h2 class="text-2xl font-bold mb-4">Misi Kami</h2>
      <p class="text-gray-600 text-lg">
        Sebatam hadir untuk memberdayakan UMKM dan warga Kota Batam dengan menyediakan platform yang mudah diakses guna mempromosikan bisnis serta menyebarkan informasi melalui iklan baris.
      </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
      <div class="p-6 rounded-lg bg-gray-50 shadow">
        <div class="mb-4 text-sky-600 text-4xl">
          <i class="bi bi-shop"></i>
        </div>
        <h4 class="font-semibold text-xl mb-2">Direktori Usaha</h4>
        <p class="text-gray-600 text-sm">
          Temukan informasi lengkap mengenai profil usaha, layanan jasa, dan profil bisnis profesional di setiap sudut Kota Batam.
        </p>
      </div>
      <div class="p-6 rounded-lg bg-gray-50 shadow">
        <div class="mb-4 text-green-500 text-4xl">
          <i class="bi bi-megaphone"></i>
        </div>
        <h4 class="font-semibold text-xl mb-2">Iklan Baris</h4>
        <p class="text-gray-600 text-sm">
          Platform praktis bagi siapa saja yang ingin memasarkan produk, jasa, otomotif, properti, hingga lowongan kerja secara gratis.
        </p>
      </div>
    </div>

    <div class="mt-12 border-t border-gray-200 pt-10 text-center">
      <h4 class="font-bold text-2xl mb-6">Ikuti Kami</h4>
      <div class="flex justify-center space-x-6 mb-8">
        <a href="https://www.facebook.com/SemuaSebatam" target="_blank" class="text-sky-600 hover:text-sky-800 text-3xl"><i class="bi bi-facebook"></i></a>
        <a href="https://www.instagram.com/semuasebatam/" target="_blank" class="text-pink-600 hover:text-pink-800 text-3xl"><i class="bi bi-instagram"></i></a>
        <a href="https://www.tiktok.com/@sebatam.com" target="_blank" class="text-gray-800 hover:text-gray-900 text-3xl"><i class="bi bi-tiktok"></i></a>
        <a href="https://www.youtube.com/@SemuaSebatam" target="_blank" class="text-red-600 hover:text-red-800 text-3xl"><i class="bi bi-youtube"></i></a>
      </div>

      <h4 class="font-bold text-2xl mb-4">Mari Bergabung!</h4>
      <p class="text-gray-600 mb-6">Daftarkan usaha Anda atau pasang iklan baris hari ini di sebatam.com.</p>

      <div class="flex justify-center gap-4">
        <a href="{{ route('register') }}" class="px-6 py-2 bg-sky-600 hover:bg-sky-700 text-white font-semibold rounded-full">Daftar Sekarang</a>
        <a href="{{ url('/') }}" class="px-6 py-2 border border-sky-600 text-sky-600 font-semibold rounded-full hover:bg-sky-100">Jelajahi Direktori</a>
      </div>
    </div>
  </div>
</div>
@endsection
