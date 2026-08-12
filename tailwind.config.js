/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',

  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.js',
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
  ],

  theme: {
    extend: {
      colors: {

        // Background utama halaman pelanggan (dark, atmosferik)
        ink: '#14171B',

        // Warna card/container di atas 'ink'. Sedikit lebih terang
        // dari background supaya card terlihat "naik" (elevation).
        surface: '#1E2229',

        // Background terang untuk dashboard kasir/admin 
        paper: '#F6F1E7',

        // Card di atas 'paper' — sedikit lebih terang dari paper
        // supaya card tetap kelihatan "naik" di light mode juga.
        'paper-card': '#FCFAF5',

        // Teks utama di atas background GELAP ('ink'/'surface').
        kertas: '#EDE7DA',

        // Teks utama di atas background terang
        arang: '#1C1E24',

        'muted-dark': '#8891A3',   // teks abu-abu di atas background gelap
        'muted-light': '#8B8578',  // teks abu-abu di atas background terang

        // Garis pembatas/divider antar elemen (tabel, card border).
        'border-dark': '#2A2F3A',   // dipakai di atas ink/surface
        'border-light': '#E3DCC9',  // dipakai di atas paper/paper-card


        accent: 'var(--color-accent, #F2A63D)',
        'accent-dark': 'var(--color-accent-dark, #D9902E)',

        'preset-amber': '#F2A63D',
        'preset-teal': '#2C8C82',
        'preset-purple': '#7B5EA7',
        'preset-rose': '#C9587A',

        // SUCCESS: badge "tersedia", tombol "Terima" pesanan, notifikasi
        // berhasil. Terinspirasi warna daun/tanaman di mural burjo.
        daun: '#4E9A51',
        'daun-dark': '#3F7E42',

        // DANGER: tombol hapus, badge "ditolak"/"habis", pesan error.
        cabai: '#D64545',
        'cabai-dark': '#B93838',

        // INFO: notifikasi real-time, badge "diproses", indikator live.
        gas: '#4FA8C9',
        'gas-dark': '#3D8AA8',

        //  tag kategori "Minuman"
        merak: '#2C8C82',

        // Chart colors (used in ApexCharts JS)
        'chart-yellow': '#EAB308',
        'chart-purple': '#9B59B6',
      },

      // Tombol utama (Checkout, Simpan)  → bg-accent hover:bg-accent-dark, text-ink
      // Tombol sukses (Terima pesanan)   → bg-daun hover:bg-daun-dark, text-kertas
      // Tombol bahaya (Hapus, Tolak)     → bg-cabai hover:bg-cabai-dark, text-kertas
      // Tombol sekunder/outline          → bg-transparent border-border-light text-ink (dark) / text-arang (light)
      // Tombol nonaktif (disabled)       → bg-muted-light/40 text-muted-light (cursor-not-allowed)

      fontFamily: {
        display: ['"Space Grotesk"', 'sans-serif'],
        body: ['"Plus Jakarta Sans"', 'sans-serif'],
        mono: ['"JetBrains Mono"', 'monospace'],
      },
    },
  },

  plugins: [],
}
