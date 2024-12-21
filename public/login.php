<?php
session_start();
require '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Kredensial host
    $hostUsername = "host";
    $hostPassword = "host123";

    // Cek apakah login sebagai host
    if ($username === $hostUsername && $password === $hostPassword) {
        $_SESSION['host'] = true;
        $_SESSION['users'] = $hostUsername;
        $_SESSION['user_data'] = [
            'name' => 'Host Admin',
            'email' => 'host@opeenmarket.com',
            'profile_picture' => 'img/host-profile.png'
        ];
        header("Location: index.php");
        exit();
    }

    $query = new MongoDB\Driver\Query(['username' => $username]);
    $cursor = $mongoClient->executeQuery("$dbName.users", $query);
    $user = current($cursor->toArray());

    if ($user && $password === $user->password) {
        $_SESSION['users'] = $username;
        header("Location: index.php");
        exit();
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - OpeenMarket</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .auth-container {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 2rem;
            width: 100%;
            max-width: 400px;
            margin: 100px auto;
        }

        .auth-title {
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 1.5rem;
            color: #333;
        }

        .form-group {
            margin-bottom: 1.5rem;
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
            padding: 0.75rem 0.55rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.9rem;
        }

        .submit-btn {
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
            font-size: 0.95rem;
            text-align: center;
            border: 2px solid #feb2b2;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="auth-container">
        <h1 class="auth-title">Login</h1>
        <form method="POST" action="login.php">
            <div class="form-group">
                <label class="form-label">Username:</label>
                <input type="text" name="username" required class="form-input" placeholder="Masukkan username">
            </div>
            
            <div class="form-group">
                <label class="form-label">Password:</label>
                <input type="password" name="password" required class="form-input" placeholder="Masukkan password">
            </div>
            
            <button type="submit" class="submit-btn">Login</button>
            <?php if (isset($error)): ?>
                <p class="error-message"><?php echo $error; ?></p>
            <?php endif; ?>
        </form>
        <div class="auth-footer">
            Belum punya akun? <a href="register.php" class="auth-link">Daftar di sini</a>
        </div>
    </div>
</body>
</html> 