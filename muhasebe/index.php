<?php require('_class/config.php'); 
if(isset($admin->id)){ 
    echo '<script>window.location.href="main.php";</script>'; 
} 
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <?php require('_inc/head.php'); ?>
    <title>Giriş Yap</title>
    
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="theme/plugins/fontawesome-free/css/all.min.css">
    
   <!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- SweetAlert2 Ayarları -->
<script>
    // Orijinal Swal'ı yedekle
    const OriginalSwal = Swal.fire;

    // Swal.fire metodunu override et
    Swal.fire = function(options) {
        // Eğer bir string parametre geldiyse, onu title olarak kullan
        if (typeof options === 'string') {
            options = { title: options };
        }

        // Varsayılan ayarları belirle
        const defaults = {
            position: 'top',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            toast: true,
            backdrop: false,
            width: 'auto',
            padding: '1em',
            customClass: {
                popup: 'animate__animated animate__fadeInDown'
            }
        };

        // Ayarları birleştir
        options = { ...defaults, ...options };

        // Orijinal metodu çağır
        return OriginalSwal.call(this, options);
    };
</script>

<style>
    /* SweetAlert özel stil */
    .swal2-popup {
        position: fixed !important;
        top: 0 !important;
        right: 0 !important;
        left: 0 !important;
        margin: 0 auto !important;
        transform: none !important;
    }
    .swal2-container {
        position: absolute !important;
    }
</style>

<!-- Animate.css -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        :root {
            --primary-color: #2196F3;
            --secondary-color: #1976D2;
            --accent-color: #64B5F6;
            --background: #0A0A0A;
            --card-bg: rgba(18, 18, 18, 0.98);
            --text-primary: #fff;
            --text-secondary: #B2BAC2;
            --border-color: rgba(255, 255, 255, 0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--background);
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            width: 150%;
            height: 150%;
            background: radial-gradient(circle at center, 
                rgba(255, 255, 255, 0.05) 0%,
                rgba(255, 255, 255, 0.02) 50%,
                transparent 100%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .login-container {
            width: 100%;
            max-width: 480px;
            padding: 3rem;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(20px);
            position: relative;
            z-index: 1;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo {
            max-width: 300px;
            height: auto;
            margin-bottom: 1.5rem;
            filter: drop-shadow(0 0 15px rgba(33, 150, 243, 0.3));
            transition: transform 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.03);
        }

        .login-header p {
            color: var(--text-secondary);
            font-size: 1.2rem;
            font-weight: 500;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-top: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            color: var(--text-primary);
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.15);
            background: rgba(255, 255, 255, 0.05);
        }

        .login-button {
            width: 100%;
            padding: 1rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .login-button:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(33, 150, 243, 0.3);
        }

        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        .login-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .login-footer a:hover {
            color: var(--accent-color);
            text-shadow: 0 0 10px rgba(33, 150, 243, 0.3);
        }

        @media (max-width: 520px) {
            .login-container {
                margin: 1rem;
                padding: 2rem;
            }

            .logo {
                max-width: 220px;
            }

            .login-header p {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="logo.png" alt="Logo" class="logo">
            <p>Muhasebe Sistemi</p>
        </div>

        <form action="_ajax/_ajaxLogin.php" method="post" autocomplete="off">
            <div class="form-group">
                <input type="text" name="email" placeholder="Email" required>
            </div>

            <div class="form-group">
                <input type="password" name="password" placeholder="Şifre" required>
            </div>

            <button type="submit" class="login-button">
                Giriş Yap
            </button>

            <input type="hidden" name="frm" value="frmLogin" />
        </form>

        <div style="margin-top: 20px; display: flex; gap: 5px; justify-content: center;">
            <a href="../personel/" class="login-button" style="display: block; text-align: center; text-decoration: none; width: 120px; padding: 6px; background: #17a2b8; font-size: 14px;">
                Personel
            </a>
            <a href="/" class="login-button" style="display: block; text-align: center; text-decoration: none; width: 120px; padding: 6px; background: #6c757d; font-size: 14px;">
                Siteye Dön
            </a>
        </div>

        <div class="login-footer">
            <p>Ofis Ve Personel Sistemleri <a href="https://ersinemlak.com.tr" target="_blank">ERS GROUP A.Ş.</a></p>
        </div>
    </div>

    <?php require('_inc/footer.php'); ?>
</body>
</html>