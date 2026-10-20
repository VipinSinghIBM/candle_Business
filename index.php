<?php
$pageTitle = "Artisanal Spiritual & Botanical Candles";
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// Fetch Featured & Bestseller Candles
$featuredStmt = $db->query("SELECT * FROM products WHERE is_featured = 1 ORDER BY id ASC LIMIT 4");
$featuredProducts = $featuredStmt->fetchAll();

$bestsellerStmt = $db->query("SELECT * FROM products WHERE is_bestseller = 1 ORDER BY id DESC LIMIT 4");
$bestsellerProducts = $bestsellerStmt->fetchAll();

// Dynamic Categories
$categories = getCategories(true);

// Customer Reviews & Satisfaction Data (Text & YouTube Video Reviews)
$reviews = getReviews('all', false);
?>

<!-- Hero Section with Shiv Vani Enterprises Emblem -->
<section class="relative overflow-hidden pt-8 pb-20 lg:pt-14 lg:pb-28">
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-blush-200/40 rounded-full blur-3xl pointer-events-none -z-10"></div>
    <div class="absolute top-1/3 right-10 w-[350px] h-[350px] bg-gold-400/20 rounded-full blur-2xl pointer-events-none -z-10"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
            
            <!-- Left Copy -->
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                
                <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-white border border-[#ebdccd] shadow-sm">
                    <img src="<?= STORE_LOGO ?>" alt="Logo" class="w-5 h-5 rounded-full object-cover border border-gold-400">
                    <span class="text-xs font-bold uppercase tracking-wider text-charcoal-900"><?= STORE_NAME ?></span>
                    <span class="text-gold-500 text-xs">✦</span>
                    <span class="text-[11px] font-semibold text-blush-700">Artisanal Purity</span>
                </div>

                <h1 class="font-serif text-4xl sm:text-6xl lg:text-7xl font-bold tracking-tight text-charcoal-900 leading-[1.08]">
                    Sacred Ambiance &amp; <span class="italic font-normal text-blush-600 block sm:inline">Hand-Poured Purity.</span>
                </h1>

                <p class="text-base sm:text-lg text-charcoal-800/80 max-w-xl mx-auto lg:mx-0 font-light leading-relaxed">
                    Welcome to <strong><?= STORE_NAME ?></strong>. We formulate clean-burning soy-coconut candles infused with pure Mysore sandalwood, royal mogra, velvet roses, and therapeutic botanical oils. Designed for quiet prayer, reading, and deep serenity.
                </p>

                <!-- Action CTAs -->
                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-2">
                    <a href="shop.php" class="btn-luxury w-full sm:w-auto px-8 py-4 rounded-full bg-charcoal-900 hover:bg-blush-700 text-cream-50 font-semibold text-xs tracking-widest uppercase transition-all shadow-lg hover:shadow-xl text-center">
                        Explore All Collections
                    </a>
                    <a href="shop.php?discount=1" class="w-full sm:w-auto px-8 py-4 rounded-full bg-white/90 hover:bg-white text-charcoal-800 border border-[#ebdccd] font-semibold text-xs tracking-wider uppercase transition-all shadow-sm hover:shadow-md text-center flex items-center justify-center gap-2">
                        <i data-lucide="tag" class="w-4 h-4 text-blush-600"></i>
                        <span>Special Offers (Save up to 30%)</span>
                    </a>
                </div>

                <!-- Trust Badges -->
                <div class="pt-6 grid grid-cols-3 gap-4 border-t border-[#ebdccd]/80 max-w-lg mx-auto lg:mx-0 text-center lg:text-left">
                    <div>
                        <p class="font-serif text-2xl font-bold text-charcoal-900">100%</p>
                        <p class="text-xs text-charcoal-800/60 font-medium">Organic Plant Wax</p>
                    </div>
                    <div>
                        <p class="font-serif text-2xl font-bold text-charcoal-900">65+ Hrs</p>
                        <p class="text-xs text-charcoal-800/60 font-medium">Even Clean Burn</p>
                    </div>
                    <div>
                        <p class="font-serif text-2xl font-bold text-charcoal-900">5.0 ★</p>
                        <p class="text-xs text-charcoal-800/60 font-medium">Patron Satisfaction</p>
                    </div>
                </div>
            </div>

            <!-- Right Hero Visual -->
            <div class="lg:col-span-5 relative">
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    
                    <div class="absolute -inset-3 rounded-3xl bg-gradient-to-tr from-blush-300/40 via-gold-400/30 to-cream-200/50 blur-lg"></div>

                    <div class="relative rounded-3xl overflow-hidden shadow-2xl border-4 border-white aspect-[4/5] bg-cream-100">
                        <img 
                            src="https://images.unsplash.com/photo-1602874801007-bd458bb1b8b8?auto=format&fit=crop&w=1000&q=85" 
                            alt="Kashi Chandan & Royal Mogra - Shiv Vani Enterprises" 
                            class="w-full h-full object-cover object-center transform hover:scale-105 transition-transform duration-700"
                        >

                        <!-- Floating Glass Card with 2 Prices -->
                        <div class="absolute bottom-6 left-6 right-6 p-4 rounded-2xl glass-panel shadow-lg flex items-center justify-between">
                            <div>
                                <span class="text-[10px] uppercase font-bold tracking-widest text-gold-700 block flex items-center gap-1">
                                    <i data-lucide="sparkles" class="w-3 h-3"></i> Masterpiece
                                </span>
                                <h4 class="font-serif text-lg font-bold text-charcoal-900">Kashi Chandan &amp; Royal Mogra</h4>
                                <p class="text-xs text-charcoal-800/70">Pure Mysore Sandalwood • Royal Mogra</p>
                            </div>
                            <div class="text-right">
                                <span class="text-xs line-through text-charcoal-800/50 block">M.R.P. <?= STORE_CURRENCY ?> 899.00</span>
                                <span class="text-lg font-bold text-blush-700 font-serif block"><?= STORE_CURRENCY ?> 674.25</span>
                            </div>
                        </div>

                        <!-- 25% Discount badge -->
                        <div class="absolute top-4 right-4 bg-blush-600 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-md tracking-wider uppercase">
                            25% OFF
                        </div>

                        <!-- Brand Emblem Watermark -->
                        <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-md p-1.5 rounded-full shadow-md border border-gold-400/50 flex items-center gap-2 pr-3">
                            <img src="<?= STORE_LOGO ?>" alt="Shiv Vani Logo" class="w-6 h-6 rounded-full object-cover">
                            <span class="text-[10px] font-bold tracking-wider uppercase text-charcoal-900">Shiv Vani</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Dynamic Mood & Scent Category Pills (Fetched From Database) -->
<section class="py-8 border-y border-[#ebdccd]/80 bg-white/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-start sm:justify-center gap-3 overflow-x-auto pb-2 scrollbar-none">
            <a href="shop.php" class="px-5 py-2.5 rounded-full bg-charcoal-900 text-white text-xs font-semibold tracking-wider uppercase whitespace-nowrap shadow-sm">
                All Collections
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="shop.php?category=<?= urlencode($cat['name']) ?>" class="px-5 py-2.5 rounded-full bg-cream-100 hover:bg-blush-100 text-charcoal-800 text-xs font-semibold tracking-wider uppercase whitespace-nowrap transition-colors border border-[#ebdccd] flex items-center gap-1.5">
                    <span>✧</span>
                    <span><?= htmlspecialchars($cat['name']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Candles Grid (With Clear 2 Prices & Discount Rate) -->
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12">
            <div>
                <span class="text-xs uppercase tracking-[0.25em] text-blush-600 font-bold block mb-1">Handcrafted Selections</span>
                <h2 class="font-serif text-3xl sm:text-4xl text-charcoal-900 font-bold">Featured Artisanal Candles</h2>
                <p class="text-xs text-charcoal-800/60 mt-1">Showing both Regular M.R.P. &amp; Discounted Offer Rates</p>
            </div>
            <a href="shop.php" class="mt-4 md:mt-0 text-xs font-bold text-charcoal-900 hover:text-blush-600 inline-flex items-center gap-1 group transition-colors uppercase tracking-wider">
                <span>View Full Catalog</span>
                <i data-lucide="arrow-right" class="w-4 h-4 transform group-hover:translate-x-1 transition-transform"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php foreach ($featuredProducts as $item): ?>
                <div class="product-card group bg-white rounded-3xl overflow-hidden border border-[#ebdccd] flex flex-col shadow-sm">
                    
                    <!-- Thumbnail container -->
                    <div class="relative overflow-hidden aspect-square bg-cream-50">
                        <a href="product.php?id=<?= $item['id'] ?>" class="block w-full h-full">
                            <img 
                                src="<?= htmlspecialchars($item['image_url']) ?>" 
                                alt="<?= htmlspecialchars($item['name']) ?>" 
                                class="product-img w-full h-full object-cover object-center"
                            >
                        </a>

                        <!-- Discount Rate Badge -->
                        <?php if ($item['discount_percent'] > 0): ?>
                            <span class="absolute top-3 left-3 bg-blush-600 text-white text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider shadow-sm">
                                <?= $item['discount_percent'] ?>% OFF
                            </span>
                        <?php endif; ?>

                        <span class="absolute bottom-3 left-3 bg-white/90 backdrop-blur-sm text-charcoal-800 text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                            <?= htmlspecialchars($item['category']) ?>
                        </span>
                    </div>

                    <!-- Details -->
                    <div class="p-6 flex flex-col flex-grow justify-between">
                        <div>
                            <!-- Rating -->
                            <div class="flex items-center gap-1 mb-2">
                                <div class="flex text-gold-500">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                        <i data-lucide="star" class="w-3.5 h-3.5 fill-gold-500"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="text-[11px] text-charcoal-800/60 font-semibold ml-1">(<?= $item['reviews_count'] ?> reviews)</span>
                            </div>

                            <a href="product.php?id=<?= $item['id'] ?>" class="block">
                                <h3 class="font-serif text-lg font-bold text-charcoal-900 group-hover:text-blush-600 transition-colors leading-snug">
                                    <?= htmlspecialchars($item['name']) ?>
                                </h3>
                            </a>

                            <p class="text-xs text-charcoal-800/70 mt-1.5 line-clamp-2">
                                <?= htmlspecialchars($item['short_desc']) ?>
                            </p>

                            <div class="mt-2 text-[11px] text-gold-700 font-medium tracking-wide">
                                <span>✧ <?= htmlspecialchars($item['scent_notes']) ?></span>
                            </div>
                        </div>

                        <!-- 2 PRICES DISPLAY (Regular & Discounted) -->
                        <div class="pt-5 mt-4 border-t border-[#f2e6d9] flex items-center justify-between">
                            <div>
                                <!-- Discounted Selling Rate -->
                                <span class="font-serif text-xl font-bold text-charcoal-900 block leading-none">
                                    <?= formatPrice($item['final_price']) ?>
                                </span>
                                <!-- Regular Price with Strike-through -->
                                <?php if ($item['discount_percent'] > 0): ?>
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <span class="text-[11px] text-charcoal-800/50 line-through">
                                            M.R.P: <?= formatPrice($item['price']) ?>
                                        </span>
                                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-1 rounded">
                                            Save <?= formatPrice($item['price'] - $item['final_price']) ?>
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-[10px] text-charcoal-800/50 block mt-1">Regular M.R.P. Rate</span>
                                <?php endif; ?>
                            </div>

                            <!-- Add To Bag Form -->
                            <form action="cart.php" method="POST" class="inline">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="bg-blush-50 hover:bg-blush-600 hover:text-white text-blush-700 p-2.5 rounded-full transition-all duration-200 border border-blush-200 shadow-sm" title="Add to Bag">
                                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- Luxury Promo / Coupon Callout Banner -->
<section class="py-12 bg-charcoal-900 text-cream-50 relative overflow-hidden">
    <div class="absolute -right-20 -bottom-20 w-80 h-80 rounded-full bg-blush-500/20 blur-3xl pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="rounded-3xl border border-charcoal-700 p-8 sm:p-12 bg-gradient-to-r from-charcoal-900 via-[#27201e] to-charcoal-900 flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="space-y-3 text-center md:text-left">
                <span class="text-xs uppercase tracking-[0.25em] text-gold-400 font-semibold">Shiv Vani Festive Courtesy</span>
                <h3 class="font-serif text-3xl sm:text-4xl font-bold">Flat 15% Off Your Entire Sacred Order</h3>
                <p class="text-sm text-cream-100/70 max-w-lg">
                    Experience authentic hand-poured temple &amp; botanical candles. Pay securely via QR scan or card at checkout.
                </p>
            </div>
            <div class="flex flex-col sm:flex-row items-center gap-4 flex-shrink-0">
                <div class="bg-charcoal-800 border border-gold-400/50 rounded-2xl px-6 py-3.5 text-center">
                    <span class="text-[10px] tracking-widest uppercase text-cream-100/60 block">Coupon Code</span>
                    <strong class="font-mono text-xl tracking-wider text-gold-400 font-bold">SHIVVANI15</strong>
                </div>
                <a href="shop.php" class="px-8 py-4 rounded-2xl bg-blush-600 hover:bg-blush-700 text-white font-semibold text-xs tracking-wider uppercase transition-colors shadow-lg">
                    Shop with Discount
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ========================================================================= -->
<!-- CUSTOMER REVIEW & SATISFACTION SECTION WITH STAR RATING + TEXT & VIDEOS   -->
<!-- ========================================================================= -->
<section id="reviews" class="py-20 bg-[#f7f0e6]/70 border-t border-[#ebdccd]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Header with Satisfaction Rating Score -->
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="text-xs uppercase tracking-[0.25em] text-blush-600 font-bold block mb-1">Patron Love &amp; Experiences</span>
            <h2 class="font-serif text-3xl sm:text-4xl font-bold text-charcoal-900">Customer Reviews &amp; Satisfaction</h2>
            
            <!-- Overall Rating Bar -->
            <div class="mt-4 inline-flex items-center gap-3 bg-white px-5 py-2.5 rounded-full border border-[#ebdccd] shadow-sm">
                <div class="flex text-gold-500">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <i data-lucide="star" class="w-4 h-4 fill-gold-500"></i>
                    <?php endfor; ?>
                </div>
                <span class="font-bold text-charcoal-900 text-sm">4.9 / 5.0 Rating</span>
                <span class="text-xs text-charcoal-800/60">&bull; 500+ Verified Buyers across India</span>
            </div>
        </div>

        <!-- Review Category Filter Tabs (All, Written, Video) -->
        <div class="flex items-center justify-center gap-3 mb-10">
            <button onclick="filterReviews('all', this)" class="review-tab-btn active px-5 py-2 rounded-full text-xs font-bold uppercase tracking-wider bg-charcoal-900 text-white shadow-sm transition-all">
                All Reviews (<?= count($reviews) ?>)
            </button>
            <button onclick="filterReviews('text', this)" class="review-tab-btn px-5 py-2 rounded-full text-xs font-bold uppercase tracking-wider bg-white border border-[#ebdccd] text-charcoal-800 hover:bg-blush-50 transition-all">
                Written Testimonials
            </button>
            <button onclick="filterReviews('video', this)" class="review-tab-btn px-5 py-2 rounded-full text-xs font-bold uppercase tracking-wider bg-white border border-[#ebdccd] text-charcoal-800 hover:bg-blush-50 transition-all flex items-center gap-1.5">
                <i data-lucide="play-circle" class="w-3.5 h-3.5 text-rose-600"></i>
                <span>YouTube Video Reviews</span>
            </button>
        </div>

        <!-- Uniform Reviews Grid (Text & Video Cards with same dimensions) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8" id="reviews-grid">
            <?php foreach ($reviews as $rev): ?>
                <div class="review-card-item bg-white rounded-3xl border border-[#ebdccd] shadow-sm overflow-hidden flex flex-col justify-between transition-all hover:shadow-md" data-type="<?= $rev['review_type'] ?>">
                    
                    <!-- Top Section: Video Frame OR Quote Header -->
                    <?php if ($rev['review_type'] === 'video' && !empty($rev['video_url'])): ?>
                        <!-- YouTube Video Frame (Exact Proportion & Responsive) -->
                        <div class="relative aspect-video w-full bg-charcoal-900 overflow-hidden border-b border-[#ebdccd]">
                            <iframe 
                                src="<?= htmlspecialchars($rev['video_url']) ?>" 
                                title="<?= htmlspecialchars($rev['title']) ?>"
                                class="w-full h-full border-0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen
                            ></iframe>
                        </div>
                    <?php else: ?>
                        <!-- Text Testimonial Decorative Header -->
                        <div class="p-6 pb-0 flex items-center justify-between">
                            <span class="text-3xl font-serif text-blush-300 font-bold leading-none">&ldquo;</span>
                            <span class="text-[10px] bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold px-2 py-0.5 rounded-full flex items-center gap-1">
                                <i data-lucide="check-circle" class="w-3 h-3 text-emerald-600"></i> Verified Purchase
                            </span>
                        </div>
                    <?php endif; ?>

                    <!-- Body Content -->
                    <div class="p-6 flex-grow flex flex-col justify-between">
                        <div>
                            <!-- Star Rating Mark -->
                            <div class="flex items-center gap-1 text-gold-500 mb-2">
                                <?php for ($s = 0; $s < (int)$rev['rating']; $s++): ?>
                                    <i data-lucide="star" class="w-4 h-4 fill-gold-500"></i>
                                <?php endfor; ?>
                                <span class="text-xs font-bold text-charcoal-900 ml-1"><?= $rev['rating'] ?>.0</span>
                            </div>

                            <h4 class="font-serif text-base font-bold text-charcoal-900 mb-2 leading-snug">
                                <?= htmlspecialchars($rev['title']) ?>
                            </h4>

                            <p class="text-xs text-charcoal-800/80 leading-relaxed mb-4">
                                <?= nl2br(htmlspecialchars($rev['content'])) ?>
                            </p>
                        </div>

                        <!-- Card Footer / Patron Details -->
                        <div class="pt-4 border-t border-[#f2e6d9] flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-blush-100 border border-blush-200 text-blush-800 font-bold flex items-center justify-center text-xs">
                                    <?= strtoupper(substr($rev['author_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <h5 class="text-xs font-bold text-charcoal-900"><?= htmlspecialchars($rev['author_name']) ?></h5>
                                    <span class="text-[10px] text-charcoal-800/60"><?= htmlspecialchars($rev['author_city']) ?></span>
                                </div>
                            </div>
                            
                            <?php if (!empty($rev['product_name'])): ?>
                                <span class="text-[10px] text-gold-700 bg-gold-50 border border-gold-200 px-2 py-0.5 rounded-full font-semibold max-w-[130px] truncate" title="<?= htmlspecialchars($rev['product_name']) ?>">
                                    <?= htmlspecialchars($rev['product_name']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                    </div>

                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- Review Filtering Tab JS -->
<script>
    function filterReviews(type, btn) {
        document.querySelectorAll('.review-tab-btn').forEach(b => {
            b.classList.remove('bg-charcoal-900', 'text-white');
            b.classList.add('bg-white', 'text-charcoal-800');
        });
        btn.classList.add('bg-charcoal-900', 'text-white');
        btn.classList.remove('bg-white', 'text-charcoal-800');

        document.querySelectorAll('.review-card-item').forEach(card => {
            const cardType = card.getAttribute('data-type');
            if (type === 'all' || cardType === type) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
