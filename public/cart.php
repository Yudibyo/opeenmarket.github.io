<?php
session_start();
include '../includes/navbar.php';

if (!$user) {
    header("Location: login.php");
    exit();
}

// Mendapatkan cart user
$query = new MongoDB\Driver\Query(['user_id' => $user->_id], ['sort' => ['created_at' => -1]]);
$cursor = $mongoClient->executeQuery("$dbName.cart", $query);
$cartItems = $cursor->toArray();

// Menghitung total
$total = 0;
foreach ($cartItems as $item) {
    $total += $item->price * ($item->quantity ?? 1);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keranjang - OpeenMarket</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .cart-container {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 2rem;
            max-width: 900px;
            margin: 20px auto;
        }

        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .cart-title {
            font-size: 1.8rem;
            font-weight: 800;
            color: #1a202c;
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            background: white;
            border-radius: 20px;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            border: 1px solid #ddd;
        }

        .cart-item:hover {
            background: #f9f9f9;
        }

        .cart-item-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 15px;
            margin-right: 2rem;
        }

        .cart-item-info {
            flex: 1;
        }

        .cart-item-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .cart-item-price {
            font-size: 1.3rem;
            font-weight: 800;
            color: #1a73e8;
        }

        .cart-item-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: #f8fafc;
            padding: 0.5rem;
            border-radius: 12px;
        }

        .quantity-btn {
            background: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #1a73e8;
        }

        .quantity-btn:hover {
            background: #1a73e8;
            color: white;
        }

        .cart-total {
            background: #f8fafc;
            padding: 2rem;
            border-radius: 20px;
            margin-top: 2rem;
        }

        .cart-total-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 1rem;
        }

        .cart-total-amount {
            font-size: 2rem;
            font-weight: 800;
            color: #1a73e8;
        }

        .checkout-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(45deg, #1a73e8, #64b5f6);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
            box-shadow: 0 4px 15px rgba(26,115,232,0.2);
        }

        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26,115,232,0.3);
        }

        @media (max-width: 768px) {
            .cart-item {
                flex-direction: column;
                text-align: center;
                padding: 1rem;
            }

            .cart-item-image {
                margin: 0 auto 1rem;
            }

            .cart-item-actions {
                flex-direction: column;
                margin-top: 1rem;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="cart-container">
        <div class="cart-header">
            <h1 class="cart-title">Keranjang Belanja</h1>
        </div>

        <?php if (isset($_GET['order_id'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <i class="fas fa-check-circle"></i> Pembayaran berhasil! Pesanan Anda sedang diproses.
            </div>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart text-gray-400 text-5xl mb-4"></i>
                <p class="text-gray-600">Keranjang belanja Anda masih kosong</p>
                <a href="index.php" class="shop-now-btn">
                    Mulai Belanja
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($cartItems as $item): ?>
                <div class="cart-item">
                    <img src="<?php echo htmlspecialchars($item->product_image); ?>" 
                        alt="<?php echo htmlspecialchars($item->product_name); ?>" 
                        class="cart-item-image">
                    <div class="cart-item-info">
                        <h3 class="cart-item-title"><?php echo htmlspecialchars($item->product_name); ?></h3>
                        <p class="cart-item-price">
                            Rp <?php echo number_format($item->price, 0, ',', '.'); ?>
                        </p>
                    </div>
                    <div class="cart-item-actions">
                        <div class="quantity-control">
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html> 