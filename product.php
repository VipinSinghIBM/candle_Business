<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: shop.php');
    exit;
}

$pageTitle = $product['name'];
require_once __DIR__ . '/includes/header.php';

// Fetch related items from the same category
$relatedStmt = $db->prepare("SELECT * FROM products WHERE category = ? AND id != ? LIMIT 3");
$relatedStmt->execute([$product['category'], $product['id']]);
$related = $relatedStmt->fetchAll();

// Split scent notes into pills
$notes = array_map('trim', explode('•', $product['scent_notes']));
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-charcoal-800/60 mb-8">
        <a href="index.php" class="hover:text-blush-600">Home</a>
        <span>/</span>
        <a href="shop.php" class="hover:text-blush-600">Catalog</a>
        <span>/</span>
        <a href="shop.php?category=<?= urlencode($product['category']) ?>" class="hover:text-blush-600"><?= htmlspecialchars($product['category']) ?></a>
        <span>/</span>
        <span class="text-charcoal-900 font-bold truncate"><?= htmlspecialchars($product['name']) ?></span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
        
        <!-- Left Column: Vessel Image -->
        <div class="lg:col-span-6">
            <div class="relative rounded-3xl overflow-hidden border border-[#ebdccd] bg-white shadow-lg aspect-square">
                <img 
                    src="<?= htmlspecialchars($product['image_url']) ?>" 
                    alt="<?= htmlspecialchars($product['name']) ?>" 
                    class="w-full h-full object-cover object-center"
                >

                <?php if ($product['discount_percent'] > 0): ?>
                    <div class="absolute top-4 left-4 bg-blush-600 text-white font-bold text-xs uppercase px-3.5 py-1.5 rounded-full shadow-md tracking-wider">
                        <?= $product['discount_percent'] ?>% DISCOUNT OFFER
                    </div>
                <?php endif; ?>

                <?php if ($product['is_bestseller']): ?>
                    <div class="absolute top-4 right-4 bg-charcoal-900 text-cream-50 font-bold text-[10px] uppercase px-3 py-1.5 rounded-full shadow-md tracking-wider">
                        ★ Shiv Vani Bestseller
                    </div>
                <?php endif; ?>

                <!-- Logo Watermark Pill -->
                <div class="absolute bottom-4 left-4 bg-white/95 backdrop-blur-md px-3 py-1.5 rounded-full border border-gold-400 shadow-sm flex items-center gap-2">
                    <img src="<?= STORE_LOGO ?>" alt="" class="w-5 h-5 rounded-full object-cover">
                    <span class="text-[10px] font-bold text-charcoal-900"><?= STORE_NAME ?></span>
                </div>
            </div>

            <!-- Artisan Assurance Cards -->
            <div class="grid grid-cols-3 gap-4 mt-6">
                <div class="p-4 rounded-2xl bg-white border border-[#ebdccd] text-center shadow-sm">
                    <i data-lucide="flame" class="w-5 h-5 mx-auto text-gold-600 mb-1"></i>
                    <p class="text-[10px] uppercase tracking-wider text-charcoal-800/60 font-semibold">Burn Time</p>
                    <p class="font-serif text-sm font-bold text-charcoal-900 mt-0.5"><?= htmlspecialchars($product['burn_time']) ?></p>
                </div>

                <div class="p-4 rounded-2xl bg-white border border-[#ebdccd] text-center shadow-sm">
                    <i data-lucide="leaf" class="w-5 h-5 mx-auto text-emerald-600 mb-1"></i>
                    <p class="text-[10px] uppercase tracking-wider text-charcoal-800/60 font-semibold">Clean Wax</p>
                    <p class="font-serif text-sm font-bold text-charcoal-900 mt-0.5"><?= htmlspecialchars($product['wax_type']) ?></p>
                </div>

                <div class="p-4 rounded-2xl bg-white border border-[#ebdccd] text-center shadow-sm">
                    <i data-lucide="scale" class="w-5 h-5 mx-auto text-blush-600 mb-1"></i>
                    <p class="text-[10px] uppercase tracking-wider text-charcoal-800/60 font-semibold">Net Weight</p>
                    <p class="font-serif text-sm font-bold text-charcoal-900 mt-0.5"><?= htmlspecialchars($product['weight']) ?></p>
                </div>
            </div>
        </div>

        <!-- Right Column: Product Narrative & Add to Cart -->
        <div class="lg:col-span-6 space-y-6">
            
            <div>
                <span class="text-xs uppercase tracking-[0.25em] text-blush-600 font-bold block mb-1">
                    <?= htmlspecialchars($product['category']) ?>
                </span>
                <h1 class="font-serif text-3xl sm:text-4xl lg:text-5xl font-bold text-charcoal-900 leading-tight">
                    <?= htmlspecialchars($product['name']) ?>
                </h1>

                <!-- Rating -->
                <div class="flex items-center gap-2 mt-3">
                    <div class="flex text-gold-500">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <i data-lucide="star" class="w-4 h-4 fill-gold-500"></i>
                        <?php endfor; ?>
                    </div>
                    <span class="text-xs font-bold text-charcoal-900"><?= $product['rating'] ?></span>
                    <span class="text-xs text-charcoal-800/60">&bull; <?= $product['reviews_count'] ?> Verified Patron Ratings</span>
                </div>
            </div>

            <!-- 2 PRICES DISPLAY (Regular & Discounted Price Box) -->
            <div class="p-5 rounded-2xl bg-white border border-[#ebdccd] shadow-sm flex flex-wrap items-baseline gap-4">
                <div>
                    <span class="text-[10px] uppercase font-bold tracking-wider text-charcoal-800/60 block mb-0.5">Special Offer Rate</span>
                    <span class="font-serif text-3xl font-bold text-charcoal-900">
                        <?= formatPrice($product['final_price']) ?>
                    </span>
                </div>

                <?php if ($product['discount_percent'] > 0): ?>
                    <div class="border-l border-[#ebdccd] pl-4">
                        <span class="text-[10px] uppercase font-bold tracking-wider text-charcoal-800/60 block mb-0.5">Regular Price (M.R.P.)</span>
                        <span class="text-base text-charcoal-800/50 line-through">
                            <?= formatPrice($product['price']) ?>
                        </span>
                    </div>

                    <div class="w-full sm:w-auto">
                        <span class="text-xs font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full inline-block">
                            Save <?= formatPrice($product['price'] - $product['final_price']) ?> (<?= $product['discount_percent'] ?>% OFF)
                        </span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Scent Notes Pill Tags -->
            <div>
                <h4 class="text-xs uppercase tracking-widest font-bold text-charcoal-900 mb-2.5 flex items-center gap-1.5">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-gold-600"></i>
                    <span>Fragrance Notes &amp; Botanical Profile</span>
                </h4>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($notes as $note): ?>
                        <span class="px-3.5 py-1.5 rounded-full bg-cream-100 border border-[#ebdccd] text-xs font-semibold text-charcoal-800 shadow-sm">
                            ✦ <?= htmlspecialchars($note) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Description -->
            <div class="prose prose-sm text-charcoal-800/80 leading-relaxed pt-2">
                <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            </div>

            <!-- Add to Cart Form -->
            <form action="cart.php" method="POST" class="pt-4 border-t border-[#ebdccd] space-y-4">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                <div class="flex items-center gap-4">
                    <!-- Quantity Stepper -->
                    <div class="qty-container flex items-center border border-[#ebdccd] rounded-full bg-white px-2 py-1 shadow-sm">
                        <button type="button" class="qty-btn-minus w-8 h-8 flex items-center justify-center text-charcoal-800 hover:text-blush-600 font-bold transition-colors">
                            &minus;
                        </button>
                        <input type="number" name="quantity" value="1" min="1" max="<?= max(1, $product['stock']) ?>" class="qty-input w-12 text-center bg-transparent text-sm font-bold text-charcoal-900 focus:outline-none">
                        <button type="button" class="qty-btn-plus w-8 h-8 flex items-center justify-center text-charcoal-800 hover:text-blush-600 font-bold transition-colors">
                            +
                        </button>
                    </div>

                    <!-- Add Button -->
                    <button type="submit" class="btn-luxury flex-grow bg-charcoal-900 hover:bg-blush-700 text-cream-50 font-bold py-3.5 px-8 rounded-full text-xs tracking-widest uppercase transition-all shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                        <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                        <span>Add To Bag • <?= formatPrice($product['final_price']) ?></span>
                    </button>
                </div>

                <!-- Stock availability message -->
                <div class="flex items-center gap-2 text-xs text-emerald-800 font-medium pt-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>In Stock (<?= $product['stock'] ?> candles ready at Shiv Vani atelier) &bull; Quick Dispatch</span>
                </div>
            </form>

            <!-- Shiv Vani Care Ritual -->
            <div class="p-5 rounded-2xl bg-cream-50/90 border border-[#ebdccd] space-y-2 mt-6">
                <h5 class="text-xs uppercase tracking-wider font-bold text-charcoal-900 flex items-center gap-1.5">
                    <i data-lucide="info" class="w-4 h-4 text-blush-600"></i>
                    <span>Shiv Vani Candle Care &amp; Burn Ritual</span>
                </h5>
                <p class="text-xs text-charcoal-800/70 leading-relaxed">
                    Always allow the candle to burn until the full wax pool reaches the glass rim on your first lighting. Trim wick to 5mm before every relighting for a soothing, crackle-free, and smokeless sacred ambiance.
                </p>
            </div>

        </div>
    </div>

    <!-- Related Aromas -->
    <?php if (count($related) > 0): ?>
        <div class="mt-24 pt-12 border-t border-[#ebdccd]">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <span class="text-xs uppercase tracking-widest text-blush-600 font-bold">Similar Fragrances</span>
                    <h3 class="font-serif text-2xl sm:text-3xl font-bold text-charcoal-900">You May Also Adore</h3>
                </div>
                <a href="shop.php?category=<?= urlencode($product['category']) ?>" class="text-xs font-bold uppercase tracking-wider text-charcoal-900 hover:text-blush-600">
                    Explore Category &rarr;
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
                <?php foreach ($related as $rel): ?>
                    <div class="product-card bg-white rounded-3xl overflow-hidden border border-[#ebdccd] flex flex-col shadow-sm">
                        <a href="product.php?id=<?= $rel['id'] ?>" class="aspect-square relative block bg-cream-50">
                            <img src="<?= htmlspecialchars($rel['image_url']) ?>" alt="<?= htmlspecialchars($rel['name']) ?>" class="product-img w-full h-full object-cover">
                            <?php if ($rel['discount_percent'] > 0): ?>
                                <span class="absolute top-3 left-3 bg-blush-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">
                                    <?= $rel['discount_percent'] ?>% OFF
                                </span>
                            <?php endif; ?>
                        </a>
                        <div class="p-5 flex flex-col justify-between flex-grow">
                            <div>
                                <a href="product.php?id=<?= $rel['id'] ?>" class="font-serif text-base font-bold text-charcoal-900 hover:text-blush-600">
                                    <?= htmlspecialchars($rel['name']) ?>
                                </a>
                                <p class="text-xs text-charcoal-800/60 mt-1">✧ <?= htmlspecialchars($rel['scent_notes']) ?></p>
                            </div>
                            <div class="pt-4 mt-3 border-t border-[#f2e6d9] flex items-center justify-between">
                                <div>
                                    <span class="font-serif text-lg font-bold text-charcoal-900"><?= formatPrice($rel['final_price']) ?></span>
                                    <?php if ($rel['discount_percent'] > 0): ?>
                                        <span class="text-[10px] text-charcoal-800/50 line-through block"><?= formatPrice($rel['price']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <a href="product.php?id=<?= $rel['id'] ?>" class="text-xs font-bold text-blush-600 hover:underline">View Candle</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
