<?php
// Tugas 3 - Mengedit atau mengacak 10 angka dan menampilkan statistiknya.
declare(strict_types=1);

session_start();

const HALAMAN = 'tugas3_array_acak.php';

/** Escape output HTML. */
function e(string $teks): string
{
    return htmlspecialchars($teks, ENT_QUOTES, 'UTF-8');
}

// Batasi akses pada GET untuk tampilan dan POST untuk mengirim angka.
$metode = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!in_array($metode, ['GET', 'POST'], true)) {
    http_response_code(405);
    header('Allow: GET, POST');
    exit('Metode request tidak diizinkan.');
}

// Token CSRF melindungi form pengubahan angka.
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
$errors = [];
$errorUmum = '';
$angka = array_fill(0, 10, '');
$angkaManual = false;

if ($metode === 'POST') {
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf'], $token)) {
        $errorUmum = 'Sesi formulir kedaluwarsa. Silakan muat ulang halaman.';
    } else {
        $input = $_POST['angka'] ?? [];
        for ($i = 0; $i < 10; $i++) {
            $raw = is_array($input) ? ($input[$i] ?? '') : '';
            $teks = is_string($raw) ? trim($raw) : '';
            $angka[$i] = $teks;

            if ($teks === '') {
                $errors[$i] = 'Angka wajib diisi.';
            } elseif (!preg_match('/^\d{1,3}$/', $teks)) {
                $errors[$i] = 'Masukkan bilangan bulat dari 1 sampai 100.';
            } elseif ((int) $teks < 1 || (int) $teks > 100) {
                $errors[$i] = 'Angka harus berada pada rentang 1 sampai 100.';
            } else {
                $angka[$i] = (int) $teks;
            }
        }

        if (!$errors) {
            // PRG mencegah browser mengirim ulang form saat halaman di-refresh.
            $_SESSION['flash_angka'] = $angka;
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            header('Location: ' . HALAMAN, true, 303);
            exit;
        }
    }
} elseif (isset($_GET['acak'])) {
    // Parameter acak selalu menghasilkan set baru, meskipun ada hasil sementara di sesi.
    unset($_SESSION['flash_angka']);
    $angka = array_map(static fn(): int => random_int(1, 100), range(1, 10));
} elseif (isset($_SESSION['flash_angka'])) {
    // Ambil angka hasil pengeditan setelah redirect.
    $angka = $_SESSION['flash_angka'];
    unset($_SESSION['flash_angka']);
    $angkaManual = true;
} else {
    // Saat pertama dibuka, isi halaman dengan 10 angka acak.
    $angka = array_map(static fn(): int => random_int(1, 100), range(1, 10));
}

// Hitung statistik hanya jika seluruh input valid.
$statistikValid = !$errors && !$errorUmum;
if ($statistikValid) {
    $angka = array_map('intval', $angka);
    $ganjil = array_values(array_filter($angka, static fn(int $n): bool => $n % 2 !== 0));
    $genap  = array_values(array_filter($angka, static fn(int $n): bool => $n % 2 === 0));
    $ganjilTerkecil = $ganjil ? min($ganjil) : null;
    $genapTerbesar  = $genap ? max($genap) : null;
    $tertinggi = max($angka);
    $terendah  = min($angka);
    $selisih   = $tertinggi - $terendah;
} else {
    $ganjilTerkecil = $genapTerbesar = $tertinggi = $terendah = $selisih = null;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tugas 3 - Array Acak</title>
    <!-- Tailwind dan aset CSS/JavaScript bersama untuk tampilan serta popup. -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/style.css">
    <script src="assets/app.js" defer></script>
</head>
<body class="min-h-screen overflow-x-hidden bg-[#080d1b] text-slate-100 antialiased">
<!-- Latar dekoratif khusus halaman array acak. -->
<div aria-hidden="true" class="pointer-events-none fixed inset-0 overflow-hidden"><div class="absolute -left-32 -top-32 h-96 w-96 rounded-full bg-amber-500/15 blur-3xl"></div><div class="absolute -right-32 top-1/3 h-96 w-96 rounded-full bg-orange-500/10 blur-3xl"></div></div>
<main class="relative mx-auto max-w-6xl px-4 py-5 sm:px-6 sm:py-8">
    <!-- Navigasi kembali ke daftar seluruh tugas. -->
    <nav class="mb-7 flex items-center justify-between">
        <a href="index.php" class="inline-flex items-center gap-2 rounded-full border border-cyan-300/20 bg-gradient-to-r from-cyan-300/10 to-blue-300/10 px-3 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-cyan-100 shadow-lg shadow-cyan-950/20 sm:px-4 sm:text-xs sm:tracking-[0.18em]"><span class="h-2 w-2 rounded-full bg-cyan-300 shadow-sm shadow-cyan-200/70"></span>PEMROGRAMAN WEB II</a>
        <a href="index.php" class="inline-flex items-center gap-2 rounded-full border border-cyan-300/20 bg-gradient-to-r from-cyan-300/10 to-blue-300/10 px-3 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-cyan-100 shadow-lg shadow-cyan-950/20 transition hover:border-cyan-200/40 sm:px-4 sm:text-xs sm:tracking-[0.18em]">Semua tugas</a>
    </nav>
    <!-- Judul dan ringkasan tugas. -->
    <header class="relative overflow-hidden rounded-3xl border border-amber-300/20 bg-gradient-to-br from-amber-500 via-orange-600 to-rose-900 px-5 py-7 shadow-2xl shadow-orange-950/30 sm:px-9 sm:py-9">
        <div aria-hidden="true" class="absolute -right-8 -top-14 h-56 w-56 rounded-full border-[28px] border-white/10 sm:h-72 sm:w-72"></div>
        <div class="relative flex items-end justify-between gap-4"><div><p class="mb-3 inline-flex rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.2em] text-amber-100">Tugas 03 <span class="mx-2 text-white/40">/</span> Array</p><h1 class="text-3xl font-black tracking-tight text-white sm:text-5xl">Angka Acak</h1><p class="mt-2 max-w-lg text-sm leading-6 text-amber-100 sm:text-base">Ubah sepuluh angka secara manual, atau buat set acak baru.</p></div><span class="hidden text-6xl font-black text-white/15 sm:block">#</span></div>
    </header>
    <!-- Data angka acak, hasil analisis, dan penjelasan proses. -->
    <section class="mt-5 grid gap-5 lg:grid-cols-12">
        <div class="overflow-hidden rounded-3xl bg-white text-slate-800 shadow-2xl shadow-black/20 lg:col-span-7">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-7"><div><p class="text-xs font-semibold uppercase tracking-widest text-amber-600">Edit angka</p><h2 class="mt-1 text-lg font-bold">Atur 10 angka</h2></div><span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">1–100</span></div>
            <div class="p-5 sm:p-7">
                <?php if ($errorUmum): ?><p role="alert" class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700"><?= e($errorUmum) ?></p><?php endif; ?>
                <form method="POST" action="<?= HALAMAN ?>" class="space-y-4" autocomplete="off">
                    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                        <?php foreach ($angka as $i => $n): $error = $errors[$i] ?? null; ?>
                            <div>
                                <label for="angka-<?= $i ?>" class="mb-1.5 block text-xs font-semibold text-slate-500">Angka <?= $i + 1 ?></label>
                                <input type="number" min="1" max="100" step="1" required
                                       id="angka-<?= $i ?>" name="angka[<?= $i ?>]" value="<?= e((string) $n) ?>"
                                       <?= $error ? 'aria-invalid="true" aria-describedby="angka-error-' . $i . '"' : '' ?>
                                       class="w-full rounded-xl border px-3 py-3 text-center text-lg font-black outline-none transition focus:ring-4 <?= $error ? 'border-red-400 bg-red-50 text-red-700 focus:ring-red-100' : (((int) $n) % 2 === 0 ? 'border-blue-100 bg-blue-50 text-blue-700 focus:border-blue-400 focus:ring-blue-100' : 'border-amber-100 bg-amber-50 text-amber-700 focus:border-amber-400 focus:ring-amber-100') ?>">
                                <?php if ($error): ?><p id="angka-error-<?= $i ?>" class="mt-1 text-[10px] leading-4 text-red-600"><?= e($error) ?></p><?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="flex items-center gap-4 text-xs font-medium text-slate-500"><span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>Genap</span><span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>Ganjil</span></div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 px-5 py-3.5 font-bold text-white shadow-lg shadow-orange-500/20 transition hover:-translate-y-0.5 hover:from-amber-600 hover:to-orange-700 focus:outline-none focus:ring-4 focus:ring-amber-200">Hitung angka <span aria-hidden="true">&rarr;</span></button>
                        <a href="<?= HALAMAN ?>?acak=1" class="flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-5 py-3.5 font-bold text-slate-700 transition hover:border-amber-300 hover:bg-amber-50 focus:outline-none focus:ring-4 focus:ring-amber-100"><span aria-hidden="true">&#8635;</span> Acak angka</a>
                    </div>
                </form>
                <?php if ($statistikValid): ?>
                    <div class="mt-7 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl border border-amber-100 bg-amber-50/70 p-4"><p class="text-xs font-semibold leading-5 text-amber-700">Ganjil terkecil</p><p class="mt-2 text-3xl font-black text-slate-900"><?= $ganjilTerkecil ?? '—' ?></p></div>
                        <div class="rounded-2xl border border-blue-100 bg-blue-50/70 p-4"><p class="text-xs font-semibold leading-5 text-blue-700">Genap terbesar</p><p class="mt-2 text-3xl font-black text-slate-900"><?= $genapTerbesar ?? '—' ?></p></div>
                        <div class="rounded-2xl border border-violet-100 bg-violet-50/70 p-4"><p class="text-xs font-semibold leading-5 text-violet-700">Rentang nilai</p><p class="mt-2 text-3xl font-black text-slate-900"><?= $selisih ?></p><p class="mt-1 text-[10px] text-slate-500"><?= $terendah ?> sampai <?= $tertinggi ?></p></div>
                    </div>
                <?php else: ?>
                    <p class="mt-5 rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-500">Perbaiki angka yang ditandai untuk melihat hasil perhitungan.</p>
                <?php endif; ?>
            </div>
        </div>
        <aside class="rounded-3xl border border-white/10 bg-slate-900/80 p-5 shadow-xl shadow-black/20 sm:p-7 lg:col-span-5">
            <div class="mb-5 flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/15 font-bold text-amber-300">03</span><div><p class="text-[10px] font-bold uppercase tracking-[0.2em] text-amber-300">Di balik program</p><h2 class="font-bold text-white">Cara kerja</h2></div></div>
            <ol class="space-y-4 text-sm leading-6 text-slate-400"><li class="flex gap-3"><span class="font-bold text-amber-300">01</span><span>Sepuluh angka awal dibuat secara acak dan dapat diganti langsung di kolom input.</span></li><li class="flex gap-3"><span class="font-bold text-amber-300">02</span><span>Setiap kolom menerima bilangan bulat dari 1 sampai 100; data diperiksa kembali di server.</span></li><li class="flex gap-3"><span class="font-bold text-amber-300">03</span><span><code class="rounded bg-white/10 px-1.5 py-0.5 text-xs text-amber-200">array_filter()</code> memisahkan angka genap dan ganjil.</span></li><li class="flex gap-3"><span class="font-bold text-amber-300">04</span><span><code class="rounded bg-white/10 px-1.5 py-0.5 text-xs text-amber-200">min()</code> dan <code class="rounded bg-white/10 px-1.5 py-0.5 text-xs text-amber-200">max()</code> digunakan untuk menghitung statistik.</span></li></ol>
            <div class="mt-6 rounded-2xl border border-amber-400/15 bg-amber-500/10 p-4"><p class="text-xs font-semibold text-amber-200">Catatan</p><p class="mt-1 text-xs leading-5 text-slate-400">Tekan “Hitung angka” untuk menyimpan perubahan, atau “Acak angka” untuk mengganti semua nilai secara otomatis.</p></div>
        </aside>
    </section>
    <!-- Footer identitas mahasiswa. -->
    <footer class="mt-8 flex justify-center border-t border-white/10 py-6">
        <span class="inline-flex max-w-full items-center rounded-full border border-cyan-300/20 bg-gradient-to-r from-cyan-300/10 to-blue-300/10 px-3 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-cyan-100 shadow-lg shadow-cyan-950/20 sm:px-4 sm:text-xs sm:tracking-[0.18em]">Nama Mahasiswa_NIM_Kelas</span>
    </footer>
</main>
<?php
// Data popup dikirim ke JavaScript bersama hasil atau pesan validasi.
if ($errorUmum || $errors) {
    $popup = [
        'type' => 'error',
        'title' => 'Periksa Angka',
        'message' => $errorUmum ?: 'Pastikan setiap angka berada pada rentang 1 sampai 100.',
    ];
} else {
    $popup = [
        'type' => 'info',
        'title' => $angkaManual ? 'Angka Diperbarui' : 'Array Angka',
        'message' => 'Angka: ' . implode(', ', $angka),
        'items' => [
            ['Ganjil terkecil', $ganjilTerkecil !== null ? (string) $ganjilTerkecil : '-'],
            ['Genap terbesar', $genapTerbesar !== null ? (string) $genapTerbesar : '-'],
            ['Selisih max - min', (string) $selisih],
        ],
    ];
}
?>
<div id="popup-data" hidden data-popup="<?= e(json_encode($popup, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG)) ?>"></div>
</body>
</html>
