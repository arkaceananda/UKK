import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/js/**/*.js',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],

                // harga, ID pesanan, angka yang perlu dibedakan, kode unik, dan teks monospaced lainnya
                mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],

                // Font untuk teks utama, body, dan konten
                body: ['Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],

                // Font untuk judul, heading, dan teks penting
                display: ['Space Grotesk', ...defaultTheme.fontFamily.sans]
            },
            colors: {
                // === NEUTRAL — dasar UI, dipakai di hampir semua tempat ===

                // Background utama halaman pelanggan (dark, atmosferik).
                // Untuk admin, jangan gunakan warna ini sebagai warna utama, gunakan warna terang.
                ink: '#14171B',

                // Warna card/container di atas ink
                surface: '#1E2229',

                // Background terang untuk dashboard kasir/admin
                paper: '#F6F1E7',

                // Card di atas paper
                'paper-card': '#FCFAF5',

                // Teks utama di atas background gelap
                kertas: '#EDE7DA',

                // Teks sekunder/muted
                'muted-dark': '#8891A3',
                'muted-light': '#8B8578',

                // Garis pembatas/divider
                'border-dark': '#2A2F3A',
                'border-light': '#E3DCC9',

                // === SEMANTIC — warna fungsional ===

                // PRIMARY: harga, tombol CTA utama
                petromax: '#F2A63B',
                'petromax-dark': '#D9902E',

                // SUCCESS: badge "tersedia", tombol "Terima"
                daun: '#4E9A51',
                'daun-dark': '#3F7E42',

                // DANGER: tombol hapus, badge "ditolak"/"habis"
                cabai: '#D64545',
                'cabai-dark': '#B93838',

                // INFO: notifikasi real-time, badge "diproses", indikator live
                gas: '#4FA8C9',
                'gas-dark': '#3D8AA8',

                // === DECORATIVE — aksen mural, pakai separuh-separuh ===

                // Cocok buat tag kategori "Minuman"
                merak: '#2C8C82',

                // Cocok buat tag kategori "Gorengan" atau aksen ilustrasi
                terong: '#7B5EA7',
            }
        },
    },

    plugins: [forms],
};
