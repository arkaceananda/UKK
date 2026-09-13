<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class GenerateBiayaDocx extends Command
{
    protected $signature = 'docx:biaya {--output=docs/Perkiraan-Biaya-BurjoOrder.docx}';

    protected $description = 'Generate formal .docx Perkiraan Biaya BurjoOrder dari markdown';

    public function handle(): int
    {
        $output = $this->option('output');
        $outPath = base_path($output);

        $phpWord = new PhpWord;
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(10.5);

        // Styles
        $phpWord->addTitleStyle(1, ['name' => 'Cambria', 'size' => 16, 'color' => '1F2430', 'bold' => true], ['spaceAfter' => 120, 'keepNext' => true, 'spacing' => 0]);
        $phpWord->addTitleStyle(2, ['name' => 'Cambria', 'size' => 13, 'color' => '2A3040', 'bold' => true], ['spaceBefore' => 200, 'spaceAfter' => 100]);
        $phpWord->addTitleStyle(3, ['name' => 'Calibri', 'size' => 11, 'color' => '3A4155', 'bold' => true, 'italic' => true], ['spaceBefore' => 160, 'spaceAfter' => 80]);
        $phpWord->addTitleStyle(4, ['name' => 'Calibri', 'size' => 10.5, 'color' => '3A4155', 'bold' => true], ['spaceBefore' => 120, 'spaceAfter' => 60]);

        $sectionStyle = ['marginTop' => 1800, 'marginBottom' => 1000, 'marginLeft' => 1700, 'marginRight' => 1700, 'headerHeight' => 400, 'footerHeight' => 400];
        $headerFooterStyle = ['marginTop' => 200, 'marginBottom' => 200];

        // Helper closures
        $addPara = function ($section, $text, $style = [], $pStyle = []) {
            $section->addText($text, $style, $pStyle);
        };
        $addBullet = function ($section, $text, $boldPrefix = null) {
            $run = $section->addListItem($text, 0, null, null, ['spaceAfter' => 40]);
        };

        $tableBase = ['borderSize' => 4, 'borderColor' => 'B0B7C8', 'cellMargin' => 60, 'alignment' => 'center'];
        $headCell = ['bgColor' => '1F2430', 'valign' => 'center'];
        $headFont = ['name' => 'Calibri', 'size' => 8.5, 'color' => 'FFFFFF', 'bold' => true];
        $cellFont = ['name' => 'Calibri', 'size' => 8.5, 'color' => '1F2430'];
        $cellFontBold = ['name' => 'Calibri', 'size' => 8.5, 'color' => '1F2430', 'bold' => true];
        $cellMono = ['name' => 'Consolas', 'size' => 8, 'color' => '1F2430'];
        $smallNote = ['name' => 'Calibri', 'size' => 8, 'color' => '5F6779', 'italic' => true];

        // ===== COVER SECTION =====
        $cover = $phpWord->addSection(array_merge($sectionStyle, ['marginTop' => 2600]));
        $cover->addText('PERKIRAAN BIAYA APLIKASI', ['name' => 'Cambria', 'size' => 11, 'color' => 'E2A33D', 'bold' => true], ['alignment' => 'center', 'spaceAfter' => 60]);
        $cover->addText('BurjoOrder', ['name' => 'Cambria', 'size' => 30, 'color' => '0A0C11', 'bold' => true], ['alignment' => 'center', 'spaceAfter' => 80]);
        $cover->addText('Cafe Order Management System', ['name' => 'Calibri', 'size' => 11, 'color' => '5F6779'], ['alignment' => 'center', 'spaceAfter' => 240]);
        $cover->addText('Versi “burjo baru buka” — hemat, UKM/UMI', ['name' => 'Calibri', 'size' => 10.5, 'color' => '3A4155', 'italic' => true], ['alignment' => 'center', 'spaceAfter' => 400]);
        $cover->addText('Fokus pada biaya operasional. Semua angka Agustus 2026, termasuk PPN 11% (diperiksa vendor).', ['name' => 'Calibri', 'size' => 8.5, 'color' => '8891A3'], ['alignment' => 'center', 'spaceAfter' => 400]);
        $box = $cover->addTable(['borderSize' => 6, 'borderColor' => 'E2A33D', 'cellMargin' => 120, 'alignment' => 'center']);
        $box->addRow();
        $c = $box->addCell(9000, ['bgColor' => 'FFF8E6', 'valign' => 'center']);
        $c->addText('Skenario: burjo dekat kampus (reservasi 10–100 orang) · 25 meja · ±71 menu · Rp15.000/menu', ['name' => 'Calibri', 'size' => 8.5, 'color' => '1F2430', 'bold' => true], ['alignment' => 'center']);
        $c->addText('Volume: sepi 100 / sedang 250 / ramai 400 tx/hari · QRIS 60–70% · Tunai Rp0', ['name' => 'Calibri', 'size' => 8, 'color' => '5F6779'], ['alignment' => 'center']);
        $cover->addTextBreak(2);
        $cover->addText('Agustus 2026  ·  Dokumen Perancangan Biaya  ·  BurjoOrder', ['name' => 'Calibri', 'size' => 8, 'color' => '8891A3'], ['alignment' => 'center']);
        $cover->addText('Aplikasi tugas akhir / UKK — biaya pengembangan internal Rp0', ['name' => 'Calibri', 'size' => 8, 'color' => '8891A3'], ['alignment' => 'center']);

        // Footer for cover
        $footer = $cover->addFooter();
        $footer->addText('Perkiraan Biaya — BurjoOrder · Agustus 2026 · Bersifat estimasi, konfirmasi ke vendor sebelum transaksi', ['name' => 'Calibri', 'size' => 7, 'color' => '9AA0B2'], ['alignment' => 'center']);

        // ===== MAIN SECTION =====
        $section = $phpWord->addSection($sectionStyle);
        $header = $section->addHeader();
        $header->addText('BurjoOrder — Perkiraan Biaya Operasional', ['name' => 'Calibri', 'size' => 7, 'color' => '9AA0B2'], ['alignment' => 'right']);
        $footer2 = $section->addFooter();
        $footer2->addPreserveText('Halaman {PAGE} dari {NUMPAGES}', ['name' => 'Calibri', 'size' => 7, 'color' => '9AA0B2'], ['alignment' => 'center']);

        // TOC
        $section->addTitle('Daftar Isi', 1);
        $section->addText('Dokumen ini menjawab: berapa biaya jalan produksi setiap bulan supaya tidak mencekik — untuk burjo baru buka dekat kampus.', ['name' => 'Calibri', 'size' => 9, 'color' => '5F6779', 'italic' => true], ['spaceAfter' => 120]);
        $tocItems = [
            '1. Ringkasan Eksekutif',
            '2. Asumsi & Stack',
            '3. Biaya Internal (Pengembangan)',
            '4. Biaya Eksternal (Operasional & Infrastruktur)',
            '5. Simulasi Biaya Payment Gateway (Midtrans QRIS)',
            '6. Rekap & TCO 3 Tahun',
            '7. Cara Menekan Biaya',
            '8. Catatan Akhir',
        ];
        foreach ($tocItems as $it) {
            $section->addText($it, ['name' => 'Calibri', 'size' => 9, 'color' => '2A3040'], ['spaceAfter' => 20, 'indentation' => ['left' => 200]]);
        }
        $section->addText('Lampiran: Sumber harga & metode hitung', ['name' => 'Calibri', 'size' => 8, 'color' => '8891A3', 'italic' => true], ['spaceBefore' => 80]);
        $section->addTextBreak(1);

        // Helper to add table
        $addTable = function ($section, $headers, $rows, $colWidths = null) use ($tableBase, $headCell, $headFont, $cellFont, $cellFontBold) {
            $table = $section->addTable($tableBase);
            $table->addRow(null, ['cantSplit' => true]);
            foreach ($headers as $i => $h) {
                $w = $colWidths[$i] ?? 1800;
                $cell = $table->addCell($w, $headCell);
                $cell->addText($h, $headFont, ['alignment' => 'center', 'spaceAfter' => 0]);
            }
            foreach ($rows as $r) {
                $table->addRow();
                foreach ($r as $i => $val) {
                    $w = $colWidths[$i] ?? 1800;
                    $isBold = str_contains($val, '≈') || str_contains($val, 'Rp') && str_contains($val, 'Total');
                    $cell = $table->addCell($w, ['valign' => 'center']);
                    $font = $isBold ? $cellFontBold : $cellFont;
                    // crude bold detection for header row values already handled
                    $cell->addText($val, $font, ['alignment' => 'center', 'spaceAfter' => 0]);
                }
            }
            $section->addTextBreak(1);
        };

        // 1. Ringkasan Eksekutif
        $section->addTitle('1. Ringkasan Eksekutif', 1);
        $section->addText('Dua paket utama untuk burjo baru (single outlet). Angka memakai status merchant UMI → QRIS 0% MDR. Jika belum UMI, gateway bisa menambah Rp0,5–1,5 jt/bulan (lihat Bab 5).', ['name' => 'Calibri', 'size' => 9, 'color' => '1F2430'], ['spaceAfter' => 120, 'alignment' => 'both']);
        $addTable($section,
            ['Paket', 'VPS', 'Domain', 'QRIS (UMI)', 'Lain', 'Total/bulan', 'Total/tahun'],
            [
                ['A. Hemat (100–250 tx/hari)', 'Rp87–105rb', 'Rp17rb', 'Rp0', 'Rp10–20rb', '≈ Rp114–142rb', '≈ Rp1,4–1,7jt'],
                ['B. Produksi / event besar (≤400 tx/hari)', 'Rp245rb', 'Rp17rb', 'Rp0', 'Rp10–20rb', '± Rp272–282rb', '≈ Rp3,3jt'],
            ],
            [2100, 1400, 1100, 1100, 1100, 1500, 1500]
        );
        $section->addText('Catatan: VPS Paket A = 2c/2GB NVMe (IDCloudHost Basic/Rocket 1), Paket B = 4c/16GB/120GB (Rocket VPS 4). Domain .com Rp200rb/tahun (amortisasi Rp17rb/bulan).', $smallNote, ['spaceAfter' => 60]);
        $section->addText('Komposisi Paket A (hemat) per bulan: VPS 2c/2GB Rp96rb (75%) · Domain Rp17rb (13%) · Backup & ops Rp15rb (12%) — total ≈ Rp128rb.', ['name' => 'Calibri', 'size' => 8.5, 'color' => '1F2430'], ['spaceAfter' => 100, 'alignment' => 'both']);

        // 2. Asumsi
        $section->addTitle('2. Asumsi', 1);
        $addTable($section,
            ['Parameter', 'Nilai'],
            [
                ['Lokasi', '1 (single outlet, dekat kampus)'],
                ['Meja / Menu', '25 / ±71'],
                ['Harga per menu', 'Rp15.000 (rata-rata order ± Rp15–20rb)'],
                ['Transaksi/hari', 'sepi 100 / sedang 250 / ramai 400 (event reservasi ~100 orang)'],
                ['QRIS share', '60–70% (sisa Tunai — Rp0)'],
                ['Rate developer (ref)', 'Junior Rp125rb/jam · Mid Rp275rb/jam · Senior Rp500rb/jam'],
                ['Merchant QRIS', 'Usaha Mikro (UMI) → 0% (reguler 0,7%)'],
            ],
            [2500, 6500]
        );
        $section->addTitle('Stack — biaya apa yang benar-benar ada', 2);
        $addTable($section,
            ['Komponen', 'OSS / Gratis', 'Komersial', 'Catatan'],
            [
                ['Laravel 13, Livewire, Tailwind', '✓', '—', '—'],
                ['PostgreSQL 18, Redis', '✓', '—', 'di VPS'],
                ['FrankenPHP + Reverb (self-host)', '✓', '—', 'web + realtime 1 proses'],
                ['Cloudflare Tunnel (publik)', '✓ Free', '—', 'gratis exposure'],
                ['Midtrans', '—', 'komisioner', 'satu-satunya biaya variabel'],
                ['simple-qrcode, dompdf, intervention', '✓', '—', '—'],
            ],
            [2300, 1400, 1400, 2900]
        );

        // 3. Biaya Internal
        $section->addTitle('3. Biaya Internal (Pengembangan)', 1);
        $section->addText('Aplikasi sudah dibangun sebagai tugas akhir / UKK → Rp0. Tabel di bawah hanya acuan bila nanti ada fitur tambahan.', ['name' => 'Calibri', 'size' => 9, 'color' => '1F2430'], ['spaceAfter' => 100, 'alignment' => 'both']);
        $addTable($section,
            ['Kompleksitas', 'Estimasi jam', 'Junior (Rp125k)', 'Mid (Rp275k)', 'Senior (Rp500k)'],
            [
                ['Bug-fixes / scaling kecil', '15–20', '≈Rp2jt', '≈Rp5jt', '≈Rp10jt'],
                ['Fitur baru (mis. loyalitas/point)', '40–60', '≈Rp6jt', '≈Rp15jt', '≈Rp30jt'],
            ],
            [2000, 1500, 1500, 1500, 1500]
        );
        $section->addText('Tidak perlu bayar dev kecuali ada perubahan fitur nyata.', $smallNote, ['spaceAfter' => 80]);

        // 4. Biaya Eksternal
        $section->addTitle('4. Biaya Eksternal (Operasional & Infrastruktur)', 1);
        $section->addTitle('4.1 Satu kali / tahunan', 2);
        $addTable($section,
            ['Item', 'Biaya', 'Periode', 'Catatan'],
            [
                ['Domain .com', 'Rp200.000', '/tahun', 'renewal 2026; tahun-1 promo Rp15–110rb'],
                ['atau .my.id/.web.id promo', 'Rp2.000–50.000', '/tahun', 'promo registrar, renewal kembali normal'],
                ['SSL', 'Rp0', '/tahun', 'Caddy/FrankenPHP Let\'s Encrypt otomatis'],
                ['Setup VPS', 'Rp0', 'sekali', 'install Docker via Sail'],
            ],
            [2200, 1500, 1200, 3100]
        );
        $section->addTitle('4.2 Bulanan — Opsi A (hemat, 100–250 tx/hari)', 2);
        $addTable($section,
            ['Komponen', 'Biaya', 'Sumber'],
            [
                ['VPS 2c/2GB NVMe (IDCloudHost Basic/Rocket 1)', 'Rp87–105.000', 'idcloudhost.com'],
                ['Cloudflare Tunnel (publik)', 'Rp0', 'Free'],
                ['Email (Resend/Brevo free ≤3k/bln)', 'Rp0', 'free tier'],
                ['Monitoring (Sentry free + UptimeRobot)', 'Rp0', 'free'],
                ['Backup (snapshot VPS 1×/hari)', 'Rp10.000', 'opsional'],
                ['Domain (1/12)', 'Rp17.000', 'amortisasi'],
                ['Subtotal', '≈ Rp114–142.000', '—'],
            ],
            [3200, 1700, 2100]
        );
        $section->addTitle('4.3 Bulanan — Opsi B (produksi, siap event ~100 orang)', 2);
        $addTable($section,
            ['Komponen', 'Biaya', 'Sumber'],
            [
                ['VPS 4c/16GB/120GB NVMe (Rocket VPS 4)', 'Rp245.000', 'idcloudhost.com'],
                ['Cloudflare Tunnel', 'Rp0', 'Free'],
                ['Email / monitoring / backup', 'Rp10.000', 'free tier + snapshot'],
                ['Laravel Forge', 'Rp0 (skip)', 'self-manage hemat ±Rp186rb/bln'],
                ['Domain (1/12)', 'Rp17.000', '—'],
                ['Subtotal', '≈ Rp272.000', '—'],
            ],
            [3200, 1700, 2100]
        );
        $section->addText('Tip: pakai opsi A sehari-hari, scale up ke VPS B hanya saat event besar (mis. kuis/resepsi kampus). Bisa manual resize atau jadwalkan.', $smallNote, ['spaceAfter' => 60]);
        $section->addTitle('4.4 Ringkasan bulanan (termasuk QRIS)', 2);
        $addTable($section,
            ['Item', 'Hemat (A)', 'Produksi (B)', 'Reguler QRIS (opsional)'],
            [
                ['VPS', 'Rp87–105rb', 'Rp245rb', '—'],
                ['Domain', 'Rp17rb', 'Rp17rb', '—'],
                ['Backup/ops', 'Rp10–20rb', 'Rp10–20rb', '—'],
                ['QRIS (UMI)', 'Rp0', 'Rp0', 'Rp210–979rb*'],
                ['Total/bulan', '≈ Rp114–142rb', '≈ Rp272rb', 'Rp320–1,2jt'],
            ],
            [2200, 1500, 1500, 1800]
        );
        $section->addText('* UMI belum terdaftar → pakai MDR reguler 0,7% + PPN.', $smallNote, ['spaceAfter' => 80]);

        // 5. Simulasi Gateway
        $section->addTitle('5. Simulasi Biaya Payment Gateway (Midtrans)', 1);
        $section->addText('Hanya biaya variabel (transaksi sukses); tidak ada langganan/iuran.', ['name' => 'Calibri', 'size' => 9, 'color' => '1F2430', 'bold' => true], ['spaceAfter' => 80]);
        $section->addTitle('Tarif QRIS (BI, Agustus 2026)', 2);
        $addTable($section,
            ['Merchant', 'MDR QRIS per tx', 'PPN'],
            [
                ['Usaha Mikro (UMI), tx ≤ Rp500.000', '0%', '—'],
                ['Reguler (UKE/UME/UBE)', '0,7%', '11% (dari MDR)'],
                ['Kartu kredit', '2,9% + Rp2.000', '11%'],
                ['VA / bank transfer', 'Rp4.000', '11% (bisa dinon-aktifkan)'],
            ],
            [3000, 2000, 2000]
        );
        $section->addText('Karena tiket Rp15.000 ≪ Rp500.000 → UMI = QRIS GRATIS. Daftarkan usaha via Perekraf/OCO/aplikasi Pengusaha agar kategori UMI. Ini kunci pembekuan biaya.', ['name' => 'Calibri', 'size' => 8.5, 'color' => '1F2430'], ['spaceAfter' => 80, 'alignment' => 'both']);
        $section->addTitle('Simulasi (tiket Rp15.000, 30 hari/bulan)', 2);
        $addTable($section,
            ['Skenario', 'Tx/hari', 'QRIS %', 'Volume QRIS/bulan', 'Reguler 0,7%+PPN', 'UMI 0%'],
            [
                ['Sepi (100)', '80', '60%', 'Rp27,0jt', 'Rp210rb', 'Rp0'],
                ['Sedang (250)', '150', '65%', 'Rp73,1jt', 'Rp568rb', 'Rp0'],
                ['Ramai (400)', '300', '70%', 'Rp126jt', 'Rp979rb', 'Rp0'],
            ],
            [1300, 1000, 1000, 1500, 1400, 1000]
        );
        $section->addText('Contoh hitung sedang: 250 tx × 65% × Rp15.000 × 30 hari = Rp73.125.000 → 0,7% = Rp511.875 → ×1,11 = Rp568.181/bulan.', $smallNote, ['spaceAfter' => 60]);
        $section->addText('Jika dipaksa pakai VA: Rp4.000/tx → 250×30 = 7.500 tx/bulan × Rp4.000 = Rp300.000/bulan. Disarankan: non-aktifkan VA, pakai QRIS atau Tunai saja.', ['name' => 'Calibri', 'size' => 8.5, 'color' => 'C0392B'], ['spaceAfter' => 80]);

        // 6. Rekap & TCO
        $section->addTitle('6. Rekap & TCO 3 Tahun', 1);
        $section->addText('Self-build (dev Rp0), QRIS UMI (0%):', ['name' => 'Calibri', 'size' => 9, 'color' => '1F2430'], ['spaceAfter' => 80]);
        $addTable($section,
            ['Item', 'Per bulan', '36 bulan'],
            [
                ['VPS (opsi A, Rp105rb)', 'Rp105.000', 'Rp3.780.000'],
                ['VPS (opsi B, Rp245rb)', 'Rp245.000', 'Rp8.820.000'],
                ['Domain (.com Rp200rb/th)', 'Rp17.000', 'Rp600.000'],
                ['QRIS (UMI)', 'Rp0', 'Rp0'],
                ['Backup/ops', 'Rp15.000', 'Rp540.000'],
                ['TCO 3 th (hemat, UMI)', '—', '≈ Rp4,9jt'],
                ['TCO 3 th (produksi, UMI)', '—', '≈ Rp10,0jt'],
                ['Jika paksa reguler 0,7% (sedang)', '+Rp568rb/bln', '+Rp20,4jt → ≈ Rp25,3jt'],
            ],
            [3000, 1500, 2500]
        );
        $section->addText('Komposisi TCO 3 th (Opsi A, UMI): VPS Rp3.780rb (77%) · Domain Rp600rb (12%) · Backup & ops Rp540rb (11%) — total ≈ Rp4,92jt.', ['name' => 'Calibri', 'size' => 8.5, 'color' => '1F2430'], ['spaceAfter' => 60, 'alignment' => 'both']);
        $section->addText('Intinya: selama UMI terdaftar & pakai QRIS, biaya operasional ≈ Rp110–140rb/bulan (≈ Rp1,6jt/tahun) — tidak mencekik. Paksa reguler 0,7% malah naik 5× lipat ke ±Rp25jt/3thn.', ['name' => 'Calibri', 'size' => 8.5, 'color' => '1F2430', 'bold' => true], ['spaceAfter' => 80, 'alignment' => 'both']);

        // 7. Cara Menekan Biaya
        $section->addTitle('7. Cara Menekan Biaya', 1);
        $items = [
            'Daftarkan UMI dulu (via aplikasi Pengusaha/Perekraf) → QRIS 0% MDR sampai Rp500rb/tx. Paling penting.',
            'Non-aktifkan VA bank di Midtrans → hemat Rp4.000/tx.',
            'Cloudflare Tunnel = gratis → jangan bayar IP publik/koneksi khusus.',
            'Free tier email (Resend 3k/bln), Sentry, UptimeRobot — cukup untuk burjo kecil.',
            'Domain promo tahun pertama, atau pakai .my.id/.web.id promo Rp2.000 sekali (perpanjang normal).',
            'Scale VPS manual: pakai paket A (Rp105rb) sehari-hari, resize ke B (Rp245rb) hanya waktu event; kembali turun setelahnya.',
            'Skip Laravel Forge → hemat ~Rp186rb/bln; cukup Sail/FrankenPHP via systemd.',
        ];
        foreach ($items as $i => $t) {
            $section->addListItem(($i + 1).'. '.$t, 0, ['name' => 'Calibri', 'size' => 8.5, 'color' => '1F2430'], null, ['spaceAfter' => 30]);
        }

        // 8. Catatan Akhir
        $section->addTitle('8. Catatan Akhir', 1);
        $bullets = [
            'Semua harga Agustus 2026, termasuk PPN 11% di fee gateway (dipotong otomatis Midtrans pada payout nett).',
            'Untuk infrastruktur (VPS/domain), PPN 11% belum selalu termasuk di harga iklan — tambahkan bila penyedia belum PKP.',
            'Angka bersifat perkiraan; konfirmasi lagi ke vendor sebelum beli/subskripsi.',
            'Fokus pada biaya variabel (gateway). Jika UMI berhasil didaftarkan, biaya tetap operasional ≤ Rp150rb/bulan.',
        ];
        foreach ($bullets as $b) {
            $section->addListItem($b, 0, ['name' => 'Calibri', 'size' => 8.5, 'color' => '1F2430'], null, ['spaceAfter' => 30]);
        }
        $section->addTextBreak(1);
        $section->addText('— Dokumen ini digenerate otomatis dari Perkiraan-Biaya-BurjoOrder.md —', ['name' => 'Calibri', 'size' => 7, 'color' => '9AA0B2', 'italic' => true], ['alignment' => 'center']);
        $section->addText('BurjoOrder · Laravel 13 · PostgreSQL 18 · Reverb · FrankenPHP · Agustus 2026', ['name' => 'Calibri', 'size' => 7, 'color' => '9AA0B2'], ['alignment' => 'center']);

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        // Ensure dir exists
        $dir = dirname($outPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $objWriter->save($outPath);

        $this->info("Docx generated: {$outPath}");

        return 0;
    }
}
