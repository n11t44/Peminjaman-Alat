@extends('layouts.app')

@section('title', 'Riwayat Pengembalian - Panel Admin')
@section('header-title', 'Riwayat Pengembalian Alat')

@section('content')
    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg shadow-sm text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg shadow-sm text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex justify-between items-center gap-4">
            <h3 class="text-lg font-bold text-gray-800">Data Pengembalian</h3>
            <form action="{{ route('admin.pengembalian.riwayat') }}" method="GET" class="flex">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama peminjam/kondisi..." class="px-3 py-2 text-sm border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 text-sm font-semibold rounded-r-lg">Cari</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead><tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                    <th class="py-3 px-4 border-b">Peminjam</th><th class="py-3 px-4 border-b">Alat</th><th class="py-3 px-4 border-b">Tgl Kembali</th><th class="py-3 px-4 border-b">Kondisi</th><th class="py-3 px-4 border-b">Denda</th><th class="py-3 px-4 border-b">Diproses Oleh</th><th class="py-3 px-4 border-b">Aksi</th>
                </tr></thead>
                <tbody class="text-gray-700 text-sm">
                    @forelse($pengembalians as $p)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 px-4 border-b font-medium">{{ $p->peminjaman->user->name ?? '-' }}</td>
                            
                            {{-- KOLOM ALAT ANTI-EROR --}}
                            <td class="py-3 px-4 border-b">
                                @php
                                    $details = null;
                                    if (isset($p->peminjaman)) {
                                        $details = $p->peminjaman->detailPinjam
                                                ?? $p->peminjaman->detailPeminjaman 
                                                ?? $p->peminjaman->detailPinjam 
                                                ?? null;
                                    }
                                @endphp

                                @if($details && (is_array($details) || $details instanceof \Countable) && count($details) > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($details as $d)
                                            <span class="text-xs bg-gray-200 px-1.5 py-0.5 rounded">{{ $d->alat->nama_alat ?? '-' }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic">Tidak ada detail alat</span>
                                @endif
                            </td>

                            <td class="py-3 px-4 border-b">{{ $p->tgl_kembali }}</td>
                            <td class="py-3 px-4 border-b"><span class="px-2.5 py-1 text-xs font-semibold rounded-full @if($p->kondisi_kembali == 'Baik') bg-emerald-100 text-emerald-800 @else bg-red-100 text-red-800 @endif">{{ $p->kondisi_kembali }}</span></td>
                            <td class="py-3 px-4 border-b">{{ $p->denda > 0 ? 'Rp ' . number_format($p->denda) : '-' }}</td>
                            <td class="py-3 px-4 border-b">{{ $p->petugas->name ?? '-' }}</td>
                            <td class="py-3 px-4 border-b"><div class="flex items-center space-x-2"><a href="{{ route('admin.pengembalian.edit', $p->id) }}" class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded text-xs font-semibold">Edit</a><form action="{{ route('admin.pengembalian.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Hapus data ini? Status peminjaman akan kembali ke Dipinjam dan stok akan dikurangi lagi.')">@csrf @method('DELETE')<button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded text-xs font-semibold">Hapus</button></form></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-4 text-center text-gray-500">Belum ada data pengembalian.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200 bg-gray-50">{{ $pengembalians->links() }}</div>
    </div>
@endsection
