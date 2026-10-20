<?php
$pageTitle = "Candle Catalog, Pricing & Stock";
require_once __DIR__ . '/includes/admin_header.php';

$db = getDB();

// Handle Delete Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $delId = (int)$_POST['product_id'];
    
    $checkStmt = $db->prepare("SELECT name FROM products WHERE id = ?");
    $checkStmt->execute([$delId]);
    $prod = $checkStmt->fetch();

    if ($prod) {
        $delStmt = $db->prepare("DELETE FROM products WHERE id = ?");
        $delStmt->execute([$delId]);
        setFlash('success', "Candle '{$prod['name']}' removed from catalog.");
    } else {
        setFlash('error', "Product not found.");
    }

    header('Location: products.php');
    exit;
}

// Search and filter
$search = trim($_GET['search'] ?? '');
$filterCategory = trim($_GET['category'] ?? '');

$categories = getCategories(false);

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR scent_notes LIKE ?)";
    $term = "%{$search}%";
    $params[] = $term;
    $params[] = $term;
}

if (!empty($filterCategory)) {
    $sql .= " AND category = ?";
    $params[] = $filterCategory;
}

$sql .= " ORDER BY id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<!-- Header Toolbar -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 mb-6 border-b border-[#ebdccd] gap-4">
    <div>
        <span class="text-xs uppercase tracking-widest text-blush-600 font-bold block mb-1"><?= STORE_NAME ?></span>
        <h1 class="font-serif text-3xl font-bold text-charcoal-900">Manage Candles, Rates &amp; Discounts</h1>
        <p class="text-xs text-charcoal-800/60 mt-0.5">Control 2 prices (Regular M.R.P. &amp; Discount Rate), direct uploads, and inventory.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="categories.php" class="px-4 py-2.5 rounded-xl bg-white border border-[#ebdccd] text-charcoal-800 font-bold text-xs uppercase tracking-wider hover:bg-cream-100 transition-colors flex items-center gap-1.5 shadow-sm">
            <i data-lucide="folder-plus" class="w-4 h-4 text-blush-600"></i>
            <span>Categories</span>
        </a>
        <a href="product-add.php" class="btn-luxury inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-charcoal-900 hover:bg-blush-700 text-cream-50 font-bold text-xs uppercase tracking-wider transition-all shadow-md">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Add New Candle</span>
        </a>
    </div>
</div>

<!-- Filters -->
<div class="bg-white p-4 rounded-2xl border border-[#ebdccd] shadow-sm mb-6 flex flex-col sm:flex-row items-center justify-between gap-3">
    <form method="GET" action="products.php" class="flex flex-wrap sm:flex-nowrap items-center gap-3 w-full sm:w-auto flex-grow">
        <div class="relative w-full sm:w-64">
            <input 
                type="text" 
                name="search" 
                value="<?= htmlspecialchars($search) ?>" 
                placeholder="Search candle name..." 
                class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl pl-9 pr-4 py-2 text-xs focus:outline-none focus:border-blush-500"
            >
            <i data-lucide="search" class="w-4 h-4 text-charcoal-800/40 absolute left-3 top-2.5"></i>
        </div>

        <select name="category" onchange="this.form.submit()" class="bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-3 py-2 text-xs font-semibold text-charcoal-900 focus:outline-none">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat['name']) ?>" <?= $filterCategory === $cat['name'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <?php if (!empty($search) || !empty($filterCategory)): ?>
            <a href="products.php" class="text-xs text-blush-600 hover:underline font-bold">Reset</a>
        <?php endif; ?>
    </form>

    <div class="text-xs text-charcoal-800/60 whitespace-nowrap">
        Total: <strong class="text-charcoal-900"><?= count($products) ?></strong> candles listed
    </div>
</div>

<!-- Products Table (Displaying 2 Prices) -->
<div class="bg-white rounded-3xl border border-[#ebdccd] shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="bg-cream-50/70 text-charcoal-800 uppercase tracking-wider border-b border-[#ebdccd] text-[11px]">
                    <th class="py-4 px-6 font-bold">Candle Vessel</th>
                    <th class="py-4 px-4 font-bold">Category</th>
                    <th class="py-4 px-4 font-bold">Regular M.R.P.</th>
                    <th class="py-4 px-4 font-bold">Discount Rate</th>
                    <th class="py-4 px-4 font-bold">Final Customer Rate</th>
                    <th class="py-4 px-4 font-bold">Stock</th>
                    <th class="py-4 px-4 font-bold">Badges</th>
                    <th class="py-4 px-6 font-bold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f2e6d9]">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $prod): ?>
                        <?php 
                        $imgSrc = str_starts_with($prod['image_url'], 'uploads/') ? '../' . $prod['image_url'] : $prod['image_url'];
                        ?>
                        <tr class="hover:bg-cream-50/40 transition-colors">
                            
                            <!-- Vessel & Name -->
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <img src="<?= htmlspecialchars($imgSrc) ?>" alt="" class="w-12 h-12 rounded-xl object-cover border border-[#ebdccd] bg-cream-50 flex-shrink-0">
                                    <div class="min-w-0">
                                        <a href="../product.php?id=<?= $prod['id'] ?>" target="_blank" class="font-serif font-bold text-sm text-charcoal-900 hover:text-blush-600 block truncate">
                                            <?= htmlspecialchars($prod['name']) ?>
                                        </a>
                                        <span class="text-[10px] text-charcoal-800/60 block truncate">
                                            ✧ <?= htmlspecialchars($prod['scent_notes']) ?>
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Category -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <span class="bg-[#FAF7F2] border border-[#ebdccd] px-2.5 py-1 rounded-full text-[10px] font-bold text-charcoal-800">
                                    <?= htmlspecialchars($prod['category']) ?>
                                </span>
                            </td>

                            <!-- Regular M.R.P. Price (Price 1) -->
                            <td class="py-4 px-4 font-mono font-semibold text-charcoal-800/70 whitespace-nowrap">
                                <span class="line-through"><?= formatPrice($prod['price']) ?></span>
                            </td>

                            <!-- Discount % Rate -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <?php if ($prod['discount_percent'] > 0): ?>
                                    <span class="bg-blush-100 text-blush-800 font-bold px-2 py-0.5 rounded-full text-[10px]">
                                        <?= $prod['discount_percent'] ?>% OFF
                                    </span>
                                <?php else: ?>
                                    <span class="text-charcoal-800/40 text-[10px]">Standard</span>
                                <?php endif; ?>
                            </td>

                            <!-- Final Customer Price (Price 2) -->
                            <td class="py-4 px-4 font-serif font-bold text-sm text-charcoal-900 whitespace-nowrap">
                                <span class="text-emerald-800 bg-emerald-50 px-2 py-1 rounded-lg border border-emerald-100">
                                    <?= formatPrice($prod['final_price']) ?>
                                </span>
                            </td>

                            <!-- Stock -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <span class="font-bold <?= $prod['stock'] < 30 ? 'text-rose-600' : 'text-emerald-700' ?>">
                                    <?= $prod['stock'] ?> units
                                </span>
                            </td>

                            <!-- Badges -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    <?php if ($prod['is_featured']): ?>
                                        <span class="text-[9px] bg-amber-100 text-amber-900 font-bold px-1.5 py-0.5 rounded">Featured</span>
                                    <?php endif; ?>
                                    <?php if ($prod['is_bestseller']): ?>
                                        <span class="text-[9px] bg-charcoal-900 text-white font-bold px-1.5 py-0.5 rounded">Bestseller</span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-6 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    <a href="product-edit.php?id=<?= $prod['id'] ?>" class="bg-cream-100 hover:bg-cream-200 text-charcoal-900 px-3 py-1.5 rounded-xl font-bold text-xs transition-colors flex items-center gap-1">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5 text-blush-600"></i>
                                        <span>Edit Rates</span>
                                    </a>

                                    <form method="POST" action="products.php" onsubmit="return confirm('Permanently remove <?= addslashes($prod['name']) ?>?');" class="inline">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                        <button type="submit" class="bg-rose-50 hover:bg-rose-100 text-rose-700 p-1.5 rounded-xl transition-colors" title="Delete Candle">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-12 text-charcoal-800/60">
                            No candle products found. Click "Add New Candle" above to create one.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
