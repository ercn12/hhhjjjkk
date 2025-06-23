<?php
// admin_panel.php - Yönetim Paneli (Admin, Operasyon Müdürü, Bölge Müdürü, Şube Müdürü vb. için)
// app_signature_byeyn.1
include_once __DIR__ . '/config/app_config.php'; // Uygulama ayarlarını dahil et
$pageTitle = "Yönetim Paneli - " . SITE_NAME;
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

        <!-- Yönetim Paneli -->
        <div id="adminScreen" class="screen active"> <!-- Başlangıçta aktif olacak -->
            <h2><i class="fa-solid fa-user-gear"></i> Yönetim Paneli</h2>

            <!-- Geri Bildirim Gelen Kutusu -->
            <div class="section-header">
                <h3><i class="fa-solid fa-inbox"></i> Geri Bildirim Gelen Kutusu</h3>
                <div class="feedback-inbox" id="feedbackInbox">
                    <div class="loading">
                        <i class="fa-solid fa-spinner fa-spin"></i> Yükleniyor...
                    </div>
                </div>
            </div>

            <!-- Kullanıcı Ekle/Yönet -->
            <div class="section-header">
                <h3><i class="fa-solid fa-user-plus"></i> Kullanıcı Ekle/Yönet</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="newUserEmail">E-posta</label>
                        <input type="email" id="newUserEmail" placeholder="kullanici@example.com" required>
                    </div>
                    <div class="form-group">
                        <label for="newUserPassword">Şifre</label>
                        <input type="password" id="newUserPassword" placeholder="En az 6 karakter" required>
                    </div>
                    <div class="form-group">
                        <label for="newUserName">Ad Soyad</label>
                        <input type="text" id="newUserName" placeholder="Kullanıcı adı soyadı" required>
                    </div>
                    <div class="form-group">
                        <label for="newUserRole">Rol</label>
                        <select id="newUserRole">
                            <?php foreach ($roles as $value => $label): ?>
                                <option value="<?php echo htmlspecialchars($value); ?>">
                                    <?php echo htmlspecialchars($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Pro Versiyon: Kullanıcıya Şube Atama -->
                    <div class="form-group">
                        <label for="newUserBranch">Şube (Şube Müdürü ve Çalışan için)</label>
                        <select id="newUserBranch">
                             <option value="">Şube Seçin (Opsiyonel)</option>
                             <?php foreach ($branches as $branch): // app_config.php'deki dummy branches'i kullanıyoruz. Gerçekte JS dolduracak ?>
                                <option value="<?php echo htmlspecialchars($branch['id']); ?>">
                                    <?php echo htmlspecialchars($branch['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button class="btn btn-primary" onclick="addUser()">
                    <i class="fa-solid fa-user-plus"></i> Kullanıcı Ekle
                </button>
            </div>

            <h3><i class="fa-solid fa-users"></i> Mevcut Kullanıcılar</h3>
            <div id="userList" class="loading">
                <i class="fa-solid fa-spinner fa-spin"></i> Yükleniyor...
            </div>

            <!-- Pro Versiyon: Şube Yönetimi -->
            <div class="section-header">
                <h3><i class="fa-solid fa-building"></i> Şube Yönetimi</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="newBranchName">Şube Adı</label>
                        <input type="text" id="newBranchName" placeholder="Yeni şube adı (örn: Merkez Şube)" required>
                    </div>
                    <div class="form-group">
                        <label for="newBranchAddress">Adres</label>
                        <input type="text" id="newBranchAddress" placeholder="Şube adresi (örn: Sokak No, İlçe, İl)">
                    </div>
                    <div class="form-group">
                        <label for="newBranchPhone">Telefon</label>
                        <input type="tel" id="newBranchPhone" placeholder="Şube telefon numarası (örn: 0XXX YYY ZZ TT)">
                    </div>
                </div>
                <button class="btn btn-primary" onclick="addBranch()">
                    <i class="fa-solid fa-plus"></i> Şube Ekle
                </button>
                <h4 style="margin-top: 30px;"><i class="fa-solid fa-list-alt"></i> Kayıtlı Şubeler</h4>
                <div id="branchList" class="loading">
                    <i class="fa-solid fa-spinner fa-spin"></i> Yükleniyor...
                </div>
            </div>

            <!-- Pro Versiyon: Hizmet Yönetimi (Eklenen diğer Pro özelliği) -->
            <div class="section-header">
                <h3><i class="fa-solid fa-hand-sparkles"></i> Hizmet Yönetimi</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="newServiceType">Hizmet Adı</label>
                        <input type="text" id="newServiceType" placeholder="Yeni hizmet adı (örn: Motor Yıkama)" required>
                    </div>
                    <div class="form-group">
                        <label for="newServicePrice">Fiyat (₺)</label>
                        <input type="number" id="newServicePrice" placeholder="Hizmet fiyatı (örn: 200)" min="0">
                    </div>
                    <div class="form-group">
                        <label for="newServiceEstimatedTime">Tahmini Süre (dk)</label>
                        <input type="number" id="newServiceEstimatedTime" placeholder="Tahmini süre (örn: 60)" min="1">
                    </div>
                </div>
                <button class="btn btn-primary" onclick="addService()">
                    <i class="fa-solid fa-plus"></i> Hizmet Ekle
                </button>
                <h4 style="margin-top: 30px;"><i class="fa-solid fa-list"></i> Mevcut Hizmetler</h4>
                <div id="serviceList" class="loading">
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