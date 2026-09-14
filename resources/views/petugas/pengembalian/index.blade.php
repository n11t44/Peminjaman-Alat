@extends('layouts.app')

@section('title', 'Peminjaman Pengembalian - Dashboard Petugas')
@section('header-title', 'Peminjaman & Proses Pengembalian Alat')

@section('content')

@if(session('success'))
    <div class="mb-8 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg shadow-sm text-sm">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-8 bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg shadow-sm text-sm">
        {{ session('error') }}
    </div>
@endif

<div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">

    <!-- HEADER -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <div>
                <h3 class="text-lg font-bold text-gray-800">
                    Daftar Peminjaman Aktif (Belum Kembali)
                </h3>

                <p class="text-sm text-gray-500 mt-1">
                    Daftar alat yang masih dalam status dipinjam atau terlambat dikembalikan.
                </p>
            </div>

            <!-- SEARCH -->
            <form
                action="{{ route('petugas.pengembalian.index') }}"
                method="GET"
                class="flex w-full md:w-96"
            >
                <input
                    type="text"
                    name="search"
                    id="search"
                    value="{{ request('search') }}"
                    placeholder="Cari nama peminjam..."
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                >

                <button
                    type="submit"
                    class="ml-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 text-sm font-semibold rounded-lg transition"
                >
                    Cari
                </button>

                @if(request('search'))
                    <a
                        href="{{ route('petugas.pengembalian.index') }}"
                        class="ml-2 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 text-sm font-semibold rounded-lg transition"
                    >
                        Reset
                    </a>
                @endif
            </form>

        </div>
    </div>

    <!-- TABLE -->
    <div class="overflow-x-auto">

        <table class="w-full text-left border-collapse">

            <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                <tr>
                    <th class="py-3 px-4 border-b">
                        Peminjam
                    </th>

                    <th class="py-3 px-4 border-b">
                        Tgl Pinjam
                    </th>

                    <th class="py-3 px-4 border-b">
                        Rencana Kembali
                    </th>

                    <th class="py-3 px-4 border-b">
                        Status
                    </th>

                    <th class="py-3 px-4 border-b">
                        Detail Alat
                    </th>

                    <th class="py-3 px-4 border-b text-center">
                        Aksi Pengembalian
                    </th>
                </tr>
            </thead>

            <tbody class="text-gray-700 text-sm">

                @forelse($peminjamans as $item)

                    <tr class="hover:bg-gray-50 transition align-top">

                        <!-- PEMINJAM -->
                        <td class="py-3 px-4 border-b font-medium text-gray-900">
                            {{ $item->user->name ?? 'User Dihapus' }}
                        </td>

                        <!-- TANGGAL PINJAM -->
                        <td class="py-3 px-4 border-b">
                            {{ $item->tgl_pinjam ?? '-' }}
                        </td>

                        <!-- RENCANA KEMBALI -->
                        <td class="py-3 px-4 border-b">
                            {{ $item->tgl_kembali_plan ?? '-' }}
                        </td>

                        <!-- STATUS -->
                        <td class="py-3 px-4 border-b">

                            @if($item->status === 'telat')

                                <span class="px-2.5 py-1 rounded text-xs font-semibold bg-red-100 text-red-700">
                                    Telat
                                </span>

                            @elseif($item->status === 'dipinjam')

                                <span class="px-2.5 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-700">
                                    Dipinjam
                                </span>

                            @else

                                <span class="px-2.5 py-1 rounded text-xs font-semibold bg-gray-100 text-gray-700">
                                    {{ ucfirst($item->status ?? '-') }}
                                </span>

                            @endif

                        </td>

                        <!-- DETAIL ALAT -->
                        <td class="py-3 px-4 border-b">

                            @if($item->detailPinjam->count())

                                <ul class="list-disc list-inside space-y-1 text-xs">

                                    @foreach($item->detailPinjam as $detail)

                                        <li>
                                            <span class="font-semibold">
                                                {{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}
                                            </span>

                                            <span class="text-gray-500">
                                                (Jumlah: {{ $detail->jumlah ?? 0 }})
                                            </span>
                                        </li>

                                    @endforeach

                                </ul>

                            @else

                                <span class="text-gray-400 text-xs">
                                    Tidak ada detail alat
                                </span>

                            @endif

                        </td>

                        <!-- AKSI PENGEMBALIAN -->
                        <td class="py-3 px-4 border-b text-center">

                            <form
                                action="{{ route('petugas.pengembalian.proses', $item->id) }}"
                                method="POST"
                                class="inline-block bg-gray-50 p-3 rounded border border-gray-200 text-left space-y-2 min-w-[180px]"
                            >

                                @csrf

                                <!-- KONDISI -->
                                <div>

                                    <label
                                        for="kondisi_kembali_{{ $item->id }}"
                                        class="block text-xs font-semibold text-gray-600 mb-1"
                                    >
                                        Kondisi Kembali
                                    </label>

                                    <select
                                        name="kondisi_kembali"
                                        id="kondisi_kembali_{{ $item->id }}"
                                        required
                                        class="w-full text-sm border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    >
                                        <option value="Baik">
                                            Baik
                                        </option>

                                        <option value="Rusak Ringan">
                                            Rusak Ringan
                                        </option>

                                        <option value="Rusak Berat">
                                            Rusak Berat
                                        </option>
                                    </select>

                                </div>

                                <!-- DENDA -->
                                <div>

                                    <label
                                        for="denda_{{ $item->id }}"
                                        class="block text-xs font-semibold text-gray-600 mb-1"
                                    >
                                        Denda (Rp)
                                    </label>

                                    <input
                                        type="number"
                                        name="denda"
                                        id="denda_{{ $item->id }}"
                                        value="0"
                                        min="0"
                                        placeholder="0"
                                        class="w-full text-sm border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    >

                                </div>

                                <!-- BUTTON -->
                                <div>

                                    <button
                                        type="submit"
                                        onclick="return confirm('Proses pengembalian alat ini?')"
                                        class="w-full bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded text-xs font-semibold transition shadow-sm"
                                    >
                                        Terima Pengembalian
                                    </button>

                                </div>

                            </form>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="6"
                            class="py-10 text-center text-gray-500"
                        >

                            <div class="flex flex-col items-center">

                                <div class="text-4xl mb-3">
                                    📦
                                </div>

                                <p class="font-medium text-gray-600">
                                    Tidak ada peminjaman aktif
                                </p>

                                <p class="text-xs text-gray-400 mt-1">
                                    Belum ada alat yang sedang dipinjam atau terlambat dikembalikan.
                                </p>

                            </div>

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection