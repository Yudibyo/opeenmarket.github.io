<?php
session_start();
include '../includes/navbar.php';

if (!$user) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $updateData = [
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address']
    ];

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "assets/images/profiles/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $updateData['profile_picture'] = $target_file;
        }
    }

    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->update(
        ['username' => $username],
        ['$set' => $updateData]
    );
    $mongoClient->executeBulkWrite("$dbName.users", $bulk);
    
    // Tampilkan pesan sukses
    $success = true;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil - OpeenMarket</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 5rem auto 1rem auto;
            padding: 2rem;
            background: white;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .profile-header {
            padding: 1rem;
            text-align: center;
            margin-bottom: 2rem;
        }

        .profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 1rem;
            border: 2px solid #1a73e8;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            padding: 0 0.5rem;
        }

        .form-group.full-width {
            grid-column: span 2;
            padding: 0 0.5rem;
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
            background: white;
            margin-top: 0.5rem;
        }

        .form-input:focus {
            outline: none;
            border-color: #1a73e8;
            box-shadow: 0 0 0 4px rgba(26,115,232,0.1);
        }

        .form-input.disabled {
            background-color: #f8fafc;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .save-btn {
            margin-top: 1rem;
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
            box-shadow: 0 4px 15px rgba(26,115,232,0.2);
        }

        .save-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26,115,232,0.3);
        }

        .profile-upload {
            text-align: center;
            margin-bottom: 3rem;
        }

        .profile-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin: 0 auto 1rem;
            border: 2px solid #1a73e8;
            overflow: hidden;
            position: relative;
            background: #f8fafc;
        }

        .profile-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .profile-upload label {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: #1a73e8;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }

        .profile-upload label:hover {
            opacity: 0.9;
        }

        .profile-upload input[type="file"] {
            display: none;
        }

        .auth-title {
            font-size: 2rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 3rem;
            color: #1a202c;
            position: relative;
        }

        .auth-title::after {
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

        .success-message {
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
            animation: fadeIn 0.5s ease-out;
        }

        .success-message i {
            font-size: 1.25rem;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-group.full-width {
                grid-column: span 1;
            }
            
            .auth-container {
                margin: 5rem 20px 20px 20px;
                padding: 2rem;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="profile-container">
        <h1 class="auth-title">Profil Saya</h1>
        
        <?php if (isset($success)): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                Profil berhasil diperbarui!
            </div>
        <?php endif; ?>

        <form method="POST" action="profile.php" enctype="multipart/form-data" class="profile-form">
            <div class="profile-upload">
                <div class="profile-preview">
                    <img id="preview-image" src="<?php echo htmlspecialchars($user->profile_picture); ?>" 
                         alt="Profile Picture" class="profile-picture">
                </div>
                <label>
                    <i class="fas fa-camera"></i> Ubah Foto Profil
                    <input type="file" name="profile_picture" accept="image/*" onchange="previewImage(this)">
                </label>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" value="<?php echo htmlspecialchars($user->username); ?>" 
                           class="form-input disabled" disabled>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user->name); ?>" 
                           class="form-input" required placeholder="Masukkan nama lengkap">
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user->email); ?>" 
                           class="form-input" required placeholder="Masukkan email">
                </div>

                <div class="form-group">
                    <label class="form-label">No. Telepon</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($user->phone ?? ''); ?>" 
                           class="form-input" placeholder="Masukkan nomor telepon">
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Alamat</label>
                    <textarea name="address" class="form-input" rows="3" 
                              placeholder="Masukkan alamat lengkap"><?php echo htmlspecialchars($user->address ?? ''); ?></textarea>
                </div>
            </div>

            <button type="submit" class="save-btn">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
        </form>
    </div>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    document.getElementById('preview-image').src = e.target.result;
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
