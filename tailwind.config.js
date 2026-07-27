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
                // Background untuk halaman pelanggan {dark, atmosphere}
                // Untuk admin, jangan gunakan warna ini sebagai warna utama, gunakan warna terang
                ink: '#14171B',

                // Warna card/container di atas ink
                surface: '#1E2229',

                // Aksen utama: harga, tombol CTA, badge
                petromax: '#F2A63B',

                // Aksen bahaya/urgent: tombol hapus, badge, "diproses", indikator status live
                gas: '#4FA8C9',

                // Warna text utama di atas background gelap
                kertas: '#EDE7DA,'
            }
        },
    },

    plugins: [forms],
};
