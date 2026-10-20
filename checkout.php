<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$cart = getCart();

if (empty($cart)) {
    header('Location: shop.php');
    exit;
}

$totals = calculateOrderTotals();
$subtotal = $totals['subtotal'];
$discountAmount = $totals['discount_amount'];
$shippingFee = $totals['shipping_fee'];
$totalAmount = $totals['total_amount'];

// Fetch ONLY enabled payment methods configured by Admin
$activeGateways = getPaymentGateways(true);

if (empty($activeGateways)) {
    // Fallback default gateway if admin disabled all
    $activeGateways = [
        ['gateway_key' => 'upi_qr', 'title' => 'UPI / QR Code Scan', 'subtitle' => 'Google Pay, PhonePe, Paytm', 'upi_id' => 'shivvani.enterprises@okaxis', 'api_key' => '', 'instructions' => 'Scan QR to pay'],
        ['gateway_key' => 'cod', 'title' => 'Cash on Delivery', 'subtitle' => 'Pay at doorstep', 'upi_id' => '', 'api_key' => '', 'instructions' => 'Pay cash on arrival'],
    ];
}

$errors = [];

// Handle Order Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['customer_name'] ?? '');
    $email = filter_var($_POST['customer_email'] ?? '', FILTER_VALIDATE_EMAIL);
    $phone = sanitize($_POST['customer_phone'] ?? '');
    $address = sanitize($_POST['shipping_address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $postal = sanitize($_POST['postal_code'] ?? '');
    $selectedGateway = sanitize($_POST['payment_method_key'] ?? $activeGateways[0]['gateway_key']);
    $transactionRef = sanitize($_POST['transaction_ref'] ?? '');

    if (empty($name)) $errors[] = "Please provide your full recipient name.";
    if (!$email) $errors[] = "Please provide a valid email address.";
    if (empty($phone)) $errors[] = "Please provide your active contact phone.";
    if (empty($address)) $errors[] = "Please enter your street address.";
    if (empty($city)) $errors[] = "Please enter your city.";
    if (empty($postal)) $errors[] = "Please enter your postal PIN code.";

    if (empty($errors)) {
        $db = getDB();
        try {
            $db->beginTransaction();

            $orderCode = 'SV-' . mt_rand(10000, 99999);
            
            // Map gateway key to readable title
            $paymentMethodTitle = 'Online Payment';
            foreach ($activeGateways as $gw) {
                if ($gw['gateway_key'] === $selectedGateway) {
                    $paymentMethodTitle = $gw['title'];
                    break;
                }
            }

            $paymentStatus = ($selectedGateway === 'cod') ? 'Pending' : 'Paid';

            // Insert into orders
            $orderStmt = $db->prepare("INSERT INTO orders (
                order_code, customer_name, customer_email, customer_phone, shipping_address,
                city, postal_code, subtotal, discount_amount, shipping_fee, total_amount,
                payment_method, payment_status, order_status, transaction_ref
            ) VALUES (
                :code, :name, :email, :phone, :address,
                :city, :postal, :subtotal, :discount, :shipping, :total,
                :method, :pstatus, 'Processing', :tref
            )");

            $orderStmt->execute([
                ':code' => $orderCode,
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':address' => $address,
                ':city' => $city,
                ':postal' => $postal,
                ':subtotal' => $subtotal,
                ':discount' => $discountAmount,
                ':shipping' => $shippingFee,
                ':total' => $totalAmount,
                ':method' => $paymentMethodTitle,
                ':pstatus' => $paymentStatus,
                ':tref' => $transactionRef,
            ]);

            $orderId = $db->lastInsertId();

            // Insert into order_items
            $itemStmt = $db->prepare("INSERT INTO order_items (
                order_id, product_id, product_name, price, quantity, total
            ) VALUES (
                :order_id, :product_id, :product_name, :price, :quantity, :total
            )");

            foreach ($cart as $item) {
                $itemStmt->execute([
                    ':order_id' => $orderId,
                    ':product_id' => $item['id'],
                    ':product_name' => $item['name'],
                    ':price' => $item['price'],
                    ':quantity' => $item['quantity'],
                    ':total' => ($item['price'] * $item['quantity']),
                ]);

                // Reduce inventory stock
                $db->prepare("UPDATE products SET stock = MAX(0, stock - ?) WHERE id = ?")
                   ->execute([$item['quantity'], $item['id']]);
            }

            $db->commit();

            // Clear the cart
            clearCart();

            // Redirect to Success Receipt
            header('Location: order-success.php?code=' . urlencode($orderCode));
            exit;

        } catch (Throwable $e) {
            $db->rollBack();
            $errors[] = "Failed to process order: " . $e->getMessage();
        }
    }
}

$pageTitle = "Checkout & Secure Payment";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <div class="mb-10 text-center sm:text-left">
        <span class="text-xs uppercase tracking-[0.25em] text-blush-600 font-bold block mb-1"><?= STORE_NAME ?></span>
        <h1 class="font-serif text-3xl sm:text-4xl text-charcoal-900 font-bold">Secure Checkout</h1>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="mb-8 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
            <div class="font-bold mb-1">Please review the details below:</div>
            <ul class="list-disc list-inside space-y-1">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="checkout.php" id="checkout-form">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
            
            <!-- Left Column: Shipping & Admin-Configured Payment Methods -->
            <div class="lg:col-span-7 space-y-8">
                
                <!-- 1. Recipient Details -->
                <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#ebdccd] shadow-sm space-y-6">
                    <h3 class="font-serif text-xl font-bold text-charcoal-900 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-full bg-blush-100 text-blush-800 flex items-center justify-center text-xs font-sans font-bold">1</span>
                        <span>Recipient &amp; Delivery Address</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs uppercase tracking-wider font-bold text-charcoal-800 mb-1">Full Name *</label>
                            <input type="text" name="customer_name" required value="<?= htmlspecialchars($_POST['customer_name'] ?? '') ?>" placeholder="e.g. Pooja Sharma" class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blush-500">
                        </div>

                        <div>
                            <label class="block text-xs uppercase tracking-wider font-bold text-charcoal-800 mb-1">Email Address *</label>
                            <input type="email" name="customer_email" required value="<?= htmlspecialchars($_POST['customer_email'] ?? '') ?>" placeholder="pooja.sharma@example.com" class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blush-500">
                        </div>

                        <div>
                            <label class="block text-xs uppercase tracking-wider font-bold text-charcoal-800 mb-1">Contact Phone *</label>
                            <input type="tel" name="customer_phone" required value="<?= htmlspecialchars($_POST['customer_phone'] ?? '') ?>" placeholder="+91 98765 43210" class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blush-500">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs uppercase tracking-wider font-bold text-charcoal-800 mb-1">Address / Landmark *</label>
                            <input type="text" name="shipping_address" required value="<?= htmlspecialchars($_POST['shipping_address'] ?? '') ?>" placeholder="Flat 402, Shivam Residency, MG Road" class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blush-500">
                        </div>

                        <div>
                            <label class="block text-xs uppercase tracking-wider font-bold text-charcoal-800 mb-1">City / Region *</label>
                            <input type="text" name="city" required value="<?= htmlspecialchars($_POST['city'] ?? '') ?>" placeholder="New Delhi" class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blush-500">
                        </div>

                        <div>
                            <label class="block text-xs uppercase tracking-wider font-bold text-charcoal-800 mb-1">Postal PIN Code *</label>
                            <input type="text" name="postal_code" required value="<?= htmlspecialchars($_POST['postal_code'] ?? '') ?>" placeholder="110001" class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blush-500">
                        </div>
                    </div>
                </div>

                <!-- 2. Dynamic Payment Methods (Controlled by Admin Panel) -->
                <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#ebdccd] shadow-sm space-y-6">
                    <div class="flex items-center justify-between pb-3 border-b border-[#f2e6d9]">
                        <h3 class="font-serif text-xl font-bold text-charcoal-900 flex items-center gap-2">
                            <span class="w-7 h-7 rounded-full bg-blush-100 text-blush-800 flex items-center justify-center text-xs font-sans font-bold">2</span>
                            <span>Choose Payment Method</span>
                        </h3>
                        <span class="text-[10px] text-charcoal-800/50 bg-cream-100 px-2 py-0.5 rounded font-mono">
                            Admin Configured
                        </span>
                    </div>

                    <!-- Payment Method Option Selector Tabs -->
                    <div class="grid grid-cols-1 sm:grid-cols-<?= min(3, count($activeGateways)) ?> gap-3">
                        <?php foreach ($activeGateways as $idx => $gateway): ?>
                            <?php $isFirst = ($idx === 0); ?>
                            <label class="payment-card-tab cursor-pointer border-2 <?= $isFirst ? 'border-blush-600 bg-blush-50/60' : 'border-[#ebdccd] bg-white' ?> rounded-2xl p-4 flex flex-col items-center text-center transition-all hover:border-blush-400">
                                <input 
                                    type="radio" 
                                    name="payment_method_key" 
                                    value="<?= htmlspecialchars($gateway['gateway_key']) ?>" 
                                    <?= $isFirst ? 'checked' : '' ?> 
                                    class="hidden" 
                                    onchange="switchPaymentTab('<?= $gateway['gateway_key'] ?>')"
                                >
                                
                                <?php if ($gateway['gateway_key'] === 'upi_qr'): ?>
                                    <i data-lucide="qr-code" class="w-6 h-6 text-emerald-600 mb-2"></i>
                                <?php elseif ($gateway['gateway_key'] === 'card_gateway'): ?>
                                    <i data-lucide="credit-card" class="w-6 h-6 text-blush-600 mb-2"></i>
                                <?php elseif ($gateway['gateway_key'] === 'cod'): ?>
                                    <i data-lucide="banknote" class="w-6 h-6 text-charcoal-700 mb-2"></i>
                                <?php else: ?>
                                    <i data-lucide="landmark" class="w-6 h-6 text-gold-600 mb-2"></i>
                                <?php endif; ?>

                                <span class="text-xs font-bold text-charcoal-900"><?= htmlspecialchars($gateway['title']) ?></span>
                                <span class="text-[10px] text-charcoal-800/60 mt-0.5 line-clamp-1"><?= htmlspecialchars($gateway['subtitle']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <!-- DYNAMIC PANELS ACCORDING TO ADMIN'S SETTINGS -->
                    <?php foreach ($activeGateways as $idx => $gateway): ?>
                        <?php $isFirst = ($idx === 0); ?>
                        <div id="panel-<?= $gateway['gateway_key'] ?>" class="payment-method-panel <?= $isFirst ? '' : 'hidden' ?> pt-4 border-t border-[#f2e6d9] space-y-4">
                            
                            <!-- 1. UPI / QR SCAN PANEL -->
                            <?php if ($gateway['gateway_key'] === 'upi_qr'): ?>
                                <?php 
                                $upiId = !empty($gateway['upi_id']) ? $gateway['upi_id'] : 'shivvani.enterprises@okaxis';
                                $upiPayUri = "upi://pay?pa=" . urlencode($upiId) . "&pn=" . urlencode(STORE_NAME) . "&am=" . number_format($totalAmount, 2, '.', '') . "&cu=INR";
                                $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=" . urlencode($upiPayUri);
                                ?>
                                <div class="p-6 rounded-2xl bg-cream-50/80 border border-[#ebdccd] flex flex-col sm:flex-row items-center gap-6">
                                    <div class="bg-white p-3 rounded-2xl border border-cream-300 shadow-md text-center flex-shrink-0">
                                        <img src="<?= $qrApiUrl ?>" alt="UPI QR Code" class="w-40 h-40 mx-auto rounded-lg">
                                        <span class="text-[10px] uppercase tracking-wider font-bold text-emerald-800 block mt-2">
                                            Scan to Pay <?= formatPrice($totalAmount) ?>
                                        </span>
                                    </div>
                                    <div class="space-y-3 text-center sm:text-left">
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-900 text-xs font-bold">
                                            <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-700"></i>
                                            <span>Scan with GPay, PhonePe, Paytm or BHIM</span>
                                        </div>
                                        <p class="text-xs text-charcoal-800/80 leading-relaxed">
                                            <?= htmlspecialchars($gateway['instructions']) ?>
                                        </p>
                                        <div class="p-3 bg-white rounded-xl border border-[#ebdccd] flex items-center justify-between gap-3">
                                            <div>
                                                <span class="text-[9px] uppercase font-bold text-charcoal-800/50 block">Shiv Vani UPI VPA</span>
                                                <span class="font-mono text-xs font-bold text-charcoal-900"><?= htmlspecialchars($upiId) ?></span>
                                            </div>
                                            <button type="button" onclick="navigator.clipboard.writeText('<?= $upiId ?>'); alert('UPI ID copied to clipboard!');" class="text-xs font-bold text-blush-700 hover:underline">
                                                Copy
                                            </button>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold text-charcoal-800 mb-1">Transaction UTR / Reference ID (Optional):</label>
                                            <input type="text" name="transaction_ref" placeholder="e.g. 394810294821" class="w-full bg-white border border-[#ebdccd] rounded-xl px-3 py-2 text-xs focus:outline-none">
                                        </div>
                                    </div>
                                </div>

                            <!-- 2. CARD GATEWAY (PAYMENT API) PANEL -->
                            <?php elseif ($gateway['gateway_key'] === 'card_gateway'): ?>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between p-3 rounded-xl bg-cream-50 border border-[#ebdccd]">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
                                            <span class="text-xs font-bold text-charcoal-900">Secure Payment API Gateway</span>
                                        </div>
                                        <span class="font-mono text-[10px] text-charcoal-800/60 bg-white px-2 py-0.5 rounded border border-[#ebdccd]">
                                            API Key: <?= substr($gateway['api_key'] ?: 'rzp_live_shivvani928', 0, 14) ?>...
                                        </span>
                                    </div>

                                    <!-- Interactive Card Preview -->
                                    <div class="relative bg-gradient-to-tr from-charcoal-900 via-[#352525] to-charcoal-900 text-cream-50 p-6 rounded-2xl shadow-xl max-w-sm mx-auto">
                                        <div class="flex justify-between items-center mb-6">
                                            <span class="font-serif italic text-base text-gold-400">Shiv Vani Privilège</span>
                                            <img src="<?= STORE_LOGO ?>" alt="" class="w-6 h-6 rounded-full object-cover">
                                        </div>
                                        <div class="font-mono text-lg tracking-widest mb-4">•••• •••• •••• 5084</div>
                                        <div class="flex justify-between text-xs text-cream-100/70">
                                            <div>
                                                <span class="text-[9px] uppercase tracking-wider block text-cream-100/40">Holder</span>
                                                <span>POOJA SHARMA</span>
                                            </div>
                                            <div>
                                                <span class="text-[9px] uppercase tracking-wider block text-cream-100/40">Valid Thru</span>
                                                <span>09/29</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3 pt-2">
                                        <div class="col-span-2">
                                            <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Card Number</label>
                                            <input type="text" placeholder="Card number" value="4532 8492 0192 5084" class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-2.5 text-xs focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Expiry Date</label>
                                            <input type="text" placeholder="MM/YY" value="09/29" class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-2.5 text-xs focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">CVV</label>
                                            <input type="password" placeholder="CVV" value="789" maxlength="4" class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-2.5 text-xs focus:outline-none">
                                        </div>
                                    </div>
                                </div>

                            <!-- 3. CASH ON DELIVERY PANEL -->
                            <?php elseif ($gateway['gateway_key'] === 'cod'): ?>
                                <div class="p-5 rounded-2xl bg-cream-50 border border-[#ebdccd] space-y-2">
                                    <div class="flex items-center gap-2 text-charcoal-900 font-bold text-sm">
                                        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i>
                                        <span>Cash / UPI on Doorstep Handover</span>
                                    </div>
                                    <p class="text-xs text-charcoal-800/80 leading-relaxed">
                                        <?= htmlspecialchars($gateway['instructions'] ?: 'Pay in cash or scan delivery agent QR code upon candle box handover.') ?>
                                    </p>
                                </div>

                            <!-- 4. BANK TRANSFER PANEL -->
                            <?php else: ?>
                                <div class="p-5 rounded-2xl bg-cream-50 border border-[#ebdccd] space-y-2 text-xs">
                                    <p class="font-bold text-charcoal-900">Direct Bank Account Details:</p>
                                    <p class="text-charcoal-800/80 font-mono bg-white p-3 rounded-xl border border-[#ebdccd]">
                                        <?= nl2br(htmlspecialchars($gateway['instructions'])) ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                        </div>
                    <?php endforeach; ?>

                </div>

            </div>

            <!-- Right Column: Order Summary with 2 Prices breakdown -->
            <div class="lg:col-span-5 space-y-6">
                
                <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#ebdccd] shadow-sm space-y-6">
                    <h3 class="font-serif text-xl font-bold text-charcoal-900 pb-3 border-b border-[#f2e6d9]">Your Selection</h3>

                    <!-- Mini items list -->
                    <div class="space-y-4 max-h-72 overflow-y-auto pr-1">
                        <?php foreach ($cart as $item): ?>
                            <div class="flex items-center gap-3">
                                <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-12 h-12 rounded-xl object-cover border border-[#ebdccd] bg-cream-50">
                                <div class="flex-grow min-w-0">
                                    <h5 class="font-serif text-sm font-bold text-charcoal-900 truncate"><?= htmlspecialchars($item['name']) ?></h5>
                                    <div class="text-[11px] text-charcoal-800/60">
                                        <span>Qty: <?= $item['quantity'] ?> &bull; <?= formatPrice($item['price']) ?></span>
                                        <?php if ($item['discount_percent'] > 0): ?>
                                            <span class="line-through text-charcoal-800/40 ml-1"><?= formatPrice($item['original_price']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="font-serif font-bold text-sm text-charcoal-900">
                                    <?= formatPrice($item['price'] * $item['quantity']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Financial Summary -->
                    <div class="space-y-3 pt-4 border-t border-[#f2e6d9] text-sm">
                        <div class="flex justify-between text-charcoal-800/70">
                            <span>Subtotal</span>
                            <span class="font-bold text-charcoal-900"><?= formatPrice($subtotal) ?></span>
                        </div>

                        <?php if ($discountAmount > 0): ?>
                            <div class="flex justify-between text-emerald-800 font-semibold bg-emerald-50 p-2 rounded-xl border border-emerald-100">
                                <span>Promotional Discount</span>
                                <span>-<?= formatPrice($discountAmount) ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="flex justify-between text-charcoal-800/70">
                            <span>Shipping</span>
                            <?php if ($shippingFee == 0): ?>
                                <span class="text-emerald-800 font-bold uppercase text-xs">Complimentary</span>
                            <?php else: ?>
                                <span class="font-bold text-charcoal-900"><?= formatPrice($shippingFee) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="pt-4 border-t border-[#f2e6d9] flex justify-between items-baseline">
                            <span class="font-serif text-lg font-bold text-charcoal-900">Total Payable</span>
                            <span class="font-serif text-2xl font-bold text-blush-700"><?= formatPrice($totalAmount) ?></span>
                        </div>
                    </div>

                    <!-- Place Order CTA Button -->
                    <button type="submit" class="btn-luxury w-full bg-charcoal-900 hover:bg-blush-700 text-cream-50 font-bold py-4 px-6 rounded-2xl text-xs tracking-widest uppercase transition-all shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Confirm &amp; Place Order • <?= formatPrice($totalAmount) ?></span>
                    </button>

                    <p class="text-[11px] text-center text-charcoal-800/60 leading-relaxed">
                        Hand-packaged with sacred intention and delivered with <?= STORE_NAME ?>'s Luxury Guarantee.
                    </p>
                </div>

            </div>

        </div>
    </form>

</div>

<script>
    function switchPaymentTab(key) {
        document.querySelectorAll('.payment-card-tab').forEach(tab => {
            tab.classList.remove('border-blush-600', 'bg-blush-50/60');
            tab.classList.add('border-[#ebdccd]', 'bg-white');
        });

        document.querySelectorAll('.payment-method-panel').forEach(panel => {
            panel.classList.add('hidden');
        });

        const activePanel = document.getElementById('panel-' + key);
        if (activePanel) {
            activePanel.classList.remove('hidden');
        }

        const activeRadio = document.querySelector(`input[name="payment_method_key"][value="${key}"]`);
        if (activeRadio) {
            activeRadio.checked = true;
            const parentLabel = activeRadio.closest('.payment-card-tab');
            if (parentLabel) {
                parentLabel.classList.add('border-blush-600', 'bg-blush-50/60');
                parentLabel.classList.remove('border-[#ebdccd]', 'bg-white');
            }
        }
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
