<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Peminjaman - Peminjam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ route('peminjam.katalog') }}">Katalog Alat</a>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('peminjam.katalog') }}" class="btn btn-outline-light btn-sm">Ajukan Peminjaman</a>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h3 class="mb-0">Riwayat Peminjaman</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal Pinjam</th>
                                <th>Rencana Kembali</th>
                                <th>Alat yang Dipinjam</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($peminjamans as $peminjaman)
                                <tr>
                                    <td>{{ $peminjaman->tgl_pinjam?->format('d-m-Y') ?? $peminjaman->tgl_pinjam }}</td>
                                    <td>{{ $peminjaman->tgl_kembali_plan?->format('d-m-Y') ?? $peminjaman->tgl_kembali_plan }}</td>
                                    <td>
                                        <ul class="mb-0 ps-3">
                                            @forelse($peminjaman->detailPinjam as $detail)
                                                <li>{{ $detail->alat->nama_alat ?? 'Alat tidak ditemukan' }} <span class="text-muted">[{{ $detail->alat->kategori->nama_kategori ?? 'Kategori tidak tersedia' }}]</span> ({{ $detail->jumlah }} pcs)</li>
                                            @empty
                                                <li class="text-muted">Belum ada detail alat</li>
                                            @endforelse
                                        </ul>
                                    </td>
                                    <td><span class="badge bg-secondary">{{ ucfirst($peminjaman->status) }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Belum ada riwayat peminjaman.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
