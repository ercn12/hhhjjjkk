<?php
// employee_panel.php - Çalışan Paneli
// app_signature_byeyn.1
// PHP hata gösterimini aç (geçici, hata tespiti için)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// SITE_NAME sabitini tanımlayan app_config.php dosyasını dahil et
include_once __DIR__ . '/config/app_config.php';

$pageTitle = "Çalışan Paneli - " . SITE_NAME; // Sayfa başlığı
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

        <!-- Çalışan Paneli -->
        <div id="employeeScreen" class="screen active">
            <h2><i class="fa-solid fa-users-gear"></i> Çalışan Paneli</h2>

            <div class="section-header">
                <h3 id="carFormTitle"><i class="fa-solid fa-plus"></i> Yeni Araç Ekle</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="newPlate">Plaka Numarası</label>
                        <input type="text" id="newPlate" placeholder="34ABC123" required>
                    </div>
                    <div class="form-group">
                        <label for="customerName">Müşteri Adı</label>
                        <input type="text" id="customerName" placeholder="Müşteri adı">
                    </div>
                    <div class="form-group">
                        <label for="customerPhone">Telefon</label>
                        <input type="tel" id="customerPhone" placeholder="05551234567">
                    </div>
                    <div class="form-group">
                        <label for="serviceType">Hizmet Türü</label>
                        <select id="serviceType">
                            <option value="Dış Yıkama">Dış Yıkama</option>
                            <option value="İç Dış Yıkama">İç Dış Yıkama</option>
                            <option value="Detaylı Yıkama">Detaylı Yıkama</option>
                            <option value="Cila">Cila</option>
                            <option value="Nano Koruma">Nano Koruma</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="estimatedTime">Tahmini Süre (dk)</label>
                        <input type="number" id="estimatedTime" placeholder="45" min="1">
                    </div>
                    <div class="form-group">
                        <label for="price">Fiyat (₺)</label>
                        <input type="number" id="price" placeholder="150" min="0">
                    </div>
                    <!-- Pro Versiyon: Çalışanlar kendi şubelerine araç ekleyebilir -->
                    <!-- input hidden ile çalışanın şube ID'si JS tarafından otomatik gönderilecek. -->
                </div>
                <button class="btn btn-primary" onclick="addCar()">
                    <i class="fa-solid fa-plus"></i> Araç Ekle
                </button>
            </div>

            <h3><i class="fa-solid fa-list"></i> Aktif Araçlar</h3>
            <div id="employeeCarList" class="loading">
                <i class="fa-solid fa-spinner fa-spin"></i> Yükleniyor...
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