<?php
$pageTitle = "Manage Customer Reviews & Video Testimonials";
require_once __DIR__ . '/includes/admin_header.php';

$db = getDB();

// Handle Add Review
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_review') {
    $author = sanitize($_POST['author_name'] ?? '');
    $city = sanitize($_POST['author_city'] ?? '');
    $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
    $type = sanitize($_POST['review_type'] ?? 'text');
    $title = sanitize($_POST['title'] ?? '');
    $content = sanitize($_POST['content'] ?? '');
    $productName = sanitize($_POST['product_name'] ?? '');
    $videoUrl = trim($_POST['video_url'] ?? '');

    // Normalize YouTube URL to embed format if needed
    if ($type === 'video' && !empty($videoUrl)) {
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $videoUrl, $match)) {
            $videoUrl = 'https://www.youtube-nocookie.com/embed/' . $match[1];
        }
    }

    if (empty($author) || empty($content)) {
        setFlash('error', 'Author name and review content are required.');
    } else {
        $stmt = $db->prepare("INSERT INTO reviews (author_name, author_city, rating, review_type, title, content, video_url, product_name, is_verified, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 1)");
        $stmt->execute([$author, $city, $rating, $type, $title ?: 'Exceptional Sacred Candle Experience', $content, $videoUrl, $productName]);
        setFlash('success', 'Customer review published! It now appears on the live store reviews section.');
    }
    header('Location: reviews.php');
    exit;
}

// Handle Delete Review
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_review') {
    $revId = (int)$_POST['review_id'];
    $del = $db->prepare("DELETE FROM reviews WHERE id = ?");
    $del->execute([$revId]);
    setFlash('success', 'Review deleted from store.');
    header('Location: reviews.php');
    exit;
}

// Fetch all reviews
$reviews = $db->query("SELECT * FROM reviews ORDER BY id DESC")->fetchAll();
$products = $db->query("SELECT name FROM products ORDER BY name ASC")->fetchAll();
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 mb-8 border-b border-[#ebdccd] gap-4">
    <div>
        <span class="text-xs uppercase tracking-widest text-blush-600 font-bold block mb-1">Social Proof</span>
        <h1 class="font-serif text-3xl font-bold text-charcoal-900">Customer Satisfaction &amp; Video Reviews</h1>
        <p class="text-xs text-charcoal-800/60 mt-0.5">
            Add written testimonials or embed YouTube video experiences that appear in the Customer Reviews section.
        </p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
    
    <!-- Left Column: Add Review Form (5 Cols) -->
    <div class="lg:col-span-5 bg-white p-6 sm:p-8 rounded-3xl border border-[#ebdccd] shadow-sm space-y-6">
        <div class="pb-3 border-b border-[#f2e6d9]">
            <h3 class="font-serif text-xl font-bold text-charcoal-900 flex items-center gap-2">
                <i data-lucide="message-square-plus" class="w-5 h-5 text-blush-600"></i>
                <span>Add Patron Review</span>
            </h3>
            <p class="text-xs text-charcoal-800/60 mt-0.5">Supports both Written Text and YouTube Video embeds</p>
        </div>

        <form method="POST" action="reviews.php" class="space-y-4">
            <input type="hidden" name="action" value="add_review">

            <!-- Review Type Switcher -->
            <div>
                <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Review Category Type *</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer border border-[#ebdccd] p-3 rounded-xl flex items-center gap-2 hover:border-blush-500">
                        <input type="radio" name="review_type" value="text" checked onchange="toggleVideoField(false)">
                        <span class="text-xs font-bold text-charcoal-900">Written Text</span>
                    </label>
                    <label class="cursor-pointer border border-[#ebdccd] p-3 rounded-xl flex items-center gap-2 hover:border-blush-500">
                        <input type="radio" name="review_type" value="video" onchange="toggleVideoField(true)">
                        <span class="text-xs font-bold text-charcoal-900">YouTube Video</span>
                    </label>
                </div>
            </div>

            <!-- YouTube Video URL (Conditional) -->
            <div id="video_url_container" class="hidden">
                <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">YouTube Video Link *</label>
                <input 
                    type="url" 
                    name="video_url" 
                    id="video_url_input" 
                    placeholder="https://www.youtube.com/watch?v=..." 
                    class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-2.5 text-xs font-mono focus:outline-none focus:border-blush-500"
                >
                <span class="text-[10px] text-charcoal-800/60 mt-1 block">Supports YouTube watch URL or embed URL.</span>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Patron Name *</label>
                    <input 
                        type="text" 
                        name="author_name" 
                        required 
                        placeholder="e.g. Sneha Reddy" 
                        class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-blush-500"
                    >
                </div>

                <div>
                    <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">City / Location</label>
                    <input 
                        type="text" 
                        name="author_city" 
                        placeholder="e.g. Hyderabad" 
                        class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-blush-500"
                    >
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Star Rating</label>
                    <select name="rating" class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-3 py-2 text-xs focus:outline-none">
                        <option value="5">★★★★★ (5 Stars)</option>
                        <option value="4">★★★★☆ (4 Stars)</option>
                        <option value="3">★★★☆☆ (3 Stars)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Product Tag</label>
                    <select name="product_name" class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-3 py-2 text-xs focus:outline-none">
                        <?php foreach ($products as $p): ?>
                            <option value="<?= htmlspecialchars($p['name']) ?>"><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Headline Title *</label>
                <input 
                    type="text" 
                    name="title" 
                    placeholder="e.g. The sandalwood aroma is deeply relaxing" 
                    class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-blush-500"
                >
            </div>

            <div>
                <label class="block text-xs uppercase font-bold text-charcoal-800 mb-1">Review Description *</label>
                <textarea 
                    name="content" 
                    rows="3" 
                    required 
                    placeholder="Write detailed customer feedback..." 
                    class="w-full bg-[#FAF7F2] border border-[#ebdccd] rounded-xl px-4 py-2 text-xs focus:outline-none focus:border-blush-500"
                ></textarea>
            </div>

            <button type="submit" class="btn-luxury w-full bg-charcoal-900 hover:bg-blush-700 text-cream-50 font-bold py-3.5 px-6 rounded-2xl text-xs uppercase tracking-widest transition-all shadow-md">
                Publish Review to Storefront
            </button>
        </form>
    </div>

    <!-- Right Column: Existing Reviews List (7 Cols) -->
    <div class="lg:col-span-7 space-y-4">
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#ebdccd] shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#f2e6d9]">
                <h3 class="font-serif text-xl font-bold text-charcoal-900">Current Reviews Archive</h3>
                <span class="text-xs text-charcoal-800/60 font-semibold"><?= count($reviews) ?> Reviews Published</span>
            </div>

            <div class="space-y-4 max-h-[650px] overflow-y-auto pr-1">
                <?php foreach ($reviews as $rev): ?>
                    <div class="p-4 rounded-2xl border border-[#ebdccd] bg-cream-50/50 flex flex-col sm:flex-row justify-between items-start gap-4">
                        <div class="space-y-1.5 flex-grow">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase <?= $rev['review_type'] === 'video' ? 'bg-rose-100 text-rose-800' : 'bg-blush-100 text-blush-800' ?>">
                                    <?= $rev['review_type'] === 'video' ? '▶ YouTube Video' : 'Written Review' ?>
                                </span>
                                <div class="flex text-gold-500 text-xs">
                                    <?php for ($i=0; $i<$rev['rating']; $i++): ?>★<?php endfor; ?>
                                </div>
                            </div>

                            <h4 class="font-serif text-sm font-bold text-charcoal-900"><?= htmlspecialchars($rev['title']) ?></h4>
                            <p class="text-xs text-charcoal-800/70 line-clamp-2"><?= htmlspecialchars($rev['content']) ?></p>

                            <div class="text-[10px] text-charcoal-800/60 pt-1">
                                By <strong class="text-charcoal-900"><?= htmlspecialchars($rev['author_name']) ?></strong> (<?= htmlspecialchars($rev['author_city']) ?>) &bull; <?= htmlspecialchars($rev['product_name']) ?>
                            </div>
                        </div>

                        <form method="POST" action="reviews.php" onsubmit="return confirm('Delete this review?');" class="flex-shrink-0">
                            <input type="hidden" name="action" value="delete_review">
                            <input type="hidden" name="review_id" value="<?= $rev['id'] ?>">
                            <button type="submit" class="text-rose-600 hover:text-rose-800 p-2 rounded-xl hover:bg-rose-50" title="Delete Review">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div>

<script>
    function toggleVideoField(show) {
        const container = document.getElementById('video_url_container');
        if (show) {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    }
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
