<?php
$pageTitle = "Payment Gateways & API Configuration";
require_once __DIR__ . '/includes/admin_header.php';

$db = getDB();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_payments') {
    
    // 1. UPI QR
    $upiEnabled = isset($_POST['upi_enabled']) ? 1 : 0;
    $upiId = sanitize($_POST['upi_id'] ?? 'shivvani.enterprises@okaxis');
    $upiInstructions = sanitize($_POST['upi_instructions'] ?? '');
    
    // Check if custom QR image uploaded
    $uploadedQr = handleImageUpload('upi_qr_file');
    $upiQrUrl = $uploadedQr ?: trim($_POST['current_upi_qr'] ?? '');

    $stmt1 = $db->prepare("UPDATE payment_gateways SET 
        is_enabled = :enabled,
        upi_id = :upi_id,
        qr_image_url = :qr,
        instructions = :inst
        WHERE gateway_key = 'upi_qr'
    ");
    $stmt1->execute([
        ':enabled' => $upiEnabled,
        ':upi_id' => $upiId,
        ':qr' => $upiQrUrl,
        ':inst' => $upiInstructions,
    ]);

    // 2. Card Payment API
    $cardEnabled = isset($_POST['card_enabled']) ? 1 : 0;
    $cardApiKey = trim($_POST['card_api_key'] ?? '');
    $cardApiSecret = trim($_POST['card_api_secret'] ?? '');
    $cardMerchantId = trim($_POST['card_merchant_id'] ?? '');
    $cardInstructions = sanitize($_POST['card_instructions'] ?? '');

    $stmt2 = $db->prepare("UPDATE payment_gateways SET 
        is_enabled = :enabled,
        api_key = :api_key,
        api_secret = :api_secret,
        merchant_id = :mid,
        instructions = :inst
        WHERE gateway_key = 'card_gateway'
    ");
    $stmt2->execute([
        ':enabled' => $cardEnabled,
        ':api_key' => $cardApiKey,
        ':api_secret' => $cardApiSecret,
        ':mid' => $cardMerchantId,
        ':inst' => $cardInstructions,
    ]);

    // 3. COD
    $codEnabled = isset($_POST['cod_enabled']) ? 1 : 0;
    $codInstructions = sanitize($_POST['cod_instructions'] ?? '');

    $stmt3 = $db->prepare("UPDATE payment_gateways SET 
        is_enabled = :enabled,
        instructions = :inst
        WHERE gateway_key = 'cod'
    ");
    $stmt3->execute([
        ':enabled' => $codEnabled,
        ':inst' => $codInstructions,
    ]);

    // 4. Bank Transfer
    $bankEnabled = isset($_POST['bank_enabled']) ? 1 : 0;
    $bankInstructions = sanitize($_POST['bank_instructions'] ?? '');

    $stmt4 = $db->prepare("UPDATE payment_gateways SET 
        is_enabled = :enabled,
        instructions = :inst
        WHERE gateway_key = 'netbanking'
    ");
    $stmt4->execute([
        ':enabled' => $bankEnabled,
        ':inst' => $bankInstructions,
    ]);

    setFlash('success', 'Payment API & Gateway settings updated successfully! Changes are live on the customer checkout page.');
    header('Location: payment-settings.php');
    exit;
}

// Fetch all gateways
$allGateways = $db->query("SELECT * FROM payment_gateways")->fetchAll();
$gatewaysByKey = [];
foreach ($allGateways as $gw) {
    $gatewaysByKey[$gw['gateway_key']] = $gw;
}

$upi = $gatewaysByKey['upi_qr'] ?? [];
$card = $gatewaysByKey['card_gateway'] ?? [];
$cod = $gatewaysByKey['cod'] ?? [];
$bank = $gatewaysByKey['netbanking'] ?? [];
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 mb-8 border-b border-[#ebdccd] gap-4">
    <div>
        <span class="text-xs uppercase tracking-widest text-blush-600 font-bold block mb-1">Financial Controls</span>
        <h1 class="font-serif text-3xl font-bold text-charcoal-900">Payment API &amp; Gateway Settings</h1>
        <p class="text-xs text-charcoal-800/60 mt-0.5">
            Configure Scan to Pay UPI, Credit/Debit Card API credentials, and enable/disable customer payment methods.
        </p>
    </div>
    <div>
        <a href="../checkout.php" target="_blank" class="px-4 py-2.5 rounded-xl bg-white border border-[#ebdccd] text-xs font-bold text-charcoal-900 hover:bg-cream-100 flex items-center gap-1.5 shadow-sm">
            <i data-lucide="external-link" class="w-3.5 h-3.5 text-blush-600"></i>
            <span>Preview Checkout Page</span>
        </a>
    </div>
</div>

<form method="POST" action="payment-settings.php" enctype="multipart/form-data" class="space-y-8">
    <input type="hidden" name="action" value="save_payments">

    <!-- 1. UPI / QR SCAN TO PAY SETTINGS -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#ebdccd] shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-[#f2e6d9] gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center border border-emerald-100">
                    <i data-lucide="qr-code" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="font-serif text-xl font-bold text-charcoal-900">1. Scan &amp; Pay via UPI / QR Code</h3>
                    <p class="text-xs text-charcoal-800/60">Customers scan with GPay, PhonePe, Paytm, BHIM</p>
                </div>
            </div>

            <label class="flex items-center gap-2 cursor-pointer bg-[#FAF7F2] px-4 py-2 rounded-xl border border-[#ebdccd]">
                <input type="checkbox" name="upi_enabled" value="1" <?= (!empty($upi['is_enabled'])) ? 'checked' : '' ?> class="w-4 h-4 text-emerald-600 rounded">
                <span class="text-xs font-bold text-charcoal-900">Enable UPI Scan on Checkout</span>
            </label>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Business UPI ID (VPA) *</label>
                <input 
                    type="text" 
                    name="upi_id" 
                    value="<?= htmlspecialchars($upi['upi_id'] ?? 'shivvani.enterprises@okaxis') ?>" 
                    class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-2.5 text-xs font-mono font-bold focus:outline-none focus:border-blush-500"
                    placeholder="e.g. shivvani.enterprises@okaxis"
                >
                <span class="text-[10px] text-charcoal-800/60 mt-1 block">The QR code and direct pay link will dynamically generate from this VPA.</span>
            </div>

            <div>
                <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Custom Merchant QR Image (Optional)</label>
                <input 
                    type="file" 
                    name="upi_qr_file" 
                    accept="image/*"
                    class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-2 text-xs focus:outline-none"
                >
                <input type="hidden" name="current_upi_qr" value="<?= htmlspecialchars($upi['qr_image_url'] ?? '') ?>">
                <span class="text-[10px] text-charcoal-800/60 mt-1 block">Leave empty to use automatic high-res dynamic UPI QR generator.</span>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Instructions for Patron</label>
                <input 
                    type="text" 
                    name="upi_instructions" 
                    value="<?= htmlspecialchars($upi['instructions'] ?? 'Scan the QR code with any UPI app on your mobile phone to complete payment.') ?>"
                    class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-blush-500"
                >
            </div>
        </div>
    </div>

    <!-- 2. CARD PAYMENT GATEWAY & API CONFIGURATION -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#ebdccd] shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-[#f2e6d9] gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blush-50 text-blush-700 flex items-center justify-center border border-blush-100">
                    <i data-lucide="credit-card" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="font-serif text-xl font-bold text-charcoal-900">2. Credit / Debit Card Payment API</h3>
                    <p class="text-xs text-charcoal-800/60">Visa, MasterCard, RuPay, Amex &amp; NetBanking Gateway</p>
                </div>
            </div>

            <label class="flex items-center gap-2 cursor-pointer bg-[#FAF7F2] px-4 py-2 rounded-xl border border-[#ebdccd]">
                <input type="checkbox" name="card_enabled" value="1" <?= (!empty($card['is_enabled'])) ? 'checked' : '' ?> class="w-4 h-4 text-blush-600 rounded">
                <span class="text-xs font-bold text-charcoal-900">Enable Card Payment on Checkout</span>
            </label>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Payment API Publishable Key</label>
                <input 
                    type="text" 
                    name="card_api_key" 
                    value="<?= htmlspecialchars($card['api_key'] ?? 'rzp_live_shivvani928374') ?>" 
                    class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-2.5 text-xs font-mono focus:outline-none focus:border-blush-500"
                    placeholder="e.g. rzp_live_... or pk_live_..."
                >
            </div>

            <div>
                <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Payment API Secret Key</label>
                <input 
                    type="password" 
                    name="card_api_secret" 
                    value="<?= htmlspecialchars($card['api_secret'] ?? 'sec_shivvani_secret_key') ?>" 
                    class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-2.5 text-xs font-mono focus:outline-none focus:border-blush-500"
                    placeholder="e.g. sec_live_..."
                >
            </div>

            <div>
                <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Merchant Account ID</label>
                <input 
                    type="text" 
                    name="card_merchant_id" 
                    value="<?= htmlspecialchars($card['merchant_id'] ?? 'MERCHANT_SHIVVANI') ?>" 
                    class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-2.5 text-xs font-mono focus:outline-none focus:border-blush-500"
                    placeholder="e.g. SHIVVANI_MERC_01"
                >
            </div>

            <div class="sm:col-span-3">
                <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Security &amp; Encryption Instructions</label>
                <input 
                    type="text" 
                    name="card_instructions" 
                    value="<?= htmlspecialchars($card['instructions'] ?? 'Secure 256-bit encrypted card gateway transaction with instant OTP verification.') ?>"
                    class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-blush-500"
                >
            </div>
        </div>
    </div>

    <!-- 3. CASH ON DELIVERY (COD) -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#ebdccd] shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-[#f2e6d9] gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center border border-amber-100">
                    <i data-lucide="banknote" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="font-serif text-xl font-bold text-charcoal-900">3. Cash on Delivery (COD)</h3>
                    <p class="text-xs text-charcoal-800/60">Allow customers to pay upon package receipt</p>
                </div>
            </div>

            <label class="flex items-center gap-2 cursor-pointer bg-[#FAF7F2] px-4 py-2 rounded-xl border border-[#ebdccd]">
                <input type="checkbox" name="cod_enabled" value="1" <?= (!empty($cod['is_enabled'])) ? 'checked' : '' ?> class="w-4 h-4 text-amber-600 rounded">
                <span class="text-xs font-bold text-charcoal-900">Enable Cash on Delivery</span>
            </label>
        </div>

        <div>
            <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">COD Delivery Instructions</label>
            <input 
                type="text" 
                name="cod_instructions" 
                value="<?= htmlspecialchars($cod['instructions'] ?? 'Pay in cash or card upon delivery at your doorstep with tamper-proof packaging guarantee.') ?>"
                class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-blush-500"
            >
        </div>
    </div>

    <!-- 4. DIRECT BANK / NET BANKING -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#ebdccd] shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-[#f2e6d9] gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-700 flex items-center justify-center border border-purple-100">
                    <i data-lucide="landmark" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="font-serif text-xl font-bold text-charcoal-900">4. Direct Bank / NEFT / IMPS</h3>
                    <p class="text-xs text-charcoal-800/60">Manual bank wire transfer details</p>
                </div>
            </div>

            <label class="flex items-center gap-2 cursor-pointer bg-[#FAF7F2] px-4 py-2 rounded-xl border border-[#ebdccd]">
                <input type="checkbox" name="bank_enabled" value="1" <?= (!empty($bank['is_enabled'])) ? 'checked' : '' ?> class="w-4 h-4 text-purple-600 rounded">
                <span class="text-xs font-bold text-charcoal-900">Enable Bank Transfer Option</span>
            </label>
        </div>

        <div>
            <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Bank Account Credentials &amp; IFSC</label>
            <textarea 
                name="bank_instructions" 
                rows="2" 
                class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-2.5 text-xs font-mono focus:outline-none focus:border-blush-500"
            ><?= htmlspecialchars($bank['instructions'] ?? "Bank: HDFC Bank | Account: 50200084920192 | IFSC: HDFC0001234 | Name: SHIV VANI ENTERPRISES") ?></textarea>
        </div>
    </div>

    <!-- Save Button -->
    <div class="pt-4 flex justify-end gap-3">
        <button type="submit" class="btn-luxury px-8 py-3.5 rounded-2xl bg-charcoal-900 hover:bg-blush-700 text-cream-50 font-bold text-xs uppercase tracking-widest transition-all shadow-lg flex items-center gap-2">
            <i data-lucide="save" class="w-4 h-4"></i>
            <span>Save Payment Gateway Settings</span>
        </button>
    </div>

</form>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
