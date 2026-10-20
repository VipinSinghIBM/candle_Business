-- ========================================================
-- SHIV VANI ENTERPRISES - Database Schema & Seed Data
-- Premium Artisanal & Spiritual Aroma Candles
-- ========================================================

CREATE DATABASE IF NOT EXISTS `lumina_candles` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `lumina_candles`;

-- Drop existing tables in dependency order
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `payment_gateways`;
DROP TABLE IF EXISTS `coupons`;
DROP TABLE IF EXISTS `admins`;

-- --------------------------------------------------------
-- Table: categories (Dynamic Category Management)
-- --------------------------------------------------------
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `icon` VARCHAR(50) NOT NULL DEFAULT 'sparkles',
  `description` VARCHAR(255) DEFAULT '',
  `display_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: products
-- --------------------------------------------------------
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,            -- Regular M.R.P. Price
  `discount_percent` INT NOT NULL DEFAULT 0,   -- Discount Rate %
  `final_price` DECIMAL(10,2) NOT NULL,       -- Discounted Selling Price
  `short_desc` VARCHAR(500) NOT NULL,
  `description` TEXT NOT NULL,
  `scent_notes` VARCHAR(255) NOT NULL,
  `burn_time` VARCHAR(50) NOT NULL DEFAULT '55 Hours',
  `wax_type` VARCHAR(100) NOT NULL DEFAULT '100% Organic Coconut-Soy Wax',
  `weight` VARCHAR(50) NOT NULL DEFAULT '280g / 9.8 oz',
  `image_url` VARCHAR(500) NOT NULL,
  `stock` INT NOT NULL DEFAULT 50,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_bestseller` TINYINT(1) NOT NULL DEFAULT 0,
  `rating` DECIMAL(2,1) NOT NULL DEFAULT 4.9,
  `reviews_count` INT NOT NULL DEFAULT 48,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: payment_gateways (Admin Configurable Payment API & Methods)
-- --------------------------------------------------------
CREATE TABLE `payment_gateways` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `gateway_key` VARCHAR(50) NOT NULL UNIQUE,
  `title` VARCHAR(100) NOT NULL,
  `subtitle` VARCHAR(255) NOT NULL,
  `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `api_key` VARCHAR(255) DEFAULT '',
  `api_secret` VARCHAR(255) DEFAULT '',
  `merchant_id` VARCHAR(100) DEFAULT '',
  `upi_id` VARCHAR(100) DEFAULT 'shivvani.enterprises@okaxis',
  `qr_image_url` VARCHAR(500) DEFAULT '',
  `instructions` TEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: reviews (Customer Reviews with Text & YouTube Video Cards)
-- --------------------------------------------------------
CREATE TABLE `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `author_name` VARCHAR(100) NOT NULL,
  `author_city` VARCHAR(100) NOT NULL DEFAULT '',
  `rating` INT NOT NULL DEFAULT 5,
  `review_type` VARCHAR(20) NOT NULL DEFAULT 'text', -- 'text' or 'video'
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `video_url` VARCHAR(500) DEFAULT '',              -- YouTube Embed or Watch URL
  `product_name` VARCHAR(150) NOT NULL DEFAULT '',
  `is_verified` TINYINT(1) NOT NULL DEFAULT 1,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: orders
-- --------------------------------------------------------
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_code` VARCHAR(50) NOT NULL UNIQUE,
  `customer_name` VARCHAR(255) NOT NULL,
  `customer_email` VARCHAR(255) NOT NULL,
  `customer_phone` VARCHAR(50) NOT NULL,
  `shipping_address` TEXT NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `postal_code` VARCHAR(50) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `shipping_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL,
  `payment_status` VARCHAR(50) NOT NULL DEFAULT 'Paid',
  `order_status` VARCHAR(50) NOT NULL DEFAULT 'Processing',
  `transaction_ref` VARCHAR(100) DEFAULT '',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: order_items
-- --------------------------------------------------------
CREATE TABLE `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `product_name` VARCHAR(255) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `quantity` INT NOT NULL,
  `total` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: coupons
-- --------------------------------------------------------
CREATE TABLE `coupons` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `discount_percent` INT NOT NULL DEFAULT 10,
  `min_order` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: admins
-- --------------------------------------------------------
CREATE TABLE `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `role` VARCHAR(50) NOT NULL DEFAULT 'Managing Director',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Seed Data: Admin (admin / admin123)
-- --------------------------------------------------------
INSERT INTO `admins` (`id`, `username`, `password`, `name`, `role`) VALUES
(1, 'admin', '$2y$10$pLwJbB3Nlq4a/d62jPcvY.T81xZ9xGj23X5QxWl0C/eM93l36Oq.W', 'Shiv Vani Administrator', 'Founder & Managing Director');

-- --------------------------------------------------------
-- Seed Data: Categories (Dynamic)
-- --------------------------------------------------------
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `display_order`, `is_active`) VALUES
(1, 'Spiritual & Temple Bloom', 'spiritual-temple-bloom', 'sparkles', 'Sacred Sandalwood, Mogra, Kasturi & Divine Incense', 1, 1),
(2, 'Floral & Romance', 'floral-romance', 'flower-2', 'Velvet Rose, Pink Peony, White Lilac & Dewy Petals', 2, 1),
(3, 'Sweet & Gourmand', 'sweet-gourmand', 'cookie', 'Madagascar Vanilla, Golden Honey & Warm Cashmere', 3, 1),
(4, 'Woody & Earthy', 'woody-earthy', 'trees', 'Australian Sandalwood, Amber Resin & Sacred Oud', 4, 1),
(5, 'Fresh & Citrus', 'fresh-citrus', 'sun', 'Calabrian Bergamot, Mimosa & Rain Dew Herbs', 5, 1);

-- --------------------------------------------------------
-- Seed Data: Payment Gateways (Admin Controllable)
-- --------------------------------------------------------
INSERT INTO `payment_gateways` (`id`, `gateway_key`, `title`, `subtitle`, `is_enabled`, `api_key`, `api_secret`, `merchant_id`, `upi_id`, `qr_image_url`, `instructions`) VALUES
(1, 'upi_qr', 'UPI / QR Code Scan', 'Google Pay, PhonePe, Paytm, BHIM or any UPI App', 1, '', '', 'SHIVVANI01', 'shivvani.enterprises@okaxis', '', 'Scan the dynamic QR code with any UPI app on your phone to complete instant payment.'),
(2, 'card_gateway', 'Credit / Debit Card (API)', 'Visa, MasterCard, RuPay, Amex & NetBanking', 1, 'rzp_live_shivvani928374', 'sec_shivvani_secret_key', 'MERCHANT_SHIVVANI', '', '', 'Secure 256-bit encrypted card gateway transaction with instant OTP verification.'),
(3, 'cod', 'Cash on Delivery (COD)', 'Pay cash or card when your candle arrives', 1, '', '', '', '', '', 'Pay upon delivery at your doorstep with tamper-proof packaging guarantee.'),
(4, 'netbanking', 'Direct Bank Transfer (NEFT/IMPS)', 'Direct transfer to Shiv Vani Enterprises account', 0, '', '', 'HDFC0001234', '', '', 'Account: 50200084920192 | IFSC: HDFC0001234 | Shiv Vani Enterprises');

-- --------------------------------------------------------
-- Seed Data: Products (with 2 Prices: Regular & Discounted)
-- --------------------------------------------------------
INSERT INTO `products` (`id`, `name`, `slug`, `category`, `price`, `discount_percent`, `final_price`, `short_desc`, `description`, `scent_notes`, `burn_time`, `wax_type`, `weight`, `image_url`, `stock`, `is_featured`, `is_bestseller`, `rating`, `reviews_count`) VALUES
(
  1,
  'Kashi Chandan & Royal Mogra',
  'kashi-chandan-royal-mogra',
  'Spiritual & Temple Bloom',
  899.00,
  25,
  674.25,
  'Sacred temple sandalwood infused with midnight-blooming Indian mogra and holy camphor.',
  'Handcrafted by Shiv Vani Enterprises. Inspired by the evening aartis of Varanasi and serene temple gardens. Poured with pure natural coconut-soy wax, pure Mysore sandalwood oil, and crushed royal mogra petals to fill your sacred space with peace and positive aura.',
  'Mysore Sandalwood • Royal Mogra • Golden Camphor',
  '65 Hours',
  '100% Organic Coconut-Soy Wax',
  '300g / 10.5 oz',
  'https://images.unsplash.com/photo-1602874801007-bd458bb1b8b8?auto=format&fit=crop&w=800&q=80',
  50,
  1,
  1,
  5.0,
  142
),
(
  2,
  'Velvet Rose & Peony Garland',
  'velvet-rose-peony-garland',
  'Floral & Romance',
  799.00,
  20,
  639.20,
  'Soft dewy garden roses layered with blushing peony blossoms and silk amber.',
  'An opulent tribute to romantic bloom. Hand-poured in a custom frosted rose glass vessel by Shiv Vani Enterprises. Emits a lingering, elegant fragrance throw that transforms your living room or bedroom into a blooming rose garden.',
  'Kashmiri Rose • Pink Peony • Spun Silk Amber',
  '60 Hours',
  'Natural Soy Wax with Cotton Wick',
  '280g / 9.8 oz',
  'https://images.unsplash.com/photo-1603006905003-be475563bc59?auto=format&fit=crop&w=800&q=80',
  45,
  1,
  1,
  4.9,
  98
),
(
  3,
  'Madagascar Vanilla & Warm Cashmere',
  'madagascar-vanilla-warm-cashmere',
  'Sweet & Gourmand',
  749.00,
  15,
  636.65,
  'Decadent vanilla bean folded into roasted almond honey and golden cashmere warmth.',
  'Indulge in sweet, comforting tranquility. Formulated by Shiv Vani Enterprises with organic plant wax and crackling wood wick that evokes a warm fireside glow on cozy evenings.',
  'Bourbon Vanilla • Spun Sugar • Toasted Honey',
  '55 Hours',
  'Organic Soy Wax with Wood Wick',
  '280g / 9.8 oz',
  'https://images.unsplash.com/photo-1596461404969-9ae70f2830c1?auto=format&fit=crop&w=800&q=80',
  60,
  1,
  1,
  5.0,
  116
),
(
  4,
  'Royal Oud & Smoked Frankincense',
  'royal-oud-smoked-frankincense',
  'Woody & Earthy',
  1099.00,
  30,
  769.30,
  'Resinous royal agarwood paired with sacred frankincense and dark leather amber.',
  'A rich sanctuary in a jar. Designed for deep meditation and luxurious interior aroma. The warm resinous oud grounds the senses while smoked cedarwood cleanses the ambient aura.',
  'Sacred Agarwood • Frankincense • Cedar Resin',
  '70 Hours',
  'Eco-Friendly Soy Wax with Dual Wicks',
  '340g / 12 oz',
  'https://images.unsplash.com/photo-1508746829417-e6f548d8d6ed?auto=format&fit=crop&w=800&q=80',
  35,
  1,
  0,
  4.9,
  82
),
(
  5,
  'Calabrian Bergamot & Sunlit Citrus',
  'calabrian-bergamot-sunlit-citrus',
  'Fresh & Citrus',
  699.00,
  15,
  594.15,
  'Uplifting Italian bergamot zest dancing over sunny yellow mimosa and sweet tea.',
  'Bright, energizing, and refreshing. Crafted by Shiv Vani Enterprises to revitalize your work desk and morning meditation corners with sunshine-soaked botanical notes.',
  'Calabrian Bergamot • Golden Mimosa • Crisp Lime',
  '55 Hours',
  'Clean Burning Coconut Wax',
  '260g / 9.1 oz',
  'https://images.unsplash.com/photo-1572726729437-3732efed3f8a?auto=format&fit=crop&w=800&q=80',
  40,
  1,
  0,
  4.8,
  64
),
(
  6,
  'Lavender Haze & White Lotus',
  'lavender-haze-white-lotus',
  'Floral & Romance',
  749.00,
  10,
  674.10,
  'Tranquil French lavender crushed with sacred white lotus petals and soothing mint.',
  'Engineered for deep sleep, peaceful bedtime reading, and stress relief. Made with unbleached organic wicks and therapeutic-grade lavender essential oils.',
  'Provence Lavender • Sacred White Lotus • Spearmint',
  '50 Hours',
  '100% Coconut-Soy Wax',
  '270g / 9.5 oz',
  'https://images.unsplash.com/photo-1543857778-c4a1a3e0b2eb?auto=format&fit=crop&w=800&q=80',
  50,
  0,
  1,
  4.9,
  73
);

-- --------------------------------------------------------
-- Seed Data: Customer Reviews (Text & YouTube Video Reviews)
-- --------------------------------------------------------
INSERT INTO `reviews` (`id`, `author_name`, `author_city`, `rating`, `review_type`, `title`, `content`, `video_url`, `product_name`, `is_verified`, `is_featured`) VALUES
(
  1,
  'Pooja Sharma',
  'New Delhi',
  5,
  'video',
  'Watch: Unboxing & First Burn of Kashi Chandan!',
  'The packaging from Shiv Vani Enterprises is top-notch! The fragrance of sandalwood and mogra instantly filled our entire prayer room without giving any smoke. Here is my live video review and burning experience.',
  'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
  'Kashi Chandan & Royal Mogra',
  1,
  1
),
(
  2,
  'Ananya Deshmukh',
  'Mumbai',
  5,
  'text',
  'Pure luxury! The rose fragrance is absolutely divine',
  'I ordered the Velvet Rose candle for our living room. You can clearly feel the two prices: regular price ₹799 vs discounted ₹639 was an absolute steal. The aesthetic jar with the Shiv Vani gold logo looks iconic on our marble coffee table!',
  '',
  'Velvet Rose & Peony Garland',
  1,
  1
),
(
  3,
  'Rohan Singhania',
  'Bangalore',
  5,
  'video',
  'Watch: Aesthetic Night Desk Ambiance & Crackle Wick',
  'The wooden wick crackles like a gentle fireplace. Clean burn, 100% natural wax, and no tunneling. Watch the video to see how warm and golden the flame looks while working at night.',
  'https://www.youtube-nocookie.com/embed/jfKfPfyJRdk',
  'Madagascar Vanilla & Warm Cashmere',
  1,
  1
),
(
  4,
  'Dr. Meenakshi Sundaram',
  'Chennai',
  5,
  'text',
  'Authentic Temple Scent - Highly Recommended!',
  'Being someone who is sensitive to artificial scents, I am so grateful to Shiv Vani Enterprises. These candles use authentic botanical oils. The scan to pay QR code option at checkout was so fast and smooth!',
  '',
  'Kashi Chandan & Royal Mogra',
  1,
  1
),
(
  5,
  'Vikram Malhotra',
  'Jaipur',
  5,
  'video',
  'Watch: Royal Oud Luxury Vessel & Scent Throw Test',
  'The scent throw of Royal Oud is phenomenal. Even when unlit, the jar smells rich and royal. Watch my video showcase of the matte black vessel and velvet packaging.',
  'https://www.youtube-nocookie.com/embed/9bZkp7q19f0',
  'Royal Oud & Smoked Frankincense',
  1,
  1
),
(
  6,
  'Shalini Gupta',
  'Kolkata',
  5,
  'text',
  'Delivered in 2 days with beautiful matches & note',
  'Shiv Vani Enterprises provides exceptional customer service. I used the coupon code and paid via UPI Scan. The candles burn completely evenly right down to the base. 10/10 satisfaction!',
  '',
  'Lavender Haze & White Lotus',
  1,
  1
);

-- --------------------------------------------------------
-- Seed Data: Coupons
-- --------------------------------------------------------
INSERT INTO `coupons` (`code`, `discount_percent`, `min_order`, `is_active`) VALUES
('SHIVVANI15', 15, 500.00, 1),
('AURA20', 20, 1000.00, 1),
('WELCOME10', 10, 0.00, 1);

-- --------------------------------------------------------
-- Seed Data: Sample Orders
-- --------------------------------------------------------
INSERT INTO `orders` (`order_code`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `city`, `postal_code`, `subtotal`, `discount_amount`, `shipping_fee`, `total_amount`, `payment_method`, `payment_status`, `order_status`, `transaction_ref`) VALUES
('SV-9081', 'Pooja Sharma', 'pooja.s@example.com', '+91 98765 43210', 'Plot 42, Vasant Vihar', 'New Delhi', '110057', 1313.45, 197.02, 0.00, 1116.43, 'UPI / QR Code Scan', 'Paid', 'Delivered', 'UPI/294819028'),
('SV-9082', 'Vikram Malhotra', 'vikram.m@example.com', '+91 98111 22334', '18 Civil Lines', 'Jaipur', '302006', 769.30, 0.00, 0.00, 769.30, 'Credit / Debit Card (API)', 'Paid', 'Shipped', 'RZP_PAY_92841'),
('SV-9083', 'Ananya Deshmukh', 'ananya.d@example.com', '+91 99200 88776', 'B-12, Marine Drive Tower', 'Mumbai', '400020', 639.20, 0.00, 0.00, 639.20, 'Cash on Delivery (COD)', 'Pending', 'Processing', '');

INSERT INTO `order_items` (`order_id`, `product_id`, `product_name`, `price`, `quantity`, `total`) VALUES
(1, 1, 'Kashi Chandan & Royal Mogra', 674.25, 1, 674.25),
(1, 2, 'Velvet Rose & Peony Garland', 639.20, 1, 639.20),
(2, 4, 'Royal Oud & Smoked Frankincense', 769.30, 1, 769.30),
(3, 2, 'Velvet Rose & Peony Garland', 639.20, 1, 639.20);
