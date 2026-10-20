<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();

// Handle Cart Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty = (int)($_POST['quantity'] ?? 1);
        if (addToCart($productId, $qty)) {
            setFlash('success', 'Candle added to your shopping bag.');
        } else {
            setFlash('error', 'Unable to add candle to bag.');
        }
        header('Location: cart.php');
        exit;
    }

    if ($action === 'update') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty = (int)($_POST['quantity'] ?? 1);
        updateCartQuantity($productId, $qty);
        setFlash('success', 'Bag quantities updated.');
        header('Location: cart.php');
        exit;
    }

    if ($action === 'remove') {
        $productId = (int)($_POST['product_id'] ?? 0);
        removeFromCart($productId);
        setFlash('info', 'Candle removed from bag.');
        header('Location: cart.php');
        exit;
    }

    if ($action === 'clear') {
        clearCart();
        setFlash('info', 'Your shopping bag has been cleared.');
        header('Location: cart.php');
        exit;
    }

    if ($action === 'apply_coupon') {
        $code = strtoupper(trim($_POST['coupon_code'] ?? ''));
        if (!empty($code)) {
            $stmt = $db->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1");
            $stmt->execute([$code]);
            $coupon = $stmt->fetch();

            if ($coupon) {
                $_SESSION['coupon'] = [
                    'code' => $coupon['code'],
                    'discount_percent' => (int)$coupon['discount_percent'],
                    'min_order' => (float)$coupon['min_order'],
                ];
                setFlash('success', "Coupon '{$coupon['code']}' applied! You received {$coupon['discount_percent']}% off.");
            } else {
                setFlash('error', "Invalid promo code '{$code}'. Try using 'SHIVVANI15'.");
            }
        }
        header('Location: cart.php');
        exit;
    }

    if ($action === 'remove_coupon') {
        unset($_SESSION['coupon']);
        setFlash('info', 'Coupon removed.');
        header('Location: cart.php');
        exit;
    }
}

$pageTitle = "Your Shopping Bag";
require_once __DIR__ . '/includes/header.php';

$cart = getCart();
$totals = calculateOrderTotals();
$subtotal = $totals['subtotal'];
$discountAmount = $totals['discount_amount'];
$appliedCoupon = $totals['applied_coupon'];
$shippingFee = $totals['shipping_fee'];
$totalAmount = $totals['total_amount'];

$amountNeededForFreeShip = max(0, FREE_SHIPPING_THRESHOLD - $subtotal);
$freeShippingProgress = min(100, ($subtotal / FREE_SHIPPING_THRESHOLD) * 100);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <div class="mb-10 text-center sm:text-left">
        <span class="text-xs uppercase tracking-[0.25em] text-blush-600 font-bold block mb-1"><?= STORE_NAME ?></span>
        <h1 class="font-serif text-3xl sm:text-4xl text-charcoal-900 font-bold">Shopping Bag</h1>
    </div>

    <?php if (empty($cart)): ?>
        <div class="bg-white rounded-3xl p-16 text-center border border-[#ebdccd] max-w-lg mx-auto shadow-sm">
            <div class="w-20 h-20 rounded-full bg-blush-50 text-blush-600 flex items-center justify-center mx-auto mb-5 border border-blush-100">
                <i data-lucide="shopping-bag" class="w-10 h-10"></i>
            </div>
            <h3 class="font-serif text-2xl font-bold text-charcoal-900">Your bag is currently empty</h3>
            <p class="text-xs text-charcoal-800/70 mt-2 mb-8">
                Discover handcrafted spiritual and botanical candles by <?= STORE_NAME ?>.
            </p>
            <a href="shop.php" class="btn-luxury inline-block px-8 py-3.5 rounded-full bg-charcoal-900 hover:bg-blush-700 text-cream-50 text-xs uppercase tracking-widest font-semibold transition-all shadow-md">
                Browse All Candles
            </a>
        </div>
    <?php else: ?>
        
        <!-- Free Shipping Meter -->
        <div class="mb-8 bg-white p-5 rounded-2xl border border-[#ebdccd] shadow-sm max-w-4xl">
            <div class="flex items-center justify-between text-xs font-semibold mb-2">
                <div class="flex items-center gap-2 text-charcoal-900">
                    <i data-lucide="truck" class="w-4 h-4 text-blush-600"></i>
                    <?php if ($amountNeededForFreeShip == 0): ?>
                        <span class="text-emerald-800 font-bold">✨ Complimentary Shipping Unlocked by Shiv Vani Enterprises!</span>
                    <?php else: ?>
                        <span>Add <strong class="text-blush-700"><?= formatPrice($amountNeededForFreeShip) ?></strong> more for Complimentary Shipping!</span>
                    <?php endif; ?>
                </div>
                <span class="text-charcoal-800/60 font-bold"><?= round($freeShippingProgress) ?>%</span>
            </div>
            <div class="w-full bg-cream-200 h-2.5 rounded-full overflow-hidden">
                <div class="bg-blush-600 h-full rounded-full transition-all duration-500" style="width: <?= $freeShippingProgress ?>%"></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
            
            <!-- Left Items List -->
            <div class="lg:col-span-8 space-y-4">
                <div class="bg-white rounded-3xl border border-[#ebdccd] overflow-hidden shadow-sm">
                    <div class="divide-y divide-[#f2e6d9]">
                        <?php foreach ($cart as $item): ?>
                            <div class="p-6 flex flex-col sm:flex-row items-center gap-6">
                                
                                <!-- Candle Thumbnail -->
                                <a href="product.php?id=<?= $item['id'] ?>" class="w-24 h-24 rounded-2xl overflow-hidden bg-cream-50 flex-shrink-0 border border-[#ebdccd]">
                                    <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-full h-full object-cover">
                                </a>

                                <!-- Candle Meta with 2 Prices -->
                                <div class="flex-grow text-center sm:text-left">
                                    <span class="text-[10px] uppercase tracking-wider text-blush-600 font-bold block">
                                        <?= htmlspecialchars($item['category']) ?>
                                    </span>
                                    <a href="product.php?id=<?= $item['id'] ?>" class="font-serif text-lg font-bold text-charcoal-900 hover:text-blush-600 transition-colors">
                                        <?= htmlspecialchars($item['name']) ?>
                                    </a>
                                    <p class="text-xs text-charcoal-800/60 mt-0.5">✧ <?= htmlspecialchars($item['scent_notes']) ?></p>
                                    
                                    <div class="mt-2 flex items-center gap-2 justify-center sm:justify-start">
                                        <span class="text-sm font-bold text-charcoal-900"><?= formatPrice($item['price']) ?></span>
                                        <?php if ($item['discount_percent'] > 0): ?>
                                            <span class="text-xs text-charcoal-800/40 line-through">
                                                M.R.P: <?= formatPrice($item['original_price']) ?>
                                            </span>
                                            <span class="text-[10px] bg-blush-100 text-blush-800 font-bold px-1.5 py-0.5 rounded">
                                                <?= $item['discount_percent'] ?>% Off
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Quantity Stepper Form -->
                                <form action="cart.php" method="POST" class="flex items-center gap-2">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                    <div class="qty-container flex items-center border border-[#ebdccd] rounded-full bg-[#FAF7F2] px-2 py-1">
                                        <button type="submit" name="quantity" value="<?= $item['quantity'] - 1 ?>" class="w-7 h-7 flex items-center justify-center text-charcoal-800 hover:text-blush-600 font-bold">
                                            &minus;
                                        </button>
                                        <span class="w-8 text-center text-xs font-bold text-charcoal-900"><?= $item['quantity'] ?></span>
                                        <button type="submit" name="quantity" value="<?= $item['quantity'] + 1 ?>" class="w-7 h-7 flex items-center justify-center text-charcoal-800 hover:text-blush-600 font-bold">
                                            +
                                        </button>
                                    </div>
                                </form>

                                <!-- Line Total -->
                                <div class="text-right min-w-[90px]">
                                    <span class="font-serif text-lg font-bold text-charcoal-900">
                                        <?= formatPrice($item['price'] * $item['quantity']) ?>
                                    </span>
                                </div>

                                <!-- Remove Button -->
                                <form action="cart.php" method="POST">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="text-charcoal-800/40 hover:text-rose-600 p-2 transition-colors" title="Remove Candle">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>

                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Cart Footer actions -->
                    <div class="p-4 bg-cream-50/50 border-t border-[#f2e6d9] flex justify-between items-center text-xs">
                        <a href="shop.php" class="text-charcoal-900 hover:text-blush-600 font-bold flex items-center gap-1">
                            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                            <span>Continue Shopping</span>
                        </a>

                        <form action="cart.php" method="POST" onsubmit="return confirm('Clear entire shopping bag?');">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="text-charcoal-800/60 hover:text-rose-700 font-semibold">Clear Bag</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Summary Column -->
            <div class="lg:col-span-4 space-y-6">
                
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#ebdccd] shadow-sm space-y-5">
                    <h3 class="font-serif text-xl font-bold text-charcoal-900 pb-3 border-b border-[#f2e6d9]">Order Summary</h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-charcoal-800/70">
                            <span>Subtotal (<?= getCartCount() ?> items)</span>
                            <span class="font-bold text-charcoal-900"><?= formatPrice($subtotal) ?></span>
                        </div>

                        <?php if ($discountAmount > 0): ?>
                            <div class="flex justify-between text-emerald-800 font-semibold bg-emerald-50 p-2.5 rounded-xl border border-emerald-100">
                                <span class="flex items-center gap-1">
                                    <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                                    <span>Coupon (<?= htmlspecialchars($appliedCoupon['code']) ?>)</span>
                                </span>
                                <span>-<?= formatPrice($discountAmount) ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="flex justify-between text-charcoal-800/70">
                            <span>Delivery Shipping</span>
                            <?php if ($shippingFee == 0): ?>
                                <span class="text-emerald-800 font-bold uppercase text-xs bg-emerald-50 px-2 py-0.5 rounded-full">Complimentary</span>
                            <?php else: ?>
                                <span class="font-bold text-charcoal-900"><?= formatPrice($shippingFee) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="pt-4 border-t border-[#f2e6d9] flex justify-between items-baseline">
                            <span class="font-serif text-lg font-bold text-charcoal-900">Total Payable</span>
                            <span class="font-serif text-2xl font-bold text-blush-700"><?= formatPrice($totalAmount) ?></span>
                        </div>
                    </div>

                    <a href="checkout.php" class="btn-luxury w-full block text-center bg-charcoal-900 hover:bg-blush-700 text-cream-50 font-bold py-4 px-6 rounded-2xl text-xs tracking-widest uppercase transition-all shadow-lg hover:shadow-xl">
                        Proceed to Checkout &rarr;
                    </a>

                    <div class="text-center pt-2 text-[11px] text-charcoal-800/60 flex items-center justify-center gap-2">
                        <i data-lucide="lock" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span>Scan &amp; Pay UPI / Cards Encrypted</span>
                    </div>
                </div>

                <!-- Promo Coupon Form -->
                <div class="bg-white rounded-3xl p-6 border border-[#ebdccd] shadow-sm">
                    <h4 class="text-xs uppercase tracking-wider font-bold text-charcoal-900 mb-3 flex items-center gap-2">
                        <i data-lucide="gift" class="w-4 h-4 text-gold-600"></i>
                        <span>Apply Shiv Vani Coupon</span>
                    </h4>

                    <?php if ($appliedCoupon): ?>
                        <div class="flex items-center justify-between bg-cream-100 p-3 rounded-2xl border border-cream-300">
                            <div>
                                <span class="font-mono font-bold text-charcoal-900 text-xs"><?= htmlspecialchars($appliedCoupon['code']) ?></span>
                                <span class="text-[11px] text-emerald-800 block font-semibold"><?= $appliedCoupon['discount_percent'] ?>% discount applied</span>
                            </div>
                            <form action="cart.php" method="POST">
                                <input type="hidden" name="action" value="remove_coupon">
                                <button type="submit" class="text-xs text-rose-600 hover:underline font-bold">Remove</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <form action="cart.php" method="POST" class="flex gap-2">
                            <input type="hidden" name="action" value="apply_coupon">
                            <input 
                                type="text" 
                                name="coupon_code" 
                                placeholder="Try 'SHIVVANI15'" 
                                class="bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-3.5 py-2 text-xs uppercase font-mono text-charcoal-900 placeholder-charcoal-800/40 focus:outline-none focus:border-blush-500 flex-grow"
                                required
                            >
                            <button type="submit" class="bg-charcoal-900 hover:bg-blush-600 text-white text-xs font-bold px-4 py-2 rounded-xl transition-colors">
                                Apply
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

            </div>

        </div>

    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
