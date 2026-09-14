@extends('layouts.app')

@section('title', 'Edit Pengembalian - Panel Admin')
@section('header-title', 'Edit Pengembalian')

@section('content')
<div class="max-w-2xl bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <form action="{{ route('admin.pengembalian.update', $pengembalian->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Peminjaman</label>
            <input type="text" value="#{{ $pengembalian->peminjaman_id }} - {{ $pengembalian->peminjaman->user->name ?? 'User Dihapus' }}" disabled class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100">
        </div>

        <div class="mb-4 rounded-lg bg-gray-50 border border-gray-200 p-4 text-sm text-gray-600">
            <p class="font-semibold text-gray-700">Alat yang dikembalikan</p>
            <ul class="mt-2 list-disc list-inside">
                @foreach($pengembalian->peminjaman->detailPinjam as $detail)
                    <li>{{ $detail->alat->nama_alat ?? 'Alat tidak ditemukan' }} ({{ $detail->jumlah }} pcs)</li>
                @endforeach
            </ul>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Tanggal Kembali</label>
                <input type="date" name="tgl_kembali" value="{{ old('tgl_kembali', $pengembalian->tgl_kembali?->format('Y-m-d')) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                @error('tgl_kembali') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Jumlah Dikembalikan</label>
                <input type="number" value="{{ $pengembalian->jumlah_kembali }}" disabled class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100">
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Kondisi Kembali</label>
            <select name="kondisi_kembali" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                @foreach(['Baik', 'Rusak', 'Hilang'] as $kondisi)
                    <option value="{{ $kondisi }}" {{ old('kondisi_kembali', $pengembalian->kondisi_kembali) === $kondisi ? 'selected' : '' }}>{{ $kondisi }}</option>
                @endforeach
            </select>
            @error('kondisi_kembali') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Denda</label>
            <input type="number" name="denda" value="{{ old('denda', $pengembalian->denda) }}" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            @error('denda') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Keterangan</label>
            <textarea name="keterangan" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg">{{ old('keterangan', $pengembalian->keterangan) }}</textarea>
            @error('keterangan') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            <p class="mt-1 text-xs text-gray-500">Terlambat: {{ $pengembalian->terlambat_hari }} hari</p>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.pengembalian.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-sm font-semibold">Batal</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
