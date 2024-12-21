<?php
session_start();
include '../includes/navbar.php';

// Mendapatkan semua produk
$query = new MongoDB\Driver\Query([], ['sort' => ['created_at' => -1]]);
$products = $mongoClient->executeQuery("$dbName.produk", $query);

// Handle edit produk
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_product'])) {
    $productId = new MongoDB\BSON\ObjectId($_POST['product_id']);
    $updateData = [
        'name' => $_POST['name'],
        'price' => (int)$_POST['price'],
        'description' => $_POST['description'],
        'type' => $_POST['type'],
        'location' => $_POST['location']
    ];

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "assets/images/products/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $updateData['image'] = $target_file;
        }
    }

    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->update(
        ['_id' => $productId],
        ['$set' => $updateData]
    );
    $mongoClient->executeBulkWrite("$dbName.produk", $bulk);
    header("Location: products.php?success=edit");
    exit();
}

// Handle hapus produk
if (isset($_POST['delete_product']) && isset($_SESSION['host']) && $_SESSION['host'] === true) {
    $productId = new MongoDB\BSON\ObjectId($_POST['product_id']);
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->delete(['_id' => $productId]);
    $mongoClient->executeBulkWrite("$dbName.produk", $bulk);
    header("Location: products.php?success=delete");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productData = [
        'name' => $_POST['name'],
        'price' => (int)$_POST['price'],
        'description' => $_POST['description'],
        'type' => $_POST['type'],
        'location' => $_POST['location'],
        'created_at' => new MongoDB\BSON\UTCDateTime(time() * 1000),
        'rating' => 0,
        'status' => 'active'
    ];

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "assets/images/products/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $productData['image'] = $target_file;
        }
    }

    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->insert($productData);
    try {
        $result = $mongoClient->executeBulkWrite("$dbName.produk", $bulk);
        header("Location: manage-products.php?success=add");
    } catch (Exception $e) {
        header("Location: products.php?error=add");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Produk - OpeenMarket</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .product-container {
            max-width: 800px;
            margin: 7rem auto 2rem auto;
            padding: 0 20px;
        }

        .add-product-form {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 3rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }

        .section-title {
            font-size: 2rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 2rem;
            color: #1a202c;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: linear-gradient(to right, #1a73e8, #64b5f6);
            border-radius: 2px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-label {
            display: block;
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: #2d3748;
        }

        .form-input {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #1a73e8;
            box-shadow: 0 0 0 4px rgba(26,115,232,0.1);
        }

        .image-preview {
            width: 100%;
            height: 200px;
            border: 2px dashed #e2e8f0;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .image-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .product-container {
                margin: 5rem 20px 20px 20px;
            }
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            max-width: 600px;
            margin: 2rem auto;
            position: relative;
        }

        .close-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: #718096;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #4a5568;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="product-container">
        <div class="add-product-form">
            <h2 class="section-title">Tambah Produk Baru</h2>
            <form action="products.php" method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nama Produk</label>
                        <input type="text" name="name" required class="form-input" placeholder="Masukkan nama produk">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Lokasi</label>
                        <input type="text" name="location" required class="form-input" placeholder="Masukkan lokasi">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Harga</label>
                        <input type="number" name="price" required class="form-input" placeholder="Masukkan harga">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tipe Produk</label>
                        <select name="type" required class="form-input" onchange="toggleDiscount(this.value)">
                            <option value="Regular">Regular</option>
                            <option value="Promo">Promo</option>
                        </select>
                    </div>

                    <div class="form-group" id="discount-field" style="display: none;">
                        <label class="form-label">Diskon (%)</label>
                        <input type="number" name="discount" class="form-input" min="0" max="100" placeholder="Masukkan persentase diskon">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Gambar Produk</label>
                        <div class="image-preview" id="imagePreview">
                            <img id="preview" src="#" alt="Preview" style="display: none;">
                            <span id="placeholder">Pilih gambar</span>
                        </div>
                        <input type="file" name="image" accept="image/*" required class="form-input" onchange="previewImage(this)">
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" required class="form-input" rows="3" placeholder="Masukkan deskripsi produk"></textarea>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Tambah Produk</button>
                <a href="index.php""></a>
            </form>
        </div>
    </div>

    <!-- Modal Edit Produk -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeEditModal()">&times;</span>
            <h2 class="text-xl font-bold mb-4">Edit Produk</h2>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="product_id" id="edit_product_id">
                <div class="form-group">
                    <label class="form-label">Gambar Produk</label>
                    <img id="current_image" src="" alt="Current Image" class="w-32 h-32 object-cover mb-2 rounded">
                    <input type="file" name="image" accept="image/*" class="form-input">
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Produk</label>
                    <input type="text" name="name" id="edit_name" required class="form-input">
                </div>

                <div class="form-group">
                    <label class="form-label">Harga</label>
                    <input type="number" name="price" id="edit_price" required class="form-input">
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" id="edit_description" required class="form-input" rows="4"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Tipe</label>
                    <select name="type" id="edit_type" required class="form-input">
                        <option value="Regular">Regular</option>
                        <option value="Promo">Promo</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Lokasi</label>
                    <input type="text" name="location" id="edit_location" required class="form-input">
                </div>

                <button type="submit" name="edit_product" class="add-btn w-full">
                    Simpan Perubahan
                </button>
            </form>
        </div>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('preview');
            const placeholder = document.getElementById('placeholder');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    placeholder.style.display = 'none';
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }

        function toggleDiscount(type) {
            const discountField = document.getElementById('discount-field');
            discountField.style.display = type === 'Promo' ? 'block' : 'none';
        }

        function openEditModal(product) {
            document.getElementById('editModal').style.display = 'block';
            document.getElementById('edit_product_id').value = product._id;
            document.getElementById('edit_name').value = product.name;
            document.getElementById('edit_price').value = product.price;
            document.getElementById('edit_description').value = product.description;
            document.getElementById('edit_type').value = product.type;
            document.getElementById('edit_location').value = product.location;
            document.getElementById('current_image').src = product.image;
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('editModal')) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>