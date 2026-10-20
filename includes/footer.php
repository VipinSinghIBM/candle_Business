    </main>

    <!-- Boutique Features / Value Proposition Banner -->
    <section class="mt-20 border-t border-[#ebdccd]/80 bg-[#f7f2ea]/70 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div class="flex flex-col items-center">
                    <div class="w-12 h-12 rounded-full bg-blush-100 flex items-center justify-center text-blush-600 mb-3 shadow-sm border border-blush-200">
                        <i data-lucide="sparkles" class="w-6 h-6 text-gold-600"></i>
                    </div>
                    <h4 class="font-serif text-lg font-bold text-charcoal-900">Hand-Poured by Shiv Vani</h4>
                    <p class="text-xs text-charcoal-800/70 mt-1 max-w-[200px]">Small batch artisanal candles poured with pure coconut-soy wax.</p>
                </div>

                <div class="flex flex-col items-center">
                    <div class="w-12 h-12 rounded-full bg-blush-100 flex items-center justify-center text-blush-600 mb-3 shadow-sm border border-blush-200">
                        <i data-lucide="flower-2" class="w-6 h-6 text-blush-600"></i>
                    </div>
                    <h4 class="font-serif text-lg font-bold text-charcoal-900">Spiritual &amp; Clean Oils</h4>
                    <p class="text-xs text-charcoal-800/70 mt-1 max-w-[200px]">Sandalwood, Mogra, Rose &amp; botanicals. 100% Phthalate-free.</p>
                </div>

                <div class="flex flex-col items-center">
                    <div class="w-12 h-12 rounded-full bg-blush-100 flex items-center justify-center text-blush-600 mb-3 shadow-sm border border-blush-200">
                        <i data-lucide="qr-code" class="w-6 h-6 text-emerald-600"></i>
                    </div>
                    <h4 class="font-serif text-lg font-bold text-charcoal-900">Scan &amp; Pay via UPI</h4>
                    <p class="text-xs text-charcoal-800/70 mt-1 max-w-[200px]">Instant Google Pay, PhonePe, Cards &amp; COD payment options.</p>
                </div>

                <div class="flex flex-col items-center">
                    <div class="w-12 h-12 rounded-full bg-blush-100 flex items-center justify-center text-blush-600 mb-3 shadow-sm border border-blush-200">
                        <i data-lucide="gift" class="w-6 h-6 text-gold-600"></i>
                    </div>
                    <h4 class="font-serif text-lg font-bold text-charcoal-900">Luxury Gift Packaging</h4>
                    <p class="text-xs text-charcoal-800/70 mt-1 max-w-[200px]">Complimentary premium packaging, velvet ribbon &amp; matchbox.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Luxury Footer -->
    <footer class="bg-charcoal-900 text-cream-100 pt-16 pb-12 border-t border-charcoal-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-12 pb-12 border-b border-charcoal-800">
                
                <!-- Brand Story with Real Logo -->
                <div class="md:col-span-5 space-y-4">
                    <div class="flex items-center gap-3">
                        <img src="<?= STORE_LOGO ?>" alt="<?= STORE_NAME ?>" class="w-12 h-12 rounded-full object-cover border-2 border-gold-400">
                        <div>
                            <span class="font-serif text-2xl tracking-tight text-cream-50 font-bold block">
                                <?= STORE_NAME ?>
                            </span>
                            <span class="text-[9px] uppercase tracking-[0.2em] text-gold-400 font-semibold block">
                                <?= STORE_TAGLINE ?>
                            </span>
                        </div>
                    </div>
                    <p class="text-sm text-cream-100/70 leading-relaxed max-w-md">
                        <strong><?= STORE_NAME ?></strong> is committed to bringing sacred serenity, positive vibration, and aesthetic luxury into modern sanctuaries. Hand-formulated using clean-burning waxes, crackling wood wicks, and pure essential extracts.
                    </p>
                    <div class="pt-2 flex items-center space-x-3 text-gold-400 text-xs tracking-wider uppercase font-semibold">
                        <span>Pure Handcrafted Artisanal Candles</span>
                        <span>•</span>
                        <span>Direct From Atelier</span>
                    </div>
                </div>

                <!-- Dynamic Categories Navigation -->
                <div class="md:col-span-3 space-y-3">
                    <h5 class="text-xs tracking-[0.2em] uppercase font-bold text-gold-400">Scent Categories</h5>
                    <ul class="space-y-2 text-sm text-cream-100/70">
                        <?php 
                        $footerCats = getCategories(true);
                        foreach (array_slice($footerCats, 0, 5) as $fCat): 
                        ?>
                            <li>
                                <a href="shop.php?category=<?= urlencode($fCat['name']) ?>" class="hover:text-blush-300 transition-colors">
                                    <?= htmlspecialchars($fCat['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <li><a href="shop.php?discount=1" class="hover:text-blush-300 transition-colors text-blush-400 font-medium">Special Discounted Sale (2 Prices)</a></li>
                    </ul>
                </div>

                <!-- Newsletter & VIP Access -->
                <div class="md:col-span-4 space-y-4">
                    <h5 class="text-xs tracking-[0.2em] uppercase font-bold text-gold-400">Shiv Vani Privilege Club</h5>
                    <p class="text-sm text-cream-100/70">
                        Join our family to receive direct alerts on limited edition candle drops, sacred festival festive editions, and exclusive member discounts.
                    </p>
                    <form onsubmit="event.preventDefault(); alert('Dhanyavaad! You have been enrolled in Shiv Vani Privilege with coupon code SHIVVANI15.');" class="flex flex-col sm:flex-row gap-2">
                        <input type="email" required placeholder="your.email@domain.com" class="bg-charcoal-800 border border-charcoal-700 rounded-xl px-4 py-2.5 text-sm text-cream-50 placeholder-cream-100/40 focus:outline-none focus:border-blush-400 flex-grow">
                        <button type="submit" class="bg-blush-600 hover:bg-blush-700 text-white font-semibold text-xs uppercase tracking-wider px-5 py-2.5 rounded-xl transition-colors shadow-sm">
                            Join Club
                        </button>
                    </form>
                </div>
            </div>

            <!-- Bottom Disclaimer & Copyright -->
            <div class="pt-8 flex flex-col md:flex-row justify-between items-center text-xs text-cream-100/50 gap-4">
                <p>&copy; <?= date('Y') ?> <strong><?= STORE_NAME ?></strong>. Handcrafted with devotion &amp; intention. All rights reserved.</p>
                <div class="flex items-center space-x-6">
                    <a href="admin/login.php" class="hover:text-cream-100 underline decoration-blush-400 underline-offset-4 font-bold">Admin Portal</a>
                    <span>•</span>
                    <a href="shop.php" class="hover:text-cream-100">Quality Assurance</a>
                    <span>•</span>
                    <a href="shop.php" class="hover:text-cream-100">Shipping &amp; Delivery</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Init Lucide Icons -->
    <script>
        lucide.createIcons();

        // Mobile drawer toggle
        const menuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        if (menuBtn && mobileMenu) {
            menuBtn.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });
        }
    </script>
    <script src="assets/js/main.js"></script>
</body>
</html>
