<?php
session_start();
include '../includes/navbar.php';

// Mendapatkan produk promo
$queryPromo = new MongoDB\Driver\Query(
    ['type' => 'Promo', 'status' => 'active'],
    ['sort' => ['created_at' => -1]]
);
$promoProducts = $mongoClient->executeQuery("$dbName.produk", $queryPromo);

// Mendapatkan produk rekomendasi
$queryRekomendasi = new MongoDB\Driver\Query(
    ['type' => 'Regular', 'status' => 'active'],
    ['sort' => ['created_at' => -1]]
);
$rekomendasiProducts = $mongoClient->executeQuery("$dbName.produk", $queryRekomendasi);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OpeenMarket - Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {box-sizing: border-box;}
body {font-family: Verdana, sans-serif;}
.mySlides {display: none;}
img {vertical-align: middle;}

/* Slideshow container */
.slideshow-container {
  max-width: 1000px;
  position: relative;
  margin: auto;
  margin-top: 100px;
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.mySlides img {
  width: 100%;
  height: 400px;
  object-fit: cover;
}

/* Caption text */
.text {
  color: #f2f2f2;
  font-size: 15px;
  padding: 8px 12px;
  position: absolute;
  bottom: 8px;
  width: 100%;
  text-align: center;
  background: rgba(0,0,0,0.5);
  backdrop-filter: blur(5px);
}

/* Number text (1/3 etc) */
.numbertext {
  color: #f2f2f2;
  font-size: 12px;
  padding: 8px 12px;
  position: absolute;
  top: 0;
  background: rgba(0,0,0,0.5);
  border-radius: 0 0 10px 0;
}

/* The dots/bullets/indicators */
.dot {
  height: 15px;
  width: 15px;
  margin: 0 2px;
  background-color: #ddd;
  border-radius: 50%;
  display: inline-block;
  transition: background-color 0.6s ease;
  cursor: pointer;
}

.active {
  background-color: #1a73e8;
}

/* Fading animation */
.fade {
  animation-name: fade;
  animation-duration: 1.5s;
}

@keyframes fade {
  from {opacity: .4} 
  to {opacity: 1}
}

/* On smaller screens, decrease text size */
@media only screen and (max-width: 300px) {
  .text {font-size: 11px}
}
        /* Product Grid & Card Styles */
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            text-align: left;
            margin: 1.5rem 0;
            color: #1a202c;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
            padding: 1rem;
        }

        .product-card {
            text-decoration: none;
            background: white;
            border-radius: 5px;
            border: 1px solid #ddd;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            cursor: pointer;
            display: block;
        }

        .product-card:hover {
            background: #f9f9f9;
        }

        .product-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .product-info {
            padding: 0.75rem;
        }

        .product-title {
            font-size: 0.9rem;
            color: #222;
            margin-bottom: 0.5rem;
            line-height: 1.3;
            height: 2.4em;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .price {
            color: #1a73e8;
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .price::before {
            content: 'Rp';
            font-size: 0.85rem;
            font-weight: 400;
            margin-right: 2px;
        }

        .rating {
            display: flex;
            align-items: center;
            gap: 2px;
            font-size: 0.8rem;
            color: #1a73e8;
            margin-bottom: 0.5rem;
        }

        .location-badge {
            font-size: 0.75rem;
            color: #666;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .promo-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #e8f0fe;
            color: #1a73e8;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .sold-count {
            font-size: 0.75rem;
            color: #666;
            margin-left: 8px;
        }

        /* Category Navigation */
        .category-nav {
            background: white;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .category-list {
            display: flex;
            gap: 2rem;
            overflow-x: auto;
            padding: 0.5rem;
        }

        .category-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            min-width: 80px;
            text-decoration: none;
            color: #333;
        }

        .category-icon {
            width: 40px;
            height: 40px;
            background: #e8f0fe;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1a73e8;
        }

        .category-name {
            font-size: 0.8rem;
            text-align: center;
        }

        .product-card .buy-btn {
            position: absolute;
            bottom: -50px;
            left: 0;
            right: 0;
            background: #1a73e8;
            color: white;
            padding: 1rem;
            text-align: center;
            transition: all 0.3s ease;
            opacity: 0;
        }

        .product-card:hover .buy-btn {
            bottom: 0;
            opacity: 1;
        }

        .product-info-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 1.5rem;
            border-radius: 5px;
            border: 1px solid #ddd;
            z-index: 1100;
            width: 90%;
            max-width: 600px;
        }

        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1050;
        }

        .close-popup {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: #718096;
        }

        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
                padding: 0.75rem;
            }

            .section-title {
                font-size: 1.5rem;
                margin: 1.5rem 0;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8 mt-16">
        <h2>Automatic Slideshow</h2>
        <p>Change image every 2 seconds:</p>

        <div class="slideshow-container">
            <div class="mySlides fade">
                <div class="numbertext"></div>
                <img src="img/iklan1.jpg" alt="Promo 1">
            </div>

            <div class="mySlides fade">
                <div class="numbertext"></div>
                <img src="img/iklan2.jpg" alt="Promo 2">
            </div>

            <div class="mySlides fade">
                <div class="numbertext"></div>
                <img src="img/iklan3.jpg" alt="Promo 3">
            </div>
        </div>
        <br>

        <div style="text-align:center">
            <span class="dot" onclick="currentSlide(1)"></span> 
            <span class="dot" onclick="currentSlide(2)"></span> 
            <span class="dot" onclick="currentSlide(3)"></span> 
        </div>

        <script>
            let slideIndex = 0;
            showSlides();

            function showSlides() {
                let i;
                let slides = document.getElementsByClassName("mySlides");
                let dots = document.getElementsByClassName("dot");
                for (i = 0; i < slides.length; i++) {
                    slides[i].style.display = "none";  
                }
                slideIndex++;
                if (slideIndex > slides.length) {slideIndex = 1}    
                for (i = 0; i < dots.length; i++) {
                    dots[i].className = dots[i].className.replace(" active", "");
                }
                slides[slideIndex-1].style.display = "block";  
                dots[slideIndex-1].className += " active";
                setTimeout(showSlides, 5000); // Ubah ke 5 detik
            }

            function currentSlide(n) {
                slideIndex = n - 1;
                showSlides();
            }
        </script>
        <!-- Promo Section -->
        <h2 class="section-title">Promo Spesial</h2>
        <div class="product-grid">
            <?php foreach ($promoProducts as $product): ?>
                <a href="product-detail.php?id=<?php echo $product->_id; ?>" class="product-card">
                    <div class="promo-badge"><?php echo $product->discount; ?>% OFF</div>
                    <img src="<?php echo htmlspecialchars($product->image); ?>" 
                         alt="<?php echo htmlspecialchars($product->name); ?>"
                         class="product-image">
                    <div class="product-info">
                        <h3 class="product-title"><?php echo htmlspecialchars($product->name); ?></h3>
                        <div class="price">
                            <?php if (isset($product->original_price)): ?>
                                <span class="original-price">
                                    Rp <?php echo number_format($product->original_price, 0, ',', '.'); ?>
                                </span>
                            <?php endif; ?>
                            <?php echo number_format($product->price, 0, ',', '.'); ?>
                        </div>
                        <div class="rating">
                            <?php for($i = 0; $i < 5; $i++): ?>
                                <?php if($i < $product->rating): ?>
                                    <i class="fas fa-star"></i>
                                <?php else: ?>
                                    <i class="far fa-star"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <div class="location-badge">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($product->location); ?>
                        </div>
                    </div>
                    <div class="buy-btn">
                        <i class="fas fa-shopping-cart"></i> Beli Sekarang
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Rekomendasi Section -->
        <h2 class="section-title">Rekomendasi untuk Anda</h2>
        <div class="product-grid">
            <?php foreach ($rekomendasiProducts as $product): ?>
                <a href="product-detail.php?id=<?php echo $product->_id; ?>" class="product-card">
                    <img src="<?php echo htmlspecialchars($product->image); ?>" 
                         alt="<?php echo htmlspecialchars($product->name); ?>"
                         class="product-image">
                    <div class="product-info">
                        <h3 class="product-title"><?php echo htmlspecialchars($product->name); ?></h3>
                        <div class="price">
                            <?php echo number_format($product->price, 0, ',', '.'); ?>
                        </div>
                        <div class="rating">
                            <?php for($i = 0; $i < 5; $i++): ?>
                                <?php if($i < $product->rating): ?>
                                    <i class="fas fa-star"></i>
                                <?php else: ?>
                                    <i class="far fa-star"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <div class="location-badge">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($product->location); ?>
                        </div>
                    </div>
                    <div class="buy-btn">
                        <i class="fas fa-shopping-cart"></i> Beli Sekarang
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Product Info Popup -->
        <div class="popup-overlay" id="popupOverlay"></div>
        <div class="product-info-popup" id="productInfoPopup">
            <i class="fas fa-times close-popup" onclick="closePopup()"></i>
            <div id="popupContent"></div>
        </div>

        <script>
            function showProductInfo(product) {
                const popup = document.getElementById('productInfoPopup');
                const overlay = document.getElementById('popupOverlay');
                const content = document.getElementById('popupContent');
                
                content.innerHTML = `
                    <img src="${product.image}" alt="${product.name}" style="width: 100%; height: 300px; object-fit: cover; border-radius: 15px; margin-bottom: 1rem;">
                    <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem;">${product.name}</h2>
                    <p style="color: #718096; margin-bottom: 1rem;">${product.description}</p>
                    <div style="font-size: 1.3rem; font-weight: 700; color: #1a73e8; margin-bottom: 1rem;">
                        Rp ${product.price.toLocaleString()}
                    </div>
                    <div style="color: #718096; margin-bottom: 1.5rem;">
                        <i class="fas fa-map-marker-alt"></i> ${product.location}
                    </div>
                    <button onclick="addToCart(${JSON.stringify(product)})" 
                            style="width: 100%; padding: 1rem; background: #1a73e8; color: white; border: none; border-radius: 15px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-shopping-cart"></i> Tambah ke Keranjang
                    </button>
                `;
                
                popup.style.display = 'block';
                overlay.style.display = 'block';
            }
            
            function closePopup() {
                const popup = document.getElementById('productInfoPopup');
                const overlay = document.getElementById('popupOverlay');
                popup.style.display = 'none';
                overlay.style.display = 'none';
            }
            
            function addToCart(product) {
                // Implementasi tambah ke keranjang
                alert('Produk ditambahkan ke keranjang!');
                closePopup();
            }
        </script>
    </div>
</body>
</html>