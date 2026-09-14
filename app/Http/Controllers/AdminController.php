<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\DetailPinjam;
use App\Models\Kategori;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // =========================================================
    // DASHBOARD
    // =========================================================

    public function index()
    {
        $logs = LogAktivitas::with('user')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('logs'));
    }

    // =========================================================
    // CRUD ALAT
    // =========================================================

    public function indexAlat(Request $request)
    {
        $search = $request->input('search');

        $alats = Alat::with('kategori')
            ->when($search, function ($query, $search) {
                return $query
                    ->where('nama_alat', 'like', "%{$search}%")
                    ->orWhere('status_kondisi', 'like', "%{$search}%")
                    ->orWhereHas('kategori', function ($q) use ($search) {
                        $q->where(
                            'nama_kategori',
                            'like',
                            "%{$search}%"
                        );
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.alat.index', compact('alats', 'search'));
    }

    public function createAlat()
    {
        $kategoris = Kategori::all();

        return view('admin.alat.create', compact('kategoris'));
    }

    public function storeAlat(Request $request)
    {
        $request->validate([
            'nama_alat' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'stok' => 'required|integer|min:0',
            'status_kondisi' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . $file->getClientOriginalName();

            $file->move(
                public_path('storage/alat'),
                $filename
            );

            $data['gambar'] = 'storage/alat/' . $filename;
        }

        Alat::create($data);

        return redirect()
            ->route('admin.alat.index')
            ->with(
                'success',
                'Data alat berhasil ditambahkan.'
            );
    }

    public function editAlat($id)
    {
        $alat = Alat::findOrFail($id);
        $kategoris = Kategori::all();

        return view(
            'admin.alat.edit',
            compact('alat', 'kategoris')
        );
    }

    public function updateAlat(Request $request, $id)
    {
        $alat = Alat::findOrFail($id);

        $request->validate([
            'nama_alat' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'stok' => 'required|integer|min:0',
            'status_kondisi' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('gambar')) {
            if (
                $alat->gambar &&
                file_exists(public_path($alat->gambar))
            ) {
                unlink(public_path($alat->gambar));
            }

            $file = $request->file('gambar');
            $filename = time() . '_' . $file->getClientOriginalName();

            $file->move(
                public_path('storage/alat'),
                $filename
            );

            $data['gambar'] = 'storage/alat/' . $filename;
        }

        $alat->update($data);

        return redirect()
            ->route('admin.alat.index')
            ->with(
                'success',
                'Data alat berhasil diperbarui.'
            );
    }

    public function destroyAlat($id)
    {
        $alat = Alat::findOrFail($id);

        if (
            $alat->gambar &&
            file_exists(public_path($alat->gambar))
        ) {
            unlink(public_path($alat->gambar));
        }

        $alat->delete();

        return redirect()
            ->route('admin.alat.index')
            ->with(
                'success',
                'Data alat berhasil dihapus.'
            );
    }

    // =========================================================
    // CRUD USER
    // =========================================================

    public function indexUser(Request $request)
    {
        $search = $request->input('search');

        $users = User::when($search, function ($query, $search) {
            return $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('role', 'like', "%{$search}%");
        })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view(
            'admin.user.index',
            compact('users', 'search')
        );
    }

    public function createUser()
    {
        return view('admin.user.create');
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,petugas,peminjam',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'no_hp' => $request->no_hp,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with(
                'success',
                'User berhasil ditambahkan.'
            );
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);

        return view(
            'admin.user.edit',
            compact('user')
        );
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'required|in:admin,petugas,peminjam',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'no_hp' => $request->no_hp,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make(
                $request->password
            );
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with(
                'success',
                'Data user berhasil diperbarui.'
            );
    }

    public function destroyUser($id)
    {
        $user = User::findOrFail($id);

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with(
                'success',
                'User berhasil dihapus.'
            );
    }

    // =========================================================
    // CRUD KATEGORI
    // =========================================================

    public function indexKategori(Request $request)
    {
        $search = $request->input('search');

        $kategoris = Kategori::query()
            ->when($search, function ($query, $search) {
                return $query->where(
                    'nama_kategori',
                    'like',
                    "%{$search}%"
                );
            })
            ->latest()
            ->paginate(5)
            ->withQueryString();

        return view(
            'admin.kategori.index',
            compact('kategoris', 'search')
        );
    }

    public function createKategori()
    {
        return view('admin.kategori.create');
    }

    public function storeKategori(Request $request)
    {
        $request->validate([
            'nama_kategori' =>
                'required|string|max:255|unique:kategori,nama_kategori',
        ]);

        Kategori::create([
            'nama_kategori' => $request->nama_kategori,
        ]);

        return redirect()
            ->route('admin.kategori.index')
            ->with(
                'success',
                'Kategori berhasil ditambahkan.'
            );
    }

    public function editKategori($id)
    {
        $kategori = Kategori::findOrFail($id);

        return view(
            'admin.kategori.edit',
            compact('kategori')
        );
    }

    public function updateKategori(Request $request, $id)
    {
        $kategori = Kategori::findOrFail($id);

        $request->validate([
            'nama_kategori' =>
                'required|string|max:255|unique:kategori,nama_kategori,' . $id,
        ]);

        $kategori->update([
            'nama_kategori' => $request->nama_kategori,
        ]);

        return redirect()
            ->route('admin.kategori.index')
            ->with(
                'success',
                'Kategori berhasil diperbarui.'
            );
    }

    public function destroyKategori($id)
    {
        $kategori = Kategori::findOrFail($id);

        if ($kategori->alat()->count() > 0) {
            return redirect()
                ->route('admin.kategori.index')
                ->with(
                    'error',
                    'Kategori tidak dapat dihapus karena masih digunakan oleh data alat.'
                );
        }

        $kategori->delete();

        return redirect()
            ->route('admin.kategori.index')
            ->with(
                'success',
                'Kategori berhasil dihapus.'
            );
    }

    // =========================================================
    // PEMINJAMAN
    // =========================================================

    public function indexPeminjaman(Request $request)
    {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with([
            'user',
            'detailPinjam.alat',
        ])
            ->when($search, function ($query, $search) {
                return $query
                    ->where(
                        'status',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where(
                            'name',
                            'like',
                            "%{$search}%"
                        );
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view(
            'admin.peminjaman.index',
            compact('peminjamans', 'search')
        );
    }

    public function createPeminjaman()
    {
        $users = User::where(
            'role',
            'peminjam'
        )->get();

        $alats = Alat::where(
            'stok',
            '>',
            0
        )->get();

        return view(
            'admin.peminjaman.create',
            compact('users', 'alats')
        );
    }

    public function storePeminjaman(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tgl_pinjam' => 'required|date',
            'tgl_kembali_plan' =>
                'required|date|after_or_equal:tgl_pinjam',
            'alat_id' => 'required|array',
            'alat_id.*' => 'exists:alat,id',
            'jumlah' => 'required|array',
            'jumlah.*' => 'integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $peminjaman = Peminjaman::create([
                'user_id' => $request->user_id,
                'tgl_pinjam' => $request->tgl_pinjam,
                'tgl_kembali_plan' =>
                    $request->tgl_kembali_plan,
                'status' => 'diajukan',
            ]);

            foreach ($request->alat_id as $index => $alatId) {
                $jumlahPinjam = $request->jumlah[$index];

                $alat = Alat::findOrFail($alatId);

                if ($alat->stok < $jumlahPinjam) {
                    throw new \Exception(
                        "Stok alat '{$alat->nama_alat}' tidak mencukupi."
                    );
                }

                DetailPinjam::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id' => $alatId,
                    'jumlah' => $jumlahPinjam,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.peminjaman.index')
                ->with(
                    'success',
                    'Data peminjaman berhasil diajukan.'
                );
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function updateStatusPeminjaman(
        Request $request,
        $id
    ) {
        $request->validate([
            'status' =>
                'required|in:diajukan,dipinjam,selesai,telat',
        ]);

        $peminjaman = Peminjaman::with(
            'detailPinjam.alat'
        )->findOrFail($id);

        DB::beginTransaction();

        try {
            $statusLama = $peminjaman->status;
            $statusBaru = $request->status;

            if (
                $statusLama !== 'dipinjam' &&
                $statusBaru === 'dipinjam'
            ) {
                foreach (
                    $peminjaman->detailPinjam
                    as $detail
                ) {
                    $alat = $detail->alat;

                    if ($alat->stok < $detail->jumlah) {
                        throw new \Exception(
                            "Stok alat {$alat->nama_alat} tidak mencukupi untuk dipinjam."
                        );
                    }

                    $alat->decrement(
                        'stok',
                        $detail->jumlah
                    );
                }
            } elseif (
                $statusLama === 'dipinjam' &&
                $statusBaru === 'selesai'
            ) {
                foreach (
                    $peminjaman->detailPinjam
                    as $detail
                ) {
                    $detail->alat->increment(
                        'stok',
                        $detail->jumlah
                    );
                }
            }

            $peminjaman->update([
                'status' => $statusBaru,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.peminjaman.index')
                ->with(
                    'success',
                    'Status peminjaman berhasil diperbarui.'
                );
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with(
                'error',
                $e->getMessage()
            );
        }
    }

    public function destroyPeminjaman($id)
    {
        $peminjaman = Peminjaman::with(
            'detailPinjam.alat'
        )->findOrFail($id);

        if ($peminjaman->status === 'dipinjam') {
            foreach (
                $peminjaman->detailPinjam
                as $detail
            ) {
                $detail->alat->increment(
                    'stok',
                    $detail->jumlah
                );
            }
        }

        $peminjaman->delete();

        return redirect()
            ->route('admin.peminjaman.index')
            ->with(
                'success',
                'Data peminjaman berhasil dihapus.'
            );
    }

    // =========================================================
    // PENGEMBALIAN
    // =========================================================

    public function indexPengembalian(Request $request)
    {
        $search = $request->input('search');

        $pengembalians = Pengembalian::with([
            'peminjaman.user',
            'petugas',
        ])
            ->when($search, function ($query, $search) {
                return $query->where(
                    'kondisi_kembali',
                    'like',
                    "%{$search}%"
                )->orWhereHas(
                    'peminjaman.user',
                    function ($q) use ($search) {
                        $q->where(
                            'name',
                            'like',
                            "%{$search}%"
                        );
                    }
                );
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view(
            'admin.pengembalian.index',
            compact('pengembalians', 'search')
        );
    }

    public function indexMenungguPengembalian(
        Request $request
    ) {
        $search = $request->input('search');

        $peminjamanAktif = Peminjaman::with([
            'user',
            'detailPinjam.alat',
        ])
            ->where('status', 'dipinjam')
            ->when($search, function ($query, $search) {
                return $query->whereHas(
                    'user',
                    function ($q) use ($search) {
                        $q->where(
                            'name',
                            'like',
                            "%{$search}%"
                        );
                    }
                );
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view(
            'admin.pengembalian.menunggu',
            compact(
                'peminjamanAktif',
                'search'
            )
        );
    }

    public function indexRiwayatPengembalian(
        Request $request
    ) {
        $search = $request->input('search');

        $pengembalians = Pengembalian::with([
            'peminjaman.user',
            'peminjaman.detailPinjam.alat',
            'petugas',
        ])
            ->when($search, function ($query, $search) {
                return $query->where(function ($query) use ($search) {
                    $query
                        ->whereHas(
                            'peminjaman.user',
                            function ($q) use ($search) {
                                $q->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                );
                            }
                        )
                        ->orWhere(
                            'kondisi_kembali',
                            'like',
                            "%{$search}%"
                        );
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view(
            'admin.pengembalian.riwayat',
            compact(
                'pengembalians',
                'search'
            )
        );
    }

    public function prosesPengembalian(
        Request $request,
        $peminjamanId
    ) {
        $request->validate([
            'kondisi_kembali' => 'required|string',
            'denda' => 'nullable|integer|min:0',
        ]);

        $peminjaman = Peminjaman::with(
            'detailPinjam'
        )->findOrFail($peminjamanId);

        if ($peminjaman->status !== 'dipinjam') {
            return back()->with(
                'error',
                'Peminjaman ini sudah diproses sebelumnya.'
            );
        }

        DB::beginTransaction();

        try {
            Pengembalian::create([
                'peminjaman_id' => $peminjaman->id,
                'tgl_kembali' => now(),
                'kondisi_kembali' =>
                    $request->kondisi_kembali,
                'denda' => $request->denda ?? 0,
                'petugas_id' => auth()->id(),
            ]);

            $peminjaman->update([
                'status' => 'selesai',
            ]);

            foreach (
                $peminjaman->detailPinjam
                as $detail
            ) {
                $alat = Alat::findOrFail(
                    $detail->alat_id
                );

                $alat->increment(
                    'stok',
                    $detail->jumlah
                );
            }

            DB::commit();

            return back()->with(
                'success',
                'Pengembalian berhasil diproses.'
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with(
                'error',
                'Terjadi kesalahan: ' .
                    $e->getMessage()
            );
        }
    }

    public function editPengembalian($id)
    {
        $pengembalian = Pengembalian::with([
            'peminjaman.user',
            'peminjaman.detailPinjam.alat',
        ])->findOrFail($id);

        return view(
            'admin.pengembalian.edit',
            compact('pengembalian')
        );
    }

    public function updatePengembalian(
        Request $request,
        $id
    ) {
        $request->validate([
            'kondisi_kembali' =>
                'required|string',
            'denda' =>
                'nullable|integer|min:0',
        ]);

        $pengembalian = Pengembalian::findOrFail($id);

        $pengembalian->update([
            'kondisi_kembali' =>
                $request->kondisi_kembali,
            'denda' => $request->denda ?? 0,
        ]);

        return redirect()
            ->route(
                'admin.pengembalian.riwayat'
            )
            ->with(
                'success',
                'Data pengembalian berhasil diperbarui.'
            );
    }

    public function destroyPengembalian($id)
    {
        $pengembalian = Pengembalian::with(
            'peminjaman.detailPinjam'
        )->findOrFail($id);

        DB::beginTransaction();

        try {
            $peminjaman = $pengembalian->peminjaman;

            if ($peminjaman) {
                foreach (
                    $peminjaman->detailPinjam
                    as $detail
                ) {
                    $alat = Alat::findOrFail(
                        $detail->alat_id
                    );

                    if ($alat->stok < $detail->jumlah) {
                        throw new \Exception(
                            "Stok alat '{$alat->nama_alat}' tidak cukup untuk membatalkan pengembalian ini."
                        );
                    }

                    $alat->decrement(
                        'stok',
                        $detail->jumlah
                    );
                }

                $peminjaman->update([
                    'status' => 'dipinjam',
                ]);
            }

            $pengembalian->delete();

            DB::commit();

            return redirect()
                ->route(
                    'admin.pengembalian.riwayat'
                )
                ->with(
                    'success',
                    'Data pengembalian dihapus, status peminjaman dikembalikan ke "Dipinjam".'
                );
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with(
                'error',
                'Terjadi kesalahan: ' .
                    $e->getMessage()
            );
        }
    }

    public function createPengembalian()
{
    $peminjamans = Peminjaman::with([
        'user',
        'detailPinjam.alat',
    ])
        ->whereIn('status', ['dipinjam', 'telat'])
        ->latest()
        ->get();

    return view(
        'admin.pengembalian.create',
        compact('peminjamans')
    );
}

    public function storePengembalian(
        Request $request
    ) {
        $request->validate([
            'peminjaman_id' =>
                'required|exists:peminjamans,id',
            'tgl_kembali' =>
                'required|date',
            'kondisi_kembali' =>
                'required|string|max:255',
            'denda_tambahan' =>
                'nullable|integer|min:0',
        ]);

        DB::beginTransaction();

        try {
            $peminjaman = Peminjaman::with(
                'detailPinjam.alat'
            )->findOrFail(
                $request->peminjaman_id
            );

            $tglPlan = \Carbon\Carbon::parse(
                $peminjaman->tgl_kembali_plan
            );

            $tglAktual = \Carbon\Carbon::parse(
                $request->tgl_kembali
            );

            $dendaOtomatis = 0;
            $tarifDendaPerHari = 5000;

            if ($tglAktual->greaterThan($tglPlan)) {
                $selisihHari =
                    $tglPlan->diffInDays($tglAktual);

                $dendaOtomatis =
                    $selisihHari *
                    $tarifDendaPerHari;
            }

            $dendaTambahan =
                $request->denda_tambahan ?? 0;

            $totalDenda =
                $dendaOtomatis +
                $dendaTambahan;

            Pengembalian::create([
                'peminjaman_id' =>
                    $request->peminjaman_id,
                'tgl_kembali' =>
                    $request->tgl_kembali,
                'kondisi_kembali' =>
                    $request->kondisi_kembali,
                'denda' => $totalDenda,
                'petugas_id' => auth()->id(),
            ]);

            $peminjaman->update([
                'status' => 'selesai',
            ]);

            foreach (
                $peminjaman->detailPinjam
                as $detail
            ) {
                $detail->alat->increment(
                    'stok',
                    $detail->jumlah
                );
            }

            DB::commit();

            return redirect()
                ->route(
                    'admin.pengembalian.index'
                )
                ->with(
                    'success',
                    'Pengembalian berhasil diproses. Denda otomatis terhitung: Rp ' .
                        number_format(
                            $totalDenda,
                            0,
                            ',',
                            '.'
                        )
                );
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    // =========================================================
    // DETAIL USER
    // =========================================================

    public function showUser($id)
    {
        $user = User::findOrFail($id);

        $riwayatPeminjaman = Peminjaman::with([
            'detailPinjam.alat',
            'pengembalian',
        ])
            ->where('user_id', $id)
            ->latest()
            ->paginate(10);

        return view(
            'admin.user.show',
            compact(
                'user',
                'riwayatPeminjaman'
            )
        );
    }
}