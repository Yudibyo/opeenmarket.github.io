<?php
session_start();
include '../includes/navbar.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kontak - OpeenMarket</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .contact-container {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 25px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 3rem;
            width: 100%;
            max-width: 800px;
            margin: 10rem auto 2rem auto;
            text-align: center;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .contact-title {
            font-size: 2rem;
            font-weight: 800;
            color: #1a202c;
            margin-bottom: 10rem;
            position: relative;
        }

        .contact-title::after {
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

        .social-links {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin: 2rem auto;
            max-width: 600px;
        }

        .social-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem;
            border-radius: 20px;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #2d3748;
            background: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .social-link:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }

        .social-link i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .social-link.whatsapp {
            color: #25D366;
        }

        .social-link.facebook {
            color: #1877F2;
        }

        .social-link.instagram {
            color: #E4405F;
        }

        .social-link span {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .social-link p {
            font-size: 0.9rem;
            color: #718096;
            margin-top: 0.5rem;
        }

        @media (max-width: 768px) {
            .contact-container {
                margin: 5rem 20px 20px 20px;
                padding: 2rem;
            }

            .social-links {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .social-link {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="contact-container">
        <h1 class="contact-title">Hubungi Kami</h1>
        
        <div class="social-links">
            <a href="https://wa.me/0859392680709" target="_blank" class="social-link whatsapp">
                <i class="fab fa-whatsapp"></i>
                <span>WhatsApp</span>
            </a>

            <a href="https://www.facebook.com/jiddan.ys/" target="_blank" class="social-link facebook">
                <i class="fab fa-facebook"></i>
                <span>Facebook</span>
            </a>

            <a href="https://www.instagram.com/jiddan.y.s/" target="_blank" class="social-link instagram">
                <i class="fab fa-instagram"></i>
                <span>Instagram</span>
            </a>
        </div>
    </div>
</body>
</html> 