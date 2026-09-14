@extends('layouts.app')

@section('title', 'Kelola Pengembalian - Panel Admin')
@section('header-title', 'Manajemen Pengembalian')

@section('content')
    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg shadow-sm text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg shadow-sm text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="p-6 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <h2 class="text-lg font-bold text-gray-800">Riwayat Pengembalian Alat</h2>

            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <form action="{{ route('admin.pengembalian.index') }}" method="GET" class="flex w-full md:w-80">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari peminjam, petugas, kondisi..."
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-semibold rounded-r-lg transition">
                        Cari
                    </button>
                </form>

                @if($search)
                    <a href="{{ route('admin.pengembalian.index') }}" class="inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 text-sm font-medium rounded-lg border border-gray-300 transition">
                        Reset
                    </a>
                @endif
            
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs tracking-wider text-gray-500 font-semibold uppercase">
                        <th class="py-3 px-4 border-b w-16 text-center">No</th>
                        <th class="py-3 px-4 border-b">Peminjam</th>
                        <th class="py-3 px-4 border-b">Tanggal Kembali</th>
                        <th class="py-3 px-4 border-b">Alat</th>
                        <th class="py-3 px-4 border-b">Terlambat</th>
                        <th class="py-3 px-4 border-b">Kondisi</th>
                        <th class="py-3 px-4 border-b">Denda</th>
                        <th class="py-3 px-4 border-b">Diterima Oleh</th>
                        <th class="py-3 px-4 border-b">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    @forelse($pengembalians as $index => $pengembalian)
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                            <td class="py-3 px-4 border-b text-center">{{ $pengembalians->firstItem() + $index }}</td>
                            <td class="py-3 px-4 border-b font-medium text-gray-900">
                                {{ $pengembalian->peminjaman->user->name ?? '-' }}
                            </td>
                            <td class="py-3 px-4 border-b">{{ $pengembalian->tgl_kembali?->format('d-m-Y') ?? '-' }}</td>
                            <td class="py-3 px-4 border-b">
                                @foreach($pengembalian->peminjaman->detailPinjam as $detail)
                                    <div>{{ $detail->alat->nama_alat ?? 'Alat tidak ditemukan' }} ({{ $detail->jumlah }} pcs)</div>
                                @endforeach
                            </td>
                            <td class="py-3 px-4 border-b">{{ $pengembalian->terlambat_hari }} hari</td>
                            <td class="py-3 px-4 border-b">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ strtolower($pengembalian->kondisi_kembali) === 'baik' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $pengembalian->kondisi_kembali }}
                                </span>
                            </td>
                            <td class="py-3 px-4 border-b font-medium {{ $pengembalian->denda > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                Rp {{ number_format($pengembalian->denda, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 border-b">{{ $pengembalian->petugas->name ?? '-' }}</td>
                            <td class="py-3 px-4 border-b">
                                <div class="flex gap-2">
                                  
                                    <a href="{{ route('admin.pengembalian.edit', $pengembalian->id) }}" class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1 rounded text-xs font-semibold">Edit</a>
                                    <form action="{{ route('admin.pengembalian.destroy', $pengembalian->id) }}" method="POST" onsubmit="return confirm('Hapus data pengembalian ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-semibold">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-gray-400 italic">Belum ada data pengembalian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200 bg-gray-50">
            {{ $pengembalians->links() }}
        </div>
    </div>
@endsection