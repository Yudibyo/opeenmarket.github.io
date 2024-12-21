<?php
session_start();
include '../includes/navbar.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

// Mendapatkan detail produk
$productId = new MongoDB\BSON\ObjectId($_GET['id']);
$query = new MongoDB\Driver\Query(['_id' => $productId]);
$cursor = $mongoClient->executeQuery("$dbName.produk", $query);
$product = current($cursor->toArray());

if (!$product) {
    header("Location: index.php");
    exit();
}

// Handle komentar baru
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    $commentData = [
        'product_id' => $productId,
        'user_id' => $user->_id,
        'user_name' => $user->name,
        'user_image' => $user->profile_picture,
        'comment' => $_POST['comment'],
        'created_at' => new MongoDB\BSON\UTCDateTime(time() * 1000)
    ];

    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->insert($commentData);
    $mongoClient->executeBulkWrite("$dbName.comments", $bulk);

    // Mendapatkan komentar terbaru setelah menambah komentar baru
    $commentQuery = new MongoDB\Driver\Query(
        ['product_id' => $productId],
        ['sort' => ['created_at' => -1]]
    );
    $comments = $mongoClient->executeQuery("$dbName.comments", $commentQuery);
    $commentSuccess = true;
}

// Mendapatkan komentar produk
$commentQuery = new MongoDB\Driver\Query(['product_id' => $productId], ['sort' => ['created_at' => -1]]);
$comments = $mongoClient->executeQuery("$dbName.comments", $commentQuery);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product->name); ?> - OpeenMarket</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .product-container {
            max-width: 1200px;
            margin: 5rem auto 1rem auto;
            padding: 0 20px;
        }

        .product-detail {
            background: white;
            border-radius: 5px;
            padding: 1.5rem;
            border: 1px solid #ddd;
        }

        .product-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .product-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 5px;
        }

        .product-info h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 1rem;
        }

        .product-price {
            font-size: 2rem;
            font-weight: 700;
            color: #1a73e8;
            margin: 1rem 0;
        }

        .original-price {
            text-decoration: line-through;
            color: #718096;
            font-size: 1.2rem;
            margin-right: 1rem;
        }

        .discount-badge {
            background: #ffeee8;
            color: #1a73e8;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 1rem;
            display: inline-block;
            margin-left: 1rem;
        }

        .product-description {
            color: #4a5568;
            line-height: 1.6;
            margin: 1.5rem 0;
        }

        .product-meta {
            display: flex;
            gap: 2rem;
            margin: 1.5rem 0;
            color: #718096;
        }

        .buy-section {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
        }

        .buy-btn {
            width: 100%;
            padding: 0.75rem;
            background: #1a73e8;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
        }

        .buy-now {
            background: #1a73e8;
            color: white;
        }

        .buy-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        /* Comments Section */
        .comments-section {
            background: white;
            border-radius: 5px;
            padding: 1.5rem;
            border: 1px solid #ddd;
        }

        .comments-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 2rem;
        }

        .comment-form {
            margin-bottom: 2rem;
        }

        .comment-input {
            width: 100%;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 1rem;
            resize: vertical;
            min-height: 100px;
        }

        .comment-input:focus {
            outline: none;
            border-color: #ee4d2d;
        }

        .comment-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .comment-item {
            display: flex;
            gap: 1rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .comment-user-image {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #1a73e8;
        }

        .comment-content {
            flex: 1;
        }

        .comment-user {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .comment-text {
            color: #4a5568;
            line-height: 1.5;
        }

        .comment-date {
            font-size: 0.875rem;
            color: #718096;
            margin-top: 0.5rem;
        }

        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: 1fr;
            }

            .product-container {
                margin: 5rem 20px 20px 20px;
            }

            .product-image {
                height: 300px;
            }
        }

        .comment-success {
            background: #f0fff4;
            color: #2f855a;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            text-align: center;
            border: 2px solid #9ae6b4;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            animation: fadeInDown 0.5s ease-out;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .comments-section {
            margin-top: 3rem;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="product-container">
        <div class="product-detail">
            <div class="product-grid">
                <div class="product-image-container">
                    <img src="<?php echo htmlspecialchars($product->image); ?>" 
                         alt="<?php echo htmlspecialchars($product->name); ?>"
                         class="product-image">
                </div>
                <div class="product-info">
                    <h1><?php echo htmlspecialchars($product->name); ?></h1>
                    
                    <div class="product-price">
                        <?php if (isset($product->type) && $product->type == 'promo' && 
                                isset($product->original_price) && isset($product->discount)): ?>
                            <span class="original-price">
                                Rp <?php echo number_format($product->original_price, 0, ',', '.'); ?>
                            </span>
                            <span class="discount-badge"><?php echo $product->discount; ?>% OFF</span>
                        <?php endif; ?>
                        <div>
                            Rp <?php echo number_format($product->price, 0, ',', '.'); ?>
                        </div>
                    </div>

                    <div class="product-meta">
                        <div>
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($product->location); ?>
                        </div>
                        <div>
                            <i class="fas fa-box"></i>
                            Terjual 1rb+
                        </div>
                    </div>

                    <div class="product-description">
                        <?php echo nl2br(htmlspecialchars($product->description)); ?>
                    </div>

                    <div class="buy-section">
                        <button class="buy-btn buy-now" onclick="buyNow()">
                            <i class="fas fa-bolt"></i>
                            Beli Sekarang
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="comments-section" id="comments">
            <h2 class="comments-title">Komentar</h2>

            <?php if ($user): ?>
                <form class="comment-form" method="POST">
                    <textarea name="comment" class="comment-input" 
                              placeholder="Tulis komentar Anda..." required></textarea>
                    <button type="submit" class="buy-btn buy-now">
                        Kirim Komentar
                    </button>
                </form>
            <?php else: ?>
                <div class="bg-gray-100 p-4 rounded-lg mb-4 text-center">
                    <a href="login.php" class="text-blue-600 hover:underline">Login</a> untuk memberikan komentar
                </div>
            <?php endif; ?>

            <div class="comment-list">
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-item">
                        <img src="<?php echo htmlspecialchars($comment->user_image); ?>" 
                             alt="<?php echo htmlspecialchars($comment->user_name); ?>"
                             class="comment-user-image">
                        <div class="comment-content">
                            <div class="comment-user">
                                <?php echo htmlspecialchars($comment->user_name); ?>
                            </div>
                            <div class="comment-text">
                                <?php echo nl2br(htmlspecialchars($comment->comment)); ?>
                            </div>
                            <div class="comment-date">
                                <?php 
                                    $date = $comment->created_at->toDateTime();
                                    echo $date->format('d M Y H:i'); 
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        function buyNow() {
            // Redirect ke halaman pembayaran dengan ID produk
            window.location.href = 'payment.php?id=<?php echo $product->_id; ?>';
        }
    </script>

    <?php if (isset($commentSuccess)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('comments').scrollIntoView({ behavior: 'smooth' });
        });
    </script>
    <?php endif; ?>
</body>
</html> 