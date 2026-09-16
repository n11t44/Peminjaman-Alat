<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\DetailPinjam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamController extends Controller
{
    public function dashboard()
    {
        $peminjamans = Peminjaman::with('detailPinjam.alat')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        $totalPeminjaman = $peminjamans->count();
        $menunggu = $peminjamans->where('status', 'diajukan')->count();
        $sedangDipinjam = $peminjamans->whereIn('status', ['dipinjam', 'telat'])->count();
        $selesai = $peminjamans->whereIn('status', ['dikembalikan', 'selesai'])->count();

        $peminjamansTerbaru = $peminjamans->take(5);
        $alatsTersedia = Alat::with('kategori')
            ->where('stok', '>', 0)
            ->latest()
            ->take(6)
            ->get();

        return view('peminjam.dashboard', compact(
            'totalPeminjaman',
            'menunggu',
            'sedangDipinjam',
            'selesai',
            'peminjamansTerbaru',
            'alatsTersedia',
            'peminjamans'
        ));
    }

    public function katalogAlat(Request $request)
    {
        $search = $request->input('search');

        $alats = Alat::with('kategori')
            ->where('stok', '>', 0)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_alat', 'like', "%{$search}%")
                        ->orWhereHas('kategori', function ($k) use ($search) {
                            $k->where('nama_kategori', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->get();

        return view('peminjam.katalog', compact('alats', 'search'));
    }

    public function ajukanPeminjaman(Request $request)
    {
        $request->validate([
            'tgl_kembali_plan' => 'required|date|after:today',
            'alat_id' => 'required|array',
            'jumlah' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
           
            $peminjaman = Peminjaman::create([
                'user_id' => auth()->id(),
                'tgl_pinjam' => now(),
                'tgl_kembali_plan' => $request->tgl_kembali_plan,
                'status' => 'diajukan',
            ]);

            //Daftar alat yang dipinjam ke detail_pinjam
            foreach ($request->alat_id as $index => $alatId) {
                $jumlah = $request->input("jumlah.{$alatId}");

                if (!$jumlah) {
                    throw new \Exception('Jumlah alat yang dipilih tidak valid.');
                }

                DetailPinjam::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id' => $alatId,
                    'jumlah' => $jumlah,
                ]);
            }

            DB::commit();
            return redirect()->route('peminjam.riwayat')->with('success', 'Pengajuan peminjaman berhasil dikirim.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal mengajukan peminjaman: ' . $e->getMessage());
        }
    }

    
    public function riwayatPeminjaman(Request $request)
    {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with('detailPinjam.alat.kategori')
            ->where('user_id', auth()->id())
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('status', 'like', "%{$search}%")
                        ->orWhere('tgl_pinjam', 'like', "%{$search}%")
                        ->orWhereHas('detailPinjam.alat', function ($a) use ($search) {
                            $a->where('nama_alat', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->get();

        return view('peminjam.riwayat', compact('peminjamans', 'search'));
    }
}