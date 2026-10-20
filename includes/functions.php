<?php
/**
 * SHIV VANI ENTERPRISES - Core Helper Functions & Handlers
 */

require_once __DIR__ . '/../config/db.php';

function getDB(): PDO {
    return Database::getConnection();
}

function sanitize(string $data): string {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function formatPrice(float $amount): string {
    return STORE_CURRENCY . ' ' . number_format($amount, 2);
}

function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'item-' . time() : $text;
}

// ----------------------------------------------------
// Categories Helper
// ----------------------------------------------------

function getCategories(bool $activeOnly = true): array {
    $db = getDB();
    $sql = "SELECT * FROM categories";
    if ($activeOnly) {
        $sql .= " WHERE is_active = 1";
    }
    $sql .= " ORDER BY display_order ASC, name ASC";
    return $db->query($sql)->fetchAll();
}

// ----------------------------------------------------
// Payment Gateway Settings Helper
// ----------------------------------------------------

function getPaymentGateways(bool $onlyEnabled = false): array {
    $db = getDB();
    $sql = "SELECT * FROM payment_gateways";
    if ($onlyEnabled) {
        $sql .= " WHERE is_enabled = 1";
    }
    $sql .= " ORDER BY id ASC";
    return $db->query($sql)->fetchAll();
}

// ----------------------------------------------------
// Reviews Helper
// ----------------------------------------------------

function getReviews(string $type = 'all', bool $featuredOnly = false): array {
    $db = getDB();
    $sql = "SELECT * FROM reviews WHERE 1=1";
    $params = [];

    if ($type === 'text' || $type === 'video') {
        $sql .= " AND review_type = ?";
        $params[] = $type;
    }

    if ($featuredOnly) {
        $sql .= " AND is_featured = 1";
    }

    $sql .= " ORDER BY id DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// ----------------------------------------------------
// Secure Direct Image Upload Helper
// ----------------------------------------------------

function handleImageUpload(string $inputName = 'image_file'): ?string {
    if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
        return null; // No file uploaded or upload error
    }

    $file = $_FILES[$inputName];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowedMimes) || !in_array($extension, $allowedExtensions)) {
        return null;
    }

    // Limit size to 8MB
    if ($file['size'] > 8 * 1024 * 1024) {
        return null;
    }

    $uploadDir = dirname(__DIR__) . '/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $newFilename = 'shivvani_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $targetPath = $uploadDir . '/' . $newFilename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return 'uploads/' . $newFilename;
    }

    return null;
}

// ----------------------------------------------------
// Cart Management Helpers
// ----------------------------------------------------

function getCart(): array {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    return $_SESSION['cart'];
}

function getCartCount(): int {
    $cart = getCart();
    $totalCount = 0;
    foreach ($cart as $item) {
        $totalCount += (int)($item['quantity'] ?? 0);
    }
    return $totalCount;
}

function addToCart(int $productId, int $quantity = 1): bool {
    if ($quantity <= 0) $quantity = 1;

    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        return false;
    }

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'slug' => $product['slug'],
            'price' => (float)$product['final_price'],
            'original_price' => (float)$product['price'],
            'discount_percent' => (int)$product['discount_percent'],
            'image_url' => $product['image_url'],
            'category' => $product['category'],
            'scent_notes' => $product['scent_notes'],
            'quantity' => $quantity,
        ];
    }

    return true;
}

function updateCartQuantity(int $productId, int $quantity): void {
    if (isset($_SESSION['cart'][$productId])) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$productId]);
        } else {
            $_SESSION['cart'][$productId]['quantity'] = $quantity;
        }
    }
}

function removeFromCart(int $productId): void {
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
}

function clearCart(): void {
    $_SESSION['cart'] = [];
    unset($_SESSION['coupon']);
}

function calculateOrderTotals(): array {
    $cart = getCart();
    $subtotal = 0.00;

    foreach ($cart as $item) {
        $subtotal += ($item['price'] * $item['quantity']);
    }

    $discountAmount = 0.00;
    $appliedCoupon = $_SESSION['coupon'] ?? null;

    if ($appliedCoupon && $subtotal >= ($appliedCoupon['min_order'] ?? 0)) {
        $discountAmount = ($subtotal * ($appliedCoupon['discount_percent'] / 100));
    }

    $afterDiscount = max(0, $subtotal - $discountAmount);
    $shippingFee = ($subtotal >= FREE_SHIPPING_THRESHOLD || $subtotal === 0.00) ? 0.00 : FLAT_SHIPPING_FEE;
    $totalAmount = $afterDiscount + $shippingFee;

    return [
        'subtotal' => $subtotal,
        'discount_amount' => $discountAmount,
        'applied_coupon' => $appliedCoupon,
        'shipping_fee' => $shippingFee,
        'total_amount' => $totalAmount,
    ];
}

// ----------------------------------------------------
// Flash Messages
// ----------------------------------------------------

function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ----------------------------------------------------
// Admin Helpers
// ----------------------------------------------------

function isAdminLoggedIn(): bool {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireAdminAuth(): void {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}
