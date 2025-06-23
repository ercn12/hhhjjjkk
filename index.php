<?php
// index.php - Uygulamanın giriş noktası. Sadece Giriş ve Müşteri Sorgu Ekranlarını içerir.
// app_signature_byeyn.1

// PHP hata gösterimini aç (geçici, hata tespiti için)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// SITE_NAME sabitini tanımlayan app_config.php dosyasını dahil et
include_once __DIR__ . '/config/app_config.php';

$pageTitle = "Giriş ve Araç Sorgulama - " . SITE_NAME; // Sayfa başlığı
// header.php içeriğini doğrudan ekleyin
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

        <!-- Giriş Ekranı -->
        <div id="loginScreen" class="screen active"> <!-- Başlangıçta aktif olacak -->
            <div class="login-form">
                <h2 class="welcome-text">Sisteme Giriş</h2>
                <div class="form-group">
                    <label for="email">E-posta</label>
                    <input type="email" id="email" placeholder="E-posta adresinizi girin" required>
                </div>
                <div class="form-group">
                    <label for="password">Şifre</label>
                    <input type="password" id="password" placeholder="Şifrenizi girin" required>
                </div>
                <button class="btn btn-primary" onclick="login()">
                    <i class="fa-solid fa-sign-in-alt"></i> Giriş Yap
                </button>
            </div>
        </div>

        <!-- Müşteri Sorgu Ekranı -->
        <!-- Bu ekran da başlangıçta aktif olabilir, çünkü ana sayfada her zaman erişilebilir olmalı -->
        <div id="customerScreen" class="screen">
            <h2><i class="fa-solid fa-search"></i> Araç Durumu Sorgula</h2>
            <div class="search-container">
                <div class="search-icon">🔍</div>
                <input type="text" class="search-input" id="plateSearch"
                       placeholder="Plaka numarasını girin (örn: 34ABC123)"
                       oninput="searchCar()">
            </div>

            <div id="carResults">
                <p style="text-align: center; color: #777;">
                    Plaka numarasını girerek araç durumunu sorgulayabilirsiniz.
                </p>
            </div>

            <!-- Geri Bildirim Bölümü -->
            <div class="feedback-section" id="feedbackSection" style="display: none;">
                <div class="feedback-card">
                    <h3><i class="fa-solid fa-star"></i> Hizmetimizi Değerlendirin</h3>
                    <p>Araç yıkama hizmetimizden memnun kaldınız mı? Görüşleriniz bizim için değerli!</p>

                    <div class="rating-container">
                        <label>Puan Verin:</label>
                        <div class="star-rating" id="starRating">
                            <span class="star" data-rating="1">⭐</span>
                            <span class="star" data-rating="2">⭐</span>
                            <span class="star" data-rating="3">⭐</span>
                            <span class="star" data-rating="4">⭐</span>
                            <span class="star" data-rating="5">⭐</span>
                        </div>
                        <div class="rating-text" id="ratingText">Puan seçin</div>
                    </div>

                    <div class="form-group">
                        <label for="feedbackComment">Yorumunuz (İsteğe bağlı):</label>
                        <textarea id="feedbackComment"
                                  placeholder="Hizmetimiz hakkında düşüncelerinizi paylaşın..."
                                  rows="4"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="customerNameFeedback">Adınız (İsteğe bağlı):</label>
                        <input type="text" id="customerNameFeedback" placeholder="Adınız">
                    </div>

                    <button class="btn btn-primary" onclick="submitFeedback()" id="submitFeedbackBtn">
                        <i class="fa-solid fa-paper-plane"></i> Geri Bildirim Gönder
                    </button>
                </div>
            </div>
        </div>

<?php
// footer.php içeriğini doğrudan ekleyin
?>
    </div> <!-- .container kapanışı -->

    <div id="notification" class="notification"></div>

    <!-- Firebase yapılandırma ve ana uygulama scriptleri -->
    <!-- Bu scriptler projenizin ana JS ve Firebase entegrasyon dosyalarıdır. -->
    <!-- JS dosyalarının doğru yolu 'js/' klasörü içinde olmalıdır -->
    <script type="module" src="js/firebase-config.js"></script>
    <script type="module" src="js/app.js"></script>
</body>
</html>