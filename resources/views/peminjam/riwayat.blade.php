@extends('layouts.app')

@section('title', 'Riwayat Peminjaman - Peminjam')
@section('header-title', 'Riwayat Peminjaman')

@section('content')
    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg shadow-sm text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Riwayat Peminjaman</h3>
                <p class="text-xs text-gray-500 mt-1">Semua histori peminjaman Anda</p>
            </div>

            <form method="GET" action="{{ route('peminjam.riwayat') }}" class="flex gap-2 w-full md:w-auto">
                <input
                    type="text"
                    name="search"
                    value="{{ $search ?? '' }}"
                    placeholder="Cari status, alat, atau tanggal..."
                    class="w-full md:w-72 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                >
                <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    Cari
                </button>
                @if($search)
                    <a href="{{ route('peminjam.riwayat') }}" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-xs uppercase tracking-wider">
                        <th class="py-3 px-4 border-b">Tanggal Pinjam</th>
                        <th class="py-3 px-4 border-b">Rencana Kembali</th>
                        <th class="py-3 px-4 border-b">Alat yang Dipinjam</th>
                        <th class="py-3 px-4 border-b">Status</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700">
                    @forelse($peminjamans as $peminjaman)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 px-4 border-b whitespace-nowrap">
                                {{ $peminjaman->tgl_pinjam?->format('d-m-Y') ?? $peminjaman->tgl_pinjam }}
                            </td>
                            <td class="py-3 px-4 border-b whitespace-nowrap">
                                {{ $peminjaman->tgl_kembali_plan?->format('d-m-Y') ?? $peminjaman->tgl_kembali_plan }}
                            </td>
                            <td class="py-3 px-4 border-b">
                                <ul class="mb-0 ps-3 list-disc">
                                    @forelse($peminjaman->detailPinjam as $detail)
                                        <li>
                                            {{ $detail->alat->nama_alat ?? 'Alat tidak ditemukan' }}
                                            <span class="text-gray-500">[{{ $detail->alat->kategori->nama_kategori ?? 'Kategori tidak tersedia' }}]</span>
                                            ({{ $detail->jumlah }} pcs)
                                        </li>
                                    @empty
                                        <li class="text-gray-500">Belum ada detail alat</li>
                                    @endforelse
                                </ul>
                            </td>
                            <td class="py-3 px-4 border-b">
                                @php
                                    $status = strtolower($peminjaman->status);
                                    $badgeClass = [
                                        'diajukan' => 'bg-yellow-100 text-yellow-700',
                                        'dipinjam' => 'bg-blue-100 text-blue-700',
                                        'telat' => 'bg-red-100 text-red-700',
                                        'dikembalikan' => 'bg-emerald-100 text-emerald-700',
                                        'selesai' => 'bg-emerald-100 text-emerald-700',
                                    ][$status] ?? 'bg-gray-100 text-gray-700';
                                @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $badgeClass }}">
                                    {{ ucfirst($peminjaman->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-gray-500">Belum ada riwayat peminjaman.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
