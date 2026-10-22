<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UTS Pemrograman Web II</title>
    <!-- Tailwind untuk utility class, serta aset kustom bersama semua halaman. -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/style.css">
    <script src="assets/app.js" defer></script>
</head>
<body class="min-h-screen overflow-x-hidden bg-[#080d1b] text-slate-100 antialiased">
<!-- Dekorasi latar tidak mengganggu interaksi dengan konten. -->
<div aria-hidden="true" class="pointer-events-none fixed inset-0 overflow-hidden">
    <div class="absolute -left-32 -top-32 h-96 w-96 rounded-full bg-blue-600/20 blur-3xl"></div>
    <div class="absolute -right-32 top-1/3 h-96 w-96 rounded-full bg-violet-600/15 blur-3xl"></div>
</div>
<main class="relative mx-auto max-w-6xl px-4 py-5 sm:px-6 sm:py-8">
    <!-- Navigasi identitas mata kuliah. -->
    <nav class="mb-8 flex items-center justify-between">
        <a href="index.php" class="inline-flex items-center gap-2 rounded-full border border-cyan-300/20 bg-gradient-to-r from-cyan-300/10 to-blue-300/10 px-3 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-cyan-100 shadow-lg shadow-cyan-950/20 sm:px-4 sm:text-xs sm:tracking-[0.18em]" aria-label="Pemrograman Web II">
            <span class="h-2 w-2 rounded-full bg-cyan-300 shadow-sm shadow-cyan-200/70"></span>PEMROGRAMAN WEB II
        </a>
    </nav>

    <!-- Hero memperkenalkan kumpulan modul tugas. -->
    <section class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-gradient-to-br from-indigo-600 via-blue-700 to-slate-900 px-6 py-8 shadow-2xl shadow-blue-950/30 sm:px-10 sm:py-12">
        <div aria-hidden="true" class="absolute -right-16 -top-24 h-72 w-72 rounded-full border-[36px] border-white/5 sm:h-96 sm:w-96"></div>
        <div aria-hidden="true" class="absolute bottom-0 right-24 h-40 w-40 rounded-full bg-cyan-400/20 blur-3xl"></div>
        <div class="relative max-w-2xl">
            <p class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-cyan-100"><span class="h-2 w-2 rounded-full bg-cyan-300"></span> Pemrograman Web II</p>
            <h1 class="text-3xl font-black leading-tight tracking-tight text-white sm:text-5xl">Kumpulan Tugas<br class="hidden sm:block"> <span class="text-cyan-200">Ujian Tengah Semester</span></h1>
            <p class="mt-4 max-w-xl text-sm leading-6 text-blue-100 sm:text-base">Pilih modul tugas untuk mencoba program dan melihat ringkasan cara kerjanya.</p>
        </div>
        <div class="relative mt-7 flex items-center gap-3 sm:absolute sm:bottom-10 sm:right-10 sm:mt-0 sm:flex-col sm:items-end">
            <span class="text-5xl font-black tracking-tighter text-white/90 sm:text-7xl">04</span>
            <span class="text-xs font-semibold uppercase tracking-[0.25em] text-cyan-100">Modul tersedia</span>
        </div>
    </section>

    <!-- Daftar navigasi modul dan identitas mahasiswa. -->
    <section class="mt-6 grid gap-5 lg:grid-cols-3">
        <div class="rounded-3xl border border-white/10 bg-white/[0.04] p-5 backdrop-blur sm:p-6 lg:col-span-2">
            <div class="mb-5 flex items-end justify-between">
                <div><p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-300">Workspace</p><h2 class="mt-1 text-xl font-bold text-white">Pilih Tugas</h2></div>
                <span class="text-xs text-slate-500">01 — 04</span>
            </div>
            <nav class="grid gap-3 sm:grid-cols-2" aria-label="Daftar tugas UTS">
                <a href="tugas1_kalkulator.php" class="group rounded-2xl border border-white/10 bg-slate-900/70 p-4 transition duration-200 hover:-translate-y-1 hover:border-blue-400/50 hover:bg-blue-950/40 hover:shadow-xl hover:shadow-blue-950/30 focus:outline-none focus:ring-2 focus:ring-cyan-400">
                    <span class="flex items-center justify-between"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/15 text-lg font-bold text-blue-300">±</span><span class="text-xs font-bold tracking-widest text-slate-500">01 / 04</span></span>
                    <span class="mt-5 block text-lg font-bold text-white">Kalkulator</span><span class="mt-1 block text-sm leading-5 text-slate-400">Operasi aritmetika dasar</span>
                    <span class="mt-4 inline-flex items-center gap-2 text-xs font-semibold text-blue-300">Buka modul <span class="transition group-hover:translate-x-1">&rarr;</span></span>
                </a>
                <a href="tugas2_kelulusan.php" class="group rounded-2xl border border-white/10 bg-slate-900/70 p-4 transition duration-200 hover:-translate-y-1 hover:border-violet-400/50 hover:bg-violet-950/30 hover:shadow-xl hover:shadow-violet-950/30 focus:outline-none focus:ring-2 focus:ring-violet-400">
                    <span class="flex items-center justify-between"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-500/15 text-lg font-bold text-violet-300">A</span><span class="text-xs font-bold tracking-widest text-slate-500">02 / 04</span></span>
                    <span class="mt-5 block text-lg font-bold text-white">Status Kelulusan</span><span class="mt-1 block text-sm leading-5 text-slate-400">Nilai ujian dan predikat</span>
                    <span class="mt-4 inline-flex items-center gap-2 text-xs font-semibold text-violet-300">Buka modul <span class="transition group-hover:translate-x-1">&rarr;</span></span>
                </a>
                <a href="tugas3_array_acak.php" class="group rounded-2xl border border-white/10 bg-slate-900/70 p-4 transition duration-200 hover:-translate-y-1 hover:border-amber-400/50 hover:bg-amber-950/20 hover:shadow-xl hover:shadow-amber-950/20 focus:outline-none focus:ring-2 focus:ring-amber-400">
                    <span class="flex items-center justify-between"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/15 text-lg font-bold text-amber-300">#</span><span class="text-xs font-bold tracking-widest text-slate-500">03 / 04</span></span>
                    <span class="mt-5 block text-lg font-bold text-white">Array Angka Acak</span><span class="mt-1 block text-sm leading-5 text-slate-400">Analisis ganjil, genap, dan rentang</span>
                    <span class="mt-4 inline-flex items-center gap-2 text-xs font-semibold text-amber-300">Buka modul <span class="transition group-hover:translate-x-1">&rarr;</span></span>
                </a>
                <a href="tugas4_array_asosiatif.php" class="group rounded-2xl border border-white/10 bg-slate-900/70 p-4 transition duration-200 hover:-translate-y-1 hover:border-emerald-400/50 hover:bg-emerald-950/20 hover:shadow-xl hover:shadow-emerald-950/20 focus:outline-none focus:ring-2 focus:ring-emerald-400">
                    <span class="flex items-center justify-between"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/15 text-lg font-bold text-emerald-300">∑</span><span class="text-xs font-bold tracking-widest text-slate-500">04 / 04</span></span>
                    <span class="mt-5 block text-lg font-bold text-white">Nilai Ujian</span><span class="mt-1 block text-sm leading-5 text-slate-400">Array asosiatif dan rata-rata</span>
                    <span class="mt-4 inline-flex items-center gap-2 text-xs font-semibold text-emerald-300">Buka modul <span class="transition group-hover:translate-x-1">&rarr;</span></span>
                </a>
            </nav>
        </div>

        <aside class="rounded-3xl border border-white/10 bg-gradient-to-b from-slate-800/90 to-slate-900/90 p-5 sm:p-6">
            <div class="mb-5 flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-400/10 text-cyan-300">✦</span><div><p class="text-xs uppercase tracking-widest text-slate-500">Profil</p><h2 class="font-bold text-white">Identitas Mahasiswa</h2></div></div>
            <dl class="space-y-4">
                <div class="border-b border-white/10 pb-3"><dt class="text-xs text-slate-500">Nama Mahasiswa</dt><dd class="mt-1 text-sm font-medium text-slate-200">Nama Mahasiswa</dd></div>
                <div class="grid grid-cols-2 gap-4 border-b border-white/10 pb-3"><div><dt class="text-xs text-slate-500">NIM</dt><dd class="mt-1 text-sm font-medium text-slate-200">NIM</dd></div><div><dt class="text-xs text-slate-500">Kelas</dt><dd class="mt-1 text-sm font-medium text-slate-200">Kelas</dd></div></div>
                <div><dt class="text-xs text-slate-500">Dosen Pengampu</dt><dd class="mt-1 text-sm font-medium text-slate-200">Dosen Pengampu</dd></div>
            </dl>
        </aside>
    </section>

    <!-- Footer identitas yang dapat diisi manual. -->
    <footer class="mt-8 flex justify-center border-t border-white/10 py-6">
        <span class="inline-flex max-w-full items-center rounded-full border border-cyan-300/20 bg-gradient-to-r from-cyan-300/10 to-blue-300/10 px-3 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-cyan-100 shadow-lg shadow-cyan-950/20 sm:px-4 sm:text-xs sm:tracking-[0.18em]">Nama Mahasiswa_NIM_Kelas</span>
    </footer>
</main>
</body>
</html>
