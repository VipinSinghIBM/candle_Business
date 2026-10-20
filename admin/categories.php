<?php
$pageTitle = "Manage Fragrance Categories";
require_once __DIR__ . '/includes/admin_header.php';

$db = getDB();

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_category') {
    $name = sanitize($_POST['category_name'] ?? '');
    $slug = sanitize($_POST['category_slug'] ?? '');
    $icon = sanitize($_POST['category_icon'] ?? 'sparkles');
    $desc = sanitize($_POST['category_desc'] ?? '');
    $order = (int)($_POST['display_order'] ?? 0);

    if (empty($name)) {
        setFlash('error', 'Category name cannot be empty.');
    } else {
        if (empty($slug)) {
            $slug = slugify($name);
        }

        try {
            $stmt = $db->prepare("INSERT INTO categories (name, slug, icon, description, display_order, is_active) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->execute([$name, $slug, $icon, $desc, $order]);
            setFlash('success', "New Category '{$name}' created! It now appears in the public navbar and all category filters.");
        } catch (Throwable $e) {
            setFlash('error', "Failed to create category: " . $e->getMessage());
        }
    }
    header('Location: categories.php');
    exit;
}

// Handle Delete Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_category') {
    $catId = (int)$_POST['category_id'];
    
    // Check if any product belongs to this category
    $checkCat = $db->prepare("SELECT name FROM categories WHERE id = ?");
    $checkCat->execute([$catId]);
    $catData = $checkCat->fetch();

    if ($catData) {
        // Delete category
        $del = $db->prepare("DELETE FROM categories WHERE id = ?");
        $del->execute([$catId]);
        setFlash('success', "Category '{$catData['name']}' removed from catalog and navbar.");
    } else {
        setFlash('error', "Category not found.");
    }
    header('Location: categories.php');
    exit;
}

// Handle Toggle Active
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_active') {
    $catId = (int)$_POST['category_id'];
    $currentStatus = (int)$_POST['current_status'];
    $newStatus = $currentStatus ? 0 : 1;

    $stmt = $db->prepare("UPDATE categories SET is_active = ? WHERE id = ?");
    $stmt->execute([$newStatus, $catId]);
    setFlash('success', "Category visibility updated.");
    header('Location: categories.php');
    exit;
}

// Fetch all categories with product counts
$categories = $db->query("
    SELECT c.*, 
    (SELECT COUNT(*) FROM products p WHERE p.category = c.name) AS product_count 
    FROM categories c 
    ORDER BY c.display_order ASC, c.id DESC
")->fetchAll();
?>

<!-- Header Toolbar -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 mb-8 border-b border-[#ebdccd] gap-4">
    <div>
        <span class="text-xs uppercase tracking-widest text-blush-600 font-bold block mb-1">Taxonomy &amp; Navigation</span>
        <h1 class="font-serif text-3xl font-bold text-charcoal-900">Product Categories</h1>
        <p class="text-xs text-charcoal-800/60 mt-0.5">
            Categories created here dynamically update the <strong>Public Navbar Dropdown</strong>, homepage pills, and catalog filters.
        </p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
    
    <!-- Left Column: Add New Category Form (5 Cols) -->
    <div class="lg:col-span-5 bg-white p-6 sm:p-8 rounded-3xl border border-[#ebdccd] shadow-sm space-y-6">
        <div class="pb-3 border-b border-[#f2e6d9]">
            <h3 class="font-serif text-xl font-bold text-charcoal-900 flex items-center gap-2">
                <i data-lucide="folder-plus" class="w-5 h-5 text-blush-600"></i>
                <span>Add New Category</span>
            </h3>
            <p class="text-xs text-charcoal-800/60 mt-0.5">Will immediately appear in the store navbar</p>
        </div>

        <form method="POST" action="categories.php" class="space-y-4">
            <input type="hidden" name="action" value="add_category">

            <div>
                <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Category Title *</label>
                <input 
                    type="text" 
                    name="category_name" 
                    required 
                    placeholder="e.g. Royal Camphor &amp; Sandalwood" 
                    class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-2.5 text-xs font-semibold focus:outline-none focus:border-blush-500"
                    oninput="document.getElementById('slug_input').value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')"
                >
            </div>

            <div>
                <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">URL Slug</label>
                <input 
                    type="text" 
                    name="category_slug" 
                    id="slug_input" 
                    placeholder="e.g. royal-camphor-sandalwood" 
                    class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-2.5 text-xs font-mono focus:outline-none focus:border-blush-500"
                >
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Display Icon</label>
                    <select name="category_icon" class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-3 py-2 text-xs focus:outline-none">
                        <option value="sparkles">✨ Sparkles</option>
                        <option value="flower-2">🌸 Floral</option>
                        <option value="flame">🔥 Flame</option>
                        <option value="cookie">🍪 Gourmand</option>
                        <option value="trees">🌲 Woody</option>
                        <option value="sun">🍊 Citrus</option>
                        <option value="moon">🌙 Evening / Night</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Order Priority</label>
                    <input 
                        type="number" 
                        name="display_order" 
                        value="1" 
                        min="0" 
                        class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-2 text-xs focus:outline-none"
                    >
                </div>
            </div>

            <div>
                <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Description / Notes</label>
                <textarea 
                    name="category_desc" 
                    rows="2" 
                    placeholder="Short description of this olfactory collection..." 
                    class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-2 text-xs focus:outline-none focus:border-blush-500"
                ></textarea>
            </div>

            <button type="submit" class="btn-luxury w-full bg-charcoal-900 hover:bg-blush-700 text-cream-50 font-bold py-3 px-6 rounded-2xl text-xs uppercase tracking-widest transition-all shadow-md">
                Create &amp; Publish to Navbar
            </button>
        </form>
    </div>

    <!-- Right Column: Categories List Table (7 Cols) -->
    <div class="lg:col-span-7 bg-white rounded-3xl border border-[#ebdccd] shadow-sm overflow-hidden p-6 sm:p-8 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-[#f2e6d9]">
            <h3 class="font-serif text-xl font-bold text-charcoal-900">Existing Categories</h3>
            <span class="text-xs text-charcoal-800/60 font-semibold"><?= count($categories) ?> Active in Database</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-cream-50/70 text-charcoal-800 uppercase tracking-wider border-b border-[#ebdccd] text-[11px]">
                        <th class="py-3 px-4 font-bold">Category</th>
                        <th class="py-3 px-3 font-bold">Slug</th>
                        <th class="py-3 px-3 font-bold text-center">Candles</th>
                        <th class="py-3 px-3 font-bold text-center">Status</th>
                        <th class="py-3 px-4 font-bold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f2e6d9]">
                    <?php foreach ($categories as $cat): ?>
                        <tr class="hover:bg-cream-50/40 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-charcoal-900 text-sm flex items-center gap-2">
                                    <span class="text-blush-600">✦</span>
                                    <span><?= htmlspecialchars($cat['name']) ?></span>
                                </div>
                                <span class="text-[10px] text-charcoal-800/60 block mt-0.5"><?= htmlspecialchars($cat['description']) ?></span>
                            </td>

                            <td class="py-3.5 px-3 font-mono text-[11px] text-charcoal-800/70">
                                <?= htmlspecialchars($cat['slug']) ?>
                            </td>

                            <td class="py-3.5 px-3 text-center">
                                <span class="bg-cream-100 text-charcoal-900 font-bold px-2 py-0.5 rounded-full text-[10px]">
                                    <?= $cat['product_count'] ?> items
                                </span>
                            </td>

                            <td class="py-3.5 px-3 text-center">
                                <form method="POST" action="categories.php" class="inline">
                                    <input type="hidden" name="action" value="toggle_active">
                                    <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                    <input type="hidden" name="current_status" value="<?= $cat['is_active'] ?>">
                                    <button type="submit" class="text-[10px] font-bold px-2 py-0.5 rounded-full <?= $cat['is_active'] ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-gray-100 text-gray-600' ?>">
                                        <?= $cat['is_active'] ? 'Active in Nav' : 'Hidden' ?>
                                    </button>
                                </form>
                            </td>

                            <td class="py-3.5 px-4 text-right">
                                <form method="POST" action="categories.php" onsubmit="return confirm('Remove category <?= addslashes($cat['name']) ?>?');" class="inline">
                                    <input type="hidden" name="action" value="delete_category">
                                    <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                    <button type="submit" class="text-rose-600 hover:text-rose-800 p-1.5 rounded-lg hover:bg-rose-50" title="Delete Category">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
