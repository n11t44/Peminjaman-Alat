<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PetugasController extends Controller
{
    /**
     * Menampilkan dashboard petugas
     */
    public function dashboard()
    {
        $totalPengajuan = Peminjaman::where('status', 'diajukan')->count();
        $sedangDipinjam = Peminjaman::whereIn('status', ['dipinjam', 'telat'])->count();
        $totalDikembalikan = Peminjaman::where('status', 'dikembalikan')->count();
        $totalAlat = Alat::count();
        $stokTersedia = Alat::sum('stok');

        $peminjamanTerbaru = Peminjaman::with(['user', 'detailPinjam.alat'])
            ->latest()
            ->take(5)
            ->get();

        $logAktivitas = LogAktivitas::with('user')
            ->latest()
            ->take(5)
            ->get();

        return view('petugas.dashboard', compact(
            'totalPengajuan',
            'sedangDipinjam',
            'totalDikembalikan',
            'totalAlat',
            'stokTersedia',
            'peminjamanTerbaru',
            'logAktivitas'
        ));
    }

    /**
     * Menampilkan daftar pengajuan peminjaman
     */
    public function indexPeminjaman(Request $request)
    {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with([
            'user',
            'detailPinjam.alat',
        ])
            ->where('status', 'diajukan')
            ->when($search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        return view(
            'petugas.peminjaman.index',
            compact('peminjamans', 'search')
        );
    }


    /**
     * Menyetujui peminjaman
     */
    public function setujuiPeminjaman($id)
    {
        DB::beginTransaction();

        try {
            $peminjaman = Peminjaman::with('detailPinjam')
                ->findOrFail($id);

            /*
             * Cek stok terlebih dahulu
             */
            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = Alat::findOrFail($detail->alat_id);

                if ($alat->stok < $detail->jumlah) {
                    throw new \Exception(
                        "Stok alat {$alat->nama_alat} tidak mencukupi."
                    );
                }
            }

            /*
             * Kurangi stok alat
             */
            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = Alat::findOrFail($detail->alat_id);

                $alat->stok -= $detail->jumlah;
                $alat->save();
            }

            /*
             * Ubah status peminjaman
             */
            $peminjaman->update([
                'status' => 'dipinjam',
            ]);

            DB::commit();

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Peminjaman disetujui dan stok alat berhasil dikurangi.'
                );
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Terjadi kesalahan: ' . $e->getMessage()
                );
        }
    }


    /**
     * Menampilkan daftar pengembalian
     */
    public function indexPengembalian(Request $request)
    {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with([
            'user',
            'detailPinjam.alat',
            'pengembalian',
        ])
            ->whereIn('status', ['dipinjam', 'telat'])
            ->when($search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        return view(
            'petugas.pengembalian.index',
            compact('peminjamans', 'search')
        );
    }


    /**
     * Memproses pengembalian alat
     */
    public function prosesPengembalian(Request $request, $peminjamanId)
    {
        $request->validate([
            'kondisi_kembali' => 'required|string',
            'denda' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();

        try {
            $peminjaman = Peminjaman::with('detailPinjam')
                ->findOrFail($peminjamanId);

            /*
             * Cegah pengembalian ganda
             */
            if ($peminjaman->status === 'dikembalikan') {
                throw new \Exception(
                    'Peminjaman ini sudah dikembalikan.'
                );
            }

            /*
             * Simpan data pengembalian
             */
            Pengembalian::create([
                'peminjaman_id' => $peminjaman->id,
                'tgl_kembali' => now(),
                'kondisi_kembali' => $request->kondisi_kembali,
                'denda' => $request->denda ?? 0,
                'petugas_id' => auth()->id(),
            ]);

            /*
             * Ubah status peminjaman
             */
            $peminjaman->update([
                'status' => 'dikembalikan',
            ]);

            /*
             * Kembalikan stok alat
             */
            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = Alat::findOrFail($detail->alat_id);

                $alat->stok += $detail->jumlah;
                $alat->save();
            }

            DB::commit();

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Pengembalian berhasil dicatat dan stok alat dipulihkan.'
                );
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Terjadi kesalahan: ' . $e->getMessage()
                );
        }
    }


    /**
     * Menampilkan laporan peminjaman dan pengembalian
     */
    public function laporan(Request $request)
    {
        $status = $request->input('status');
        $dari_tanggal = $request->input('dari_tanggal');
        $sampai_tanggal = $request->input('sampai_tanggal');

        $laporans = Peminjaman::with([
            'user',
            'detailPinjam.alat',
            'pengembalian',
        ])
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when(
                $dari_tanggal && $sampai_tanggal,
                function ($query) use ($dari_tanggal, $sampai_tanggal) {
                    $query->whereBetween(
                        'tgl_pinjam',
                        [
                            $dari_tanggal,
                            $sampai_tanggal,
                        ]
                    );
                }
            )
            ->latest()
            ->get();

        return view(
            'petugas.laporan.index',
            compact(
                'laporans',
                'status',
                'dari_tanggal',
                'sampai_tanggal'
            )
        );
    }


    /**
     * Menampilkan halaman khusus untuk cetak laporan
     */
    public function cetakLaporan(Request $request)
    {
        $status = $request->input('status');
        $dari_tanggal = $request->input('dari_tanggal');
        $sampai_tanggal = $request->input('sampai_tanggal');

        $laporans = Peminjaman::with([
            'user',
            'detailPinjam.alat',
            'pengembalian',
        ])
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when(
                $dari_tanggal && $sampai_tanggal,
                function ($query) use ($dari_tanggal, $sampai_tanggal) {
                    $query->whereBetween(
                        'tgl_pinjam',
                        [
                            $dari_tanggal,
                            $sampai_tanggal,
                        ]
                    );
                }
            )
            ->latest()
            ->get();

        return view(
            'petugas.laporan.cetak',
            compact(
                'laporans',
                'status',
                'dari_tanggal',
                'sampai_tanggal'
            )
        );
    }
}