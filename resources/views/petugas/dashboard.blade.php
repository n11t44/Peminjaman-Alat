@extends('layouts.app')

@section('title', 'Dashboard Petugas - Sistem Peminjaman')
@section('header-title', 'Dashboard Petugas')

@section('content')
    <div class="mb-6 bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-5 rounded-xl shadow-md">
        <p class="text-sm uppercase tracking-wide opacity-80">Selamat datang</p>
        <h2 class="mt-2 text-2xl font-bold">{{ auth()->user()->name }}</h2>
        <p class="mt-1 text-sm text-blue-100">Anda masuk sebagai petugas yang mengelola persetujuan, pengembalian, dan laporan peminjaman.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Pengajuan Baru</p>
                    <h3 class="mt-2 text-3xl font-bold text-gray-800">{{ $totalPengajuan }}</h3>
                </div>
                <div class="bg-blue-100 text-blue-600 p-3 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Sedang Dipinjam</p>
                    <h3 class="mt-2 text-3xl font-bold text-gray-800">{{ $sedangDipinjam }}</h3>
                </div>
                <div class="bg-amber-100 text-amber-600 p-3 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Sudah Dikembalikan</p>
                    <h3 class="mt-2 text-3xl font-bold text-gray-800">{{ $totalDikembalikan }}</h3>
                </div>
                <div class="bg-emerald-100 text-emerald-600 p-3 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 3" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Stok Tersedia</p>
                    <h3 class="mt-2 text-3xl font-bold text-gray-800">{{ $stokTersedia }}</h3>
                </div>
                <div class="bg-violet-100 text-violet-600 p-3 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 12h14M5 16h14" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-5 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800">Peminjaman Terbaru</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 text-xs uppercase tracking-wider">
                            <th class="py-3 px-4">Peminjam</th>
                            <th class="py-3 px-4">Alat</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700">
                        @forelse($peminjamanTerbaru as $peminjaman)
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium text-gray-900">{{ $peminjaman->user->name ?? '-' }}</td>
                                <td class="py-3 px-4">
                                    @foreach($peminjaman->detailPinjam as $detail)
                                        {{ $detail->alat->nama_alat ?? '-' }}@if(!$loop->last), @endif
                                    @endforeach
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @if($peminjaman->status === 'diajukan') bg-yellow-100 text-yellow-700
                                        @elseif($peminjaman->status === 'dipinjam' || $peminjaman->status === 'telat') bg-blue-100 text-blue-700
                                        @else bg-emerald-100 text-emerald-700
                                        @endif">
                                        {{ $peminjaman->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-5 text-center text-gray-500">Belum ada data peminjaman.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-5 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800">Aktivitas Terakhir</h3>
            </div>
            <div class="p-4 space-y-3">
                @forelse($logAktivitas as $log)
                    <div class="flex gap-3 items-start border-b border-gray-100 pb-3 last:border-0 last:pb-0">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm">
                            {{ strtoupper(substr($log->user->name ?? 'S', 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-800">{{ $log->user->name ?? 'Sistem' }}</p>
                            <p class="text-sm text-gray-600">{{ $log->aktivitas }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $log->created_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-5">Belum ada aktivitas terbaru.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
