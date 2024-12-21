<?php
session_start();
include '../includes/navbar.php';

$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($searchQuery) {
    // Gunakan regex untuk pencarian case-insensitive
    $regex = new MongoDB\BSON\Regex($searchQuery, 'i');
    
    // Cari produk berdasarkan nama atau deskripsi
    $query = new MongoDB\Driver\Query([
        '$or' => [
            ['name' => $regex],
            ['description' => $regex],
            ['location' => $regex]
        ]
    ]);
    
    $searchResults = $mongoClient->executeQuery("$dbName.produk", $query);
} else {
    $searchResults = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hasil Pencarian - OpeenMarket</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .search-container {
            max-width: 1200px;
            margin:2rem auto 1rem auto;
            padding: 0 20px;
        }

        .search-header {
            margin-bottom: 5rem;
        }

        .nav {
            background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
            padding: 5px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .nav-content {
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 30px;
            max-width: 1920px;
            margin: 0 auto;
            padding: 0 40px;
        }

        .search-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 0.5rem;
        }

        .search-info {
            color: #718096;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .product-info {
            padding: 1.5rem;
        }

        .product-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .product-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1a73e8;
            margin-bottom: 0.5rem;
        }

        .product-location {
            font-size: 0.9rem;
            color: #718096;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .no-results {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .no-results i {
            font-size: 3rem;
            color: #cbd5e0;
            margin-bottom: 1rem;
        }

        .no-results h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .no-results p {
            color: #718096;
            margin-bottom: 1.5rem;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: #1a73e8;
            color: white;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #1557b0;
        }

        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .search-container {
                margin-top: 5rem;
                padding: 0 15px;
            }

            .nav-content {
                padding: 0 20px;
            }
        }

        .search-results {
            position: relative;
            z-index: 1;
            background: transparent;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="search-container">
        <div class="search-header">
            <h1 class="search-title">Hasil Pencarian</h1>
            <p class="search-info">
                <?php if ($searchQuery): ?>
                    Menampilkan hasil pencarian untuk "<?php echo htmlspecialchars($searchQuery); ?>"
                <?php endif; ?>
            </p>
        </div>

        <?php if ($searchQuery && !empty($searchResults)): ?>
            <div class="product-grid search-results">
                <?php foreach ($searchResults as $product): ?>
                    <a href="product-detail.php?id=<?php echo $product->_id; ?>" class="product-card">
                        <img src="<?php echo htmlspecialchars($product->image); ?>" 
                             alt="<?php echo htmlspecialchars($product->name); ?>"
                             class="product-image">
                        <div class="product-info">
                            <h3 class="product-title"><?php echo htmlspecialchars($product->name); ?></h3>
                            <div class="product-price">
                                Rp <?php echo number_format($product->price, 0, ',', '.'); ?>
                            </div>
                            <div class="product-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($product->location); ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h3>Tidak ada hasil</h3>
                <p>
                    <?php if ($searchQuery): ?>
                        Tidak ditemukan produk untuk pencarian "<?php echo htmlspecialchars($searchQuery); ?>"
                    <?php else: ?>
                        Silakan masukkan kata kunci pencarian
                    <?php endif; ?>
                </p>
                <a href="index.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Beranda
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 