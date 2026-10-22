<?php
// Tugas 2 - Penentuan Kelulusan dan Predikat
declare(strict_types=1);

session_start();

// Nama halaman digunakan sebagai tujuan setelah form berhasil diproses.
const HALAMAN = 'tugas2_kelulusan.php';

/** Escape output HTML. */
function e(string $teks): string
{
    return htmlspecialchars($teks, ENT_QUOTES, 'UTF-8');
}

/** Validasi nilai ujian 0-100. Mengembalikan [float|null, pesan error|null]. */
function validasiNilai(mixed $raw): array
{
    if (!is_string($raw)) {
        return [null, 'Nilai tidak valid.'];
    }
    $raw = trim($raw);
    if ($raw === '') {
        return [null, 'Nilai ujian wajib diisi.'];
    }
    if (strlen($raw) > 6) {
        return [null, 'Nilai terlalu panjang.'];
    }
    // Koma atau titik diterima sebagai desimal; huruf, spasi, dan tanda minus ditolak
    $normal = str_replace(',', '.', $raw);
    if (!preg_match('/^(\d+(\.\d+)?|\.\d+)$/', $normal)) {
        return [null, 'Nilai harus berupa angka tanpa huruf atau simbol, contoh: 85 atau 72,5.'];
    }
    $nilai = (float) $normal;
    if ($nilai < 0 || $nilai > 100) {
        return [null, 'Nilai harus berada pada rentang 0 sampai 100.'];
    }
    return [$nilai, null];
}

// Batasi metode request yang dapat mengakses halaman.
$metode = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!in_array($metode, ['GET', 'POST'], true)) {
    http_response_code(405);
    header('Allow: GET, POST');
    exit('Metode request tidak diizinkan.');
}

// Token CSRF divalidasi sebelum nilai diproses.
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));

$nilai     = '';   // nilai mentah untuk ditampilkan kembali
$status    = $predikat = '';
$errorNilai = $errorUmum = '';

// Validasi nilai, tentukan predikat, lalu redirect agar refresh tidak mengulang POST.
if ($metode === 'POST') {
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf'], $token)) {
        $errorUmum = 'Sesi formulir kedaluwarsa. Silakan kirim ulang.';
    } else {
        $raw   = $_POST['nilai'] ?? '';
        $nilai = is_string($raw) ? mb_substr(trim($raw), 0, 6) : '';
        [$n, $errorNilai] = validasiNilai($raw);

        if ($errorNilai === null) {
            $errorNilai = '';
            try {
                // Struktur if-else untuk menentukan predikat dan status
                if ($n >= 85) {
                    $predikat = 'A';
                    $status = 'Lulus';
                } elseif ($n >= 70) {
                    $predikat = 'B';
                    $status = 'Lulus';
                } elseif ($n >= 60) {
                    $predikat = 'C';
                    $status = 'Lulus';
                } else {
                    $predikat = '×';
                    $status = 'Tidak Lulus';
                }
                // Post-Redirect-Get: refresh halaman tidak mengirim ulang form
                $_SESSION['flash'] = compact('nilai', 'status', 'predikat');
                $_SESSION['csrf']  = bin2hex(random_bytes(32));
                header('Location: ' . HALAMAN, true, 303);
                exit;
            } catch (Throwable $ex) {
                error_log('[tugas2] ' . $ex->getMessage());
                $status = $predikat = '';
                $errorUmum = 'Terjadi kesalahan saat memproses nilai. Silakan coba lagi.';
            }
        }
    }
} elseif (isset($_SESSION['flash'])) {
    // Tampilkan hasil sekali setelah redirect
    ['nilai' => $nilai, 'status' => $status, 'predikat' => $predikat] = $_SESSION['flash'];
    unset($_SESSION['flash']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tugas 2 - Kelulusan</title>
    <!-- Tailwind dan aset CSS/JavaScript bersama untuk tampilan serta popup. -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/style.css">
    <script src="assets/app.js" defer></script>
</head>
<body class="min-h-screen overflow-x-hidden bg-[#080d1b] text-slate-100 antialiased">
<!-- Latar dekoratif khusus halaman evaluasi. -->
<div aria-hidden="true" class="pointer-events-none fixed inset-0 overflow-hidden">
    <div class="absolute -left-32 -top-32 h-96 w-96 rounded-full bg-violet-600/20 blur-3xl"></div>
    <div class="absolute -right-32 top-1/3 h-96 w-96 rounded-full bg-fuchsia-500/10 blur-3xl"></div>
</div>
<main class="relative mx-auto max-w-6xl px-4 py-5 sm:px-6 sm:py-8">
    <!-- Navigasi kembali ke daftar seluruh tugas. -->
    <nav class="mb-7 flex items-center justify-between">
        <a href="index.php" class="inline-flex items-center gap-2 rounded-full border border-cyan-300/20 bg-gradient-to-r from-cyan-300/10 to-blue-300/10 px-3 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-cyan-100 shadow-lg shadow-cyan-950/20 sm:px-4 sm:text-xs sm:tracking-[0.18em]"><span class="h-2 w-2 rounded-full bg-cyan-300 shadow-sm shadow-cyan-200/70"></span>PEMROGRAMAN WEB II</a>
        <a href="index.php" class="inline-flex items-center gap-2 rounded-full border border-cyan-300/20 bg-gradient-to-r from-cyan-300/10 to-blue-300/10 px-3 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-cyan-100 shadow-lg shadow-cyan-950/20 transition hover:border-cyan-200/40 sm:px-4 sm:text-xs sm:tracking-[0.18em]">Semua tugas</a>
    </nav>
    <!-- Judul dan ringkasan tugas. -->
    <header class="relative overflow-hidden rounded-3xl border border-violet-300/20 bg-gradient-to-br from-violet-600 via-indigo-700 to-slate-900 px-5 py-7 shadow-2xl shadow-violet-950/30 sm:px-9 sm:py-9">
        <div aria-hidden="true" class="absolute -right-8 -top-14 h-56 w-56 rounded-full border-[28px] border-white/10 sm:h-72 sm:w-72"></div>
        <div class="relative flex items-end justify-between gap-4"><div><p class="mb-3 inline-flex rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.2em] text-violet-100">Tugas 02 <span class="mx-2 text-white/40">/</span> Evaluasi</p><h1 class="text-3xl font-black tracking-tight text-white sm:text-5xl">Cek Kelulusan</h1><p class="mt-2 max-w-lg text-sm leading-6 text-violet-100 sm:text-base">Masukkan nilai ujian dan temukan hasil evaluasimu.</p></div><span class="hidden text-6xl font-black text-white/15 sm:block">A+</span></div>
    </header>

    <!-- Form evaluasi, hasil kelulusan, dan panduan predikat. -->
    <section class="mt-5 grid gap-5 lg:grid-cols-12">
        <div class="overflow-hidden rounded-3xl bg-white text-slate-800 shadow-2xl shadow-black/20 lg:col-span-7">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-7"><p class="text-xs font-semibold uppercase tracking-widest text-violet-600">Evaluasi nilai</p><h2 class="mt-1 text-lg font-bold">Masukkan nilai ujian</h2></div>
            <div class="p-5 sm:p-7">
                <?php if ($errorUmum): ?>
                    <p role="alert" class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700"><?= e($errorUmum) ?></p>
                <?php endif; ?>

                <form method="POST" action="<?= HALAMAN ?>" autocomplete="off" class="space-y-4">
                    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700" for="nilai">Nilai ujian <span class="font-normal text-slate-400">(0&ndash;100)</span></label>
                        <div class="relative">
                            <input type="text" inputmode="decimal" maxlength="6" id="nilai" name="nilai" required placeholder="Contoh: 85"
                                   pattern="(\d+([.,]\d+)?|[.,]\d+)" title="Masukkan angka 0 sampai 100"
                                   value="<?= e($nilai) ?>"
                                   <?= $errorNilai ? 'aria-invalid="true" aria-describedby="nilai-error"' : '' ?>
                                   class="w-full rounded-xl border px-4 py-3.5 pr-16 text-lg font-semibold outline-none transition focus:bg-white focus:ring-4 <?= $errorNilai ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-red-100' : 'border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-100' ?>">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm font-medium text-slate-400">/ 100</span>
                        </div>
                        <?php if ($errorNilai): ?><p id="nilai-error" class="mt-2 text-xs font-medium text-red-600"><?= e($errorNilai) ?></p><?php endif; ?>
                    </div>
                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 px-5 py-3.5 font-bold text-white shadow-lg shadow-violet-600/20 transition hover:-translate-y-0.5 hover:from-violet-700 hover:to-indigo-700 focus:outline-none focus:ring-4 focus:ring-violet-200">Lihat hasil <span aria-hidden="true">&rarr;</span></button>
                </form>

                <?php if ($status): ?>
                    <div class="mt-6 flex items-center gap-4 rounded-2xl border p-5 <?= $status === 'Lulus' ? 'border-emerald-200 bg-emerald-50' : 'border-rose-200 bg-rose-50' ?>"><span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl text-2xl font-black <?= $status === 'Lulus' ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' ?>"><?= e($predikat) ?></span><div><p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Hasil evaluasi &middot; Nilai <?= e($nilai) ?></p><p class="mt-1 text-2xl font-black <?= $status === 'Lulus' ? 'text-emerald-700' : 'text-rose-700' ?>"><?= e($status) ?></p></div></div>
                <?php endif; ?>
            </div>
        </div>

        <aside class="rounded-3xl border border-white/10 bg-slate-900/80 p-5 shadow-xl shadow-black/20 sm:p-7 lg:col-span-5">
            <div class="mb-5"><p class="text-[10px] font-bold uppercase tracking-[0.2em] text-violet-300">Panduan penilaian</p><h2 class="mt-1 text-lg font-bold text-white">Batas predikat</h2></div>
            <div class="space-y-3">
                <div class="flex items-center justify-between rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3"><span class="flex items-center gap-3"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-400/15 font-black text-emerald-300">A</span><span class="text-sm font-medium text-slate-200">Sangat baik</span></span><span class="text-sm font-bold text-emerald-300">&ge; 85</span></div>
                <div class="flex items-center justify-between rounded-xl border border-blue-400/20 bg-blue-400/10 px-4 py-3"><span class="flex items-center gap-3"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-400/15 font-black text-blue-300">B</span><span class="text-sm font-medium text-slate-200">Baik</span></span><span class="text-sm font-bold text-blue-300">70&ndash;84</span></div>
                <div class="flex items-center justify-between rounded-xl border border-amber-400/20 bg-amber-400/10 px-4 py-3"><span class="flex items-center gap-3"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-400/15 font-black text-amber-300">C</span><span class="text-sm font-medium text-slate-200">Cukup</span></span><span class="text-sm font-bold text-amber-300">60&ndash;69</span></div>
                <div class="flex items-center justify-between rounded-xl border border-rose-400/20 bg-rose-400/10 px-4 py-3"><span class="flex items-center gap-3"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-400/15 font-black text-rose-300">&times;</span><span class="text-sm font-medium text-slate-200">Tidak lulus</span></span><span class="text-sm font-bold text-rose-300">&lt; 60</span></div>
            </div>
            <div class="mt-6 border-t border-white/10 pt-5"><h3 class="mb-3 text-sm font-bold text-white">Cara kerja</h3><ol class="space-y-2 text-xs leading-5 text-slate-400"><li>1. Token CSRF diperiksa, lalu nilai divalidasi: wajib diisi, hanya angka, dan berada pada rentang 0 sampai 100.</li><li>2. Kondisi <code class="rounded bg-white/10 px-1 text-violet-200">if-elseif-else</code> menentukan predikat dan status kelulusan.</li><li>3. Hasil disimpan sementara lalu halaman dialihkan (Post-Redirect-Get), sehingga refresh tidak mengirim ulang form.</li></ol></div>
        </aside>
    </section>
    <!-- Footer identitas mahasiswa. -->
    <footer class="mt-8 flex justify-center border-t border-white/10 py-6">
        <span class="inline-flex max-w-full items-center rounded-full border border-cyan-300/20 bg-gradient-to-r from-cyan-300/10 to-blue-300/10 px-3 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-cyan-100 shadow-lg shadow-cyan-950/20 sm:px-4 sm:text-xs sm:tracking-[0.18em]">Nama Mahasiswa_NIM_Kelas</span>
    </footer>
</main>
<?php
// Data popup dibaca oleh assets/app.js
// Popup menampilkan hasil kelulusan atau pesan validasi.
$popup = null;
if ($status) {
    $popup = [
        'type' => $status === 'Lulus' ? 'success' : 'error',
        'title' => $status,
        'message' => 'Hasil evaluasi nilai ujian.',
        'items' => [['Nilai', $nilai], ['Predikat', $predikat], ['Status', $status]],
    ];
} elseif ($errorUmum || $errorNilai) {
    $popup = ['type' => 'error', 'title' => 'Input Tidak Valid', 'message' => $errorUmum ?: $errorNilai];
}
if ($popup): ?>
<div id="popup-data" hidden data-popup="<?= e(json_encode($popup, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG)) ?>"></div>
<?php endif; ?>
</body>
</html>
