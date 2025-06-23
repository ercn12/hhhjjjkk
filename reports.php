<?php
// reports.php - Raporlar Paneli
// app_signature_byeyn.1
include_once __DIR__ . '/config/app_config.php'; // Uygulama ayarlarını dahil et
$pageTitle = "Raporlar - " . SITE_NAME;
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
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        <!-- Raporlar -->
        <div id="reportsScreen" class="screen active">
            <h2><i class="fa-solid fa-chart-line"></i> Genel Raporlar</h2>

            <!-- Pro Versiyon: Zaman Aralığı ve Şube Seçimi -->
            <div class="section-header">
                <h3>Rapor Filtreleri</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="reportTimeframe">Zaman Aralığı</label>
                        <select id="reportTimeframe" onchange="generateReports()">
                            <option value="daily">Günlük</option>
                            <option value="weekly">Haftalık</option>
                            <option value="monthly">Aylık</option>
                            <option value="yearly">Yıllık</option>
                            <option value="all">Tüm Zamanlar</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="reportBranchSelect">Şube Seçimi</label>
                        <select id="reportBranchSelect" onchange="generateReports()">
                             <option value="all">Tüm Şubeler</option>
                             <?php foreach ($branches as $branch): // app_config.php'deki dummy branches'i kullanıyoruz. Gerçekte JS dolduracak ?>
                                <option value="<?php echo htmlspecialchars($branch['id']); ?>">
                                    <?php echo htmlspecialchars($branch['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Pro Versiyon: Personel Bazında Filtreleme (Opsiyonel) -->
                    <!--
                    <div class="form-group">
                        <label for="reportEmployeeSelect">Personel Seçimi</label>
                        <select id="reportEmployeeSelect" onchange="generateReports()">
                            <option value="all">Tüm Personel</option>
                             <- PHP ile dinamik personel listesi çekilebilir ->
                        </select>
                    </div>
                    -->
                </div>
            </div>

            <div class="report-grid">
                <div class="report-card">
                    <div class="report-number" id="totalCars">0</div>
                    <div class="report-label">Toplam Araç</div>
                </div>
                <div class="report-card">
                    <div class="report-number" id="activeCars">0</div>
                    <div class="report-label">Aktif Araç</div>
                </div>
                <div class="report-card">
                    <div class="report-number" id="completedCars">0</div>
                    <div class="report-label">Tamamlanan</div>
                </div>
                <div class="report-card">
                    <div class="report-number" id="totalRevenue">₺0</div>
                    <div class="report-label">Toplam Gelir</div>
                </div>
                <div class="report-card">
                    <div class="report-number" id="avgRating">0.0</div>
                    <div class="report-label">Ortalama Puan</div>
                </div>
            </div>

            <h3><i class="fa-solid fa-history"></i> Detaylı Araç Kayıtları</h3>
            <div id="dailyCarsList" class="loading">
                <i class="fa-solid fa-spinner fa-spin"></i> Yükleniyor...
            </div>

            <!-- Pro Versiyon: Ek Rapor Grafikleri ve Detaylar -->
            <div class="section-header" style="margin-top: 40px;">
                <h3><i class="fa-solid fa-chart-bar"></i> Gelir Grafiği</h3>
                <!-- Chart.js veya benzeri bir kütüphane ile oluşturulacak canvas -->
                <canvas id="revenueChart" style="max-height: 400px; width: 100%;"></canvas>
                <p style="text-align: center; color: #777; margin-top: 10px;">
                    Bu alana seçilen zaman aralığına göre gelir grafiği yüklenecektir.
                </p>
            </div>
            <div class="section-header" style="margin-top: 40px;">
                <h3><i class="fa-solid fa-users"></i> Personel Performansı</h3>
                <div id="employeePerformanceList" class="loading">
                    <p style="text-align: center; color: #777;">
                        Bu alana personel bazında performans verileri yüklenecektir.
                    </p>
                    <i class="fa-solid fa-spinner fa-spin"></i> Yükleniyor...
                </div>
            </div>
             <div class="section-header" style="margin-top: 40px;">
                <h3><i class="fa-solid fa-bell-concierge"></i> Hizmet Karlılık Analizi</h3>
                <div id="serviceProfitabilityList" class="loading">
                    <p style="text-align: center; color: #777;">
                        Bu alana hizmet türlerine göre karlılık verileri yüklenecektir.
                    </p>
                    <i class="fa-solid fa-spinner fa-spin"></i> Yükleniyor...
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