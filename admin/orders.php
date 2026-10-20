<?php
$pageTitle = "Customer Orders";
require_once __DIR__ . '/includes/admin_header.php';

$db = getDB();

// Handle status change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $orderId = (int)$_POST['order_id'];
    $newStatus = sanitize($_POST['order_status']);
    $newPayment = sanitize($_POST['payment_status'] ?? '');

    $sql = "UPDATE orders SET order_status = :status";
    $params = [':status' => $newStatus, ':id' => $orderId];

    if (!empty($newPayment)) {
        $sql .= ", payment_status = :payment";
        $params[':payment'] = $newPayment;
    }

    $sql .= " WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    setFlash('success', "Order #{$orderId} status updated.");
    header('Location: orders.php');
    exit;
}

$statusFilter = trim($_GET['status'] ?? '');

$sql = "SELECT * FROM orders WHERE 1=1";
$params = [];

if (!empty($statusFilter)) {
    $sql .= " AND order_status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 mb-6 border-b border-[#ebdccd] gap-4">
    <div>
        <span class="text-xs uppercase tracking-widest text-blush-600 font-bold block mb-1"><?= STORE_NAME ?></span>
        <h1 class="font-serif text-3xl font-bold text-charcoal-900">Patron Orders &amp; Fulfillment</h1>
        <p class="text-xs text-charcoal-800/60 mt-0.5">Track fulfillment status, delivery addresses, and payment settlements.</p>
    </div>
</div>

<!-- Filters -->
<div class="flex items-center gap-2 overflow-x-auto pb-4 mb-6 scrollbar-none">
    <a href="orders.php" class="px-4 py-2 rounded-full text-xs font-semibold uppercase tracking-wider transition-all whitespace-nowrap <?= empty($statusFilter) ? 'bg-charcoal-900 text-white' : 'bg-white border border-[#ebdccd] text-charcoal-800 hover:bg-cream-100' ?>">
        All Orders
    </a>
    <a href="orders.php?status=Pending" class="px-4 py-2 rounded-full text-xs font-semibold uppercase tracking-wider transition-all whitespace-nowrap <?= $statusFilter === 'Pending' ? 'bg-charcoal-900 text-white' : 'bg-white border border-[#ebdccd] text-charcoal-800 hover:bg-cream-100' ?>">
        Pending
    </a>
    <a href="orders.php?status=Processing" class="px-4 py-2 rounded-full text-xs font-semibold uppercase tracking-wider transition-all whitespace-nowrap <?= $statusFilter === 'Processing' ? 'bg-charcoal-900 text-white' : 'bg-white border border-[#ebdccd] text-charcoal-800 hover:bg-cream-100' ?>">
        Processing
    </a>
    <a href="orders.php?status=Shipped" class="px-4 py-2 rounded-full text-xs font-semibold uppercase tracking-wider transition-all whitespace-nowrap <?= $statusFilter === 'Shipped' ? 'bg-charcoal-900 text-white' : 'bg-white border border-[#ebdccd] text-charcoal-800 hover:bg-cream-100' ?>">
        Shipped
    </a>
    <a href="orders.php?status=Delivered" class="px-4 py-2 rounded-full text-xs font-semibold uppercase tracking-wider transition-all whitespace-nowrap <?= $statusFilter === 'Delivered' ? 'bg-charcoal-900 text-white' : 'bg-white border border-[#ebdccd] text-charcoal-800 hover:bg-cream-100' ?>">
        Delivered
    </a>
</div>

<!-- Orders List -->
<div class="space-y-6">
    <?php if (!empty($orders)): ?>
        <?php foreach ($orders as $ord): ?>
            <?php
            $itemsStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $itemsStmt->execute([$ord['id']]);
            $ordItems = $itemsStmt->fetchAll();
            ?>
            <div class="bg-white rounded-3xl border border-[#ebdccd] shadow-sm p-6 sm:p-8 space-y-6">
                
                <!-- Order Top Header -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-4 border-b border-[#f2e6d9] gap-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="font-mono text-base font-bold text-charcoal-900"><?= htmlspecialchars($ord['order_code']) ?></span>
                            <span class="text-xs text-charcoal-800/60">&bull; <?= date('M d, Y • h:i A', strtotime($ord['created_at'])) ?></span>
                        </div>
                        <span class="text-xs text-charcoal-800/70 block mt-0.5">
                            Customer: <strong class="text-charcoal-900"><?= htmlspecialchars($ord['customer_name']) ?></strong> (<?= htmlspecialchars($ord['customer_email']) ?>)
                        </span>
                        <?php if (!empty($ord['transaction_ref'])): ?>
                            <span class="text-[10px] text-emerald-800 font-mono block mt-0.5">
                                Transaction UTR / Ref: <?= htmlspecialchars($ord['transaction_ref']) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="flex items-center gap-3">
                        <form method="POST" action="orders.php" class="flex items-center gap-2">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">

                            <select name="order_status" class="bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-3 py-1.5 text-xs font-bold text-charcoal-900 focus:outline-none">
                                <option value="Pending" <?= $ord['order_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Processing" <?= $ord['order_status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
                                <option value="Shipped" <?= $ord['order_status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                                <option value="Delivered" <?= $ord['order_status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                            </select>

                            <select name="payment_status" class="bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-3 py-1.5 text-xs font-bold text-charcoal-900 focus:outline-none">
                                <option value="Paid" <?= $ord['payment_status'] === 'Paid' ? 'selected' : '' ?>>Paid</option>
                                <option value="Pending" <?= $ord['payment_status'] === 'Pending' ? 'selected' : '' ?>>Unpaid</option>
                            </select>

                            <button type="submit" class="bg-charcoal-900 hover:bg-blush-600 text-white text-xs font-bold px-3 py-1.5 rounded-xl transition-colors">
                                Update
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Order Content (Items & Address) -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 text-xs">
                    
                    <!-- Items Purchased -->
                    <div class="md:col-span-7 space-y-2">
                        <span class="text-[10px] uppercase font-bold tracking-wider text-charcoal-800/60 block">Ordered Candle Items</span>
                        <div class="divide-y divide-[#f2e6d9] border border-[#ebdccd] rounded-2xl p-3 bg-cream-50/40">
                            <?php foreach ($ordItems as $it): ?>
                                <div class="py-2 flex justify-between items-center first:pt-0 last:pb-0">
                                    <div>
                                        <span class="font-serif font-bold text-sm text-charcoal-900"><?= htmlspecialchars($it['product_name']) ?></span>
                                        <span class="text-[10px] text-charcoal-800/60 block">Qty: <?= $it['quantity'] ?> &bull; <?= formatPrice($it['price']) ?></span>
                                    </div>
                                    <span class="font-serif font-bold text-charcoal-900 text-sm"><?= formatPrice($it['total']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Shipping Address & Financials -->
                    <div class="md:col-span-5 space-y-3 bg-[#FAF7F2] p-4 rounded-2xl border border-[#ebdccd]">
                        <div>
                            <span class="text-[10px] uppercase font-bold tracking-wider text-charcoal-800/60 block mb-1">Destination</span>
                            <p class="font-bold text-charcoal-900"><?= htmlspecialchars($ord['shipping_address']) ?></p>
                            <p class="text-charcoal-800/70"><?= htmlspecialchars($ord['city']) ?>, PIN: <?= htmlspecialchars($ord['postal_code']) ?></p>
                            <p class="text-charcoal-800/70 mt-1">Tel: <?= htmlspecialchars($ord['customer_phone']) ?></p>
                        </div>

                        <div class="pt-3 border-t border-[#ebdccd] space-y-1">
                            <div class="flex justify-between text-charcoal-800/70">
                                <span>Subtotal</span>
                                <span><?= formatPrice($ord['subtotal']) ?></span>
                            </div>
                            <?php if ($ord['discount_amount'] > 0): ?>
                                <div class="flex justify-between text-emerald-800 font-semibold">
                                    <span>Discount</span>
                                    <span>-<?= formatPrice($ord['discount_amount']) ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="flex justify-between text-charcoal-800/70">
                                <span>Delivery Fee</span>
                                <span><?= $ord['shipping_fee'] == 0 ? 'Free' : formatPrice($ord['shipping_fee']) ?></span>
                            </div>
                            <div class="pt-1 flex justify-between items-baseline font-bold font-serif text-base text-charcoal-900">
                                <span>Total (<?= htmlspecialchars($ord['payment_method']) ?>)</span>
                                <span class="text-blush-700"><?= formatPrice($ord['total_amount']) ?></span>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="bg-white rounded-3xl p-16 text-center border border-[#ebdccd] max-w-lg mx-auto shadow-sm">
            <h3 class="font-serif text-2xl font-bold text-charcoal-900">No Orders Found</h3>
            <p class="text-xs text-charcoal-800/60 mt-1">There are currently no orders registered under this filter.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
