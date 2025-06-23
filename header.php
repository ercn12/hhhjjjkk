<?php
// header.php
// Gizli İmza: Bu dosya  Eyn Oto Yıkama Takip Sistemi için oluşturulmuştur.
include_once __DIR__ . '/config/app_config.php'; // Uygulama ayarlarını dahil et

// Oturum başlatma (PHP ile oturum yönetimi için gereklidir)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Sayfa başlığını dinamik olarak ayarlamak için değişken
$pageTitle = isset($pageTitle) ? $pageTitle : SITE_NAME;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <!-- Font Awesome CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Stil dosyasının doğru yolu 'css/' klasörü içinde olmalıdır -->
    <link rel="stylesheet" href="css/styles.css">
    <!-- Tailwind CSS CDN (Eğer projenizde kullanıyorsanız) -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <i class="fa-solid fa-car-wash"></i> <?php echo SITE_NAME; ?>
            </div>
            <div class="nav-buttons">
                <!-- Navigasyon butonları, Firebase Auth durumuna göre JS tarafından gizlenip gösterilecektir -->
                <button class="btn btn-secondary" onclick="showLogin()" id="loginBtn">
                    <i class="fa-solid fa-sign-in-alt"></i> Giriş Yap
                </button>
                <button class="btn btn-primary" onclick="showCustomerSearch()" id="customerBtn">
                    <i class="fa-solid fa-search"></i> Araç Sorgula
                </button>
                <button class="btn btn-secondary" onclick="showEmployeePanel()" id="employeeBtn" style="display: none;">
                    <i class="fa-solid fa-users-gear"></i> Çalışan Paneli
                </button>
                <button class="btn btn-secondary" onclick="showAdminPanel()" id="adminBtn" style="display: none;">
                    <i class="fa-solid fa-user-gear"></i> Yönetim Paneli
                </button>
                <button class="btn btn-secondary" onclick="showReports()" id="reportsBtn" style="display: none;">
                    <i class="fa-solid fa-chart-line"></i> Raporlar
                </button>
                <button class="btn btn-danger" onclick="logout()" id="logoutBtn" style="display: none;">
                    <i class="fa-solid fa-sign-out-alt"></i> Çıkış
                </button>
            </div>
        </div>
        <!-- Ana içerik burada başlayacak -->