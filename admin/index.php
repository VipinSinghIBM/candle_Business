<?php
$pageTitle = "Dashboard Overview";
require_once __DIR__ . '/includes/admin_header.php';

$db = getDB();

// Handle quick order status update from dashboard
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_order_status') {
    $orderId = (int)$_POST['order_id'];
    $newStatus = sanitize($_POST['order_status']);
    $stmt = $db->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $orderId]);
    setFlash('success', "Order status updated to '{$newStatus}'.");
    header('Location: index.php');
    exit;
}

// Analytics calculations
$totalRevenue = (float)$db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'Paid'")->fetchColumn();
$totalOrders = (int)$db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalProducts = (int)$db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalCategories = (int)$db->query("SELECT COUNT(*) FROM categories")->fetchColumn();

// Fetch Recent Orders
$recentOrders = $db->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5")->fetchAll();

// Fetch Low Stock Candles
$lowStock = $db->query("SELECT * FROM products WHERE stock < 40 ORDER BY stock ASC LIMIT 4")->fetchAll();
?>

<!-- Top Welcome Banner -->
<div class="flex flex-col md:flex-row md:items-center justify-between pb-8 mb-8 border-b border-[#ebdccd] gap-4">
    <div>
        <span class="text-xs uppercase tracking-widest text-blush-600 font-bold block mb-1"><?= STORE_NAME ?></span>
        <h1 class="font-serif text-3xl sm:text-4xl font-bold text-charcoal-900">Atelier Control Panel</h1>
        <p class="text-xs text-charcoal-800/60 mt-1">Real-time revenue, order fulfillment, 2-price catalog controls, and payment API.</p>
    </div>
    <div class="flex flex-wrap items-center gap-3">
        <a href="product-add.php" class="btn-luxury px-5 py-2.5 rounded-2xl bg-charcoal-900 hover:bg-blush-700 text-cream-50 font-bold text-xs uppercase tracking-wider transition-all shadow-md flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Add New Candle</span>
        </a>
        <a href="categories.php" class="px-4 py-2.5 rounded-2xl bg-white border border-[#ebdccd] text-charcoal-800 hover:bg-cream-100 font-bold text-xs uppercase tracking-wider transition-all flex items-center gap-1.5 shadow-sm">
            <i data-lucide="folder-plus" class="w-4 h-4 text-blush-600"></i>
            <span>Navbar Categories</span>
        </a>
        <a href="payment-settings.php" class="px-4 py-2.5 rounded-2xl bg-white border border-[#ebdccd] text-charcoal-800 hover:bg-cream-100 font-bold text-xs uppercase tracking-wider transition-all flex items-center gap-1.5 shadow-sm">
            <i data-lucide="qr-code" class="w-4 h-4 text-emerald-600"></i>
            <span>Payment API &amp; QR</span>
        </a>
    </div>
</div>

<!-- KPI Metrics Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    
    <div class="bg-white p-6 rounded-3xl border border-[#ebdccd] shadow-sm flex items-center justify-between">
        <div>
            <span class="text-[10px] uppercase tracking-wider text-charcoal-800/60 font-semibold block">Total Revenue</span>
            <span class="font-serif text-3xl font-bold text-charcoal-900 mt-1 block"><?= formatPrice($totalRevenue) ?></span>
            <span class="text-[11px] text-emerald-700 font-medium">✓ Settled payments</span>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center border border-emerald-100">
            <i data-lucide="credit-card" class="w-6 h-6"></i>
        </div>
    </div>

    <div class="bg-white p-6 rounded-3xl border border-[#ebdccd] shadow-sm flex items-center justify-between">
        <div>
            <span class="text-[10px] uppercase tracking-wider text-charcoal-800/60 font-semibold block">Customer Orders</span>
            <span class="font-serif text-3xl font-bold text-charcoal-900 mt-1 block"><?= $totalOrders ?></span>
            <a href="orders.php" class="text-[11px] text-blush-600 hover:underline font-bold">Manage orders &rarr;</a>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-blush-50 text-blush-600 flex items-center justify-center border border-blush-100">
            <i data-lucide="shopping-bag" class="w-6 h-6"></i>
        </div>
    </div>

    <div class="bg-white p-6 rounded-3xl border border-[#ebdccd] shadow-sm flex items-center justify-between">
        <div>
            <span class="text-[10px] uppercase tracking-wider text-charcoal-800/60 font-semibold block">Active Candles</span>
            <span class="font-serif text-3xl font-bold text-charcoal-900 mt-1 block"><?= $totalProducts ?></span>
            <a href="products.php" class="text-[11px] text-blush-600 hover:underline font-bold">View catalog &rarr;</a>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center border border-amber-100">
            <i data-lucide="flame" class="w-6 h-6"></i>
        </div>
    </div>

    <div class="bg-white p-6 rounded-3xl border border-[#ebdccd] shadow-sm flex items-center justify-between">
        <div>
            <span class="text-[10px] uppercase tracking-wider text-charcoal-800/60 font-semibold block">Navbar Categories</span>
            <span class="font-serif text-3xl font-bold text-charcoal-900 mt-1 block"><?= $totalCategories ?></span>
            <a href="categories.php" class="text-[11px] text-blush-600 hover:underline font-bold">Add / Edit Categories &rarr;</a>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center border border-rose-100">
            <i data-lucide="layers" class="w-6 h-6"></i>
        </div>
    </div>

</div>

<!-- Recent Orders & Inventory Alerts -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    
    <!-- Left Column: Recent Orders (8 cols) -->
    <div class="lg:col-span-8 bg-white rounded-3xl border border-[#ebdccd] shadow-sm p-6 sm:p-8">
        <div class="flex items-center justify-between pb-4 border-b border-[#f2e6d9] mb-6">
            <div>
                <h3 class="font-serif text-xl font-bold text-charcoal-900">Recent Customer Purchases</h3>
                <p class="text-xs text-charcoal-800/60">Live orders placed by Shiv Vani patrons</p>
            </div>
            <a href="orders.php" class="text-xs font-bold text-blush-600 hover:underline">View All &rarr;</a>
        </div>

        <?php if (!empty($recentOrders)): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-charcoal-800/60 uppercase tracking-wider border-b border-[#f2e6d9]">
                            <th class="pb-3 font-semibold">Order</th>
                            <th class="pb-3 font-semibold">Customer</th>
                            <th class="pb-3 font-semibold">Total Paid</th>
                            <th class="pb-3 font-semibold">Method</th>
                            <th class="pb-3 font-semibold">Fulfillment Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f2e6d9]">
                        <?php foreach ($recentOrders as $ord): ?>
                            <tr>
                                <td class="py-4 font-mono font-bold text-charcoal-900">
                                    <?= htmlspecialchars($ord['order_code']) ?>
                                </td>
                                <td class="py-4">
                                    <div class="font-bold text-charcoal-900"><?= htmlspecialchars($ord['customer_name']) ?></div>
                                    <div class="text-[10px] text-charcoal-800/60"><?= htmlspecialchars($ord['city']) ?></div>
                                </td>
                                <td class="py-4 font-serif font-bold text-sm text-charcoal-900">
                                    <?= formatPrice($ord['total_amount']) ?>
                                </td>
                                <td class="py-4">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold <?= $ord['payment_status'] === 'Paid' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' ?>">
                                        <?= htmlspecialchars($ord['payment_method']) ?>
                                    </span>
                                </td>
                                <td class="py-4">
                                    <form method="POST" action="index.php" class="inline-block">
                                        <input type="hidden" name="action" value="update_order_status">
                                        <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                                        <select name="order_status" onchange="this.form.submit()" class="bg-[#FAF7F2] border border-[#ebdccd] rounded-lg px-2 py-1 text-[11px] font-bold text-charcoal-900 focus:outline-none">
                                            <option value="Pending" <?= $ord['order_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="Processing" <?= $ord['order_status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
                                            <option value="Shipped" <?= $ord['order_status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                                            <option value="Delivered" <?= $ord['order_status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-xs text-charcoal-800/60 py-6 text-center">No orders registered yet.</p>
        <?php endif; ?>
    </div>

    <!-- Right Column: Quick Shortcuts & Low Inventory -->
    <div class="lg:col-span-4 space-y-6">
        
        <!-- Quick Configuration Hub -->
        <div class="bg-white rounded-3xl border border-[#ebdccd] shadow-sm p-6 space-y-3">
            <h3 class="font-serif text-lg font-bold text-charcoal-900 pb-2 border-b border-[#f2e6d9]">Quick Actions</h3>
            <div class="space-y-2">
                <a href="categories.php" class="flex items-center justify-between p-3 rounded-2xl bg-cream-50 hover:bg-blush-50 border border-[#ebdccd] transition-colors">
                    <div class="flex items-center gap-2.5">
                        <i data-lucide="folder-plus" class="w-4 h-4 text-blush-600"></i>
                        <span class="text-xs font-bold text-charcoal-900">Manage Navbar Categories</span>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-charcoal-800/40"></i>
                </a>

                <a href="payment-settings.php" class="flex items-center justify-between p-3 rounded-2xl bg-cream-50 hover:bg-emerald-50 border border-[#ebdccd] transition-colors">
                    <div class="flex items-center gap-2.5">
                        <i data-lucide="qr-code" class="w-4 h-4 text-emerald-600"></i>
                        <span class="text-xs font-bold text-charcoal-900">Configure UPI QR &amp; Cards API</span>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-charcoal-800/40"></i>
                </a>

                <a href="reviews.php" class="flex items-center justify-between p-3 rounded-2xl bg-cream-50 hover:bg-rose-50 border border-[#ebdccd] transition-colors">
                    <div class="flex items-center gap-2.5">
                        <i data-lucide="video" class="w-4 h-4 text-rose-600"></i>
                        <span class="text-xs font-bold text-charcoal-900">Customer Video &amp; Text Reviews</span>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-charcoal-800/40"></i>
                </a>
            </div>
        </div>

        <!-- Inventory Watchlist -->
        <div class="bg-white rounded-3xl border border-[#ebdccd] shadow-sm p-6 space-y-4">
            <div class="pb-2 border-b border-[#f2e6d9]">
                <h3 class="font-serif text-lg font-bold text-charcoal-900">Inventory Watchlist</h3>
                <p class="text-[11px] text-charcoal-800/60">Candles needing refill</p>
            </div>

            <div class="space-y-3">
                <?php foreach ($lowStock as $ls): ?>
                    <?php 
                    $imgSrc = str_starts_with($ls['image_url'], 'uploads/') ? '../' . $ls['image_url'] : $ls['image_url'];
                    ?>
                    <div class="flex items-center justify-between p-2.5 rounded-2xl bg-[#FAF7F2] border border-[#ebdccd]">
                        <div class="flex items-center gap-3 min-w-0">
                            <img src="<?= htmlspecialchars($imgSrc) ?>" alt="" class="w-10 h-10 rounded-xl object-cover">
                            <div class="min-w-0">
                                <h5 class="text-xs font-bold text-charcoal-900 truncate"><?= htmlspecialchars($ls['name']) ?></h5>
                                <span class="text-[10px] text-charcoal-800/60 block"><?= formatPrice($ls['final_price']) ?></span>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full <?= $ls['stock'] < 30 ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800' ?>">
                                <?= $ls['stock'] ?> left
                            </span>
                            <a href="product-edit.php?id=<?= $ls['id'] ?>" class="block text-[10px] text-blush-600 hover:underline mt-0.5 font-bold">Edit</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
