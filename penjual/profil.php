<?php
$halaman = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Profil - Kantin Ceria</title>

        <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700&family=Inter:wght@400;600&display=swap"
                rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
                rel="stylesheet" />
        <link rel="stylesheet" href="../assets/css/style.css">

        <script>
                tailwind.config = {
                        darkMode: "class",
                        theme: {
                                extend: {
                                        colors: {
                                                "background": "#f7f8f9",
                                                "primary": "#004900",
                                                "second-primary": "#f9f9fb",
                                                "input": "#f3f3f5",
                                                "primary-container": "#004b00",
                                                "on-primary": "#ffffff",
                                                "on-primary-container": "#76bc65",
                                                "surface": "#f7fbf0",
                                                "surface-bright": "#f7fbf0",
                                                "surface-variant": "#e0e4d9",
                                                "surface-container": "#ecefe5",
                                                "surface-container-low": "#f1f5ea",
                                                "surface-container-lowest": "#ffffff",
                                                "on-surface": "#191d16",
                                                "on-surface-variant": "#41493d",
                                                "on-background": "#191d16",
                                                "outline": "#717a6b",
                                                "outline-variant": "#c0c9b9",
                                                "inverse-primary": "#90d87e",
                                                "text-1": "#191c1c",
                                                "text-2": "#4e5a48",
                                        },
                                        fontFamily: {
                                                "headline-xl": ["Plus Jakarta Sans"],
                                                "headline-lg": ["Plus Jakarta Sans"],
                                                "headline-md": ["Plus Jakarta Sans"],
                                                "body-lg": ["Inter"],
                                                "body-md": ["Inter"],
                                                "label-md": ["Inter"],
                                        },
                                        fontSize: {
                                                "headline-xl": ["32px", { lineHeight: "1.2", fontWeight: "700" }],
                                                "headline-lg": ["24px", { lineHeight: "1.3", fontWeight: "600" }],
                                                "headline-md": ["18px", { lineHeight: "1.4", fontWeight: "600" }],
                                                "body-lg": ["16px", { lineHeight: "1.6", fontWeight: "400" }],
                                                "body-md": ["14px", { lineHeight: "1.5", fontWeight: "400" }],
                                                "label-md": ["12px", { lineHeight: "1", letterSpacing: "0.05em", fontWeight: "600" }],
                                        },
                                        spacing: {
                                                "xs": "4px",
                                                "sm": "12px",
                                                "base": "8px",
                                                "md": "24px",
                                                "lg": "40px",
                                                "xl": "64px",
                                                "margin": "32px",
                                                "gutter": "24px",
                                        },
                                        borderRadius: {
                                                DEFAULT: "0.25rem",
                                                lg: "0.5rem",
                                                xl: "0.75rem",
                                                full: "9999px",
                                        },
                                }
                        }
                }
        </script>
        <style>
                .material-symbols-outlined {
                        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
                }
        </style>
</head>

<body class="bg-background text-on-background font-body-md min-h-screen flex">

        <!-- Sidebar Navbar -->
        <?php include 'navbar.php'; ?>

        <!-- Main Content — ml-64 untuk desktop (lebar sidebar), tidak perlu padding-top -->
        <main class="flex-1 lg:ml-80 p-6 md:p-margin w-full">
                <div class="max-w-[1200px] mx-auto">

                        <!-- Header -->
                        <header class="mb-lg">
                                <h2 class="text-headline-xl font-headline-xl text-on-background">Profil</h2>
                                <p class="text-body-lg font-body-lg text-on-surface-variant mt-xs">
                                        Kelola informasi toko dan profil pribadi Anda.
                                </p>
                        </header>

                        <!-- Bento Grid Layout -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter">

                                <!-- Left Column: Profile Card -->
                                <div class="lg:col-span-1 space-y-gutter">
                                        <div
                                                class="bg-surface-container-lowest rounded-xl p-md shadow-sm border border-surface-variant flex flex-col items-center text-center relative overflow-hidden">
                                                <!-- Banner hijau di atas -->
                                                <div class="w-full h-24 bg-primary-container absolute top-0 left-0">
                                                </div>
                                                <!-- Avatar -->
                                                <img alt="Avatar Warung Ayam Bakar"
                                                        class="w-24 h-24 rounded-full border-4 border-surface-container-lowest relative z-10 mt-8 object-cover"
                                                        src="https://lh3.googleusercontent.com/aida-public/AB6AXuDdnWgfa088PexzOZqQhzv1AhBnKpGNX4GMTixF1sbQSoMGYiAXNTiCtZcC6ITp9tRyfC18uv79xHmeZmyVMYtDeCw1Nr-zpEyTfdhZuPMZZCCnD-2atzzPI7UqNbSnseBjtQg93VI0jmKFTBGejgbd3etWFyVn3jCx0wYuskf0FSn64Bk8zPRdVNAILRxGSiCvkwRlZ1QKzUhpvsElzTxxgSh_l3he0MFbng6UQwI7jc7FCvY8tc0GReN-2iA-OZeQb4tRYEVsObY" />
                                                <h3 class="text-headline-md font-headline-md text-on-background mt-4">
                                                        Warung Ayam Bakar</h3>
                                                <p class="text-body-md font-body-md text-on-surface-variant">Vendor
                                                        Aktif</p>
                                                <button
                                                        class="mt-6 w-full bg-primary text-on-primary font-label-md text-label-md py-sm px-md rounded-lg shadow-sm hover:opacity-90 transition-opacity focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                                                        Edit Profil
                                                </button>
                                        </div>
                                </div>

                                <!-- Right Column: Info & Stats -->
                                <div class="lg:col-span-2 space-y-gutter">

                                        <!-- Informasi Pribadi -->
                                        <div
                                                class="bg-surface-container-lowest rounded-xl p-md shadow-sm border border-surface-variant">
                                                <h4
                                                        class="text-headline-md font-headline-md text-on-background mb-6 pb-4 border-b border-surface-variant">
                                                        Informasi Pribadi
                                                </h4>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                        <div class="space-y-xs">
                                                                <label
                                                                        class="text-label-md font-label-md text-on-surface-variant block">Nama
                                                                        Pemilik</label>
                                                                <input class="w-full bg-surface-bright border border-outline-variant rounded-lg px-4 py-2 text-body-md font-body-md text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors"
                                                                        readonly type="text" value="Budi Santoso" />
                                                        </div>
                                                        <div class="space-y-xs">
                                                                <label
                                                                        class="text-label-md font-label-md text-on-surface-variant block">Nama
                                                                        Toko</label>
                                                                <input class="w-full bg-surface-bright border border-outline-variant rounded-lg px-4 py-2 text-body-md font-body-md text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors"
                                                                        readonly type="text"
                                                                        value="Warung Ayam Bakar" />
                                                        </div>
                                                        <div class="space-y-xs md:col-span-2">
                                                                <label
                                                                        class="text-label-md font-label-md text-on-surface-variant block">Alamat</label>
                                                                <input class="w-full bg-surface-bright border border-outline-variant rounded-lg px-4 py-2 text-body-md font-body-md text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors"
                                                                        readonly type="text"
                                                                        value="Gedung Kantin Utama, Lantai 1, Stan C3" />
                                                        </div>
                                                        <div class="space-y-xs md:col-span-2">
                                                                <label
                                                                        class="text-label-md font-label-md text-on-surface-variant block">Nomor
                                                                        Telepon</label>
                                                                <input class="w-full bg-surface-bright border border-outline-variant rounded-lg px-4 py-2 text-body-md font-body-md text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors"
                                                                        readonly type="tel" value="0812-3456-7890" />
                                                        </div>
                                                </div>
                                        </div>

                                        <!-- Statistik Toko -->
                                        <div
                                                class="bg-surface-container-lowest rounded-xl p-md shadow-sm border border-surface-variant">
                                                <h4
                                                        class="text-headline-md font-headline-md text-on-background mb-6 pb-4 border-b border-surface-variant">
                                                        Statistik Toko
                                                </h4>
                                                <div class="grid grid-cols-2 gap-4">
                                                        <div
                                                                class="bg-surface-container-low p-4 rounded-lg flex items-center gap-4">
                                                                <div
                                                                        class="w-12 h-12 bg-primary-container/10 rounded-full flex items-center justify-center text-primary-container">
                                                                        <span
                                                                                class="material-symbols-outlined text-2xl">shopping_bag</span>
                                                                </div>
                                                                <div>
                                                                        <p
                                                                                class="text-label-md font-label-md text-on-surface-variant">
                                                                                Total Pesanan</p>
                                                                        <p
                                                                                class="text-headline-lg font-headline-lg text-on-surface">
                                                                                1,245</p>
                                                                </div>
                                                        </div>
                                                        <div
                                                                class="bg-surface-container-low p-4 rounded-lg flex items-center gap-4">
                                                                <div
                                                                        class="w-12 h-12 bg-primary-container/10 rounded-full flex items-center justify-center text-primary-container">
                                                                        <span
                                                                                class="material-symbols-outlined text-2xl">star</span>
                                                                </div>
                                                                <div>
                                                                        <p
                                                                                class="text-label-md font-label-md text-on-surface-variant">
                                                                                Rating</p>
                                                                        <p
                                                                                class="text-headline-lg font-headline-lg text-on-surface">
                                                                                4.8 <span
                                                                                        class="text-body-md text-on-surface-variant font-normal">/
                                                                                        5</span></p>
                                                                </div>
                                                        </div>
                                                </div>
                                        </div>

                                </div>
                        </div>
                </div>
        </main>

</body>

</html>