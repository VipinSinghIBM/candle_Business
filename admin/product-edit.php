<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    setFlash('error', 'Candle product not found.');
    header('Location: products.php');
    exit;
}

$errors = [];
$categories = getCategories(false);

$presetImages = [
    'https://images.unsplash.com/photo-1602874801007-bd458bb1b8b8?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1603006905003-be475563bc59?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1596461404969-9ae70f2830c1?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1508746829417-e6f548d8d6ed?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1572726729437-3732efed3f8a?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1543857778-c4a1a3e0b2eb?auto=format&fit=crop&w=800&q=80',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $discount = max(0, min(95, (int)($_POST['discount_percent'] ?? 0)));
    $scentNotes = sanitize($_POST['scent_notes'] ?? '');
    $burnTime = sanitize($_POST['burn_time'] ?? '55 Hours');
    $waxType = sanitize($_POST['wax_type'] ?? '100% Organic Coconut-Soy Wax');
    $weight = sanitize($_POST['weight'] ?? '280g / 9.8 oz');
    $stock = max(0, (int)($_POST['stock'] ?? 50));
    $shortDesc = sanitize($_POST['short_desc'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isBestseller = isset($_POST['is_bestseller']) ? 1 : 0;

    // DIRECT FILE UPLOAD HANDLING
    $uploadedImagePath = handleImageUpload('product_image_file');
    $imageUrl = $uploadedImagePath ?: trim($_POST['image_url'] ?? $product['image_url']);

    if (empty($name)) $errors[] = "Candle name is required.";
    if ($price <= 0) $errors[] = "Please provide a valid regular price/rate.";

    if (empty($errors)) {
        $finalPrice = $price - ($price * ($discount / 100));

        $updateStmt = $db->prepare("UPDATE products SET
            name = :name,
            category = :category,
            price = :price,
            discount_percent = :discount,
            final_price = :final_price,
            short_desc = :short_desc,
            description = :description,
            scent_notes = :scent_notes,
            burn_time = :burn_time,
            wax_type = :wax_type,
            weight = :weight,
            image_url = :image_url,
            stock = :stock,
            is_featured = :is_featured,
            is_bestseller = :is_bestseller
            WHERE id = :id
        ");

        $updateStmt->execute([
            ':name' => $name,
            ':category' => $category,
            ':price' => $price,
            ':discount' => $discount,
            ':final_price' => $finalPrice,
            ':short_desc' => $shortDesc,
            ':description' => $description,
            ':scent_notes' => $scentNotes,
            ':burn_time' => $burnTime,
            ':wax_type' => $waxType,
            ':weight' => $weight,
            ':image_url' => $imageUrl,
            ':stock' => $stock,
            ':is_featured' => $isFeatured,
            ':is_bestseller' => $isBestseller,
            ':id' => $id,
        ]);

        setFlash('success', "Rates, discounts & imagery for '{$name}' updated successfully!");
        header('Location: products.php');
        exit;
    }
}

$pageTitle = "Modify Candle Rates • " . $product['name'];
require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="max-w-4xl mx-auto">
    
    <div class="flex items-center justify-between pb-6 mb-8 border-b border-[#ebdccd]">
        <div>
            <span class="text-xs uppercase tracking-widest text-blush-600 font-bold block mb-1">Pricing &amp; Image Editor</span>
            <h1 class="font-serif text-3xl font-bold text-charcoal-900">Edit <?= htmlspecialchars($product['name']) ?></h1>
            <p class="text-xs text-charcoal-800/60 mt-0.5">Control both Regular M.R.P. &amp; Discounted Price, direct file uploads, and categories.</p>
        </div>
        <a href="products.php" class="text-xs font-bold text-charcoal-800 hover:text-blush-600 flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Back to Products</span>
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
            <ul class="list-disc list-inside space-y-1">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="product-edit.php?id=<?= $product['id'] ?>" enctype="multipart/form-data" class="space-y-8 bg-white p-8 sm:p-10 rounded-3xl border border-[#ebdccd] shadow-sm">
        
        <!-- 1. 2 PRICES & DISCOUNT CONFIGURATION -->
        <div>
            <h3 class="font-serif text-lg font-bold text-charcoal-900 mb-2 flex items-center gap-2">
                <i data-lucide="tag" class="w-4 h-4 text-blush-600"></i>
                <span>1. Rates &amp; Discount Configuration (2 Prices System)</span>
            </h3>
            <p class="text-xs text-charcoal-800/60 mb-4">Users will see both prices (Regular struck-through and Discounted Offer rate)</p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-5 rounded-2xl bg-cream-50/70 border border-[#ebdccd]">
                <div>
                    <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Regular Rate / M.R.P. (<?= STORE_CURRENCY ?>) *</label>
                    <input 
                        type="number" 
                        step="0.01" 
                        name="price" 
                        id="rate_price" 
                        value="<?= htmlspecialchars($product['price']) ?>" 
                        required 
                        oninput="calculateRatePreview()"
                        class="w-full bg-white border border-[#ebdccd] rounded-xl px-4 py-3 text-sm font-mono font-bold focus:outline-none focus:border-blush-500"
                    >
                    <span class="text-[10px] text-charcoal-800/60 mt-1 block">Full standard retail price</span>
                </div>

                <div>
                    <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Promotional Discount (%)</label>
                    <input 
                        type="number" 
                        step="1" 
                        min="0" 
                        max="90" 
                        name="discount_percent" 
                        id="rate_discount" 
                        value="<?= htmlspecialchars($product['discount_percent']) ?>" 
                        oninput="calculateRatePreview()"
                        class="w-full bg-white border border-[#ebdccd] rounded-xl px-4 py-3 text-sm font-mono font-bold focus:outline-none focus:border-blush-500"
                    >
                    <span class="text-[10px] text-charcoal-800/60 mt-1 block">0 = No discount</span>
                </div>

                <div>
                    <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Current Customer Rate</label>
                    <div class="bg-white border border-emerald-300 rounded-xl px-4 py-3 text-emerald-800 font-mono font-bold text-sm">
                        <?= STORE_CURRENCY ?> <span id="rate_preview"><?= number_format($product['final_price'], 2) ?></span>
                    </div>
                    <span class="text-[10px] text-emerald-800 mt-1 block font-bold">Auto-calculated final rate</span>
                </div>
            </div>
        </div>

        <!-- 2. DIRECT IMAGE UPLOAD -->
        <div>
            <h3 class="font-serif text-lg font-bold text-charcoal-900 mb-2 flex items-center gap-2">
                <i data-lucide="image" class="w-4 h-4 text-emerald-600"></i>
                <span>2. Direct File Upload &amp; Image Management</span>
            </h3>
            <p class="text-xs text-charcoal-800/60 mb-4">Directly upload a new image from your computer to replace current vessel photo</p>

            <div class="p-5 rounded-2xl bg-[#FAF7F2] border border-[#ebdccd] space-y-4">
                <div class="flex items-center gap-5">
                    <img src="../<?= htmlspecialchars($product['image_url']) ?>" id="current_image_preview" alt="" class="w-20 h-20 rounded-2xl object-cover border-2 border-gold-400 shadow-sm bg-white">
                    <div class="space-y-1">
                        <span class="text-xs font-bold text-charcoal-900">Current Candle Image</span>
                        <p class="text-[11px] text-charcoal-800/60 font-mono truncate max-w-sm"><?= htmlspecialchars($product['image_url']) ?></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-[#ebdccd]">
                    <div>
                        <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Upload New Image File</label>
                        <input 
                            type="file" 
                            name="product_image_file" 
                            accept="image/*"
                            class="w-full bg-white border border-[#ebdccd] rounded-xl px-3 py-2 text-xs focus:outline-none"
                        >
                        <span class="text-[10px] text-charcoal-800/60 mt-1 block">Uploading a new file will automatically save it to uploads/</span>
                    </div>

                    <div>
                        <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Or Image URL</label>
                        <input 
                            type="text" 
                            name="image_url" 
                            id="image_url_input" 
                            value="<?= htmlspecialchars($product['image_url']) ?>" 
                            class="w-full bg-white border border-[#ebdccd] rounded-xl px-3 py-2 text-xs font-mono focus:outline-none"
                        >
                    </div>
                </div>

                <div>
                    <span class="text-[11px] font-bold text-charcoal-800/70 block mb-2">Or Select Studio Preset:</span>
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
                        <?php foreach ($presetImages as $img): ?>
                            <button type="button" onclick="document.getElementById('image_url_input').value = '<?= $img ?>'; document.getElementById('current_image_preview').src = '<?= $img ?>';" class="aspect-square rounded-xl overflow-hidden border-2 border-transparent hover:border-blush-500 focus:border-blush-500 transition-all">
                                <img src="<?= $img ?>" alt="" class="w-full h-full object-cover">
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Candle Identity & Dynamic Categories -->
        <div>
            <h3 class="font-serif text-lg font-bold text-charcoal-900 mb-4 pb-2 border-b border-[#f2e6d9]">3. Candle Identity &amp; Category</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Candle Name *</label>
                    <input type="text" name="name" required value="<?= htmlspecialchars($product['name']) ?>" class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blush-500">
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label class="text-xs uppercase font-bold text-charcoal-800">Category *</label>
                        <a href="categories.php" target="_blank" class="text-[10px] text-blush-600 font-bold hover:underline">+ New Category</a>
                    </div>
                    <select name="category" required class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blush-500">
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= htmlspecialchars($c['name']) ?>" <?= $product['category'] === $c['name'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Scent Notes (Bullet • separated)</label>
                    <input type="text" name="scent_notes" value="<?= htmlspecialchars($product['scent_notes']) ?>" class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blush-500">
                </div>
            </div>
        </div>

        <!-- 4. Specs & Stock -->
        <div>
            <h3 class="font-serif text-lg font-bold text-charcoal-900 mb-4 pb-2 border-b border-[#f2e6d9]">4. Specifications &amp; Stock</h3>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Stock Count</label>
                    <input type="number" name="stock" value="<?= htmlspecialchars($product['stock']) ?>" min="0" class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blush-500">
                </div>

                <div>
                    <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Burn Time</label>
                    <input type="text" name="burn_time" value="<?= htmlspecialchars($product['burn_time']) ?>" class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blush-500">
                </div>

                <div>
                    <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Wax Formulation</label>
                    <input type="text" name="wax_type" value="<?= htmlspecialchars($product['wax_type']) ?>" class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blush-500">
                </div>

                <div>
                    <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Weight</label>
                    <input type="text" name="weight" value="<?= htmlspecialchars($product['weight']) ?>" class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blush-500">
                </div>
            </div>
        </div>

        <!-- 5. Description Narrative -->
        <div>
            <h3 class="font-serif text-lg font-bold text-charcoal-900 mb-4 pb-2 border-b border-[#f2e6d9]">5. Descriptions</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Short Tagline</label>
                    <input type="text" name="short_desc" value="<?= htmlspecialchars($product['short_desc']) ?>" class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blush-500">
                </div>
                <div>
                    <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Description Story</label>
                    <textarea name="description" rows="3" class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blush-500"><?= htmlspecialchars($product['description']) ?></textarea>
                </div>
            </div>
        </div>

        <!-- 6. Badges -->
        <div class="flex items-center gap-6 pt-2">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_featured" value="1" <?= $product['is_featured'] ? 'checked' : '' ?> class="w-4 h-4 text-blush-600 rounded">
                <span class="text-xs font-bold text-charcoal-900">Featured in Home Grid</span>
            </label>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_bestseller" value="1" <?= $product['is_bestseller'] ? 'checked' : '' ?> class="w-4 h-4 text-blush-600 rounded">
                <span class="text-xs font-bold text-charcoal-900">Mark as Shiv Vani Bestseller</span>
            </label>
        </div>

        <!-- Buttons -->
        <div class="pt-6 border-t border-[#ebdccd] flex justify-end gap-3">
            <a href="products.php" class="px-6 py-3 rounded-2xl bg-cream-100 hover:bg-cream-200 text-charcoal-800 font-bold text-xs uppercase tracking-wider transition-colors">
                Cancel
            </a>
            <button type="submit" class="btn-luxury px-8 py-3.5 rounded-2xl bg-charcoal-900 hover:bg-blush-700 text-cream-50 font-bold text-xs uppercase tracking-widest transition-all shadow-lg">
                Save &amp; Update Rates
            </button>
        </div>

    </form>

</div>

<script>
    function calculateRatePreview() {
        const price = parseFloat(document.getElementById('rate_price').value) || 0;
        const discount = parseFloat(document.getElementById('rate_discount').value) || 0;
        const finalVal = Math.max(0, price - (price * (discount / 100)));
        document.getElementById('rate_preview').textContent = finalVal.toFixed(2);
    }
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
