<?php
$pageTitle = "Add New Candle Product";
require_once __DIR__ . '/includes/admin_header.php';

$db = getDB();
$errors = [];

// Fetch dynamic categories from database
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

    // DIRECT FILE UPLOAD HANDLING (Saves to uploads/ and database)
    $uploadedImagePath = handleImageUpload('product_image_file');
    $imageUrl = $uploadedImagePath ?: trim($_POST['image_url'] ?? '');

    if (empty($imageUrl)) {
        $imageUrl = $presetImages[0];
    }

    if (empty($name)) $errors[] = "Candle product name is required.";
    if ($price <= 0) $errors[] = "Please provide a valid regular price/rate.";
    if (empty($category)) $errors[] = "Please select or assign a category.";

    if (empty($errors)) {
        $finalPrice = $price - ($price * ($discount / 100));
        $slug = slugify($name);

        $stmt = $db->prepare("INSERT INTO products (
            name, slug, category, price, discount_percent, final_price, short_desc,
            description, scent_notes, burn_time, wax_type, weight, image_url, stock,
            is_featured, is_bestseller, rating, reviews_count
        ) VALUES (
            :name, :slug, :category, :price, :discount, :final_price, :short_desc,
            :description, :scent_notes, :burn_time, :wax_type, :weight, :image_url, :stock,
            :is_featured, :is_bestseller, 5.0, 1
        )");

        $stmt->execute([
            ':name' => $name,
            ':slug' => $slug,
            ':category' => $category,
            ':price' => $price,
            ':discount' => $discount,
            ':final_price' => $finalPrice,
            ':short_desc' => $shortDesc ?: 'Artisanal sacred candle hand-poured by Shiv Vani Enterprises.',
            ':description' => $description ?: 'Formulated with organic plant wax, unbleached wicks, and pure essential extracts.',
            ':scent_notes' => $scentNotes ?: 'Botanical Florals • Mysore Sandalwood • Sweet Amber',
            ':burn_time' => $burnTime,
            ':wax_type' => $waxType,
            ':weight' => $weight,
            ':image_url' => $imageUrl,
            ':stock' => $stock,
            ':is_featured' => $isFeatured,
            ':is_bestseller' => $isBestseller,
        ]);

        setFlash('success', "Candle '{$name}' added to Shiv Vani catalog with 2 prices & direct image upload!");
        header('Location: products.php');
        exit;
    }
}
?>

<div class="max-w-4xl mx-auto">
    
    <div class="flex items-center justify-between pb-6 mb-8 border-b border-[#ebdccd]">
        <div>
            <span class="text-xs uppercase tracking-widest text-blush-600 font-bold block mb-1"><?= STORE_NAME ?></span>
            <h1 class="font-serif text-3xl font-bold text-charcoal-900">Add New Candle Vessel</h1>
            <p class="text-xs text-charcoal-800/60 mt-0.5">Upload images directly, configure 2 prices (Regular &amp; Discount), and choose categories.</p>
        </div>
        <a href="products.php" class="text-xs font-bold text-charcoal-800 hover:text-blush-600 flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Cancel</span>
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

    <form method="POST" action="product-add.php" enctype="multipart/form-data" class="space-y-8 bg-white p-8 sm:p-10 rounded-3xl border border-[#ebdccd] shadow-sm">
        
        <!-- 1. Identity & Dynamic Categories -->
        <div>
            <h3 class="font-serif text-lg font-bold text-charcoal-900 mb-4 pb-2 border-b border-[#f2e6d9]">1. Candle Identity &amp; Category</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Candle Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Kashi Chandan &amp; Royal Mogra" class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blush-500">
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label class="text-xs uppercase font-bold text-charcoal-800">Category *</label>
                        <a href="categories.php" target="_blank" class="text-[10px] text-blush-600 font-bold hover:underline">+ New Category</a>
                    </div>
                    <select name="category" required class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blush-500">
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= htmlspecialchars($c['name']) ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Scent Notes (Bullet • separated) *</label>
                    <input type="text" name="scent_notes" placeholder="e.g. Mysore Sandalwood • Royal Mogra • Camphor" class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blush-500">
                </div>
            </div>
        </div>

        <!-- 2. TWO PRICES & DISCOUNT RATE CONFIGURATION -->
        <div>
            <h3 class="font-serif text-lg font-bold text-charcoal-900 mb-2 flex items-center gap-2">
                <i data-lucide="tag" class="w-4 h-4 text-blush-600"></i>
                <span>2. Pricing &amp; Discount Configuration (2 Prices System)</span>
            </h3>
            <p class="text-xs text-charcoal-800/60 mb-4">Users will see both the Regular M.R.P. Rate &amp; the Discounted Selling Rate</p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-5 rounded-2xl bg-cream-50/70 border border-[#ebdccd]">
                <div>
                    <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Regular Rate / M.R.P. (<?= STORE_CURRENCY ?>) *</label>
                    <input 
                        type="number" 
                        step="0.01" 
                        name="price" 
                        id="rate_price" 
                        value="899.00" 
                        required 
                        oninput="calculateRatePreview()"
                        class="w-full bg-white border border-[#ebdccd] rounded-xl px-4 py-3 text-sm font-mono font-bold focus:outline-none focus:border-blush-500"
                    >
                    <span class="text-[10px] text-charcoal-800/60 mt-1 block">Original printed M.R.P. price</span>
                </div>

                <div>
                    <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Discount Rate (%)</label>
                    <input 
                        type="number" 
                        step="1" 
                        min="0" 
                        max="90" 
                        name="discount_percent" 
                        id="rate_discount" 
                        value="20" 
                        oninput="calculateRatePreview()"
                        class="w-full bg-white border border-[#ebdccd] rounded-xl px-4 py-3 text-sm font-mono font-bold focus:outline-none focus:border-blush-500"
                    >
                    <span class="text-[10px] text-charcoal-800/60 mt-1 block">Percentage savings for customer</span>
                </div>

                <div>
                    <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Final Customer Selling Price</label>
                    <div class="bg-white border border-emerald-300 rounded-xl px-4 py-3 text-emerald-800 font-mono font-bold text-sm">
                        <?= STORE_CURRENCY ?> <span id="rate_preview">719.20</span>
                    </div>
                    <span class="text-[10px] text-emerald-800 mt-1 block font-bold">Auto-calculated final rate</span>
                </div>
            </div>
        </div>

        <!-- 3. DIRECT IMAGE FILE UPLOAD -->
        <div>
            <h3 class="font-serif text-lg font-bold text-charcoal-900 mb-2 flex items-center gap-2">
                <i data-lucide="upload-cloud" class="w-4 h-4 text-emerald-600"></i>
                <span>3. Direct Image File Upload &amp; Photography</span>
            </h3>
            <p class="text-xs text-charcoal-800/60 mb-4">Directly upload a photo from your computer (saved to uploads/ and database) or select a preset</p>

            <div class="p-5 rounded-2xl bg-[#FAF7F2] border border-[#ebdccd] space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Direct File Upload from Device</label>
                        <input 
                            type="file" 
                            name="product_image_file" 
                            accept="image/*"
                            class="w-full bg-white border border-[#ebdccd] rounded-xl px-3 py-2 text-xs focus:outline-none"
                            onchange="previewUploadedImage(this)"
                        >
                        <span class="text-[10px] text-charcoal-800/60 mt-1 block">Supports JPG, PNG, WEBP up to 8MB.</span>
                    </div>

                    <div>
                        <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Or External Image URL</label>
                        <input 
                            type="url" 
                            name="image_url" 
                            id="image_url_input" 
                            value="<?= $presetImages[0] ?>" 
                            class="w-full bg-white border border-[#ebdccd] rounded-xl px-3 py-2 text-xs font-mono focus:outline-none"
                        >
                    </div>
                </div>

                <div>
                    <span class="text-[11px] font-bold text-charcoal-800/70 block mb-2">Or Choose from Curated Studio Presets:</span>
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
                        <?php foreach ($presetImages as $img): ?>
                            <button type="button" onclick="document.getElementById('image_url_input').value = '<?= $img ?>'" class="aspect-square rounded-xl overflow-hidden border-2 border-transparent hover:border-blush-500 focus:border-blush-500 transition-all">
                                <img src="<?= $img ?>" alt="" class="w-full h-full object-cover">
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Specifications & Stock -->
        <div>
            <h3 class="font-serif text-lg font-bold text-charcoal-900 mb-4 pb-2 border-b border-[#f2e6d9]">4. Vessel Specifications &amp; Stock</h3>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Stock Count</label>
                    <input type="number" name="stock" value="50" min="0" class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blush-500">
                </div>

                <div>
                    <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Burn Time</label>
                    <input type="text" name="burn_time" value="55 Hours" class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blush-500">
                </div>

                <div>
                    <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Wax Formulation</label>
                    <input type="text" name="wax_type" value="100% Organic Coconut-Soy Wax" class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blush-500">
                </div>

                <div>
                    <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Weight</label>
                    <input type="text" name="weight" value="280g / 9.8 oz" class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blush-500">
                </div>
            </div>
        </div>

        <!-- 5. Description Narrative -->
        <div>
            <h3 class="font-serif text-lg font-bold text-charcoal-900 mb-4 pb-2 border-b border-[#f2e6d9]">5. Sensory Narrative</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Short Tagline Summary</label>
                    <input type="text" name="short_desc" placeholder="A single evocative sentence describing the sacred aroma..." class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blush-500">
                </div>

                <div>
                    <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Detailed Story / Burn Ritual</label>
                    <textarea name="description" rows="3" placeholder="Full romantic description of the candle notes and atmosphere..." class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blush-500"></textarea>
                </div>
            </div>
        </div>

        <!-- 6. Badges -->
        <div class="flex items-center gap-6 pt-2">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_featured" value="1" checked class="w-4 h-4 text-blush-600 rounded">
                <span class="text-xs font-bold text-charcoal-900">Pin to Featured Home Grid</span>
            </label>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_bestseller" value="1" class="w-4 h-4 text-blush-600 rounded">
                <span class="text-xs font-bold text-charcoal-900">Mark as Shiv Vani Bestseller</span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="pt-6 border-t border-[#ebdccd] flex justify-end gap-3">
            <a href="products.php" class="px-6 py-3 rounded-2xl bg-cream-100 hover:bg-cream-200 text-charcoal-800 font-bold text-xs uppercase tracking-wider transition-colors">
                Cancel
            </a>
            <button type="submit" class="btn-luxury px-8 py-3.5 rounded-2xl bg-charcoal-900 hover:bg-blush-700 text-cream-50 font-bold text-xs uppercase tracking-widest transition-all shadow-lg">
                Publish Candle to Shiv Vani Catalog
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
    calculateRatePreview();
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
