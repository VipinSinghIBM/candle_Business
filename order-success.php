<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$code = isset($_GET['code']) ? trim($_GET['code']) : '';

if (empty($code)) {
    header('Location: shop.php');
    exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM orders WHERE order_code = ?");
$stmt->execute([$code]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: shop.php');
    exit;
}

// Fetch order items
$itemStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
$itemStmt->execute([$order['id']]);
$items = $itemStmt->fetchAll();

$pageTitle = "Order Confirmed • " . $order['order_code'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    
    <!-- Celebration Hero with Shiv Vani Logo -->
    <div class="text-center space-y-4 mb-12">
        <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto shadow-md border-2 border-gold-400 p-1">
            <img src="<?= STORE_LOGO ?>" alt="Logo" class="w-full h-full rounded-full object-cover">
        </div>
        <span class="text-xs uppercase tracking-[0.25em] text-blush-600 font-bold block">Order Successfully Registered</span>
        <h1 class="font-serif text-3xl sm:text-4xl lg:text-5xl font-bold text-charcoal-900">
            Dhanyavaad, <?= htmlspecialchars($order['customer_name']) ?>!
        </h1>
        <p class="text-xs sm:text-sm text-charcoal-800/70 max-w-lg mx-auto">
            Your sacred candle order has been received at the <strong><?= STORE_NAME ?></strong> atelier. We are carefully preparing your hand-poured vessels.
        </p>
    </div>

    <!-- Order Receipt Box (Printable) -->
    <div id="printable-invoice" class="bg-white rounded-3xl border border-[#ebdccd] p-6 sm:p-10 shadow-sm space-y-8">
        
        <!-- Top Receipt Meta -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-6 border-b border-[#f2e6d9] gap-4">
            <div>
                <span class="text-[10px] uppercase tracking-wider text-charcoal-800/60 font-semibold block">Order Reference</span>
                <span class="font-mono text-lg font-bold text-charcoal-900"><?= htmlspecialchars($order['order_code']) ?></span>
            </div>
            <div class="sm:text-right">
                <span class="text-[10px] uppercase tracking-wider text-charcoal-800/60 font-semibold block">Date &amp; Time</span>
                <span class="text-xs font-semibold text-charcoal-900"><?= date('F j, Y • h:i A', strtotime($order['created_at'])) ?></span>
            </div>
            <div class="sm:text-right">
                <span class="text-[10px] uppercase tracking-wider text-charcoal-800/60 font-semibold block">Payment Mode</span>
                <span class="text-xs font-bold px-2.5 py-1 rounded-full <?= $order['payment_status'] === 'Paid' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' ?>">
                    <?= htmlspecialchars($order['payment_status']) ?> (<?= htmlspecialchars($order['payment_method']) ?>)
                </span>
                <?php if (!empty($order['transaction_ref'])): ?>
                    <span class="text-[10px] text-charcoal-800/60 block mt-1 font-mono">Ref: <?= htmlspecialchars($order['transaction_ref']) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recipient & Delivery Details -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
            <div>
                <h5 class="text-xs uppercase tracking-wider font-bold text-charcoal-900 mb-2">Delivery Destination</h5>
                <p class="text-charcoal-800 font-bold"><?= htmlspecialchars($order['customer_name']) ?></p>
                <p class="text-charcoal-800/70 text-xs mt-0.5"><?= htmlspecialchars($order['shipping_address']) ?></p>
                <p class="text-charcoal-800/70 text-xs"><?= htmlspecialchars($order['city']) ?>, PIN: <?= htmlspecialchars($order['postal_code']) ?></p>
            </div>
            <div>
                <h5 class="text-xs uppercase tracking-wider font-bold text-charcoal-900 mb-2">Customer &amp; Tracking</h5>
                <p class="text-charcoal-800/70 text-xs">Email: <span class="font-semibold text-charcoal-900"><?= htmlspecialchars($order['customer_email']) ?></span></p>
                <p class="text-charcoal-800/70 text-xs mt-0.5">Phone: <span class="font-semibold text-charcoal-900"><?= htmlspecialchars($order['customer_phone']) ?></span></p>
                <p class="text-charcoal-800/70 text-xs mt-0.5">Fulfillment Status: <span class="font-bold text-blush-700 uppercase"><?= htmlspecialchars($order['order_status']) ?></span></p>
            </div>
        </div>

        <!-- Items Table -->
        <div class="pt-4">
            <h5 class="text-xs uppercase tracking-wider font-bold text-charcoal-900 mb-4">Ordered Fragrance Vessels</h5>
            <div class="divide-y divide-[#f2e6d9] border-y border-[#f2e6d9]">
                <?php foreach ($items as $item): ?>
                    <div class="py-4 flex justify-between items-center text-sm">
                        <div>
                            <span class="font-serif font-bold text-charcoal-900"><?= htmlspecialchars($item['product_name']) ?></span>
                            <span class="text-xs text-charcoal-800/60 block">Qty: <?= $item['quantity'] ?> &bull; <?= formatPrice($item['price']) ?></span>
                        </div>
                        <span class="font-serif font-bold text-charcoal-900"><?= formatPrice($item['total']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Totals Breakdown -->
        <div class="space-y-2 text-sm pt-2 max-w-xs ml-auto">
            <div class="flex justify-between text-charcoal-800/70">
                <span>Subtotal</span>
                <span><?= formatPrice($order['subtotal']) ?></span>
            </div>
            <?php if ($order['discount_amount'] > 0): ?>
                <div class="flex justify-between text-emerald-800 font-semibold">
                    <span>Discount</span>
                    <span>-<?= formatPrice($order['discount_amount']) ?></span>
                </div>
            <?php endif; ?>
            <div class="flex justify-between text-charcoal-800/70">
                <span>Shipping Delivery</span>
                <span><?= $order['shipping_fee'] == 0 ? 'Complimentary' : formatPrice($order['shipping_fee']) ?></span>
            </div>
            <div class="pt-3 border-t border-[#f2e6d9] flex justify-between items-baseline font-serif font-bold text-lg text-charcoal-900">
                <span>Total Paid</span>
                <span class="text-blush-700"><?= formatPrice($order['total_amount']) ?></span>
            </div>
        </div>

    </div>

    <!-- Actions -->
    <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
        <button onclick="window.print()" class="w-full sm:w-auto px-6 py-3 rounded-full bg-white border border-[#ebdccd] text-charcoal-800 font-bold text-xs uppercase tracking-wider hover:bg-cream-100 transition-colors flex items-center justify-center gap-2">
            <i data-lucide="printer" class="w-4 h-4"></i>
            <span>Print Invoice Receipt</span>
        </button>

        <a href="shop.php" class="btn-luxury w-full sm:w-auto px-8 py-3 rounded-full bg-charcoal-900 hover:bg-blush-700 text-cream-50 font-bold text-xs uppercase tracking-widest transition-all shadow-md text-center">
            Continue Scent Journey
        </a>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
