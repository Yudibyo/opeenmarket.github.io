<?php
session_start();
include '../includes/navbar.php';

if (!$user) {
    header("Location: login.php");
    exit();
}

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

// Handle pembayaran
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi metode pembayaran dipilih
    if (!isset($_POST['payment_method'])) {
        $error = "Silakan pilih metode pembayaran";
    } else {
        $orderData = [
            'user_id' => $user->_id,
            'product_id' => $productId,
            'price' => $product->price,
            'status' => 'paid',
            'payment_method' => $_POST['payment_method'],
            'payment_detail' => $_POST['payment_detail'],
            'created_at' => new MongoDB\BSON\UTCDateTime(time() * 1000)
        ];

        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->insert($orderData);
        $mongoClient->executeBulkWrite("$dbName.orders", $bulk);

        // Tambahkan ke cart
        $cartData = [
            'user_id' => $user->_id,
            'product_id' => $productId,
            'product_name' => $product->name,
            'product_image' => $product->image,
            'price' => $product->price,
            'quantity' => 1,
            'status' => 'completed',
            'created_at' => new MongoDB\BSON\UTCDateTime(time() * 1000)
        ];

        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->insert($cartData);
        $mongoClient->executeBulkWrite("$dbName.cart", $bulk);

        // Set flag sukses
        $success = true;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembayaran - OpeenMarket</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .payment-container {
            max-width: 800px;
            margin: 5rem auto 2rem auto;
            padding: 1.5rem;
            background: white;
            border-radius: 10px;
            border: 1px solid #ddd;
        }

        .payment-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 2rem;
            text-align: center;
        }

        .product-summary {
            display: flex;
            gap: 2rem;
            padding: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
        }

        .payment-methods {
            display: grid;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .payment-group {
            margin-bottom: 2rem;
        }

        .payment-group-title {
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .payment-method {
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
        }

        .payment-method:hover {
            background: #f5f5f5;
        }

        .payment-method.selected {
            background: #e3f2fd;
            border-color: #2196f3;
        }

        .payment-logo {
            width: 50px;
            height: 30px;
            object-fit: contain;
        }

        .payment-details {
            display: none;
            margin-top: 1rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 10px;
        }

        .payment-details.active {
            display: block;
        }

        .confirm-btn {
            width: 100%;
            padding: 1rem;
            background: #1a73e8;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .confirm-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .payment-info {
            color: #718096;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .error-message {
            background: #fff5f5;
            color: #e53e3e;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            text-align: center;
            border: 2px solid #feb2b2;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .success-message {
            background: #f0fff4;
            color: #2f855a;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            text-align: center;
            border: 2px solid #9ae6b4;
        }

        .success-message h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .success-message p {
            margin-bottom: 1.5rem;
        }

        .success-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .action-btn {
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .action-btn:first-child {
            background: #1a73e8;
            color: white;
        }

        .action-btn.secondary {
            background: #f8fafc;
            color: #1a73e8;
            border: 2px solid #1a73e8;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="payment-container">
        <h1 class="payment-title">Pembayaran</h1>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <div>
                    <h3>Pembayaran Berhasil!</h3>
                    <p>Terima kasih telah berbelanja di OpeenMarket</p>
                    <div class="success-actions">
                        <a href="cart.php" class="action-btn">Lihat Pesanan</a>
                        <a href="index.php" class="action-btn secondary">Belanja Lagi</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
        <form method="POST" id="paymentForm">
            <div class="product-summary">
                <img src="<?php echo htmlspecialchars($product->image); ?>" 
                     alt="<?php echo htmlspecialchars($product->name); ?>"
                     class="product-image">
                <div>
                    <h2 class="font-semibold"><?php echo htmlspecialchars($product->name); ?></h2>
                    <p class="text-lg font-bold text-blue-600">
                        Rp <?php echo number_format($product->price, 0, ',', '.'); ?>
                    </p>
                </div>
            </div>

            <div class="payment-methods">
                <!-- E-Wallet -->
                <div class="payment-group">
                    <h3 class="payment-group-title">E-Wallet</h3>
                    <label class="payment-method">
                        <input type="radio" name="payment_method" value="dana" required class="hidden">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/7/72/Logo_dana_blue.svg" alt="DANA" class="payment-logo">
                        <span>DANA</span>
                    </label>
                    <label class="payment-method">
                        <input type="radio" name="payment_method" value="gopay" required class="hidden">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/8/86/Gopay_logo.svg" alt="GoPay" class="payment-logo">
                        <span>GoPay</span>
                    </label>
                    <label class="payment-method">
                        <input type="radio" name="payment_method" value="ovo" required class="hidden">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/e/eb/Logo_ovo_purple.svg" alt="OVO" class="payment-logo">
                        <span>OVO</span>
                    </label>
                </div>

                <!-- Bank Transfer -->
                <div class="payment-group">
                    <h3 class="payment-group-title">Transfer Bank</h3>
                    <label class="payment-method">
                        <input type="radio" name="payment_method" value="bca" required class="hidden">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg" alt="BCA" class="payment-logo">
                        <span>Bank BCA</span>
                    </label>
                    <label class="payment-method">
                        <input type="radio" name="payment_method" value="bni" required class="hidden">
                        <img src="https://upload.wikimedia.org/wikipedia/id/5/55/BNI_logo.svg" alt="BNI" class="payment-logo">
                        <span>Bank BNI</span>
                    </label>
                    <label class="payment-method">
                        <input type="radio" name="payment_method" value="bri" required class="hidden">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/6/68/BANK_BRI_logo.svg" alt="BRI" class="payment-logo">
                        <span>Bank BRI</span>
                    </label>
                    <label class="payment-method">
                        <input type="radio" name="payment_method" value="mandiri" required class="hidden">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/a/ad/Bank_Mandiri_logo_2016.svg" alt="Mandiri" class="payment-logo">
                        <span>Bank Mandiri</span>
                    </label>
                </div>
            </div>

            <!-- Payment Details -->
            <div id="danaDetails" class="payment-details">
                <p>Nomor DANA: 085123456789</p>
                <p class="payment-info">Silakan transfer ke nomor DANA di atas</p>
            </div>
            <div id="gopayDetails" class="payment-details">
                <p>Nomor GoPay: 085123456789</p>
                <p class="payment-info">Silakan transfer ke nomor GoPay di atas</p>
            </div>
            <div id="ovoDetails" class="payment-details">
                <p>Nomor OVO: 085123456789</p>
                <p class="payment-info">Silakan transfer ke nomor OVO di atas</p>
            </div>
            <div id="bcaDetails" class="payment-details">
                <p>No. Rekening BCA: 1234567890</p>
                <p>Atas Nama: OpeenMarket</p>
                <p class="payment-info">Silakan transfer sesuai nominal ke rekening di atas</p>
            </div>
            <div id="bniDetails" class="payment-details">
                <p>No. Rekening BNI: 1234567890</p>
                <p>Atas Nama: OpeenMarket</p>
                <p class="payment-info">Silakan transfer sesuai nominal ke rekening di atas</p>
            </div>
            <div id="briDetails" class="payment-details">
                <p>No. Rekening BRI: 1234567890</p>
                <p>Atas Nama: OpeenMarket</p>
                <p class="payment-info">Silakan transfer sesuai nominal ke rekening di atas</p>
            </div>
            <div id="mandiriDetails" class="payment-details">
                <p>No. Rekening Mandiri: 1234567890</p>
                <p>Atas Nama: OpeenMarket</p>
                <p class="payment-info">Silakan transfer sesuai nominal ke rekening di atas</p>
            </div>

            <input type="hidden" name="payment_detail" id="paymentDetail">
            <button type="submit" class="confirm-btn">
                Konfirmasi Pembayaran
            </button>
        </form>
        <?php endif; ?>
    </div>

    <script>
        // Highlight metode pembayaran yang dipilih
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', function() {
                // Hapus selected class dari semua metode
                document.querySelectorAll('.payment-method').forEach(m => 
                    m.classList.remove('selected'));
                
                // Tambah selected class ke metode yang dipilih
                this.classList.add('selected');

                // Sembunyikan semua detail pembayaran
                document.querySelectorAll('.payment-details').forEach(detail => 
                    detail.classList.remove('active'));

                // Tampilkan detail pembayaran yang sesuai
                const paymentMethod = this.querySelector('input').value;
                document.getElementById(paymentMethod + 'Details').classList.add('active');
                
                // Set payment detail untuk form
                document.getElementById('paymentDetail').value = 
                    document.getElementById(paymentMethod + 'Details').textContent;
            });
        });
    </script>
</body>
</html> 