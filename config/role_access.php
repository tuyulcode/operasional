<?php


return [

    'always_allowed' => [
        'dashboard',
        'logout',
        'profile.*',
    ],

    'petugas' => [
        // Petugas boleh akses semua menu seperti biasa...
        'allow' => ['*'],
        // ...KECUALI semua aksi hapus (route yang namanya diakhiri .destroy).
        'deny' => [
            '*.destroy',
        ],
    ],

    'lingkungan' => [
        // Lingkungan hanya boleh lihat rekapan BBM, rekapan Tagihan Air, dan master data
        // Data Air (Nama Pengguna & Titik Meter). Tidak ada tambah/edit/hapus (read-only).
        'allow' => [
            'tagihan-air.index',      // Halaman Tagihan Air (dia cuma akan lihat tab Rekapan-nya di view)
            'rekapan.*',               // rekapan.index / rekapan.excel / rekapan.pdf (export rekap tagihan air)
            'pemakaian-bbm.rekapan',        // Tab Rekapan BBM & Consumable
            'pemakaian-bbm.export-excel',   // Export excel rekap BBM
            'pemakaian-bbm.export-pdf',     // Export pdf rekap BBM
            'area.index',               // Master data Data Air: Nama Pengguna (read-only)
            'titik-meter.index',        // Master data Data Air: Titik Meter (read-only)
        ],
        'deny' => [],
    ],

];