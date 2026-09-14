@extends('layouts.app')

@section('title', 'Kelola Alat - Panel Admin')
@section('header-title', 'Manajemen Data Alat')

@section('content')
    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg shadow-sm text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="p-6 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="text-lg font-bold text-gray-800">Daftar Alat Laboratorium</h2>
            
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <!-- Form Search -->
                <form action="{{ route('admin.alat.index') }}" method="GET" class="flex w-full md:w-80">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama alat, kategori..." class="w-full px-3 py-2 text-sm border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-semibold rounded-r-lg transition">Cari</button>
                </form>

                @if(request('search'))
                    <a href="{{ route('admin.alat.index') }}" class="inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 text-sm font-medium rounded-lg border border-gray-300 transition">
                        Reset
                    </a>
                @endif
                
                <!-- Tombol Tambah -->
                <a href="{{ route('admin.alat.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition whitespace-nowrap">
                    + Tambah Alat
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs font-uppercase tracking-wider text-gray-500 font-semibold">
                        <th class="py-3 px-4 border-b">Gambar</th>
                        <th class="py-3 px-4 border-b">Nama Alat</th>
                        <th class="py-3 px-4 border-b">Kategori</th>
                        <th class="py-3 px-4 border-b">Kondisi</th>
                        <th class="py-3 px-4 border-b">Stok</th>
                        <th class="py-3 px-4 border-b">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    @forelse($alats as $alat)
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                            <td class="py-3 px-4 border-b">
                                <img src="{{ asset('storage/' . $alat->gambar) }}" alt="{{ $alat->nama_alat }}" class="w-12 h-12 object-cover rounded-lg border">
                            </td>
                            <td class="py-3 px-4 border-b font-medium text-gray-900">{{ $alat->nama_alat }}</td>
                            <td class="py-3 px-4 border-b text-gray-500">{{ $alat->kategori->nama_kategori ?? '-' }}</td>
                            <td class="py-3 px-4 border-b">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $alat->status_kondisi == 'Baik' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $alat->status_kondisi }}
                                </span>
                            </td>
                            <td class="py-3 px-4 border-b text-gray-500">{{ $alat->stok }}</td>
                            <td class="py-3 px-4 border-b">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.alat.edit', $alat->id) }}" class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded text-xs font-semibold transition">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.alat.destroy', $alat->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus alat ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded text-xs font-semibold transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-400 italic">Belum ada data alat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
      
        <div class="p-4 border-t border-gray-200 bg-gray-50">
                {{ $alats->links() }}
         </div>
    </div>
@endsection
