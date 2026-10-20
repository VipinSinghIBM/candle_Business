<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

$cartCount = getCartCount();
$flash = getFlash();
$currentPage = basename($_SERVER['PHP_SELF']);
$navCategories = getCategories(true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' • ' : '' ?><?= STORE_NAME ?> | Artisanal Candles</title>
    
    <!-- Google Fonts: Cormorant Garamond & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
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
                            300: '#f7a8ba',
                            400: '#f17495',
                            500: '#e64673',
                            600: '#d02b5e',
                            700: '#af1e4b',
                            800: '#911b41',
                            900: '#7a1b3a',
                        },
                        cream: {
                            50: '#fdfbf7',
                            100: '#faf6ee',
                            200: '#f5ebdc',
                            300: '#eeddc4',
                            400: '#e3c6a0',
                        },
                        gold: {
                            400: '#d9b268',
                            500: '#c59b48',
                            600: '#a77d33',
                            700: '#8c6524',
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
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-[#FAF7F2] text-charcoal-800 font-sans antialiased selection:bg-blush-200 selection:text-blush-900 flex flex-col min-h-screen">

    <!-- Top Announcement Bar -->
    <div class="bg-charcoal-900 text-cream-100 text-xs py-2 px-4 text-center tracking-widest uppercase font-medium flex items-center justify-center gap-2 border-b border-charcoal-800">
        <span class="inline-block w-2 h-2 rounded-full bg-gold-400 animate-pulse"></span>
        <span>✨ <strong><?= STORE_NAME ?></strong> • Artisanal Spiritual &amp; Botanical Candles • Code: <strong class="text-gold-400">SHIVVANI15</strong> for 15% OFF • Free Shipping Over <?= STORE_CURRENCY ?> 500</span>
        <span class="hidden md:inline text-gold-400">✧</span>
    </div>

    <!-- Main Navigation Header -->
    <header class="sticky top-0 z-40 bg-[#FAF7F2]/95 backdrop-blur-md border-b border-[#ebdccd]/70 shadow-sm transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                
                <!-- Left: Navigation Links -->
                <nav class="hidden lg:flex items-center space-x-6 text-sm font-medium tracking-wide">
                    <a href="index.php" class="transition-colors hover:text-blush-600 <?= $currentPage === 'index.php' ? 'text-blush-600 font-bold' : 'text-charcoal-800/80' ?>">Home</a>
                    <a href="shop.php" class="transition-colors hover:text-blush-600 <?= $currentPage === 'shop.php' ? 'text-blush-600 font-bold' : 'text-charcoal-800/80' ?>">All Candles</a>

                    <!-- Dynamic Categories Dropdown in Navbar -->
                    <div class="relative group">
                        <button class="flex items-center gap-1 transition-colors hover:text-blush-600 text-charcoal-800/80 font-medium py-2">
                            <span>Categories</span>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform group-hover:rotate-180"></i>
                        </button>
                        
                        <div class="absolute left-0 mt-1 w-64 rounded-2xl bg-white shadow-xl border border-[#ebdccd] py-3 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                            <div class="px-4 py-1 text-[10px] uppercase font-bold tracking-widest text-gold-600 border-b border-[#f2e6d9] mb-2">
                                Curated Scent Categories
                            </div>
                            <?php foreach ($navCategories as $cat): ?>
                                <a href="shop.php?category=<?= urlencode($cat['name']) ?>" class="flex items-center justify-between px-4 py-2 text-xs font-semibold text-charcoal-800 hover:bg-blush-50 hover:text-blush-700 transition-colors">
                                    <span><?= htmlspecialchars($cat['name']) ?></span>
                                    <i data-lucide="arrow-right" class="w-3 h-3 opacity-0 group-hover:opacity-100"></i>
                                </a>
                            <?php endforeach; ?>
                            <div class="pt-2 mt-2 border-t border-[#f2e6d9] px-4">
                                <a href="shop.php" class="text-[11px] font-bold text-blush-600 hover:underline block">
                                    Browse All Scent Archives &rarr;
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Reviews Jump Link -->
                    <a href="index.php#reviews" class="transition-colors hover:text-blush-600 text-charcoal-800/80">Reviews &amp; Videos</a>

                    <!-- Special Sale Badge -->
                    <a href="shop.php?discount=1" class="transition-colors hover:text-blush-600 text-blush-600 font-semibold flex items-center gap-1.5">
                        <span>Offers</span>
                        <span class="text-[10px] bg-blush-100 text-blush-700 px-2 py-0.5 rounded-full font-bold">2 PRICES / SALE</span>
                    </a>
                </nav>

                <!-- Center: Authentic Brand Logo (From Image) -->
                <div class="flex-shrink-0 text-center">
                    <a href="index.php" class="group inline-flex items-center gap-3">
                        <img 
                            src="<?= STORE_LOGO ?>" 
                            alt="<?= STORE_NAME ?>" 
                            class="w-11 h-11 sm:w-12 sm:h-12 rounded-full object-cover border-2 border-gold-400 shadow-sm group-hover:scale-105 transition-transform"
                        >
                        <div class="text-left">
                            <span class="font-serif text-xl sm:text-2xl font-bold tracking-tight text-charcoal-900 group-hover:text-blush-700 transition-colors block leading-none">
                                <?= STORE_NAME ?>
                            </span>
                            <span class="text-[8px] sm:text-[9px] tracking-[0.24em] uppercase text-charcoal-800/60 font-bold block mt-1">
                                Luxury Spiritual &amp; Botanical Candles
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Right: Action Icons -->
                <div class="flex items-center space-x-3 sm:space-x-5">
                    <!-- Search Icon / Link -->
                    <a href="shop.php" class="text-charcoal-800/80 hover:text-blush-600 transition-colors p-1.5" title="Search Candles">
                        <i data-lucide="search" class="w-5 h-5"></i>
                    </a>

                    <!-- Admin Portal Link -->
                    <a href="admin/login.php" class="text-charcoal-800/80 hover:text-blush-600 transition-colors p-1.5 flex items-center gap-1.5 text-xs font-semibold" title="Admin Control Panel">
                        <i data-lucide="shield-check" class="w-5 h-5 text-blush-600"></i>
                        <span class="hidden sm:inline bg-cream-200/90 px-2.5 py-1 rounded-full border border-cream-300">Admin</span>
                    </a>

                    <!-- Shopping Cart Button -->
                    <a href="cart.php" class="relative group inline-flex items-center p-2 rounded-full hover:bg-blush-100/60 transition-all">
                        <i data-lucide="shopping-bag" class="w-5 h-5 text-charcoal-900 group-hover:text-blush-600 transition-colors"></i>
                        <span id="cart-badge-count" class="absolute -top-1 -right-1 bg-blush-600 text-white text-[11px] font-bold w-5 h-5 rounded-full flex items-center justify-center shadow-sm transform scale-100 transition-transform group-hover:scale-110">
                            <?= $cartCount ?>
                        </span>
                    </a>

                    <!-- Mobile Menu Button -->
                    <button id="mobile-menu-btn" class="lg:hidden text-charcoal-800 hover:text-blush-600 p-1" aria-label="Toggle Navigation">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer Menu -->
        <div id="mobile-menu" class="hidden lg:hidden bg-[#FAF7F2] border-t border-[#ebdccd]/80 px-6 py-5 space-y-4 shadow-xl">
            <a href="index.php" class="block text-sm font-semibold text-charcoal-800 hover:text-blush-600">Home</a>
            <a href="shop.php" class="block text-sm font-semibold text-charcoal-800 hover:text-blush-600">All Collections</a>
            
            <div class="pt-2 border-t border-[#ebdccd]/60">
                <span class="text-[10px] uppercase font-bold tracking-wider text-charcoal-800/50 block mb-2">Categories:</span>
                <div class="grid grid-cols-1 gap-2 pl-2">
                    <?php foreach ($navCategories as $cat): ?>
                        <a href="shop.php?category=<?= urlencode($cat['name']) ?>" class="text-xs font-medium text-charcoal-800 hover:text-blush-600">
                            ✧ <?= htmlspecialchars($cat['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <a href="index.php#reviews" class="block text-sm font-semibold text-charcoal-800 hover:text-blush-600">Customer Reviews &amp; Videos</a>
            <a href="shop.php?discount=1" class="block text-sm font-semibold text-blush-600">Special Discounted Offers</a>
            
            <div class="pt-3 border-t border-[#ebdccd]/60 flex justify-between items-center text-xs">
                <a href="cart.php" class="flex items-center gap-2 text-charcoal-800 font-bold">
                    <i data-lucide="shopping-bag" class="w-4 h-4 text-blush-600"></i> My Cart (<?= $cartCount ?>)
                </a>
                <a href="admin/login.php" class="text-xs bg-cream-200 px-3 py-1.5 rounded-full font-bold text-charcoal-800">Admin Portal</a>
            </div>
        </div>
    </header>

    <!-- Global Flash Alerts -->
    <?php if ($flash): ?>
        <div class="max-w-4xl mx-auto px-4 mt-4">
            <div class="rounded-2xl p-4 flex items-center gap-3 shadow-sm border <?= $flash['type'] === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' ?>">
                <i data-lucide="<?= $flash['type'] === 'success' ? 'check-circle-2' : 'alert-circle' ?>" class="w-5 h-5 flex-shrink-0"></i>
                <div class="text-sm font-medium"><?= htmlspecialchars($flash['message']) ?></div>
            </div>
        </div>
    <?php endif; ?>

    <main class="flex-grow">
