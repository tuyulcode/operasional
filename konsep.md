# Konsep Fitur Tagihan Air & Rekapan — e-Operasional

## Konteks
Aplikasi e-Operasional (Laravel) sudah punya menu Data Air dengan submenu:
Area, Titik Meter, PPN. Satu Area (misal "Puncak", "Hotel") punya beberapa
Titik Meter di bawahnya (misal Hotel punya "Barak 1", "Barak 2", "Wisma").

Yang akan dibangun: menu transaksi "Tagihan Air" (input meteran bulanan)
dan halaman "Rekapan" (laporan hasil hitung, read-only).

## Cara hitung (acuan utama, jangan diubah logikanya)
- Pemakaian = Meter Ini − Meter Lalu
- Pemakaian Terkoreksi = Pemakaian × Meter Faktor (default 1)
- Jumlah (Rp) = Pemakaian Terkoreksi × Tarif/m3

## Tahap 1 — Riset struktur project (tidak boleh nulis kode)
Cek migration, model, controller, route, dan view yang sudah ada untuk
Area, Titik Meter, PPN. Ringkas ke saya: kolom-kolom yang sudah ada dan
relasinya. Berhenti di sini dan tunggu konfirmasi saya sebelum lanjut Tahap 2.

## Tahap 2 — Migration & model "tagihan_air"
Kolom: titik_meter_id, periode, meter_lalu, meter_ini, meter_faktor (default 1),
tarif, pemakaian, jumlah, foto (nullable). Relasi belongsTo ke TitikMeter,
dan hasMany di model TitikMeter. Hanya migration + model, jangan buat
controller/view. Berhenti, tunggu konfirmasi.

## Tahap 3 — CRUD input "Tagihan Air"
Controller, route, view mengikuti gaya halaman "Data Area" yang sudah ada.
Form: pilih Area -> Titik Meter ter-filter otomatis -> pilih Periode ->
Meter Lalu auto-terisi dari transaksi periode sebelumnya di titik meter yang
sama -> input Meter Ini, Meter Faktor, Tarif (auto dari master tapi bisa
diubah) -> Pemakaian & Jumlah dihitung otomatis. List dengan filter Area
dan Bulan/Tahun. Berhenti, tunggu konfirmasi.

## Tahap 4 — Halaman "Rekapan"
Read-only. Filter Bulan/Tahun (wajib) + Area (opsional). Tampilkan per Area:
daftar titik meter + meter lalu/ini/pemakaian/tarif/jumlah, subtotal per Area,
grand total semua Area. Tombol export Excel/PDF. Ikuti layout UI yang sudah
ada.

## Instruksi eksekusi
Baca seluruh file ini dulu sampai selesai supaya paham alur besarnya.
Kerjakan HANYA Tahap 1 sekarang. Setelah selesai, laporkan hasilnya ke saya
dan BERHENTI — jangan lanjut ke tahap berikutnya sebelum saya konfirmasi.