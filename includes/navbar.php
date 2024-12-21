<?php
if (!isset($mongoClient)) {
    require __DIR__ . '/../config/config.php';
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['users'])) {
    $username = $_SESSION['users'];
    try {
        $query = new MongoDB\Driver\Query(['username' => $username]);
        $cursor = $mongoClient->executeQuery("$dbName.users", $query);
        $userArray = current($cursor->toArray());
        $user = $userArray ? $userArray : null;
    } catch (Exception $e) {
        $user = null;
    }
} else {
    $user = null;
}
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.tailwindcss.com" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    nav {
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

    .brand {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .brand img {
        width: 45px;
        height: 45px;
        object-fit: cover;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .brand-name {
        font-size: 1.5rem;
        font-weight: 800;
        background: linear-gradient(to right, #fff, #e3f2fd);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }

    .search-container {
        max-width: 800px;
        margin: 0 auto;
        width: 100%;
    }

    .search-box {
        display: flex;
        align-items: center;
        background: rgba(255,255,255,0.15);
        border-radius: 30px;
        padding: 5px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.2);
    }

    .search-box input {
        flex: 1;
        padding: 8px 15px;
        border: none;
        background: transparent;
        color: white;
        font-size: 0.9rem;
    }

    .search-box input::placeholder {
        color: rgba(255,255,255,0.8);
    }

    .search-box button {
        background: white;
        color: #1a73e8;
        border: none;
        padding: 8px 20px;
        border-radius: 25px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .search-box button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }

    .nav-links {
        display: flex;
        gap: 25px;
        align-items: center;
    }

    .nav-links a {
        color: white;
        text-decoration: none;
        font-size: 0.9rem;
        padding: 6px 15px;
        border-radius: 25px;
        transition: all 0.3s ease;
        position: relative;
    }

    .nav-links a:hover {
        background: rgba(255,255,255,0.1);
        transform: translateY(-2px);
    }

    .nav-links a::after {
        content: '';
        position: absolute;
        bottom: 5px;
        left: 50%;
        width: 0;
        height: 2px;
        background: white;
        transition: all 0.3s ease;
        transform: translateX(-50%);
    }

    .nav-links a:hover::after {
        width: 50%;
    }

    .login-btn {
        background: white;
        color: #1a73e8;
        padding: 12px 30px;
        border-radius: 25px;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .login-btn i,
    .login-btn span {
        color: #1a73e8;
    }

    .login-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        background: #f8f9fa;
    }

    .profile-img {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .profile-img:hover {
        transform: scale(1.1);
    }

    .dropdown-content {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        padding: 10px;
        min-width: 220px;
    }

    .dropdown-content a {
        color: #2d3748;
        padding: 12px 20px;
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .dropdown-content a:hover {
        background: #f8f9fa;
        transform: translateX(5px);
    }

    @media (max-width: 1200px) {
        .nav-content {
            padding: 0 20px;
        }
    }

    @media (max-width: 768px) {
        .brand-name, .nav-links span {
            display: none;
        }
        
        .search-box input {
            display: none;
        }
        
        .nav-links {
            gap: 10px;
        }
        
        .nav-links a {
            padding: 8px;
        }
    }

    /* Dropdown Styles */
    .dropdown {
        position: relative;
        display: inline-block;
    }

    .dropdown-trigger {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        background: rgba(255,255,255,0.1);
        border-radius: 20px;
        cursor: pointer;
        transition: all 3s ease;
    }

    .dropdown-trigger:hover {
        background: rgba(255,255,255,0.2);
    }

    .profile-img {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        transition: all 2s ease;
    }

    .dropdown-content {
        position: absolute;
        top: 120%;
        right: 0;
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        padding: 10px;
        min-width: 220px;
        display: none;
        z-index: 1000;
        animation: slideDown 2s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .dropdown:hover .dropdown-content {
        display: block;
    }

    .dropdown-content a {
        color: #2d3748;
        padding: 12px 20px;
        border-radius: 10px;
        transition: all 1s ease;
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
    }

    .dropdown-content a:hover {
        background: #f8f9fa;
        transform: translateX(5px);
    }

    .dropdown-content a i {
        width: 20px;
        text-align: center;
        color: #1a73e8;
    }

    .dropdown-divider {
        height: 1px;
        background: #e2e8f0;
        margin: 8px 0;
    }

    .user-info {
        padding: 12px 20px;
        border-bottom: 1px solid #e2e8f0;
        margin-bottom: 8px;
    }

    .user-name {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 4px;
    }

    .user-email {
        font-size: 0.85rem;
        color: #718096;
    }
</style>

<nav class="nav">
    <div class="container">
        <div class="nav-content">
            <!-- Logo dan Nama Aplikasi -->
            <div class="brand">
                <a href="index.php" class="brand-name">OpeenMarket</a>
            </div>

            <!-- Search Bar -->
            <div class="search-container">
                <form class="search-box" action="search.php" method="GET" autocomplete="off">
                    <input type="text" name="q" placeholder="Cari produk..."
                        value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                    <button type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>

            <!-- Menu Navigasi -->
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="contact.php">Kontak</a>
                <a href="cart.php">Keranjang</a>
                <a href="products.php">Produk</a>
                <a href="manage-products.php">Produk Saya</a>
                <?php if (!$user): ?>
                    <a href="login.php" class="login-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Login</span>
                    </a>
                <?php else: ?>
                    <div class="dropdown">
                        <div class="dropdown-trigger">
                            <img src="<?php echo $user->profile_picture; ?>" alt="Profile" class="profile-img">
                            <span class="text-white"><?php echo htmlspecialchars($user->name); ?></span>
                            <i class="fas fa-chevron-down text-white"></i>
                        </div>
                        <div class="dropdown-content">
                            <div class="user-info">
                                <div class="user-name"><?php echo htmlspecialchars($user->name); ?></div>
                                <div class="user-email"><?php echo htmlspecialchars($user->email); ?></div>
                            </div>
                            <a href="profile.php">
                                <i class="fas fa-user"></i>
                                <span>Profil</span>
                            </a>
                            <a href="logout.php">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>