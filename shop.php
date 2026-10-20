<?php
$pageTitle = "Artisanal Candle Collection";
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// Parameters
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$discountOnly = isset($_GET['discount']) && $_GET['discount'] == '1';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'featured';

// Fetch dynamic categories from database
$categoriesList = getCategories(true);

// Build Query
$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if (!empty($category)) {
    $sql .= " AND category = ?";
    $params[] = $category;
}

if ($discountOnly) {
    $sql .= " AND discount_percent > 0";
}

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR scent_notes LIKE ? OR description LIKE ?)";
    $term = "%{$search}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

// Sorting
switch ($sort) {
    case 'price_asc':
        $sql .= " ORDER BY final_price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY final_price DESC";
        break;
    case 'discount':
        $sql .= " ORDER BY discount_percent DESC";
        break;
    case 'rating':
        $sql .= " ORDER BY rating DESC";
        break;
    default:
        $sql .= " ORDER BY is_featured DESC, id ASC";
        break;
}

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Page Header Banner -->
    <div class="text-center max-w-2xl mx-auto mb-10">
        <span class="text-xs uppercase tracking-[0.25em] text-blush-600 font-bold block mb-1"><?= STORE_NAME ?></span>
        <h1 class="font-serif text-4xl sm:text-5xl text-charcoal-900 font-bold">The Scent Archive</h1>
        <p class="text-xs sm:text-sm text-charcoal-800/70 mt-2">
            Every candle displays both Regular M.R.P. &amp; Discounted Offer Rates. Poured with pure coconut-soy wax and sacred botanical extracts.
        </p>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white p-5 rounded-3xl border border-[#ebdccd] shadow-sm mb-10">
        <form method="GET" action="shop.php" class="flex flex-col lg:flex-row items-center justify-between gap-4">
            
            <!-- Dynamic Category Pills from Database -->
            <div class="flex items-center gap-2 overflow-x-auto w-full lg:w-auto pb-2 lg:pb-0 scrollbar-none">
                <a href="shop.php<?= $discountOnly ? '?discount=1' : '' ?>" class="px-4 py-2 rounded-full text-xs font-semibold uppercase tracking-wider transition-all whitespace-nowrap <?= empty($category) ? 'bg-charcoal-900 text-white' : 'bg-cream-100 text-charcoal-800 hover:bg-blush-100' ?>">
                    All Scents
                </a>
                <?php foreach ($categoriesList as $cat): ?>
                    <a href="shop.php?category=<?= urlencode($cat['name']) ?><?= $discountOnly ? '&discount=1' : '' ?>" class="px-4 py-2 rounded-full text-xs font-semibold uppercase tracking-wider transition-all whitespace-nowrap <?= $category === $cat['name'] ? 'bg-charcoal-900 text-white' : 'bg-cream-100 text-charcoal-800 hover:bg-blush-100' ?>">
                        <?= htmlspecialchars($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Search & Sort Controls -->
            <div class="flex flex-wrap sm:flex-nowrap items-center gap-3 w-full lg:w-auto justify-end">
                <!-- Search Input -->
                <div class="relative flex-grow sm:w-60">
                    <input 
                        type="text" 
                        name="search" 
                        value="<?= htmlspecialchars($search) ?>" 
                        placeholder="Search chandan, rose, mogra..." 
                        class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-full pl-9 pr-4 py-2 text-xs text-charcoal-900 placeholder-charcoal-800/40 focus:outline-none focus:border-blush-500"
                    >
                    <i data-lucide="search" class="w-4 h-4 text-charcoal-800/40 absolute left-3 top-2.5"></i>
                </div>

                <!-- Sort dropdown -->
                <select name="sort" onchange="this.form.submit()" class="bg-[#FAF7F2] border border-[#ebdccd] rounded-full px-4 py-2 text-xs font-semibold text-charcoal-800 focus:outline-none focus:border-blush-500">
                    <option value="featured" <?= $sort === 'featured' ? 'selected' : '' ?>>Featured</option>
                    <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                    <option value="discount" <?= $sort === 'discount' ? 'selected' : '' ?>>Biggest Discount %</option>
                    <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Top Rated</option>
                </select>

                <!-- Sale only toggle button -->
                <a href="shop.php?discount=<?= $discountOnly ? '0' : '1' ?><?= !empty($category) ? '&category=' . urlencode($category) : '' ?>" class="px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider border transition-all flex items-center gap-1.5 whitespace-nowrap <?= $discountOnly ? 'bg-blush-600 border-blush-600 text-white shadow-sm' : 'border-[#ebdccd] bg-[#FAF7F2] text-charcoal-800 hover:bg-blush-50' ?>">
                    <i data-lucide="percent" class="w-3.5 h-3.5"></i>
                    <span>Sale Items</span>
                </a>
            </div>

        </form>
    </div>

    <!-- Active Filters Display -->
    <?php if (!empty($category) || !empty($search) || $discountOnly): ?>
        <div class="flex items-center gap-2 mb-8 text-xs text-charcoal-800/70">
            <span>Filtering by:</span>
            <?php if (!empty($category)): ?>
                <span class="bg-blush-100 text-blush-800 font-semibold px-2.5 py-1 rounded-full flex items-center gap-1">
                    <?= htmlspecialchars($category) ?>
                    <a href="shop.php" class="hover:text-charcoal-900">&times;</a>
                </span>
            <?php endif; ?>
            <?php if (!empty($search)): ?>
                <span class="bg-cream-200 text-charcoal-900 font-semibold px-2.5 py-1 rounded-full flex items-center gap-1">
                    "<?= htmlspecialchars($search) ?>"
                    <a href="shop.php" class="hover:text-charcoal-900">&times;</a>
                </span>
            <?php endif; ?>
            <?php if ($discountOnly): ?>
                <span class="bg-rose-100 text-rose-800 font-semibold px-2.5 py-1 rounded-full">Sale Items (2 Prices)</span>
            <?php endif; ?>
            <a href="shop.php" class="text-blush-600 hover:underline ml-2">Clear all</a>
        </div>
    <?php endif; ?>

    <!-- Product Grid with 2 Prices & Discount Rate -->
    <?php if (count($products) > 0): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php foreach ($products as $item): ?>
                <div class="product-card group bg-white rounded-3xl overflow-hidden border border-[#ebdccd] flex flex-col shadow-sm">
                    
                    <!-- Image -->
                    <div class="relative overflow-hidden aspect-square bg-cream-50">
                        <a href="product.php?id=<?= $item['id'] ?>" class="block w-full h-full">
                            <img 
                                src="<?= htmlspecialchars($item['image_url']) ?>" 
                                alt="<?= htmlspecialchars($item['name']) ?>" 
                                class="product-img w-full h-full object-cover object-center"
                            >
                        </a>

                        <?php if ($item['discount_percent'] > 0): ?>
                            <span class="absolute top-3 left-3 bg-blush-600 text-white text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider shadow-sm">
                                <?= $item['discount_percent'] ?>% OFF
                            </span>
                        <?php elseif ($item['is_bestseller']): ?>
                            <span class="absolute top-3 left-3 bg-charcoal-900 text-cream-50 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider shadow-sm">
                                Bestseller
                            </span>
                        <?php endif; ?>

                        <span class="absolute bottom-3 left-3 bg-white/90 backdrop-blur-sm text-charcoal-800 text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                            <?= htmlspecialchars($item['category']) ?>
                        </span>
                    </div>

                    <!-- Details -->
                    <div class="p-6 flex flex-col flex-grow justify-between">
                        <div>
                            <!-- Star ratings -->
                            <div class="flex items-center gap-1 mb-2">
                                <div class="flex text-gold-500">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                        <i data-lucide="star" class="w-3.5 h-3.5 fill-gold-500"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="text-[11px] text-charcoal-800/60 font-semibold ml-1">(<?= $item['reviews_count'] ?>)</span>
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

                        <!-- 2 PRICES DISPLAY (Regular & Discounted Price) -->
                        <div class="pt-5 mt-4 border-t border-[#f2e6d9] flex items-center justify-between">
                            <div>
                                <span class="font-serif text-xl font-bold text-charcoal-900 block leading-none">
                                    <?= formatPrice($item['final_price']) ?>
                                </span>
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
                                    <span class="text-[10px] text-charcoal-800/50 block mt-1">Regular Rate</span>
                                <?php endif; ?>
                            </div>

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
    <?php else: ?>
        <div class="bg-white rounded-3xl p-16 text-center border border-[#ebdccd] max-w-lg mx-auto shadow-sm">
            <div class="w-16 h-16 rounded-full bg-blush-100 text-blush-600 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="search-x" class="w-8 h-8"></i>
            </div>
            <h3 class="font-serif text-2xl font-bold text-charcoal-900">No Candles Found</h3>
            <p class="text-xs text-charcoal-800/70 mt-2">
                We couldn't find any candles matching this category or filter.
            </p>
            <a href="shop.php" class="mt-6 inline-block px-6 py-2.5 rounded-full bg-charcoal-900 text-white text-xs font-semibold tracking-wider uppercase">
                Reset All Filters
            </a>
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
