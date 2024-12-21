<?php
session_start();
include '../includes/navbar.php';

// Cek apakah user sudah login
if (!$user) {
    header("Location: login.php");
    exit();
}

// Mendapatkan semua produk
$query = new MongoDB\Driver\Query(
    [], // Tampilkan semua produk
    ['sort' => ['created_at' => -1]]
);
$products = $mongoClient->executeQuery("$dbName.produk", $query);

// Handle hapus produk
if (isset($_POST['delete_product'])) {
    $productId = new MongoDB\BSON\ObjectId($_POST['product_id']);
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->delete(['_id' => $productId]);
    $mongoClient->executeBulkWrite("$dbName.produk", $bulk);
    header("Location: manage-products.php?success=delete");
    exit();
}

// Handle edit produk
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_product'])) {
    $productId = new MongoDB\BSON\ObjectId($_POST['product_id']);
    $updateData = [
        'name' => $_POST['name'],
        'price' => (int)$_POST['price'],
        'description' => $_POST['description'],
        'type' => $_POST['type'],
        'location' => $_POST['location'],
        'updated_at' => new MongoDB\BSON\UTCDateTime(time() * 1000)
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
    header("Location: manage-products.php?success=edit");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Produk Saya - OpeenMarket</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 0rem auto 0rem auto;
            padding:  0 5px;
        }

        .product-table {
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .product-table th,
        .product-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .product-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #4a5568;
        }

        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin: 0.25rem;
        }

        .edit-btn {
            background: #ebf5ff;
            color: #1a73e8;
        }

        .delete-btn {
            background: #fff5f5;
            color: #e53e3e;
            border: none;
        }

        .add-btn {
            background: #1a73e8;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .success-message {
            background: #f0fff4;
            color: #2f855a;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
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
            overflow-y: auto;
            padding: 20px;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            max-width: 600px;
            margin: 30px auto;
            position: relative;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .close-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: #718096;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #f7fafc;
        }

        .close-btn:hover {
            background: #edf2f7;
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
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #1a73e8;
            box-shadow: 0 0 0 3px rgba(26,115,232,0.1);
        }

        .preview-image-container {
            width: 150px;
            height: 150px;
            margin-bottom: 1rem;
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid #e2e8f0;
        }

        .preview-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .submit-btn {
            background: #1a73e8;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            background: #1557b0;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Produk Saya</h1>
            <a href="products.php" class="add-btn">
                <i class="fas fa-plus"></i> Tambah Produk
            </a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">
                <?php if ($_GET['success'] === 'delete'): ?>
                    Produk berhasil dihapus!
                <?php elseif ($_GET['success'] === 'edit'): ?>
                    Produk berhasil diperbarui!
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="product-table">
            <table class="w-full">
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Nama Produk</th>
                        <th>Harga</th>
                        <th>Tipe</th>
                        <th>Lokasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr data-id="<?php echo $product->_id; ?>">
                            <td>
                                <img src="<?php echo htmlspecialchars($product->image); ?>" 
                                     alt="<?php echo htmlspecialchars($product->name); ?>"
                                     class="product-image">
                            </td>
                            <td><?php echo htmlspecialchars($product->name); ?></td>
                            <td>Rp <?php echo number_format($product->price, 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($product->type); ?></td>
                            <td><?php echo htmlspecialchars($product->location); ?></td>
                            <td>
                                <button onclick='editProduct(<?php echo json_encode($product); ?>)'
                                        class="action-btn edit-btn">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button onclick="deleteProduct('<?php echo $product->_id; ?>')" 
                                        class="action-btn delete-btn">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Modal Edit Produk -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeEditModal()">&times;</span>
                <h2 class="modal-title">Edit Produk</h2>
                
                <form id="editForm">
                    <input type="hidden" name="product_id" id="edit_product_id">
                    <input type="hidden" name="edit_product" value="true">
                    <div class="form-group">
                        <label class="form-label">Gambar Produk</label>
                        <div class="preview-image-container">
                            <img id="current_image" src="" alt="Current Image" class="preview-image">
                        </div>
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

                    <button type="submit" class="submit-btn">
                        Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function deleteProduct(productId) {
            if (confirm('Yakin ingin menghapus produk ini?')) {
                fetch('manage-products.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'delete_product=true&product_id=' + productId
                })
                .then(response => response.text())
                .then(() => {
                    // Hapus baris tabel produk yang dihapus
                    document.querySelector(`tr[data-id="${productId}"]`).remove();
                    
                    // Tampilkan pesan sukses
                    const successMessage = document.createElement('div');
                    successMessage.className = 'success-message';
                    successMessage.textContent = 'Produk berhasil dihapus!';
                    
                    const container = document.querySelector('.container');
                    container.insertBefore(successMessage, container.querySelector('.product-table'));
                    
                    // Hilangkan pesan setelah 3 detik
                    setTimeout(() => {
                        successMessage.remove();
                    }, 3000);
                });
            }
        }

        function editProduct(product) {
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

        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('manage-products.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    window.location.href = 'manage-products.php?success=edit';
                } else {
                    alert('Gagal mengupdate produk');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengupdate produk');
            });
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('editModal')) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>