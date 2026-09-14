@extends('layouts.app')

@section('title', 'Menunggu Pengembalian - Panel Admin')
@section('header-title', 'Alat yang Sedang Dipinjam')

@section('content')
    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="p-5 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Daftar Peminjaman Aktif</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                        <th class="py-3 px-4 border-b">Peminjam</th>
                        <th class="py-3 px-4 border-b">Alat</th>
                        <th class="py-3 px-4 border-b">Tgl Pinjam</th>
                        <th class="py-3 px-4 border-b">Tgl Harus Kembali</th>
                        <th class="py-3 px-4 border-b">Status</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    @forelse($peminjamans as $p)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 px-4 border-b font-medium">{{ $p->user->name ?? '-' }}</td>
                            <td class="py-3 px-4 border-b">
                                @php
                                    $details = $p->detailPinjam ?? $p->detailPeminjaman ?? $p->detailPinjam ?? null;
                                @endphp
                                @if($details && count($details) > 0)
                                    @foreach($details as $d)
                                        <span class="text-xs bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded mr-1">
                                            {{ $d->alat->nama_alat ?? '-' }} ({{ $d->jumlah }}x)
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-gray-400 italic text-xs">Tidak ada detail</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 border-b">{{ $p->tgl_pinjam }}</td>
                            <td class="py-3 px-4 border-b text-red-600 font-medium">{{ $p->tgl_kembali_plan}}</td>
                            <td class="py-3 px-4 border-b">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $p->status}}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-500">Tidak ada alat yang sedang dipinjam saat ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
