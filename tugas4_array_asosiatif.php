<?php
// Tugas 4 - Mengedit data mata kuliah dan nilai ujian.
declare(strict_types=1);

session_start();

const HALAMAN = 'tugas4_array_asosiatif.php';

/** Escape output HTML. */
function e(string $teks): string
{
    return htmlspecialchars($teks, ENT_QUOTES, 'UTF-8');
}

// Data awal sekaligus batas jumlah baris yang dapat diedit.
$dataAwal = [
    ['matkul' => 'Pemrograman Web II', 'nilai' => '88'],
    ['matkul' => 'Basis Data', 'nilai' => '76'],
    ['matkul' => 'Algoritma', 'nilai' => '82'],
    ['matkul' => 'Jaringan Komputer', 'nilai' => '70'],
    ['matkul' => 'Sistem Operasi', 'nilai' => '91'],
];

// Batasi akses pada GET untuk tampilan dan POST untuk mengirim perubahan.
$metode = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!in_array($metode, ['GET', 'POST'], true)) {
    http_response_code(405);
    header('Allow: GET, POST');
    exit('Metode request tidak diizinkan.');
}

// Token CSRF melindungi form pengeditan data nilai.
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
$baris = $dataAwal;
$errors = [];
$errorUmum = '';
$dataDiperbarui = false;

if ($metode === 'POST') {
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf'], $token)) {
        $errorUmum = 'Sesi formulir kedaluwarsa. Silakan muat ulang halaman.';
    } else {
        $matkulInput = $_POST['matkul'] ?? [];
        $nilaiInput = $_POST['nilai'] ?? [];

        foreach ($dataAwal as $i => $_) {
            $namaRaw = is_array($matkulInput) ? ($matkulInput[$i] ?? '') : '';
            $nilaiRaw = is_array($nilaiInput) ? ($nilaiInput[$i] ?? '') : '';
            $nama = is_string($namaRaw) ? trim($namaRaw) : '';
            $nilai = is_string($nilaiRaw) ? trim($nilaiRaw) : '';
            $baris[$i] = ['matkul' => $nama, 'nilai' => $nilai];

            if ($nama === '') {
                $errors[$i]['matkul'] = 'Nama mata kuliah wajib diisi.';
            } elseif (strlen($nama) > 240 || preg_match('/[\x00-\x1F\x7F]/', $nama)) {
                $errors[$i]['matkul'] = 'Nama mata kuliah maksimal 80 karakter.';
            }

            $nilaiNormal = str_replace(',', '.', $nilai);
            if ($nilai === '') {
                $errors[$i]['nilai'] = 'Nilai wajib diisi.';
            } elseif (strlen($nilai) > 6 || !preg_match('/^\d+(?:\.\d{1,2})?$/', $nilaiNormal)) {
                $errors[$i]['nilai'] = 'Masukkan angka 0 sampai 100 (maksimal 2 desimal).';
            } elseif ((float) $nilaiNormal < 0 || (float) $nilaiNormal > 100) {
                $errors[$i]['nilai'] = 'Nilai harus berada pada rentang 0 sampai 100.';
            } else {
                $baris[$i]['nilai'] = $nilaiNormal;
            }
        }

        // Nama mata kuliah harus unik karena menjadi key array asosiatif.
        $namaTerpakai = [];
        foreach ($baris as $i => $item) {
            if (!isset($errors[$i]['matkul']) && $item['matkul'] !== '') {
                $key = mb_strtolower($item['matkul'], 'UTF-8');
                if (isset($namaTerpakai[$key])) {
                    $errors[$i]['matkul'] = 'Nama mata kuliah tidak boleh duplikat.';
                    $errors[$namaTerpakai[$key]]['matkul'] = 'Nama mata kuliah tidak boleh duplikat.';
                } else {
                    $namaTerpakai[$key] = $i;
                }
            }
        }

        if (!$errors) {
            // PRG mencegah browser mengirim ulang perubahan ketika halaman di-refresh.
            $_SESSION['flash_nilai'] = $baris;
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            header('Location: ' . HALAMAN, true, 303);
            exit;
        }
    }
} elseif (isset($_SESSION['flash_nilai'])) {
    // Pulihkan nilai yang telah disimpan setelah redirect.
    $baris = $_SESSION['flash_nilai'];
    unset($_SESSION['flash_nilai']);
    $dataDiperbarui = true;
}

// Susun array asosiatif dengan mata kuliah sebagai key dan nilai sebagai value.
$nilaiUjian = [];
if (!$errors && !$errorUmum) {
    foreach ($baris as $item) {
        $nilaiUjian[$item['matkul']] = (float) $item['nilai'];
    }
}
$total = array_sum($nilaiUjian);
$rataRata = $nilaiUjian ? $total / count($nilaiUjian) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tugas 4 - Array Asosiatif</title>
    <!-- Tailwind dan aset CSS/JavaScript bersama untuk tampilan serta popup. -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/style.css">
    <script src="assets/app.js" defer></script>
</head>
<body class="min-h-screen overflow-x-hidden bg-[#080d1b] text-slate-100 antialiased">
<!-- Latar dekoratif khusus halaman rekap nilai. -->
<div aria-hidden="true" class="pointer-events-none fixed inset-0 overflow-hidden"><div class="absolute -left-32 -top-32 h-96 w-96 rounded-full bg-emerald-500/15 blur-3xl"></div><div class="absolute -right-32 top-1/3 h-96 w-96 rounded-full bg-teal-500/10 blur-3xl"></div></div>
<main class="relative mx-auto max-w-6xl px-4 py-5 sm:px-6 sm:py-8">
    <!-- Navigasi kembali ke daftar seluruh tugas. -->
    <nav class="mb-7 flex items-center justify-between">
        <a href="index.php" class="inline-flex items-center gap-2 rounded-full border border-cyan-300/20 bg-gradient-to-r from-cyan-300/10 to-blue-300/10 px-3 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-cyan-100 shadow-lg shadow-cyan-950/20 sm:px-4 sm:text-xs sm:tracking-[0.18em]"><span class="h-2 w-2 rounded-full bg-cyan-300 shadow-sm shadow-cyan-200/70"></span>PEMROGRAMAN WEB II</a>
        <a href="index.php" class="inline-flex items-center gap-2 rounded-full border border-cyan-300/20 bg-gradient-to-r from-cyan-300/10 to-blue-300/10 px-3 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-cyan-100 shadow-lg shadow-cyan-950/20 transition hover:border-cyan-200/40 sm:px-4 sm:text-xs sm:tracking-[0.18em]">Semua tugas</a>
    </nav>
    <!-- Judul dan ringkasan tugas. -->
    <header class="relative overflow-hidden rounded-3xl border border-emerald-300/20 bg-gradient-to-br from-emerald-600 via-teal-700 to-slate-900 px-5 py-7 shadow-2xl shadow-emerald-950/30 sm:px-9 sm:py-9">
        <div aria-hidden="true" class="absolute -right-8 -top-14 h-56 w-56 rounded-full border-[28px] border-white/10 sm:h-72 sm:w-72"></div>
        <div class="relative flex items-end justify-between gap-4"><div><p class="mb-3 inline-flex rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-100">Tugas 04 <span class="mx-2 text-white/40">/</span> Data nilai</p><h1 class="text-3xl font-black tracking-tight text-white sm:text-5xl">Nilai Ujian</h1><p class="mt-2 max-w-lg text-sm leading-6 text-emerald-100 sm:text-base">Edit mata kuliah dan nilai langsung, lalu lihat rata-ratanya.</p></div><span class="hidden text-6xl font-black text-white/15 sm:block">∑</span></div>
    </header>
    <!-- Tabel nilai, kartu rata-rata, dan penjelasan proses. -->
    <section class="mt-5 grid gap-5 lg:grid-cols-12">
        <div class="overflow-hidden rounded-3xl bg-white text-slate-800 shadow-2xl shadow-black/20 lg:col-span-7">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-7"><div><p class="text-xs font-semibold uppercase tracking-widest text-emerald-600">Edit rekap</p><h2 class="mt-1 text-lg font-bold">Daftar mata kuliah</h2></div><span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">5 data</span></div>
            <div class="p-4 sm:p-7">
                <?php if ($errorUmum): ?><p role="alert" class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700"><?= e($errorUmum) ?></p><?php endif; ?>
                <form method="POST" action="<?= HALAMAN ?>" class="space-y-4" autocomplete="off">
                    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="w-full min-w-[430px] text-left text-sm">
                            <thead class="bg-slate-50 text-[10px] uppercase tracking-[0.16em] text-slate-500"><tr class="border-b border-slate-200"><th scope="col" class="w-14 px-3 py-3 font-bold">No</th><th scope="col" class="px-3 py-3 font-bold">Mata Kuliah</th><th scope="col" class="w-28 px-3 py-3 font-bold">Nilai</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($baris as $i => $item): ?>
                                    <tr class="align-top transition hover:bg-emerald-50/40">
                                        <td class="px-3 py-3 text-slate-400"><?= $i + 1 ?></td>
                                        <td class="px-3 py-3">
                                            <input type="text" maxlength="80" required name="matkul[<?= $i ?>]" value="<?= e($item['matkul']) ?>" aria-label="Nama mata kuliah ke-<?= $i + 1 ?>"
                                                   <?= isset($errors[$i]['matkul']) ? 'aria-invalid="true" aria-describedby="matkul-error-' . $i . '"' : '' ?>
                                                   class="w-full rounded-lg border px-3 py-2 font-semibold text-slate-700 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 <?= isset($errors[$i]['matkul']) ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-white' ?>">
                                            <?php if (isset($errors[$i]['matkul'])): ?><p id="matkul-error-<?= $i ?>" class="mt-1 text-xs text-red-600"><?= e($errors[$i]['matkul']) ?></p><?php endif; ?>
                                        </td>
                                        <td class="px-3 py-3">
                                            <input type="text" inputmode="decimal" maxlength="6" required name="nilai[<?= $i ?>]" value="<?= e((string) $item['nilai']) ?>" aria-label="Nilai ujian ke-<?= $i + 1 ?>" placeholder="0–100"
                                                   pattern="\d+([.,]\d{1,2})?" title="Masukkan nilai 0 sampai 100, maksimal 2 desimal"
                                                   <?= isset($errors[$i]['nilai']) ? 'aria-invalid="true" aria-describedby="nilai-error-' . $i . '"' : '' ?>
                                                   class="w-full rounded-lg border px-3 py-2 text-center font-bold text-emerald-700 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 <?= isset($errors[$i]['nilai']) ? 'border-red-400 bg-red-50' : 'border-emerald-100 bg-emerald-50' ?>">
                                            <?php if (isset($errors[$i]['nilai'])): ?><p id="nilai-error-<?= $i ?>" class="mt-1 text-xs text-red-600"><?= e($errors[$i]['nilai']) ?></p><?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <?php if (!$errors && !$errorUmum): ?>
                                <tfoot><tr class="bg-emerald-50 font-bold text-emerald-900"><td colspan="2" class="px-3 py-3">Rata-rata</td><td class="px-3 py-3"><?= number_format($rataRata, 2, ',', '.') ?></td></tr></tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-3.5 font-bold text-white shadow-lg shadow-emerald-600/20 transition hover:-translate-y-0.5 hover:from-emerald-700 hover:to-teal-700 focus:outline-none focus:ring-4 focus:ring-emerald-200">Simpan dan hitung rata-rata <span aria-hidden="true">&rarr;</span></button>
                    <?php if ($errors): ?><p class="text-sm text-slate-500">Perbaiki isian yang ditandai merah, lalu kirim kembali formulir.</p><?php endif; ?>
                </form>
            </div>
        </div>
        <aside class="space-y-5 lg:col-span-5">
            <?php if (!$errors && !$errorUmum): ?>
                <div class="relative overflow-hidden rounded-3xl border border-emerald-300/20 bg-gradient-to-br from-emerald-500 to-teal-700 p-6 shadow-xl shadow-emerald-950/30 sm:p-7"><div aria-hidden="true" class="absolute -right-8 -top-8 h-32 w-32 rounded-full border-[18px] border-white/10"></div><p class="relative text-xs font-bold uppercase tracking-[0.2em] text-emerald-100">Nilai rata-rata</p><p class="relative mt-2 text-5xl font-black tracking-tight text-white"><?= number_format($rataRata, 2, ',', '.') ?></p><p class="relative mt-2 text-sm text-emerald-100">Dari <?= count($nilaiUjian) ?> data ujian</p></div>
            <?php endif; ?>
            <div class="rounded-3xl border border-white/10 bg-slate-900/80 p-5 shadow-xl shadow-black/20 sm:p-7"><div class="mb-5 flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/15 font-bold text-emerald-300">04</span><div><p class="text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-300">Di balik program</p><h2 class="font-bold text-white">Cara kerja</h2></div></div><ol class="space-y-3 text-sm leading-6 text-slate-400"><li><span class="mr-2 font-bold text-emerald-300">01</span>Ubah nama mata kuliah dan nilai pada kolom tabel.</li><li><span class="mr-2 font-bold text-emerald-300">02</span>Server memvalidasi nama unik dan nilai pada rentang 0 sampai 100.</li><li><span class="mr-2 font-bold text-emerald-300">03</span>Data disusun kembali menjadi array asosiatif dan rata-rata dihitung.</li></ol></div>
        </aside>
    </section>
    <!-- Footer identitas mahasiswa. -->
    <footer class="mt-8 flex justify-center border-t border-white/10 py-6">
        <span class="inline-flex max-w-full items-center rounded-full border border-cyan-300/20 bg-gradient-to-r from-cyan-300/10 to-blue-300/10 px-3 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-cyan-100 shadow-lg shadow-cyan-950/20 sm:px-4 sm:text-xs sm:tracking-[0.18em]">Nama Mahasiswa_NIM_Kelas</span>
    </footer>
</main>
<?php
// Data popup dibaca oleh assets/app.js untuk hasil atau pesan validasi.
if ($errorUmum || $errors) {
    $popup = [
        'type' => 'error',
        'title' => 'Periksa Data',
        'message' => $errorUmum ?: 'Perbaiki isian yang ditandai pada tabel.',
    ];
} else {
    $popup = [
        'type' => 'info',
        'title' => $dataDiperbarui ? 'Data Diperbarui' : 'Rekap Nilai',
        'message' => 'Rata-rata dari ' . count($nilaiUjian) . ' mata kuliah.',
        'items' => [
            ['Total nilai', number_format($total, 2, ',', '.')],
            ['Rata-rata', number_format($rataRata, 2, ',', '.')],
        ],
    ];
}
?>
<div id="popup-data" hidden data-popup="<?= e(json_encode($popup, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG)) ?>"></div>
</body>
</html>
