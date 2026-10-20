<?php
/**
 * SHIV VANI ENTERPRISES - Database Connection & Configuration Handler
 * Dual-driver engine: native MySQL (PDO) with zero-setup SQLite fallback.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials (Standard XAMPP / Live Server)
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'lumina_candles');
define('DB_USER', 'root');
define('DB_PASS', '');

// Brand Configurations
define('STORE_NAME', 'SHIV VANI ENTERPRISES');
define('STORE_TAGLINE', 'Luxury Artisanal & Spiritual Aroma Candles');
define('STORE_CURRENCY', '₹');
define('STORE_LOGO', 'assets/images/logo.jpg');
define('FREE_SHIPPING_THRESHOLD', 500.00);
define('FLAT_SHIPPING_FEE', 50.00);

class Database {
    private static ?PDO $connection = null;
    private static string $driverUsed = 'mysql';

    public static function getConnection(): PDO {
        if (self::$connection !== null) {
            return self::$connection;
        }

        // 1. Try MySQL Connection First
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 2,
            ];

            // Attempt to create database if not present
            $rootDsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
            $rootPdo = new PDO($rootDsn, DB_USER, DB_PASS, $options);
            $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

            self::$connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            self::$driverUsed = 'mysql';
            self::ensureTables(self::$connection, 'mysql');
            return self::$connection;

        } catch (Throwable $e) {
            // 2. Seamless Local SQLite Fallback
            try {
                $sqlitePath = dirname(__DIR__) . '/database.sqlite';
                $sqliteDsn = "sqlite:" . $sqlitePath;
                self::$connection = new PDO($sqliteDsn, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                self::$driverUsed = 'sqlite';
                self::ensureTables(self::$connection, 'sqlite');
                return self::$connection;
            } catch (Throwable $sqliteErr) {
                die("Database Initialization Error: " . $sqliteErr->getMessage());
            }
        }
    }

    public static function getDriver(): string {
        return self::$driverUsed;
    }

    private static function ensureTables(PDO $pdo, string $driver): void {
        if ($driver === 'mysql') {
            $stmt = $pdo->query("SHOW TABLES LIKE 'categories'");
            if ($stmt->rowCount() === 0) {
                self::seedDatabase($pdo, 'mysql');
            }
        } else {
            $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='categories'");
            if (!$stmt->fetch()) {
                self::seedDatabase($pdo, 'sqlite');
            }
        }
    }

    private static function seedDatabase(PDO $pdo, string $driver): void {
        if ($driver === 'sqlite') {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS categories (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL UNIQUE,
                    slug TEXT NOT NULL UNIQUE,
                    icon TEXT NOT NULL DEFAULT 'sparkles',
                    description TEXT DEFAULT '',
                    display_order INTEGER NOT NULL DEFAULT 0,
                    is_active INTEGER NOT NULL DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS products (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    slug TEXT NOT NULL,
                    category TEXT NOT NULL,
                    price REAL NOT NULL,
                    discount_percent INTEGER NOT NULL DEFAULT 0,
                    final_price REAL NOT NULL,
                    short_desc TEXT NOT NULL,
                    description TEXT NOT NULL,
                    scent_notes TEXT NOT NULL,
                    burn_time TEXT NOT NULL DEFAULT '55 Hours',
                    wax_type TEXT NOT NULL DEFAULT '100% Organic Coconut-Soy Wax',
                    weight TEXT NOT NULL DEFAULT '280g / 9.8 oz',
                    image_url TEXT NOT NULL,
                    stock INTEGER NOT NULL DEFAULT 50,
                    is_featured INTEGER NOT NULL DEFAULT 0,
                    is_bestseller INTEGER NOT NULL DEFAULT 0,
                    rating REAL NOT NULL DEFAULT 4.9,
                    reviews_count INTEGER NOT NULL DEFAULT 48,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS payment_gateways (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    gateway_key TEXT NOT NULL UNIQUE,
                    title TEXT NOT NULL,
                    subtitle TEXT NOT NULL,
                    is_enabled INTEGER NOT NULL DEFAULT 1,
                    api_key TEXT DEFAULT '',
                    api_secret TEXT DEFAULT '',
                    merchant_id TEXT DEFAULT '',
                    upi_id TEXT DEFAULT 'shivvani.enterprises@okaxis',
                    qr_image_url TEXT DEFAULT '',
                    instructions TEXT DEFAULT '',
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS reviews (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    author_name TEXT NOT NULL,
                    author_city TEXT NOT NULL DEFAULT '',
                    rating INTEGER NOT NULL DEFAULT 5,
                    review_type TEXT NOT NULL DEFAULT 'text',
                    title TEXT NOT NULL,
                    content TEXT NOT NULL,
                    video_url TEXT DEFAULT '',
                    product_name TEXT NOT NULL DEFAULT '',
                    is_verified INTEGER NOT NULL DEFAULT 1,
                    is_featured INTEGER NOT NULL DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS orders (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    order_code TEXT NOT NULL UNIQUE,
                    customer_name TEXT NOT NULL,
                    customer_email TEXT NOT NULL,
                    customer_phone TEXT NOT NULL,
                    shipping_address TEXT NOT NULL,
                    city TEXT NOT NULL,
                    postal_code TEXT NOT NULL,
                    subtotal REAL NOT NULL,
                    discount_amount REAL NOT NULL DEFAULT 0.00,
                    shipping_fee REAL NOT NULL DEFAULT 0.00,
                    total_amount REAL NOT NULL,
                    payment_method TEXT NOT NULL,
                    payment_status TEXT NOT NULL DEFAULT 'Paid',
                    order_status TEXT NOT NULL DEFAULT 'Processing',
                    transaction_ref TEXT DEFAULT '',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS order_items (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    order_id INTEGER NOT NULL,
                    product_id INTEGER NOT NULL,
                    product_name TEXT NOT NULL,
                    price REAL NOT NULL,
                    quantity INTEGER NOT NULL,
                    total REAL NOT NULL,
                    FOREIGN KEY (order_id) REFERENCES orders(id)
                );

                CREATE TABLE IF NOT EXISTS coupons (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    code TEXT NOT NULL UNIQUE,
                    discount_percent INTEGER NOT NULL DEFAULT 10,
                    min_order REAL NOT NULL DEFAULT 0.00,
                    is_active INTEGER NOT NULL DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS admins (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT NOT NULL UNIQUE,
                    password TEXT NOT NULL,
                    name TEXT NOT NULL,
                    role TEXT NOT NULL DEFAULT 'Managing Director',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
            ");
        } else {
            $sqlFile = dirname(__DIR__) . '/database.sql';
            if (file_exists($sqlFile)) {
                $sql = file_get_contents($sqlFile);
                $pdo->exec($sql);
                return;
            }
        }

        // Insert Admin (admin / admin123)
        $hashedPass = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admins (username, password, name, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['admin', $hashedPass, 'Shiv Vani Administrator', 'Founder & Managing Director']);

        // Insert Initial Categories
        $categories = [
            ['Spiritual & Temple Bloom', 'spiritual-temple-bloom', 'sparkles', 'Sacred Sandalwood, Mogra, Kasturi & Divine Incense', 1],
            ['Floral & Romance', 'floral-romance', 'flower-2', 'Velvet Rose, Pink Peony, White Lilac & Dewy Petals', 2],
            ['Sweet & Gourmand', 'sweet-gourmand', 'cookie', 'Madagascar Vanilla, Golden Honey & Warm Cashmere', 3],
            ['Woody & Earthy', 'woody-earthy', 'trees', 'Australian Sandalwood, Amber Resin & Sacred Oud', 4],
            ['Fresh & Citrus', 'fresh-citrus', 'sun', 'Calabrian Bergamot, Mimosa & Rain Dew Herbs', 5],
        ];
        $insertCat = $pdo->prepare("INSERT INTO categories (name, slug, icon, description, display_order, is_active) VALUES (?, ?, ?, ?, ?, 1)");
        foreach ($categories as $cat) {
            $insertCat->execute($cat);
        }

        // Insert Payment Gateways (Admin Controllable)
        $gateways = [
            ['upi_qr', 'UPI / QR Code Scan', 'Google Pay, PhonePe, Paytm, BHIM or any UPI App', 1, '', '', 'SHIVVANI01', 'shivvani.enterprises@okaxis', '', 'Scan the dynamic QR code with any UPI app on your phone to complete instant payment.'],
            ['card_gateway', 'Credit / Debit Card (API)', 'Visa, MasterCard, RuPay, Amex & NetBanking', 1, 'rzp_live_shivvani928374', 'sec_shivvani_secret_key', 'MERCHANT_SHIVVANI', '', '', 'Secure 256-bit encrypted card gateway transaction with instant OTP verification.'],
            ['cod', 'Cash on Delivery (COD)', 'Pay cash or card when your candle arrives', 1, '', '', '', '', '', 'Pay upon delivery at your doorstep with tamper-proof packaging guarantee.'],
            ['netbanking', 'Direct Bank Transfer (NEFT/IMPS)', 'Direct transfer to Shiv Vani Enterprises account', 0, '', '', 'HDFC0001234', '', '', 'Account: 50200084920192 | IFSC: HDFC0001234 | Shiv Vani Enterprises'],
        ];
        $insertGate = $pdo->prepare("INSERT INTO payment_gateways (gateway_key, title, subtitle, is_enabled, api_key, api_secret, merchant_id, upi_id, qr_image_url, instructions) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($gateways as $gate) {
            $insertGate->execute($gate);
        }

        // Insert Coupons
        $pdo->exec("INSERT INTO coupons (code, discount_percent, min_order, is_active) VALUES 
            ('SHIVVANI15', 15, 500.00, 1),
            ('AURA20', 20, 1000.00, 1),
            ('WELCOME10', 10, 0.00, 1)
        ");

        // Insert Shiv Vani Candle Collection
        $candles = [
            [
                'name' => 'Kashi Chandan & Royal Mogra',
                'slug' => 'kashi-chandan-royal-mogra',
                'category' => 'Spiritual & Temple Bloom',
                'price' => 899.00,
                'discount_percent' => 25,
                'final_price' => 674.25,
                'short_desc' => 'Sacred temple sandalwood infused with midnight-blooming Indian mogra and holy camphor.',
                'description' => 'Handcrafted by Shiv Vani Enterprises. Inspired by the evening aartis of Varanasi and serene temple gardens. Poured with pure natural coconut-soy wax, pure Mysore sandalwood oil, and crushed royal mogra petals to fill your sacred space with peace and positive aura.',
                'scent_notes' => 'Mysore Sandalwood • Royal Mogra • Golden Camphor',
                'burn_time' => '65 Hours',
                'wax_type' => '100% Organic Coconut-Soy Wax',
                'weight' => '300g / 10.5 oz',
                'image_url' => 'https://images.unsplash.com/photo-1602874801007-bd458bb1b8b8?auto=format&fit=crop&w=800&q=80',
                'stock' => 50,
                'is_featured' => 1,
                'is_bestseller' => 1,
                'rating' => 5.0,
                'reviews_count' => 142
            ],
            [
                'name' => 'Velvet Rose & Peony Garland',
                'slug' => 'velvet-rose-peony-garland',
                'category' => 'Floral & Romance',
                'price' => 799.00,
                'discount_percent' => 20,
                'final_price' => 639.20,
                'short_desc' => 'Soft dewy garden roses layered with blushing peony blossoms and silk amber.',
                'description' => 'An opulent tribute to romantic bloom. Hand-poured in a custom frosted rose glass vessel by Shiv Vani Enterprises. Emits a lingering, elegant fragrance throw that transforms your room into a blooming rose garden.',
                'scent_notes' => 'Kashmiri Rose • Pink Peony • Spun Silk Amber',
                'burn_time' => '60 Hours',
                'wax_type' => 'Natural Soy Wax with Cotton Wick',
                'weight' => '280g / 9.8 oz',
                'image_url' => 'https://images.unsplash.com/photo-1603006905003-be475563bc59?auto=format&fit=crop&w=800&q=80',
                'stock' => 45,
                'is_featured' => 1,
                'is_bestseller' => 1,
                'rating' => 4.9,
                'reviews_count' => 98
            ],
            [
                'name' => 'Madagascar Vanilla & Warm Cashmere',
                'slug' => 'madagascar-vanilla-warm-cashmere',
                'category' => 'Sweet & Gourmand',
                'price' => 749.00,
                'discount_percent' => 15,
                'final_price' => 636.65,
                'short_desc' => 'Decadent vanilla bean folded into roasted almond honey and golden cashmere warmth.',
                'description' => 'Indulge in sweet, comforting tranquility. Formulated by Shiv Vani Enterprises with organic plant wax and crackling wood wick that evokes a warm fireside glow on cozy evenings.',
                'scent_notes' => 'Bourbon Vanilla • Spun Sugar • Toasted Honey',
                'burn_time' => '55 Hours',
                'wax_type' => 'Organic Soy Wax with Wood Wick',
                'weight' => '280g / 9.8 oz',
                'image_url' => 'https://images.unsplash.com/photo-1596461404969-9ae70f2830c1?auto=format&fit=crop&w=800&q=80',
                'stock' => 60,
                'is_featured' => 1,
                'is_bestseller' => 1,
                'rating' => 5.0,
                'reviews_count' => 116
            ],
            [
                'name' => 'Royal Oud & Smoked Frankincense',
                'slug' => 'royal-oud-smoked-frankincense',
                'category' => 'Woody & Earthy',
                'price' => 1099.00,
                'discount_percent' => 30,
                'final_price' => 769.30,
                'short_desc' => 'Resinous royal agarwood paired with sacred frankincense and dark leather amber.',
                'description' => 'A rich sanctuary in a jar. Designed for deep meditation and luxurious interior aroma. The warm resinous oud grounds the senses while smoked cedarwood cleanses the ambient aura.',
                'scent_notes' => 'Sacred Agarwood • Frankincense • Cedar Resin',
                'burn_time' => '70 Hours',
                'wax_type' => 'Eco-Friendly Soy Wax with Dual Wicks',
                'weight' => '340g / 12 oz',
                'image_url' => 'https://images.unsplash.com/photo-1508746829417-e6f548d8d6ed?auto=format&fit=crop&w=800&q=80',
                'stock' => 35,
                'is_featured' => 1,
                'is_bestseller' => 0,
                'rating' => 4.9,
                'reviews_count' => 82
            ],
            [
                'name' => 'Calabrian Bergamot & Sunlit Citrus',
                'slug' => 'calabrian-bergamot-sunlit-citrus',
                'category' => 'Fresh & Citrus',
                'price' => 699.00,
                'discount_percent' => 15,
                'final_price' => 594.15,
                'short_desc' => 'Uplifting Italian bergamot zest dancing over sunny yellow mimosa and sweet tea.',
                'description' => 'Bright, energizing, and refreshing. Crafted by Shiv Vani Enterprises to revitalize your work desk and morning meditation corners with sunshine-soaked botanical notes.',
                'scent_notes' => 'Calabrian Bergamot • Golden Mimosa • Crisp Lime',
                'burn_time' => '55 Hours',
                'wax_type' => 'Clean Burning Coconut Wax',
                'weight' => '260g / 9.1 oz',
                'image_url' => 'https://images.unsplash.com/photo-1572726729437-3732efed3f8a?auto=format&fit=crop&w=800&q=80',
                'stock' => 40,
                'is_featured' => 1,
                'is_bestseller' => 0,
                'rating' => 4.8,
                'reviews_count' => 64
            ],
            [
                'name' => 'Lavender Haze & White Lotus',
                'slug' => 'lavender-haze-white-lotus',
                'category' => 'Floral & Romance',
                'price' => 749.00,
                'discount_percent' => 10,
                'final_price' => 674.10,
                'short_desc' => 'Tranquil French lavender crushed with sacred white lotus petals and soothing mint.',
                'description' => 'Engineered for deep sleep, peaceful bedtime reading, and stress relief. Made with unbleached organic wicks and therapeutic-grade lavender essential oils.',
                'scent_notes' => 'Provence Lavender • Sacred White Lotus • Spearmint',
                'burn_time' => '50 Hours',
                'wax_type' => '100% Coconut-Soy Wax',
                'weight' => '270g / 9.5 oz',
                'image_url' => 'https://images.unsplash.com/photo-1543857778-c4a1a3e0b2eb?auto=format&fit=crop&w=800&q=80',
                'stock' => 50,
                'is_featured' => 0,
                'is_bestseller' => 1,
                'rating' => 4.9,
                'reviews_count' => 73
            ]
        ];

        $insertProduct = $pdo->prepare("INSERT INTO products (
            name, slug, category, price, discount_percent, final_price, short_desc, description,
            scent_notes, burn_time, wax_type, weight, image_url, stock, is_featured, is_bestseller, rating, reviews_count
        ) VALUES (
            :name, :slug, :category, :price, :discount_percent, :final_price, :short_desc, :description,
            :scent_notes, :burn_time, :wax_type, :weight, :image_url, :stock, :is_featured, :is_bestseller, :rating, :reviews_count
        )");

        foreach ($candles as $candle) {
            $insertProduct->execute($candle);
        }

        // Insert Reviews (Text & YouTube Video Reviews)
        $reviews = [
            [
                'Pooja Sharma', 'New Delhi', 5, 'video',
                'Watch: Unboxing & First Burn of Kashi Chandan!',
                'The packaging from Shiv Vani Enterprises is top-notch! The fragrance of sandalwood and mogra instantly filled our entire prayer room without giving any smoke. Here is my live video review and burning experience.',
                'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
                'Kashi Chandan & Royal Mogra', 1, 1
            ],
            [
                'Ananya Deshmukh', 'Mumbai', 5, 'text',
                'Pure luxury! The rose fragrance is absolutely divine',
                'I ordered the Velvet Rose candle for our living room. You can clearly feel the two prices: regular price ₹799 vs discounted ₹639 was an absolute steal. The aesthetic jar with the Shiv Vani gold logo looks iconic on our marble coffee table!',
                '',
                'Velvet Rose & Peony Garland', 1, 1
            ],
            [
                'Rohan Singhania', 'Bangalore', 5, 'video',
                'Watch: Aesthetic Night Desk Ambiance & Crackle Wick',
                'The wooden wick crackles like a gentle fireplace. Clean burn, 100% natural wax, and no tunneling. Watch the video to see how warm and golden the flame looks while working at night.',
                'https://www.youtube-nocookie.com/embed/jfKfPfyJRdk',
                'Madagascar Vanilla & Warm Cashmere', 1, 1
            ],
            [
                'Dr. Meenakshi Sundaram', 'Chennai', 5, 'text',
                'Authentic Temple Scent - Highly Recommended!',
                'Being someone who is sensitive to artificial scents, I am so grateful to Shiv Vani Enterprises. These candles use authentic botanical oils. The scan to pay QR code option at checkout was so fast and smooth!',
                '',
                'Kashi Chandan & Royal Mogra', 1, 1
            ],
            [
                'Vikram Malhotra', 'Jaipur', 5, 'video',
                'Watch: Royal Oud Luxury Vessel & Scent Throw Test',
                'The scent throw of Royal Oud is phenomenal. Even when unlit, the jar smells rich and royal. Watch my video showcase of the matte black vessel and velvet packaging.',
                'https://www.youtube-nocookie.com/embed/9bZkp7q19f0',
                'Royal Oud & Smoked Frankincense', 1, 1
            ],
            [
                'Shalini Gupta', 'Kolkata', 5, 'text',
                'Delivered in 2 days with beautiful matches & note',
                'Shiv Vani Enterprises provides exceptional customer service. I used the coupon code and paid via UPI Scan. The candles burn completely evenly right down to the base. 10/10 satisfaction!',
                '',
                'Lavender Haze & White Lotus', 1, 1
            ]
        ];

        $insertReview = $pdo->prepare("INSERT INTO reviews (author_name, author_city, rating, review_type, title, content, video_url, product_name, is_verified, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($reviews as $rev) {
            $insertReview->execute($rev);
        }

        // Insert Sample Orders
        $pdo->exec("INSERT INTO orders (order_code, customer_name, customer_email, customer_phone, shipping_address, city, postal_code, subtotal, discount_amount, shipping_fee, total_amount, payment_method, payment_status, order_status, transaction_ref) VALUES
            ('SV-9081', 'Pooja Sharma', 'pooja.s@example.com', '+91 98765 43210', 'Plot 42, Vasant Vihar', 'New Delhi', '110057', 1313.45, 197.02, 0.00, 1116.43, 'UPI / QR Code Scan', 'Paid', 'Delivered', 'UPI/294819028'),
            ('SV-9082', 'Vikram Malhotra', 'vikram.m@example.com', '+91 98111 22334', '18 Civil Lines', 'Jaipur', '302006', 769.30, 0.00, 0.00, 769.30, 'Credit / Debit Card (API)', 'Paid', 'Shipped', 'RZP_PAY_92841'),
            ('SV-9083', 'Ananya Deshmukh', 'ananya.d@example.com', '+91 99200 88776', 'B-12, Marine Drive Tower', 'Mumbai', '400020', 639.20, 0.00, 0.00, 639.20, 'Cash on Delivery (COD)', 'Pending', 'Processing', '')
        ");

        $pdo->exec("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, total) VALUES
            (1, 1, 'Kashi Chandan & Royal Mogra', 674.25, 1, 674.25),
            (1, 2, 'Velvet Rose & Peony Garland', 639.20, 1, 639.20),
            (2, 4, 'Royal Oud & Smoked Frankincense', 769.30, 1, 769.30),
            (3, 2, 'Velvet Rose & Peony Garland', 639.20, 1, 639.20)
        ");
    }
}
