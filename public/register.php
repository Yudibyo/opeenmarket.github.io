<?php
session_start();
require '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    // Cek username sudah ada atau belum
    $query = new MongoDB\Driver\Query(['username' => $username]);
    $cursor = $mongoClient->executeQuery("$dbName.users", $query);
    $existingUser = current($cursor->toArray());

    if ($existingUser) {
        $error = "Username sudah digunakan!";
    } else {
        // Handle profile picture upload
        $profile_picture = 'assets/images/default-profile.jpg';
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
            $target_dir = "assets/images/profiles/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $file_name;

            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
                $profile_picture = $target_file;
            }
        }

        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->insert([
            'username' => $username,
            'password' => $password,
            'email' => $email,
            'name' => $name,
            'phone' => $phone,
            'address' => $address,
            'profile_picture' => isset($profile_picture) ? $profile_picture : 'img/default-profile.png'
        ]);
        
        $mongoClient->executeBulkWrite("$dbName.users", $bulk);
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register - OpeenMarket</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .auth-container {
            background: #ffffff;
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 2rem;
            width: 100%;
            max-width: 600px;
            margin: 1rem auto;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            margin-bottom: 1rem;
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
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            background: white;
            margin-top: 0.5rem;
        }

        .register-btn {
            margin-top: 1rem;
            width: 100%;
            padding: 0.75rem;
            background: #1a73e8;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
        }

        .auth-footer {
            text-align: center;
            margin-top: 2rem;
            color: #4a5568;
        }

        .auth-link {
            color: #1a73e8;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .auth-link:hover {
            color: #1557b0;
            text-decoration: underline;
        }

        .error-message {
            background: #fff5f5;
            color: #e53e3e;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            text-align: center;
            border: 2px solid #feb2b2;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-group.full-width {
                grid-column: span 1;
            }
            
            .auth-container {
                margin: 20px;
                padding: 2rem;
            }
        }

        .profile-upload {
            text-align: center;
            margin-bottom: 2rem;
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
            font-size: 1.5rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 2rem;
            color: #1a202c;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="auth-container">
        <h1 class="auth-title">Daftar</h1>
        <form method="POST" action="register.php" enctype="multipart/form-data">
            <div class="profile-upload">
                <div class="profile-preview">
                    <img id="preview-image" src="assets/images/default-profile.jpg" alt="Profile Preview">
                </div>
                <label>
                    <i class="fas fa-camera"></i> Pilih Foto Profil
                    <input type="file" name="profile_picture" accept="image/*" onchange="previewImage(this)">
                </label>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap:</label>
                    <input type="text" name="name" required class="form-input" placeholder="Masukkan nama lengkap">
                </div>

                <div class="form-group">
                    <label class="form-label">Username:</label>
                    <input type="text" name="username" required class="form-input" placeholder="Masukkan username">
                </div>

                <div class="form-group">
                    <label class="form-label">Email:</label>
                    <input type="email" name="email" required class="form-input" placeholder="Masukkan email">
                </div>

                <div class="form-group">
                    <label class="form-label">Password:</label>
                    <input type="password" name="password" required class="form-input" placeholder="Masukkan password">
                </div>

                <div class="form-group">
                    <label class="form-label">No. Telepon:</label>
                    <input type="tel" name="phone" required class="form-input" placeholder="Masukkan nomor telepon">
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Alamat Lengkap:</label>
                    <textarea name="address" required class="form-input" rows="3" placeholder="Masukkan alamat lengkap"></textarea>
                </div>
            </div>

            <button type="submit" class="register-btn">Daftar</button>
            
            <?php if (isset($error)): ?>
                <p class="error-message"><?php echo $error; ?></p>
            <?php endif; ?>
        </form>
        <div class="auth-footer">
            Sudah punya akun? <a href="login.php" class="auth-link">Login di sini</a>
        </div>
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