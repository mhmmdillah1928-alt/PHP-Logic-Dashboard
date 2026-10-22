<?php
// Tugas 1 - Kalkulator Sederhana
declare(strict_types=1);

session_start();

// Batas input dan nama halaman dipakai untuk validasi serta redirect form.
const BATAS_ANGKA = 1000000000000; // batas ±1 triliun agar hasil perkalian tidak meluap
const HALAMAN     = 'tugas1_kalkulator.php';

/** Escape output HTML. */
function e(string $teks): string
{
    return htmlspecialchars($teks, ENT_QUOTES, 'UTF-8');
}

/** Validasi satu input angka. Mengembalikan [float|null, pesan error|null]. */
function validasiAngka(mixed $raw, string $label): array
{
    if (!is_string($raw)) {
        return [null, "$label tidak valid."];
    }
    $raw = trim($raw);
    if ($raw === '') {
        return [null, "$label wajib diisi."];
    }
    if (strlen($raw) > 20) {
        return [null, "$label terlalu panjang (maksimal 20 karakter)."];
    }
    // Koma atau titik diterima sebagai desimal; huruf, spasi, dan notasi ilmiah (1e5) ditolak
    $normal = str_replace(',', '.', $raw);
    if (!preg_match('/^[+-]?(\d+(\.\d+)?|\.\d+)$/', $normal)) {
        return [null, "$label harus berupa angka, contoh: 12 atau 12,5."];
    }
    $nilai = (float) $normal;
    if (abs($nilai) > BATAS_ANGKA) {
        return [null, "$label melebihi batas ±1.000.000.000.000."];
    }
    return [$nilai, null];
}

/** Format angka gaya Indonesia, maksimal 6 desimal, tanpa nol di belakang. */
function formatAngka(float $n): string
{
    $s = rtrim(rtrim(number_format(round($n, 6), 6, ',', '.'), '0'), ',');
    return ($s === '-0' || $s === '') ? '0' : $s;
}

// Hanya izinkan GET untuk membuka halaman dan POST untuk mengirim form.
$metode = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!in_array($metode, ['GET', 'POST'], true)) {
    http_response_code(405);
    header('Allow: GET, POST');
    exit('Metode request tidak diizinkan.');
}

// Token sesi melindungi form dari pengiriman lintas situs.
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));

$fields    = ['angka1' => 'Angka pertama', 'angka2' => 'Angka kedua'];
$input     = ['angka1' => '', 'angka2' => ''];
$errors    = [];   // error per-field
$errorUmum = '';   // error non-field (CSRF, kegagalan hitung)
$hasil     = [];

// Validasi input, hitung operasi, dan gunakan PRG untuk mencegah pengiriman ulang.
if ($metode === 'POST') {
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf'], $token)) {
        $errorUmum = 'Sesi formulir kedaluwarsa. Silakan kirim ulang.';
    } else {
        $nilai = [];
        foreach ($fields as $key => $label) {
            $raw = $_POST[$key] ?? '';
            $input[$key] = is_string($raw) ? mb_substr(trim($raw), 0, 20) : '';
            [$nilai[$key], $pesan] = validasiAngka($raw, $label);
            if ($pesan !== null) {
                $errors[$key] = $pesan;
            }
        }

        if (!$errors) {
            try {
                [$a, $b] = [$nilai['angka1'], $nilai['angka2']];
                $hasil = [
                    'Penjumlahan' => ['teks' => formatAngka($a + $b), 'galat' => false],
                    'Pengurangan' => ['teks' => formatAngka($a - $b), 'galat' => false],
                    'Perkalian'   => ['teks' => formatAngka($a * $b), 'galat' => false],
                    // Pembagian dengan nol ditangani terpisah, tidak menghentikan operasi lain
                    'Pembagian'   => $b == 0.0
                        ? ['teks' => 'Tidak dapat dibagi dengan nol', 'galat' => true]
                        : ['teks' => formatAngka($a / $b), 'galat' => false],
                ];
                // Post-Redirect-Get: refresh halaman tidak mengirim ulang form
                $_SESSION['flash'] = ['input' => $input, 'hasil' => $hasil];
                $_SESSION['csrf']  = bin2hex(random_bytes(32));
                header('Location: ' . HALAMAN, true, 303);
                exit;
            } catch (Throwable $ex) {
                error_log('[tugas1] ' . $ex->getMessage());
                $hasil = [];
                $errorUmum = 'Terjadi kesalahan saat menghitung. Silakan coba lagi.';
            }
        }
    }
} elseif (isset($_SESSION['flash'])) {
    // Tampilkan hasil sekali setelah redirect
    ['input' => $input, 'hasil' => $hasil] = $_SESSION['flash'];
    unset($_SESSION['flash']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tugas 1 - Kalkulator</title>
    <!-- Tailwind dan aset CSS/JavaScript bersama untuk tampilan serta popup. -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/style.css">
    <script src="assets/app.js" defer></script>
</head>
<body class="min-h-screen overflow-x-hidden bg-[#080d1b] text-slate-100 antialiased">
<!-- Latar dekoratif khusus halaman kalkulator. -->
<div aria-hidden="true" class="pointer-events-none fixed inset-0 overflow-hidden">
    <div class="absolute -left-32 -top-32 h-96 w-96 rounded-full bg-blue-600/20 blur-3xl"></div>
    <div class="absolute -right-32 top-1/3 h-96 w-96 rounded-full bg-cyan-500/10 blur-3xl"></div>
</div>
<main class="relative mx-auto max-w-6xl px-4 py-5 sm:px-6 sm:py-8">
    <!-- Navigasi kembali ke daftar seluruh tugas. -->
    <nav class="mb-7 flex items-center justify-between">
        <a href="index.php" class="inline-flex items-center gap-2 rounded-full border border-cyan-300/20 bg-gradient-to-r from-cyan-300/10 to-blue-300/10 px-3 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-cyan-100 shadow-lg shadow-cyan-950/20 sm:px-4 sm:text-xs sm:tracking-[0.18em]">
            <span class="h-2 w-2 rounded-full bg-cyan-300 shadow-sm shadow-cyan-200/70"></span>PEMROGRAMAN WEB II
        </a>
        <a href="index.php" class="inline-flex items-center gap-2 rounded-full border border-cyan-300/20 bg-gradient-to-r from-cyan-300/10 to-blue-300/10 px-3 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-cyan-100 shadow-lg shadow-cyan-950/20 transition hover:border-cyan-200/40 sm:px-4 sm:text-xs sm:tracking-[0.18em]">Semua tugas</a>
    </nav>

    <!-- Judul dan ringkasan tugas. -->
    <header class="relative overflow-hidden rounded-3xl border border-blue-300/20 bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-900 px-5 py-7 shadow-2xl shadow-blue-950/30 sm:px-9 sm:py-9">
        <div aria-hidden="true" class="absolute -right-8 -top-14 h-56 w-56 rounded-full border-[28px] border-white/10 sm:h-72 sm:w-72"></div>
        <div class="relative flex items-end justify-between gap-4">
            <div><p class="mb-3 inline-flex rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.2em] text-blue-100">Tugas 01 <span class="mx-2 text-white/40">/</span> Aritmetika</p><h1 class="text-3xl font-black tracking-tight text-white sm:text-5xl">Kalkulator</h1><p class="mt-2 max-w-lg text-sm leading-6 text-blue-100 sm:text-base">Hitung empat operasi dasar dengan cepat dan mudah.</p></div>
            <span class="hidden text-6xl font-black text-white/15 sm:block">±</span>
        </div>
    </header>

    <!-- Form kalkulator, hasil operasi, dan ringkasan alur program. -->
    <section class="mt-5 grid gap-5 lg:grid-cols-12">
        <div class="overflow-hidden rounded-3xl bg-white text-slate-800 shadow-2xl shadow-black/20 lg:col-span-7">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-7"><p class="text-xs font-semibold uppercase tracking-widest text-blue-600">Input angka</p><h2 class="mt-1 text-lg font-bold">Masukkan dua nilai</h2></div>
            <div class="p-5 sm:p-7">
                <?php if ($errorUmum): ?>
                    <p role="alert" class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700"><?= e($errorUmum) ?></p>
                <?php endif; ?>

                <form method="POST" action="<?= HALAMAN ?>" autocomplete="off" class="grid gap-4 sm:grid-cols-2">
                    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                    <?php foreach ($fields as $key => $label): $err = $errors[$key] ?? null; ?>
                        <div class="min-w-0">
                            <label class="mb-2 block text-sm font-semibold text-slate-700" for="<?= $key ?>"><?= $label ?></label>
                            <input type="text" inputmode="decimal" maxlength="20" id="<?= $key ?>" name="<?= $key ?>" required placeholder="Contoh: 12,5"
                                   pattern="[+\-]?(\d+([.,]\d+)?|[.,]\d+)" title="Masukkan angka, contoh: 12 atau 12,5"
                                   value="<?= e($input[$key]) ?>"
                                   <?= $err ? 'aria-invalid="true" aria-describedby="' . $key . '-error"' : '' ?>
                                   class="w-full rounded-xl border px-4 py-3 text-base outline-none transition focus:bg-white focus:ring-4 <?= $err ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-red-100' : 'border-slate-200 bg-slate-50 focus:border-blue-500 focus:ring-blue-100' ?>">
                            <?php if ($err): ?><p id="<?= $key ?>-error" class="mt-2 text-xs font-medium text-red-600"><?= e($err) ?></p><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit" class="mt-1 flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3.5 font-bold text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-4 focus:ring-blue-200 sm:col-span-2">Hitung sekarang <span aria-hidden="true">&rarr;</span></button>
                </form>

                <?php if ($hasil): ?>
                    <div class="mt-7"><h3 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400">Hasil &middot; <?= e(formatAngka((float) str_replace(',', '.', $input['angka1']))) ?> dan <?= e(formatAngka((float) str_replace(',', '.', $input['angka2']))) ?></h3><div class="overflow-hidden rounded-xl border border-slate-200"><table class="w-full text-left text-sm"><tbody class="divide-y divide-slate-100"><?php foreach ($hasil as $operasi => $baris): ?><tr><th scope="row" class="w-1/2 bg-slate-50 px-4 py-3 font-medium text-slate-600"><?= e($operasi) ?></th><td class="break-words px-4 py-3 <?= $baris['galat'] ? 'text-sm font-semibold text-red-600' : 'font-bold text-slate-900' ?>"><?= e($baris['teks']) ?></td></tr><?php endforeach; ?></tbody></table></div></div>
                <?php endif; ?>
            </div>
        </div>

        <aside class="rounded-3xl border border-white/10 bg-slate-900/80 p-5 shadow-xl shadow-black/20 sm:p-7 lg:col-span-5">
            <div class="mb-5 flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/15 font-bold text-blue-300">01</span><div><p class="text-[10px] font-bold uppercase tracking-[0.2em] text-blue-300">Di balik program</p><h2 class="font-bold text-white">Alur Kerja</h2></div></div>
            <ol class="space-y-4 text-sm leading-6 text-slate-400">
                <li class="flex gap-3"><span class="font-bold text-blue-300">01</span><span>Form mengirim dua angka menggunakan metode <code class="rounded bg-white/10 px-1.5 py-0.5 text-xs text-blue-200">POST</code>.</span></li>
                <li class="flex gap-3"><span class="font-bold text-blue-300">02</span><span>Token CSRF diperiksa, lalu tiap input divalidasi (wajib, format angka, batas nilai) dan error ditampilkan per kolom.</span></li>
                <li class="flex gap-3"><span class="font-bold text-blue-300">03</span><span>Empat operasi dihitung di dalam <code class="rounded bg-white/10 px-1.5 py-0.5 text-xs text-blue-200">try/catch</code>; pembagian dengan nol menampilkan pesan tanpa mengganggu hasil lain.</span></li>
                <li class="flex gap-3"><span class="font-bold text-blue-300">04</span><span>Hasil disimpan sementara di session lalu halaman dialihkan (Post-Redirect-Get), sehingga refresh tidak mengirim ulang form.</span></li>
            </ol>
            <div class="mt-6 rounded-2xl border border-blue-400/15 bg-blue-500/10 p-4"><p class="text-xs font-semibold text-blue-200">Tips</p><p class="mt-1 text-xs leading-5 text-slate-400">Desimal boleh memakai koma atau titik (12,5 atau 12.5). Batas nilai &plusmn;1.000.000.000.000.</p></div>
        </aside>
    </section>

    <!-- Footer identitas mahasiswa. -->
    <footer class="mt-8 flex justify-center border-t border-white/10 py-6">
        <span class="inline-flex max-w-full items-center rounded-full border border-cyan-300/20 bg-gradient-to-r from-cyan-300/10 to-blue-300/10 px-3 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-cyan-100 shadow-lg shadow-cyan-950/20 sm:px-4 sm:text-xs sm:tracking-[0.18em]">Nama Mahasiswa_NIM_Kelas</span>
    </footer>
</main>
<?php
// Data popup dibaca oleh assets/app.js
// Kirim hasil atau validasi gagal sebagai payload JSON yang di-escape.
$popup = null;
if ($hasil) {
    $items = [];
    foreach ($hasil as $operasi => $baris) {
        $items[] = [$operasi, $baris['teks']];
    }
    $popup = ['type' => 'success', 'title' => 'Perhitungan Selesai', 'message' => 'Hasil dari kedua angka:', 'items' => $items];
} elseif ($errorUmum || $errors) {
    $popup = ['type' => 'error', 'title' => 'Input Tidak Valid', 'message' => $errorUmum ?: 'Periksa kembali isian yang ditandai merah.', 'items' => array_map(null, array_map(fn($k) => $fields[$k], array_keys($errors)), array_values($errors))];
}
if ($popup): ?>
<div id="popup-data" hidden data-popup="<?= e(json_encode($popup, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG)) ?>"></div>
<?php endif; ?>
</body>
</html>
