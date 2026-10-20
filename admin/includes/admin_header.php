<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdminAuth();

$adminUser = $_SESSION['admin_user'] ?? ['name' => 'Shiv Vani Administrator', 'role' => 'Managing Director'];
$currentPage = basename($_SERVER['PHP_SELF']);
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' • ' : '' ?><?= STORE_NAME ?> Admin</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        blush: {
                            50: '#fff5f7',
                            100: '#fde8ec',
                            200: '#fbd0da',
                            500: '#e64673',
                            600: '#d02b5e',
                            700: '#af1e4b',
                        },
                        gold: {
                            400: '#d9b268',
                            500: '#c59b48',
                            600: '#a77d33',
                        },
                        charcoal: {
                            800: '#2c2523',
                            900: '#1e1918',
                        }
                    },
                    fontFamily: {
                        serif: ['"Cormorant Garamond"', 'serif'],
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-[#FAF7F2] text-charcoal-800 font-sans antialiased min-h-screen flex flex-col">

    <!-- Top Admin Bar with Shiv Vani Enterprises Logo -->
    <header class="bg-charcoal-900 text-cream-100 border-b border-charcoal-800 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <a href="index.php" class="flex items-center gap-2.5">
                        <img src="../<?= STORE_LOGO ?>" alt="Logo" class="w-8 h-8 rounded-full object-cover border border-gold-400">
                        <span class="font-serif text-lg sm:text-xl text-cream-50 font-bold"><?= STORE_NAME ?></span>
                        <span class="text-[10px] uppercase tracking-widest bg-blush-600 text-white px-2 py-0.5 rounded-full font-bold hidden sm:inline">Admin</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <nav class="hidden lg:flex items-center space-x-5 text-xs font-semibold uppercase tracking-wider">
                    <a href="index.php" class="transition-colors hover:text-blush-400 <?= $currentPage === 'index.php' ? 'text-blush-400 font-bold' : 'text-cream-100/70' ?>">
                        Dashboard
                    </a>
                    <a href="products.php" class="transition-colors hover:text-blush-400 <?= in_array($currentPage, ['products.php', 'product-edit.php']) ? 'text-blush-400 font-bold' : 'text-cream-100/70' ?>">
                        Candles &amp; Rates
                    </a>
                    <a href="product-add.php" class="transition-colors hover:text-blush-400 <?= $currentPage === 'product-add.php' ? 'text-blush-400 font-bold' : 'text-cream-100/70' ?>">
                        + Add Candle
                    </a>
                    <a href="categories.php" class="transition-colors hover:text-blush-400 <?= $currentPage === 'categories.php' ? 'text-blush-400 font-bold' : 'text-cream-100/70' ?>">
                        Categories
                    </a>
                    <a href="payment-settings.php" class="transition-colors hover:text-blush-400 <?= $currentPage === 'payment-settings.php' ? 'text-blush-400 font-bold' : 'text-cream-100/70' ?>">
                        Payment API &amp; QR
                    </a>
                    <a href="reviews.php" class="transition-colors hover:text-blush-400 <?= $currentPage === 'reviews.php' ? 'text-blush-400 font-bold' : 'text-cream-100/70' ?>">
                        Reviews &amp; Videos
                    </a>
                    <a href="orders.php" class="transition-colors hover:text-blush-400 <?= $currentPage === 'orders.php' ? 'text-blush-400 font-bold' : 'text-cream-100/70' ?>">
                        Orders
                    </a>
                </nav>

                <!-- Right profile & storefront link -->
                <div class="flex items-center space-x-3">
                    <a href="../index.php" target="_blank" class="text-xs text-cream-100/80 hover:text-cream-100 flex items-center gap-1 bg-charcoal-800 px-3 py-1.5 rounded-xl border border-charcoal-700">
                        <i data-lucide="external-link" class="w-3.5 h-3.5 text-gold-400"></i>
                        <span class="hidden sm:inline">Storefront</span>
                    </a>

                    <div class="text-right hidden xl:block">
                        <div class="text-xs font-bold text-cream-50"><?= htmlspecialchars($adminUser['name']) ?></div>
                    </div>

                    <a href="logout.php" class="text-charcoal-400 hover:text-rose-400 p-1" title="Sign Out">
                        <i data-lucide="log-out" class="w-4 h-4 text-cream-100/70 hover:text-rose-400"></i>
                    </a>
                </div>

            </div>
        </div>

        <!-- Mobile Subnav Bar for Small Screens -->
        <div class="lg:hidden flex items-center space-x-4 px-4 py-2 bg-charcoal-800 overflow-x-auto text-[11px] font-bold uppercase tracking-wider text-cream-100/70 border-t border-charcoal-700 scrollbar-none">
            <a href="index.php" class="whitespace-nowrap hover:text-white <?= $currentPage === 'index.php' ? 'text-blush-400' : '' ?>">Dashboard</a>
            <a href="products.php" class="whitespace-nowrap hover:text-white <?= $currentPage === 'products.php' ? 'text-blush-400' : '' ?>">Candles</a>
            <a href="product-add.php" class="whitespace-nowrap hover:text-white <?= $currentPage === 'product-add.php' ? 'text-blush-400' : '' ?>">+ Add</a>
            <a href="categories.php" class="whitespace-nowrap hover:text-white <?= $currentPage === 'categories.php' ? 'text-blush-400' : '' ?>">Categories</a>
            <a href="payment-settings.php" class="whitespace-nowrap hover:text-white <?= $currentPage === 'payment-settings.php' ? 'text-blush-400' : '' ?>">Payments</a>
            <a href="reviews.php" class="whitespace-nowrap hover:text-white <?= $currentPage === 'reviews.php' ? 'text-blush-400' : '' ?>">Reviews</a>
            <a href="orders.php" class="whitespace-nowrap hover:text-white <?= $currentPage === 'orders.php' ? 'text-blush-400' : '' ?>">Orders</a>
        </div>
    </header>

    <!-- Admin Flash Alert -->
    <?php if ($flash): ?>
        <div class="max-w-7xl mx-auto px-4 mt-6">
            <div class="rounded-2xl p-4 flex items-center gap-3 shadow-sm border <?= $flash['type'] === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' ?>">
                <i data-lucide="<?= $flash['type'] === 'success' ? 'check-circle-2' : 'alert-circle' ?>" class="w-5 h-5 flex-shrink-0"></i>
                <div class="text-sm font-medium"><?= htmlspecialchars($flash['message']) ?></div>
            </div>
        </div>
    <?php endif; ?>

    <main class="flex-grow py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
