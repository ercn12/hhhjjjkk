// app.js
// app_signature_byeyn.1

// Firebase Auth servislerini ve initFirebase fonksiyonunu içeri aktar
import {
    auth,
    initFirebase,
    signInWithEmailAndPassword,
    createUserWithEmailAndPassword,
    signOut,
    onAuthStateChanged
} from './firebase-config.js';

// Global uygulama değişkenleri
export let currentUser = null;
export let cars = []; // Araç verileri
export let users = []; // Kullanıcı verileri
export let feedbacks = []; // Geri bildirim verileri
export let branches = []; // Şube verileri (Pro versiyon için)
export let services = []; // Hizmet verileri (Pro versiyon için)

export let selectedRating = 0; // Müşteri geri bildirimindeki seçili puan
export let searchedPlate = ''; // Müşteri ekranında aranan plaka

// === UTILITY FONKSİYONLARI ===

// Bildirim gösterme
export function showNotification(message, type) {
    const notification = document.getElementById('notification');
    // Sağlamlık kontrolü: Bildirim elementi var mı?
    if (notification) {
        notification.textContent = message;
        notification.className = 'notification ' + type;
        notification.classList.add('show');

        setTimeout(function() {
            notification.classList.remove('show');
        }, 3000);
    } else {
        console.warn("Bildirim elementi bulunamadı. Mesaj:", message);
    }
}

// Ekranları gizleyip gösterme (Sadece index.php'deki iki ekran için geçerli)
// Diğer paneller artık ayrı PHP dosyalarında olduğu için bu fonksiyonun ana kullanım alanı index.php'dir.
function showScreen(screenId) {
    const screens = document.querySelectorAll('.screen');
    screens.forEach(screen => {
        if (screen.id === screenId) {
            screen.classList.add('active');
        } else {
            screen.classList.remove('active');
        }
    });
    const targetScreen = document.getElementById(screenId);
    if (!targetScreen) {
        console.error(`Ekran elementi bulunamadı: #${screenId}. Lütfen HTML yapısını kontrol edin.`);
    }
}


// UI Güncelleme (Navigasyon butonlarının görünürlüğünü yönetir)
export function updateUI() {
    // Elementleri her seferinde tekrar alarak en güncel DOM durumunu garanti et
    const loginBtn = document.getElementById('loginBtn');
    const logoutBtn = document.getElementById('logoutBtn');
    const employeeBtn = document.getElementById('employeeBtn');
    const adminBtn = document.getElementById('adminBtn');
    const reportsBtn = document.getElementById('reportsBtn');

    console.log("updateUI called.");
    console.log("loginBtn (in updateUI):", loginBtn);
    console.log("logoutBtn (in updateUI):", logoutBtn);
    console.log("employeeBtn (in updateUI):", employeeBtn);
    console.log("adminBtn (in updateUI):", adminBtn);
    console.log("reportsBtn (in updateUI):", reportsBtn);

    // Her bir element için ayrı ayrı varlık kontrolü yaparak 'style' erişim hatasını önle
    if (currentUser) {
        if (loginBtn) loginBtn.style.display = 'none';
        if (logoutBtn) logoutBtn.style.display = 'flex';

        const userRole = currentUser.role;

        if (userRole === 'admin' || userRole === 'operation_manager' || userRole === 'regional_manager' || userRole === 'branch_manager') {
            if (employeeBtn) employeeBtn.style.display = 'flex';
            if (adminBtn) adminBtn.style.display = 'flex';
            if (reportsBtn) reportsBtn.style.display = 'flex';
        } else if (userRole === 'employee') {
            if (employeeBtn) employeeBtn.style.display = 'flex';
            if (adminBtn) adminBtn.style.display = 'none';
            if (reportsBtn) reportsBtn.style.display = 'none';
        } else {
            // Varsayılan veya bilinmeyen rol
            if (employeeBtn) employeeBtn.style.display = 'none';
            if (adminBtn) adminBtn.style.display = 'none';
            if (reportsBtn) reportsBtn.style.display = 'none';
        }
    } else {
        // Kullanıcı yok (çıkış yapılmış)
        if (loginBtn) loginBtn.style.display = 'flex';
        if (logoutBtn) logoutBtn.style.display = 'none';
        if (employeeBtn) employeeBtn.style.display = 'none';
        if (adminBtn) adminBtn.style.display = 'none';
        if (reportsBtn) reportsBtn.style.display = 'none';
    }
}

// === SAYFA NAVİGASYON FONKSİYONLARI (Artık doğrudan PHP sayfalarına yönlendirecek) ===

// showLogin fonksiyonunu window objesine atıyoruz ki HTML'den erişilebilir olsun
window.showLogin = function() {
    window.location.href = 'index.php';
};

export function showCustomerSearch() {
    window.location.href = 'index.php?screen=customer'; // index.php yüklenecek, sonra JS ile customerScreen aktif edilecek
}
// showCustomerSearch fonksiyonunu window objesine atıyoruz ki HTML'den erişilebilir olsun
window.showCustomerSearch = showCustomerSearch;

export async function showEmployeePanel() {
    // PHP'den çekilen currentUser objesindeki rol kontrolü
    if (!currentUser || !['employee', 'branch_manager', 'operation_manager', 'admin'].includes(currentUser.role)) {
        showNotification('Erişim yetkiniz yok', 'error');
        return;
    }
    // Doğrudan çalışan paneli sayfasına yönlendir
    window.location.href = 'employee_panel.php';
}
// showEmployeePanel fonksiyonunu window objesine atıyoruz ki HTML'den erişilebilir olsun
window.showEmployeePanel = showEmployeePanel;

export async function showAdminPanel() {
    // PHP'den çekilen currentUser objesindeki rol kontrolü
    if (!currentUser || !['admin', 'operation_manager', 'regional_manager'].includes(currentUser.role)) {
        showNotification('Yönetim paneli erişimi gerekli', 'error');
        return;
    }
    // Doğrudan admin paneli sayfasına yönlendir
    window.location.href = 'admin_panel.php';
};
// showAdminPanel fonksiyonunu window objesine atıyoruz ki HTML'den erişilebilir olsun
window.showAdminPanel = showAdminPanel;

export async function showReports() {
    // PHP'den çekilen currentUser objesindeki rol kontrolü
    if (!currentUser || !['admin', 'operation_manager', 'regional_manager'].includes(currentUser.role)) {
        showNotification('Rapor erişimi gerekli', 'error');
        return;
    }
    // Doğrudan raporlar sayfasına yönlendir
    window.location.href = 'reports.php';
};
// showReports fonksiyonunu window objesine atıyoruz ki HTML'den erişilebilir olsun
window.showReports = showReports;


// === PHP BACKEND İLE ETKİLEŞİM FONKSİYONLARI (FETCH API) ===

/**
 * PHP backend'den kullanıcı bilgilerini çeker ve currentUser objesini günceller.
 * @param {string} uid Firebase Auth'tan gelen UID
 * @param {string} email Firebase Auth'tan gelen e-posta
 */
window.loadUserDataFromBackend = async function(uid, email) {
    try {
        showNotification('Kullanıcı bilgileri yükleniyor...', 'info');
        const response = await fetch('./api/get_user_details.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ uid: uid, email: email })
        });

        const result = await response.json();

        if (response.ok && result.success) {
            currentUser = { ...result.data, uid: uid, email: email }; // PHP'den gelen rol ve diğer verileri currentUser'a ata
            console.log("Kullanıcı verisi PHP backend'den yüklendi:", currentUser);
            showNotification('Hoş geldiniz ' + currentUser.name + '!', 'success');

            // Kullanıcı rolüne göre doğru PHP sayfasına yönlendir
            // updateUI çağrısı artık doğrudan yönlendirmeden önce yapılacak ve sadece header butonları için geçerli olacak.
            updateUI();

            if (currentUser.role === 'admin' || currentUser.role === 'operation_manager' || currentUser.role === 'regional_manager') {
                window.location.href = 'admin_panel.php';
            } else if (currentUser.role === 'branch_manager' || currentUser.role === 'employee') {
                window.location.href = 'employee_panel.php';
            } else {
                window.location.href = 'index.php?screen=customer';
            }

        } else {
            console.error("Kullanıcı verisi çekme hatası (PHP):", result.message || 'Bilinmeyen Hata');
            showNotification('Kullanıcı bilgileri yüklenirken hata oluştu: ' + (result.message || 'Bilinmeyen hata'), 'error');
            await signOut(auth); // Hata durumunda Firebase Auth'tan çıkış yap
        }
    } catch (error) {
        console.error("PHP backend'e bağlanırken hata oluştu:", error);
        showNotification('Sunucuya bağlanırken hata oluştu. Lütfen ağ bağlantınızı kontrol edin.', 'error');
        await signOut(auth);
    }
};

/**
 * Araç listesini PHP backend'den çeker ve global 'cars' dizisini günceller.
 * @param {string} [branchId=null] Opsiyonel olarak şube ID'si
 */
async function fetchCars(branchId = null) {
    const employeeCarList = document.getElementById('employeeCarList');
    if (!employeeCarList) { // Sadece ilgili sayfada çalışmasını sağla
        console.log("fetchCars: employeeCarList elementi mevcut değil. Bu sayfa için araç listesi çekilmiyor.");
        return;
    }
    employeeCarList.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Yükleniyor...';

    try {
        const effectiveBranchId = branchId !== null ? branchId : (currentUser ? currentUser.branch_id : null);

        let url = './api/get_cars.php';
        if (effectiveBranchId !== null && effectiveBranchId !== 'all') {
            url += `?branch_id=${effectiveBranchId}`;
        }

        const response = await fetch(url);
        const result = await response.json();

        if (response.ok && result.success) {
            cars = result.data;
            console.log('Araç listesi PHP backend\'den yüklendi: ' + cars.length + ' araç');
            renderCarLists(employeeCarList, cars, true); // employeeCarList'e render et, kontrolleri göster
        } else {
            console.error("Araç listesi çekme hatası (PHP):", result.message || 'Bilinmeyen Hata');
            showNotification("Araç verileri yüklenemedi: " + (result.message || 'Bilinmeyen hata'), "error");
        }
    }
    catch (error) {
        console.error("PHP backend'e bağlanırken hata oluştu (Araçlar):", error);
        showNotification('Sunucuya bağlanırken hata oluştu.', 'error');
    }
}

/**
 * Geri bildirim listesini PHP backend'den çeker ve global 'feedbacks' dizisini günceler.
 * @param {string} [branchId=null] Opsiyonel olarak şube ID'si
 */
async function fetchFeedbacks(branchId = null) {
    const feedbackInbox = document.getElementById('feedbackInbox');
    if (!feedbackInbox) { // Sadece ilgili sayfada çalışmasını sağla
        console.log("fetchFeedbacks: feedbackInbox elementi mevcut değil. Bu sayfa için geri bildirim çekilmiyor.");
        return;
    }
    if (!currentUser || !['admin', 'operation_manager', 'regional_manager', 'branch_manager'].includes(currentUser.role)) return;

    feedbackInbox.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Yükleniyor...';

    try {
        const effectiveBranchId = branchId !== null ? branchId : (currentUser.role === 'branch_manager' ? currentUser.branch_id : null);

        let url = './api/get_feedbacks.php';
        if (effectiveBranchId !== null && effectiveBranchId !== 'all') {
            url += `?branch_id=${effectiveBranchId}`;
        }

        const response = await fetch(url);
        const result = await response.json();

        if (response.ok && result.success) {
            feedbacks = result.data;
            console.log('Geri bildirim listesi PHP backend\'den yüklendi: ' + feedbacks.length + ' geri bildirim');
            renderFeedbackInbox();
        } else {
            console.error("Geri bildirim listesi çekme hatası (PHP):", result.message || 'Bilinmeyen Hata');
            showNotification("Geri bildirimler yüklenemedi: " + (result.message || 'Bilinmeyen hata'), "error");
        }
    } catch (error) {
        console.error("PHP backend'e bağlanırken hata oluştu (Geri Bildirimler):", error);
        showNotification('Sunucuya bağlanırken hata oluştu.', 'error');
    }
}

/**
 * Kullanıcı listesini PHP backend'den çeker ve global 'users' dizisini günceller.
 */
async function fetchUsers() {
    const userList = document.getElementById('userList');
    if (!userList) { // Sadece ilgili sayfada çalışmasını sağla
        console.log("fetchUsers: userList elementi mevcut değil. Bu sayfa için kullanıcı listesi çekilmiyor.");
        return;
    }
    if (!currentUser || !['admin', 'operation_manager', 'regional_manager'].includes(currentUser.role)) return;

    userList.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Yükleniyor...';

    try {
        const response = await fetch('./api/get_users.php');
        const result = await response.json();

        if (response.ok && result.success) {
            users = result.data;
            console.log('Kullanıcı listesi PHP backend\'den yüklendi: ' + users.length + ' kullanıcı');
            renderUserList();
        } else {
            console.error("Kullanıcı listesi çekme hatası (PHP):", result.message || 'Bilinmeyen Hata');
            showNotification("Kullanıcılar yüklenemedi: " + (result.message || 'Bilinmeyen hata'), "error");
        }
    } catch (error) {
        console.error("PHP backend'e bağlanırken hata oluştu (Kullanıcılar):", error);
        showNotification('Sunucuya bağlanırken hata oluştu.', 'error');
    }
}

/**
 * Şube listesini PHP backend'den çeker ve global 'branches' dizisini günceller.
 */
async function fetchBranches() {
    const branchList = document.getElementById('branchList');
    const newUserBranchSelect = document.getElementById('newUserBranch');
    const reportBranchSelect = document.getElementById('reportBranchSelect');

    // Sadece ilgili sayfada veya ilgili selectboxlar mevcutsa çalışsın
    if (!branchList && !newUserBranchSelect && !reportBranchSelect) {
        console.log("fetchBranches: İlgili elementler mevcut değil. Bu sayfa için şube listesi çekilmiyor.");
        return;
    }

    if (!currentUser || !['admin', 'operation_manager', 'regional_manager', 'branch_manager', 'employee'].includes(currentUser.role)) {
        // Eğer kullanıcı bir şube müdürü veya çalışansa ve kendi şube bilgilerini görmek istiyorsa
        // bu kontrolü esnetmek gerekebilir. Şimdilik sadece yönetim rolleri çeksin.
        // Ancak şube drop-down'ları için tüm rollere açık bırakalım.
    }

    if (branchList) { // Sadece admin panelindeki şube listesi için yükleme göstergesi
        branchList.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Yükleniyor...';
    }

    try {
        const response = await fetch('./api/get_branches.php');
        const result = await response.json();

        if (response.ok && result.success) {
            branches = result.data;
            console.log('Şube listesi PHP backend\'den yüklendi: ' + branches.length + ' şube');
            renderBranchList();
            updateBranchSelectOptions();
        } else {
            console.error("Şube listesi çekme hatası (PHP):", result.message || 'Bilinmeyen Hata');
            showNotification("Şubeler yüklenemedi: " + (result.message || 'Bilinmeyen hata'), "error");
        }
    } catch (error) {
        console.error("PHP backend'e bağlanırken hata oluştu (Şubeler):", error);
        showNotification('Sunucuya bağlanırken hata oluştu.', 'error');
    }
}

/**
 * Hizmet listesini PHP backend'den çeker ve global 'services' dizisini günceller.
 */
async function fetchServices() {
    const serviceList = document.getElementById('serviceList');
    const serviceTypeSelect = document.getElementById('serviceType');

    // Sadece ilgili sayfada veya ilgili selectbox mevcutsa çalışsın
    if (!serviceList && !serviceTypeSelect) {
        console.log("fetchServices: İlgili elementler mevcut değil. Bu sayfa için hizmet listesi çekilmiyor.");
        return;
    }

    if (serviceList) { // Sadece admin panelindeki hizmet listesi için yükleme göstergesi
        serviceList.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Yükleniyor...';
    }

    try {
        const response = await fetch('./api/get_services.php');
        const result = await response.json();

        if (response.ok && result.success) {
            services = result.data;
            console.log('Hizmet listesi PHP backend\'den yüklendi: ' + services.length + ' hizmet');
            renderServiceList();
            updateServiceSelectOptions();
        } else {
            console.error("Hizmet listesi çekme hatası (PHP):", result.message || 'Bilinmeyen Hata');
            showNotification("Hizmetler yüklenemedi: " + (result.message || 'Bilinmeyen hata'), "error");
        }
    } catch (error) {
        console.error("PHP backend'e bağlanırken hata oluştu (Hizmetler):", error);
        showNotification('Sunucuya bağlanırken hata oluştu.', 'error');
    }
}

// === AUTHENTICATION FONKSİYONLARI (Firebase Auth kullanılarak) ===

window.login = async function() {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    if (!email || !password) {
        showNotification('E-posta ve şifre gerekli', 'error');
        return;
    }

    try {
        showNotification("Giriş denemesi yapılıyor...", "info");
        const userCredential = await signInWithEmailAndPassword(auth, email, password);
        console.log("Firebase Auth başarılı:", userCredential.user.uid);
        // loadUserDataFromBackend Firebase Auth onAuthStateChanged listener'ı tarafından otomatik çağrılacak
    } catch (error) {
        console.error("Giriş hatası:", error);
        let errorMessage = 'Giriş başarısız';

        if (error.code === 'auth/user-not-found' || error.code === 'auth/wrong-password') {
            errorMessage = 'Kullanıcı adı veya şifre hatalı.';
        } else if (error.code === 'auth/invalid-email') {
            errorMessage = 'Geçersiz e-posta adresi.';
        } else if (error.code === 'auth/too-many-requests') {
            errorMessage = 'Çok fazla hatalı deneme. Lütfen daha sonra tekrar deneyin.';
        } else {
            errorMessage = 'Giriş başarısız: ' + error.message;
        }

        showNotification(errorMessage, 'error');
    }
};

window.logout = async function() {
    try {
        await signOut(auth);
        currentUser = null;
        showNotification('Çıkış yapıldı', 'success');
        updateUI();
        window.location.href = 'index.php'; // Çıkış yapınca ana sayfaya yönlendir
    }
    catch (error) {
        console.error("Çıkış hatası:", error);
        showNotification('Çıkış yaparken hata oluştu.', 'error');
    }
};

// === CAR MANAGEMENT FONKSİYONLARI ===

window.searchCar = async function() {
    const searchTerm = document.getElementById('plateSearch').value.trim().toUpperCase();
    const resultsDiv = document.getElementById('carResults');
    const feedbackSection = document.getElementById('feedbackSection');

    if (searchTerm.length < 2) {
        resultsDiv.innerHTML = '<p style="text-align: center; color: #777;">Plaka numarasını girerek araç durumunu sorgulayabilirsiniz.</p>';
        feedbackSection.style.display = 'none';
        searchedPlate = '';
        return;
    }

    try {
        showNotification("Araç bilgileri aranıyor...", "info");
        const branchIdForSearch = currentUser && currentUser.branch_id ? currentUser.branch_id : null;
        let url = `./api/get_cars.php?plate=${encodeURIComponent(searchTerm)}`;
        if (branchIdForSearch) {
            url += `&branch_id=${branchIdForSearch}`;
        }

        const response = await fetch(url);
        const result = await response.json();

        if (response.ok && result.success) {
            const filteredCars = result.data;
            if (filteredCars.length === 0) {
                resultsDiv.innerHTML = '<div style="text-align: center; padding: 40px;">' +
                    '<i class="fa-solid fa-search" style="font-size: 48px; color: #ddd; margin-bottom: 15px;"></i>' +
                    '<p style="color: #777; font-size: 16px;">' +
                    `"<strong>${htmlspecialchars(searchTerm)}</strong>" plakası bulunamadı.` +
                    '</p>' +
                    '<p style="color: #999; font-size: 14px;">' +
                    'Farklı bir plaka deneyin veya çalışanlarımızla iletişime geçin.' +
                    '</p>' +
                    '</div>';
                feedbackSection.style.display = 'none';
                searchedPlate = '';
                showNotification("Plaka bulunamadı.", "error");
            } else {
                renderCarLists(resultsDiv, filteredCars, false);
                searchedPlate = filteredCars[0].plate;
                feedbackSection.style.display = 'block';
                resetFeedbackForm();
                showNotification(filteredCars[0].plate + ' plakası bulundu!', 'success');
            }
        } else {
            console.error("Araç sorgulama hatası (PHP):", result.message || 'Bilinmeyen Hata');
            showNotification("Araç sorgulama hatası: " + (result.message || 'Bilinmeyen hata'), "error");
        }
    } catch (error) {
        console.error("PHP backend'e bağlanırken hata oluştu (Araç Sorgulama):", error);
        showNotification('Sunucuya bağlanırken hata oluştu.', 'error');
    }
};

window.addCar = async function() {
    if (!currentUser || !['employee', 'branch_manager', 'operation_manager', 'admin'].includes(currentUser.role)) {
        showNotification('Araç eklemeye yetkiniz yok!', 'error');
        return;
    }

    const plate = document.getElementById('newPlate').value.trim().toUpperCase();
    const customerName = document.getElementById('customerName').value.trim();
    const customerPhone = document.getElementById('customerPhone').value.trim();
    const serviceType = document.getElementById('serviceType').value;
    const estimatedTime = document.getElementById('estimatedTime').value;
    const price = document.getElementById('price').value;
    const branchId = currentUser.branch_id;

    if (!plate || !serviceType || !branchId) {
        showNotification('Plaka numarası, hizmet türü ve şube bilgisi gerekli', 'error');
        return;
    }

    try {
        showNotification("Araç ekleniyor...", "info");
        const response = await fetch('./api/add_car.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                plate: plate,
                customer_name: customerName,
                customer_phone: customerPhone,
                service_type: serviceType,
                estimated_time: estimatedTime ? parseInt(estimatedTime) : null,
                price: price ? parseFloat(price) : null,
                status: 'waiting',
                created_by_user_id: currentUser.uid,
                // created_by_name: currentUser.name, // Backend'de UID ile kullanıcı adı çekilecek
                branch_id: branchId
            })
        });

        const result = await response.json();

        if (response.ok && result.success) {
            showNotification('Araç başarıyla eklendi', 'success');
            document.getElementById('newPlate').value = '';
            document.getElementById('customerName').value = '';
            document.getElementById('customerPhone').value = '';
            document.getElementById('serviceType').value = ''; // Yeni hizmet eklemeden sonra temizle
            document.getElementById('estimatedTime').value = '';
            document.getElementById('price').value = '';

            // Eğer hizmetler dinamik olarak yükleniyorsa varsayılana çekmek yerine en üsttekini seçebiliriz
            const serviceTypeSelect = document.getElementById('serviceType');
            if (serviceTypeSelect && serviceTypeSelect.options.length > 0) {
                serviceTypeSelect.selectedIndex = 0;
            }

            await fetchCars(currentUser.branch_id);
        } else {
            console.error('Araç ekleme hatası (PHP):', result.message || 'Bilinmeyen Hata');
            showNotification('Araç ekleme hatası: ' + (result.message || 'Bilinmeyen hata'), 'error');
        }
    } catch (error) {
        console.error('PHP backend\'e bağlanırken hata oluştu (Araç Ekleme):', error);
        showNotification('Sunucuya bağlanırken hata oluştu.', 'error');
    }
};

window.updateCarStatus = async function(carId, newStatus) {
    if (!currentUser || !['employee', 'branch_manager', 'operation_manager', 'admin'].includes(currentUser.role)) {
        showNotification('Bu işlemi yapmaya yetkiniz yok!', 'error');
        return;
    }

    try {
        showNotification("Durum güncelleniyor...", "info");
        const response = await fetch('./api/update_car_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                car_id: carId,
                status: newStatus,
                updated_by_user_id: currentUser.uid,
                // updated_by_name: currentUser.name // Backend'de UID ile kullanıcı adı çekilecek
            })
        });

        const result = await response.json();

        if (response.ok && result.success) {
            const statusTexts = {
                'waiting': 'Sırada', 'washing': 'Yıkanıyor', 'drying': 'Kurutuluyor',
                'ready': 'Hazır', 'completed': 'Tamamlandı'
            };
            showNotification('Durum güncellendi: ' + statusTexts[newStatus], 'success');
            await fetchCars(currentUser.branch_id);
            // Raporları da güncelle (eğer rapor ekranı açıksa)
            if (document.getElementById('reportsScreen') && document.getElementById('reportsScreen').classList.contains('active')) {
                await generateReports();
            }
        } else {
            console.error('Durum güncelleme hatası (PHP):', result.message || 'Bilinmeyen Hata');
            showNotification('Durum güncelleme hatası: ' + (result.message || 'Bilinmeyen hata'), 'error');
        }
    } catch (error) {
        console.error('PHP backend\'e bağlanırken hata oluştu (Durum Güncelleme):', error);
        showNotification('Sunucuya bağlanırken hata oluştu.', 'error');
    }
};

window.deleteCar = async function(carId) {
    if (!currentUser || currentUser.role !== 'admin') {
        showNotification('Admin yetkisi gerekli', 'error');
        return;
    }

    if (!window.confirm('Bu aracı silmek istediğinizden emin misiniz?')) {
        return;
    }

    try {
        showNotification("Araç siliniyor...", "info");
        const response = await fetch('./api/delete_car.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ car_id: carId })
        });

        const result = await response.json();

        if (response.ok && result.success) {
            showNotification('Araç silindi', 'success');
            await fetchCars(currentUser.branch_id);
            // Raporları da güncelle (eğer rapor ekranı açıksa)
            if (document.getElementById('reportsScreen') && document.getElementById('reportsScreen').classList.contains('active')) {
                await generateReports();
            }
        } else {
            console.error('Araç silme hatası (PHP):', result.message || 'Bilinmeyen Hata');
            showNotification('Araç silme hatası: ' + (result.message || 'Bilinmeyen hata'), 'error');
        }
    } catch (error) {
        console.error('PHP backend\'e bağlanırken hata oluştu (Araç Silme):', error);
        showNotification('Sunucuya bağlanırken hata oluştu.', 'error');
    }
};

// === FEEDBACK FONKSİYONLARI ===

window.setRating = function(rating) {
    selectedRating = rating;
    const stars = document.querySelectorAll('#starRating .star');
    const ratingText = document.getElementById('ratingText');

    const ratingTexts = {
        1: 'Çok Kötü', 2: 'Kötü', 3: 'Orta', 4: 'İyi', 5: 'Mükemmel'
    };

    stars.forEach(function(star, index) {
        if (index < rating) {
            star.classList.add('active');
        } else {
            star.classList.remove('active');
        }
    });

    ratingText.textContent = ratingTexts[rating] || 'Puan seçin';
};

window.submitFeedback = async function() {
    if (selectedRating === 0) {
        showNotification('Lütfen bir puan verin!', 'error');
        return;
    }

    if (!searchedPlate) {
        showNotification('Önce bir araç sorgulayın!', 'error');
        return;
    }

    const comment = document.getElementById('feedbackComment').value.trim();
    const customerName = document.getElementById('customerNameFeedback').value.trim();
    const feedbackBranchId = currentUser && currentUser.branch_id ? currentUser.branch_id : null;

    try {
        showNotification("Geri bildirim gönderiliyor...", "info");
        const response = await fetch('./api/submit_feedback.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                plate: searchedPlate,
                rating: selectedRating,
                comment: comment,
                customer_name: customerName,
                branch_id: feedbackBranchId
            })
        });

        const result = await response.json();

        if (response.ok && result.success) {
            showNotification('Geri bildiriminiz gönderildi. Teşekkürler!', 'success');
            resetFeedbackForm();
            if (document.getElementById('adminScreen') && document.getElementById('adminScreen').classList.contains('active')) {
                await fetchFeedbacks();
            }
        } else {
            console.error('Geri bildirim gönderme hatası (PHP):', result.message || 'Bilinmeyen Hata');
            showNotification('Geri bildirim gönderilemedi. Tekrar deneyin: ' + (result.message || 'Bilinmeyen hata'), 'error');
        }
    } catch (error) {
        console.error('PHP backend\'e bağlanırken hata oluştu (Geri Bildirim):', error);
        showNotification('Sunucuya bağlanırken hata oluştu.', 'error');
    }
};

function resetFeedbackForm() {
    selectedRating = 0;
    document.getElementById('feedbackComment').value = '';
    document.getElementById('customerNameFeedback').value = '';
    document.querySelectorAll('#starRating .star').forEach(function(star) {
        star.classList.remove('active');
    });
    document.getElementById('ratingText').textContent = 'Puan seçin';
}

window.markFeedbackAsRead = async function(feedbackId) {
    if (!currentUser || !['admin', 'operation_manager', 'regional_manager', 'branch_manager'].includes(currentUser.role)) {
        showNotification('Bu işlemi yapmaya yetkiniz yok!', 'error');
        return;
    }

    try {
        showNotification("Geri bildirim güncelleniyor...", "info");
        const response = await fetch('./api/mark_feedback_as_read.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ feedback_id: feedbackId })
        });
        const result = await response.json();
        if (response.ok && result.success) {
            showNotification('Geri bildirim okundu olarak işaretlendi', 'success');
            await fetchFeedbacks();
            // Raporları da güncelle (eğer rapor ekranı açıksa)
            if (document.getElementById('reportsScreen') && document.getElementById('reportsScreen').classList.contains('active')) {
                await generateReports();
            }
        } else {
            console.error('Geri bildirim güncelleme hatası (PHP):', result.message || 'Bilinmeyen Hata');
            showNotification('İşlem başarısız: ' + (result.message || 'Bilinmeyen hata'), 'error');
        }
    } catch (error) {
        console.error('PHP backend\'e bağlanırken hata oluştu (Geri Bildirim Okundu):', error);
        showNotification('Sunucuya bağlanırken hata oluştu.', 'error');
    }
};

window.deleteFeedback = async function(feedbackId) {
    if (!currentUser || currentUser.role !== 'admin') {
        showNotification('Admin yetkisi gerekli', 'error');
        return;
    }
    if (!window.confirm('Bu geri bildirimi silmek istediğinizden emin misiniz?')) {
        return;
    }

    try {
        showNotification("Geri bildirim siliniyor...", "info");
        const response = await fetch('./api/delete_feedback.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ feedback_id: feedbackId })
        });
        const result = await response.json();
        if (response.ok && result.success) {
            showNotification('Geri bildirim silindi', 'success');
            await fetchFeedbacks();
            // Raporları da güncelle (eğer rapor ekranı açıksa)
            if (document.getElementById('reportsScreen') && document.getElementById('reportsScreen').classList.contains('active')) {
                await generateReports();
            }
        } else {
            console.error('Geri bildirim silme hatası (PHP):', result.message || 'Bilinmeyen Hata');
            showNotification('Silme işlemi başarısız: ' + (result.message || 'Bilinmeyen hata'), 'error');
        }
    } catch (error) {
        console.error('PHP backend\'e bağlanırken hata oluştu (Geri Bildirim Silme):', error);
        showNotification('Sunucuya bağlanırken hata oluştu.', 'error');
    }
};


// === USER MANAGEMENT FONKSİYONLARI ===

window.addUser = async function() {
    if (!currentUser || !['admin', 'operation_manager', 'regional_manager'].includes(currentUser.role)) {
        showNotification('Bu işlemi yapmaya yetkiniz yok!', 'error');
        return;
    }

    const email = document.getElementById('newUserEmail').value.trim();
    const password = document.getElementById('newUserPassword').value;
    const name = document.getElementById('newUserName').value.trim();
    const role = document.getElementById('newUserRole').value;
    const branchId = document.getElementById('newUserBranch').value;

    if (!email || !password || !name) {
        showNotification('Tüm alanlar gerekli', 'error');
        return;
    }

    if (password.length < 6) {
        showNotification('Şifre en az 6 karakter olmalı', 'error');
        return;
    }

    try {
        showNotification("Kullanıcı ekleniyor (Firebase Auth)...", "info");
        const userCredential = await createUserWithEmailAndPassword(auth, email, password);
        const firebaseUid = userCredential.user.uid;

        showNotification("Kullanıcı bilgileri veritabanına kaydediliyor...", "info");
        const response = await fetch('./api/add_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                uid: firebaseUid,
                email: email,
                name: name,
                role: role,
                branch_id: branchId || null,
                created_by_user_id: currentUser.uid
            })
        });

        const result = await response.json();

        if (response.ok && result.success) {
            showNotification('Kullanıcı başarıyla eklendi', 'success');
            document.getElementById('newUserEmail').value = '';
            document.getElementById('newUserPassword').value = '';
            document.getElementById('newUserName').value = '';
            document.getElementById('newUserRole').value = 'employee';
            document.getElementById('newUserBranch').value = '';

            await fetchUsers();
        } else {
            console.error('Kullanıcı ekleme hatası (PHP):', result.message || 'Bilinmeyen Hata');
            showNotification('Kullanıcı ekleme hatası: ' + (result.message || 'Bilinmeyen hata'), 'error');
        }
    } catch (error) {
        console.error('Kullanıcı ekleme hatası:', error);
        let errorMessage = 'Kullanıcı ekleme başarısız';
        if (error.code === 'auth/email-already-in-use') {
            errorMessage = 'Bu e-posta adresi zaten kullanımda.';
        } else if (error.code === 'auth/weak-password') {
            errorMessage = 'Şifre çok zayıf.';
        } else {
            errorMessage = 'Kullanıcı ekleme hatası: ' + error.message;
        }
        showNotification(errorMessage, 'error');
    }
};

// === BRANCH MANAGEMENT FONKSİYONLARI ===

window.addBranch = async function() {
    if (!currentUser || currentUser.role !== 'admin') {
        showNotification('Sadece Yönetici yeni şube ekleyebilir!', 'error');
        return;
    }
    const name = document.getElementById('newBranchName').value.trim();
    const address = document.getElementById('newBranchAddress').value.trim();
    const phone = document.getElementById('newBranchPhone').value.trim();

    if (!name) {
        showNotification('Şube adı gerekli', 'error');
        return;
    }

    try {
        showNotification("Şube ekleniyor...", "info");
        const response = await fetch('./api/add_branch.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, address, phone })
        });
        const result = await response.json();

        if (response.ok && result.success) {
            showNotification('Şube başarıyla eklendi', 'success');
            document.getElementById('newBranchName').value = '';
            document.getElementById('newBranchAddress').value = '';
            document.getElementById('newBranchPhone').value = '';
            await fetchBranches();
        } else {
            console.error('Şube ekleme hatası (PHP):', result.message || 'Bilinmeyen Hata');
            showNotification('Şube ekleme hatası: ' + (result.message || 'Bilinmeyen hata'), 'error');
        }
    } catch (error) {
        console.error('PHP backend\'e bağlanırken hata oluştu (Şube Ekleme):', error);
        showNotification('Sunucuya bağlanırken hata oluştu.', 'error');
    }
};

// Şube seçim kutularını dinamik olarak güncelle
function updateBranchSelectOptions() {
    const newUserBranchSelect = document.getElementById('newUserBranch');
    const reportBranchSelect = document.getElementById('reportBranchSelect');

    if (newUserBranchSelect) {
        newUserBranchSelect.innerHTML = '<option value="">Şube Seçin (Opsiyonel)</option>';
        branches.forEach(branch => {
            const option = document.createElement('option');
            option.value = branch.id;
            option.textContent = branch.name;
            newUserBranchSelect.appendChild(option);
        });
    }

    if (reportBranchSelect) {
        reportBranchSelect.innerHTML = '<option value="all">Tüm Şubeler</option>';
        branches.forEach(branch => {
            const option = document.createElement('option');
            option.value = branch.id;
            option.textContent = branch.name;
            reportBranchSelect.appendChild(option);
        });
    }
}

// === SERVICE MANAGEMENT FONKSİYONLARI ===

window.addService = async function() {
    if (!currentUser || currentUser.role !== 'admin') {
        showNotification('Sadece Yönetici yeni hizmet ekleyebilir!', 'error');
        return;
    }
    const type = document.getElementById('newServiceType').value.trim();
    const price = document.getElementById('newServicePrice').value;
    const estimatedTime = document.getElementById('newServiceEstimatedTime').value;

    if (!type || !price) {
        showNotification('Hizmet adı ve fiyatı gerekli', 'error');
        return;
    }

    try {
        showNotification("Hizmet ekleniyor...", "info");
        const response = await fetch('./api/add_service.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                type: type,
                price: parseFloat(price),
                estimated_time: estimatedTime ? parseInt(estimatedTime) : null
            })
        });
        const result = await response.json();

        if (response.ok && result.success) {
            showNotification('Hizmet başarıyla eklendi', 'success');
            document.getElementById('newServiceType').value = '';
            document.getElementById('newServicePrice').value = '';
            document.getElementById('newServiceEstimatedTime').value = '';
            await fetchServices();
        } else {
            console.error('Hizmet ekleme hatası (PHP):', result.message || 'Bilinmeyen Hata');
            showNotification('Hizmet ekleme hatası: ' + (result.message || 'Bilinmeyen hata'), 'error');
        }
    } catch (error) {
        console.error('PHP backend\'e bağlanırken hata oluştu (Hizmet Ekleme):', error);
        showNotification('Sunucuya bağlanırken hata oluştu.', 'error');
    }
};

// Hizmet seçim kutularını dinamik olarak güncelle
function updateServiceSelectOptions() {
    const serviceTypeSelect = document.getElementById('serviceType');
    if (serviceTypeSelect) {
        // Mevcut seçenekleri temizle
        while (serviceTypeSelect.options.length > 0) {
            serviceTypeSelect.remove(0);
        }
        services.forEach(service => {
            const option = document.createElement('option');
            option.value = service.type;
            option.textContent = `${service.type} (₺${parseFloat(service.price).toFixed(2)})`;
            serviceTypeSelect.appendChild(option);
        });
        // Eğer hiç hizmet yoksa varsayılan bir seçenek ekle
        if (services.length === 0) {
            const defaultOption = document.createElement('option');
            defaultOption.value = "";
            defaultOption.textContent = "Hizmet Yok";
            serviceTypeSelect.appendChild(defaultOption);
            serviceTypeSelect.disabled = true; // Hizmet yoksa seçimi devre dışı bırak
        } else {
             serviceTypeSelect.disabled = false;
        }
    }
}


window.deleteService = async function(serviceId) {
    if (!currentUser || currentUser.role !== 'admin') {
        showNotification('Sadece Yönetici hizmet silebilir!', 'error');
        return;
    }
    if (!window.confirm('Bu hizmeti silmek istediğinizden emin misiniz?')) {
        return;
    }
    try {
        showNotification("Hizmet siliniyor...", "info");
        const response = await fetch('./api/delete_service.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ service_id: serviceId })
        });
        const result = await response.json();
        if (response.ok && result.success) {
            showNotification('Hizmet başarıyla silindi', 'success');
            await fetchServices();
        } else {
            console.error('Hizmet silme hatası (PHP):', result.message || 'Bilinmeyen Hata');
            showNotification('Silme işlemi başarısız: ' + (result.message || 'Bilinmeyen hata'), 'error');
        }
    } catch (error) {
        console.error('PHP backend\'e bağlanırken hata oluştu (Hizmet Silme):', error);
        showNotification('Sunucuya bağlanırken hata oluştu.', 'error');
    }
};

// HTML varlıklarını güvence altına almak için basit bir helper
function htmlspecialchars(str) {
    if (typeof str !== 'string') return str; // Sadece string'ler için uygula
    return str.replace(/&/g, '&amp;')
              .replace(/</g, '&lt;')
              .replace(/>/g, '&gt;')
              .replace(/"/g, '&quot;')
              .replace(/'/g, '&#039;');
}

// Rol etiketlerini döndüren helper fonksiyon
function getRoleLabel(roleValue) {
    // config/app_config.php'deki $roles dizisinin JS karşılığı
    const rolesMap = {
        'admin': 'Yönetici (Süper Admin)',
        'operation_manager': 'Operasyon Müdürü',
        'regional_manager': 'Bölge Müdürü',
        'branch_manager': 'Şube Müdürü',
        'employee': 'Çalışan',
    };
    return rolesMap[roleValue] || roleValue;
}


// === RENDERING FUNCTIONS ===

/**
 * Renders the list of cars in the employee panel or search results.
 * @param {HTMLElement} targetElement The DOM element where cars should be rendered.
 * @param {Array} carList The array of car objects to render.
 * @param {boolean} showControls Whether to show status update and delete controls.
 */
export function renderCarLists(targetElement, carList, showControls = true) {
    if (!targetElement) {
        console.warn("renderCarLists: Hedef element sağlanmadı.");
        return;
    }

    const carsToRender = carList;

    if (!carsToRender || carsToRender.length === 0) {
        targetElement.innerHTML = '<p style="text-align: center; color: #777; padding: 20px;">' +
                                  (targetElement.id === 'carResults' ? 'Aranan plakaya ait araç bulunamadı veya henüz araç eklenmedi.' : 'Henüz hiç araç kaydı yok.') +
                                  '</p>';
        return;
    }

    let html = '';
    carsToRender.forEach(car => {
        const createdAt = new Date(car.created_at * 1000).toLocaleString('tr-TR'); // Convert Unix timestamp to readable date
        const updatedAt = car.updated_at ? new Date(car.updated_at * 1000).toLocaleString('tr-TR') : 'N/A';
        const createdBy = htmlspecialchars(car.created_by_name || 'Bilinmiyor');
        const updatedBy = htmlspecialchars(car.updated_by_name || 'Bilinmiyor');
        const branchName = htmlspecialchars(car.branch_name || 'Bilinmiyor'); // Assuming branch_name might come from API or needs lookup

        // Map status to Turkish labels and CSS classes
        const statusLabels = {
            'waiting': 'Sırada',
            'washing': 'Yıkanıyor',
            'drying': 'Kurutuluyor',
            'ready': 'Hazır',
            'completed': 'Tamamlandı'
        };
        const statusLabel = statusLabels[car.status] || car.status;

        html += `
            <div class="car-card">
                <div class="car-header">
                    <div class="plate-number">${htmlspecialchars(car.plate)}</div>
                    <div class="status ${htmlspecialchars(car.status)}">${statusLabel}</div>
                </div>
                <div class="car-details">
                    <div class="detail-item">
                        <div class="detail-label">Müşteri</div>
                        <div class="detail-value">${htmlspecialchars(car.customer_name || 'N/A')}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Telefon</div>
                        <div class="detail-value">${htmlspecialchars(car.customer_phone || 'N/A')}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Hizmet</div>
                        <div class="detail-value">${htmlspecialchars(car.service_type || 'N/A')}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Fiyat</div>
                        <div class="detail-value">₺${htmlspecialchars(parseFloat(car.price).toFixed(2) || '0.00')}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Tahmini Bitiş</div>
                        <div class="detail-value">${car.estimated_time ? htmlspecialchars(car.estimated_time) + ' dk' : 'N/A'}</div>
                    </div>
                     <div class="detail-item">
                        <div class="detail-label">Şube</div>
                        <div class="detail-value">${branchName}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Kayıt Tarihi</div>
                        <div class="detail-value">${createdAt}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Kayıt Yapan</div>
                        <div class="detail-value">${createdBy}</div>
                    </div>
                     <div class="detail-item">
                        <div class="detail-label">Son Güncelleme</div>
                        <div class="detail-value">${updatedAt}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Güncelleyen</div>
                        <div class="detail-value">${updatedBy}</div>
                    </div>
                </div>`;
        if (showControls) {
            html += `
                <div class="status-controls">
                    <button class="status-btn ${car.status === 'waiting' ? 'active' : ''}" onclick="updateCarStatus(${car.id}, 'waiting')">Sırada</button>
                    <button class="status-btn ${car.status === 'washing' ? 'active' : ''}" onclick="updateCarStatus(${car.id}, 'washing')">Yıkanıyor</button>
                    <button class="status-btn ${car.status === 'drying' ? 'active' : ''}" onclick="updateCarStatus(${car.id}, 'drying')">Kurutuluyor</button>
                    <button class="status-btn ${car.status === 'ready' ? 'active' : ''}" onclick="updateCarStatus(${car.id}, 'ready')">Hazır</button>
                    <button class="status-btn ${car.status === 'completed' ? 'active' : ''}" onclick="updateCarStatus(${car.id}, 'completed')">Tamamlandı</button>
                    ${currentUser && currentUser.role === 'admin' ? `<button class="btn btn-danger" onclick="deleteCar(${car.id})">Sil</button>` : ''}
                </div>`;
        }
        html += `</div>`;
    });
    targetElement.innerHTML = html;
}

/**
 * Renders the list of feedbacks in the admin panel.
 */
export function renderFeedbackInbox() {
    const feedbackInbox = document.getElementById('feedbackInbox');
    if (!feedbackInbox) return;

    if (feedbacks.length === 0) {
        feedbackInbox.innerHTML = '<p class="no-feedback">Henüz hiç geri bildirim yok.</p>';
        return;
    }

    let html = '';
    feedbacks.forEach(feedback => {
        const createdAt = new Date(feedback.created_at * 1000).toLocaleString('tr-TR');
        const branchName = htmlspecialchars(feedback.branch_name || 'Genel'); // Assuming branch_name might come from API
        html += `
            <div class="feedback-item">
                <div class="feedback-header">
                    <strong>Plaka: ${htmlspecialchars(feedback.plate)}</strong>
                    <div class="feedback-rating">
                        ${'<span class="star active">⭐</span>'.repeat(feedback.rating)}
                        ${'<span class="star">⭐</span>'.repeat(5 - feedback.rating)}
                    </div>
                </div>
                <div class="feedback-meta">
                    <span>${htmlspecialchars(feedback.customer_name || 'Anonim')}</span>
                    <span>Tarih: ${createdAt}</span>
                    <span>Şube: ${branchName}</span>
                    <span>Durum: ${feedback.is_read == 1 ? 'Okundu' : 'Yeni'}</span>
                </div>
                <div class="feedback-comment">
                    ${htmlspecialchars(feedback.comment || 'Yorum yapılmamış.')}
                </div>
                <div class="feedback-actions">
                    ${feedback.is_read == 0 ? `<button class="btn btn-secondary" onclick="markFeedbackAsRead(${feedback.id})">Okundu İşaretle</button>` : ''}
                    ${currentUser && currentUser.role === 'admin' ? `<button class="btn btn-danger" onclick="deleteFeedback(${feedback.id})">Sil</button>` : ''}
                </div>
            </div>
        `;
    });
    feedbackInbox.innerHTML = html;
}

/**
 * Renders the list of users in the admin panel.
 */
export function renderUserList() {
    const userList = document.getElementById('userList');
    if (!userList) return;

    if (users.length === 0) {
        userList.innerHTML = '<p class="loading">Henüz hiç kullanıcı yok.</p>';
        return;
    }

    let html = `
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ad Soyad</th>
                    <th>E-posta</th>
                    <th>Rol</th>
                    <th>Şube</th>
                    <th>Durum</th>
                    <th>Kayıt Tarihi</th>
                    <th>Kayıt Yapan</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
    `;
    users.forEach(user => {
        const createdAt = new Date(user.created_at * 1000).toLocaleString('tr-TR');
        const createdBy = htmlspecialchars(user.created_by_name || 'Sistem');
        const branchName = htmlspecialchars(user.branch_name || 'Merkez/Genel');
        html += `
            <tr>
                <td>${htmlspecialchars(user.name)}</td>
                <td>${htmlspecialchars(user.email)}</td>
                <td>${htmlspecialchars(getRoleLabel(user.role))}</td>
                <td>${branchName}</td>
                <td>${user.is_active == 1 ? 'Aktif' : 'Pasif'}</td>
                <td>${createdAt}</td>
                <td>${createdBy}</td>
                <td>
                    <!-- Yönetici dışındaki kullanıcıları düzenleme/silme yetkisi -->
                    ${currentUser && currentUser.role === 'admin' && user.role !== 'admin' ? `
                        <button class="btn btn-secondary btn-sm" onclick="editUser('${user.firebase_uid}')">Düzenle</button>
                        <button class="btn btn-danger btn-sm" onclick="toggleUserActiveStatus('${user.firebase_uid}', ${user.is_active})">
                            ${user.is_active == 1 ? 'Pasif Yap' : 'Aktif Yap'}
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="deleteUser('${user.firebase_uid}')">Sil</button>
                    ` : (currentUser && currentUser.uid === user.firebase_uid ? `(Kendi Kullanıcınız)` : `(Yetkisiz)`)}
                </td>
            </tr>
        `;
    });
    html += '</tbody></table>';
    userList.innerHTML = html;
}

// Add these helper functions for user management (e.g., editUser, toggleUserActiveStatus, deleteUser)
// You would need to implement the backend API for these as well.
window.editUser = function(uid) {
    showNotification("Kullanıcı düzenleme özelliği henüz aktif değil.", "info");
    console.log("Edit user: " + uid);
};

window.toggleUserActiveStatus = async function(uid, currentStatus) {
    if (!currentUser || currentUser.role !== 'admin') {
        showNotification('Admin yetkisi gerekli', 'error');
        return;
    }
    // NOT: window.confirm() yerine özel bir modal pencere kullanmanız önerilir.
    // Ancak mevcut yapıyı korumak için burada window.confirm kullanılmıştır.
    if (!window.confirm('Kullanıcının aktiflik durumunu değiştirmek istediğinizden emin misiniz?')) {
        return;
    }
    try {
        showNotification("Kullanıcı durumu güncelleniyor...", "info");
        const response = await fetch('./api/update_user_status.php', { // Bu API uç noktasını oluşturmanız gerekecek
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ uid: uid, is_active: currentStatus == 1 ? 0 : 1 })
        });
        const result = await response.json();
        if (response.ok && result.success) {
            showNotification('Kullanıcı durumu güncellendi', 'success');
            await fetchUsers();
        } else {
            console.error('Kullanıcı durumu güncelleme hatası (PHP):', result.message || 'Bilinmeyen Hata');
            showNotification('İşlem başarısız: ' + (result.message || 'Bilinmeyen hata'), 'error');
        }
    }
    // Catch fetch network errors
    catch (error) {
        console.error('PHP backend\'e bağlanırken hata oluştu (Kullanıcı Durumu):', error);
        showNotification('Sunucuya bağlanırken hata oluştu.', 'error');
    }
};

window.deleteUser = async function(uid) {
    if (!currentUser || currentUser.role !== 'admin') {
        showNotification('Admin yetkisi gerekli', 'error');
        return;
    }
    // NOT: window.confirm() yerine özel bir modal pencere kullanmanız önerilir.
    // Ancak mevcut yapıyı korumak için burada window.confirm kullanılmıştır.
    if (!window.confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!')) {
        return;
    }
    try {
        showNotification("Kullanıcı siliniyor...", "info");
        const response = await fetch('./api/delete_user.php', { // Bu API uç noktasını oluşturmanız gerekecek
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ uid: uid })
        });
        const result = await response.json();
        if (response.ok && result.success) {
            showNotification('Kullanıcı başarıyla silindi', 'success');
            await fetchUsers();
            // İsteğe bağlı olarak, eğer backend bunu handle etmiyorsa Firebase Auth'tan da silebilirsiniz.
            // firebase.auth().getUser(uid).then(userRecord => userRecord.delete());
        } else {
            console.error('Kullanıcı silme hatası (PHP):', result.message || 'Bilinmeyen Hata');
            showNotification('Silme işlemi başarısız: ' + (result.message || 'Bilinmeyen hata'), 'error');
        }
    }
    // Catch fetch network errors
    catch (error) {
        console.error('PHP backend\'e bağlanırken hata oluştu (Kullanıcı Silme):', error);
        showNotification('Sunucuya bağlanırken hata oluştu.', 'error');
    }
};


/**
 * Renders the list of branches in the admin panel.
 */
export function renderBranchList() {
    const branchList = document.getElementById('branchList');
    if (!branchList) return;

    if (branches.length === 0) {
        branchList.innerHTML = '<p class="loading">Henüz hiç şube kaydı yok.</p>';
        return;
    }

    let html = `
        <table class="data-table">
            <thead>
                <tr>
                    <th>Şube Adı</th>
                    <th>Adres</th>
                    <th>Telefon</th>
                    <th>Durum</th>
                    <th>Kayıt Tarihi</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
    `;
    branches.forEach(branch => {
        const createdAt = new Date(branch.created_at * 1000).toLocaleString('tr-TR');
        html += `
            <tr>
                <td>${htmlspecialchars(branch.name)}</td>
                <td>${htmlspecialchars(branch.address || 'N/A')}</td>
                <td>${htmlspecialchars(branch.phone || 'N/A')}</td>
                <td>${branch.is_active == 1 ? 'Aktif' : 'Pasif'}</td>
                <td>${createdAt}</td>
                <td>
                    <button class="btn btn-secondary btn-sm" onclick="editBranch(${branch.id})">Düzenle</button>
                    <button class="btn btn-danger btn-sm" onclick="toggleBranchActiveStatus(${branch.id}, ${branch.is_active})">
                        ${branch.is_active == 1 ? 'Pasif Yap' : 'Aktif Yap'}
                    </button>
                    ${currentUser && currentUser.role === 'admin' ? `<button class="btn btn-danger btn-sm" onclick="deleteBranch(${branch.id})">Sil</button>` : ''}
                </td>
            </tr>
        `;
    });
    html += '</tbody></table>';
    branchList.innerHTML = html;
}

// Add these helper functions for branch management (editBranch, toggleBranchActiveStatus, deleteBranch)
// You would need to implement the backend API for these as well.
window.editBranch = function(branchId) {
    showNotification("Şube düzenleme özelliği henüz aktif değil.", "info");
    console.log("Edit branch: " + branchId);
};

window.toggleBranchActiveStatus = async function(branchId, currentStatus) {
    if (!currentUser || currentUser.role !== 'admin') {
        showNotification('Admin yetkisi gerekli', 'error');
        return;
    }
    // NOT: window.confirm() yerine özel bir modal pencere kullanmanız önerilir.
    // Ancak mevcut yapıyı korumak için burada window.confirm kullanılmıştır.
    if (!window.confirm('Şubenin aktiflik durumunu değiştirmek istediğinizden emin misiniz?')) {
        return;
    }
    try {
        showNotification("Şube durumu güncelleniyor...", "info");
        const response = await fetch('./api/update_branch_status.php', { // Bu API uç noktasını oluşturmanız gerekecek
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ branch_id: branchId, is_active: currentStatus == 1 ? 0 : 1 })
        });
        const result = await response.json();
        if (response.ok && result.success) {
            showNotification('Şube durumu güncellendi', 'success');
            await fetchBranches();
        } else {
            console.error('Şube durumu güncelleme hatası (PHP):', result.message || 'Bilinmeyen Hata');
            showNotification('İşlem başarısız: ' + (result.message || 'Bilinmeyen hata'), 'error');
        }
    }
    // Catch fetch network errors
    catch (error) {
        console.error('PHP backend\'e bağlanırken hata oluştu (Şube Durumu):', error);
        showNotification('Sunucuya bağlanırken hata oluştu.', 'error');
    }
};

window.deleteBranch = async function(branchId) {
    if (!currentUser || currentUser.role !== 'admin') {
        showNotification('Admin yetkisi gerekli', 'error');
        return;
    }
    // NOT: window.confirm() yerine özel bir modal pencere kullanmanız önerilir.
    // Ancak mevcut yapıyı korumak için burada window.confirm kullanılmıştır.
    if (!window.confirm('Bu şubeyi silmek istediğinizden emin misiniz? Bu işlem geri alınamaz ve bu şubeye ait tüm kullanıcılar ve araç kayıtları etkilenecektir.')) {
        return;
    }
    try {
        showNotification("Şube siliniyor...", "info");
        const response = await fetch('./api/delete_branch.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ branch_id: branchId })
        });
        const result = await response.json();
        if (response.ok && result.success) {
            showNotification('Şube başarıyla silindi', 'success');
            await fetchBranches();
        } else {
            console.error('Şube silme hatası (PHP):', result.message || 'Bilinmeyen Hata');
            showNotification('Silme işlemi başarısız: ' + (result.message || 'Bilinmeyen hata'), 'error');
        }
    }
    // Catch fetch network errors
    catch (error) {
        console.error('PHP backend\'e bağlanırken hata oluştu (Şube Silme):', error);
        showNotification('Sunucuya bağlanırken hata oluştu.', 'error');
    }
};


/**
 * Renders the list of services in the admin panel.
 */
export function renderServiceList() {
    const serviceList = document.getElementById('serviceList');
    if (!serviceList) return;

    if (services.length === 0) {
        serviceList.innerHTML = '<p class="loading">Henüz hiç hizmet kaydı yok.</p>';
        return;
    }

    let html = `
        <table class="data-table">
            <thead>
                <tr>
                    <th>Hizmet Adı</th>
                    <th>Fiyat (₺)</th>
                    <th>Tahmini Süre (dk)</th>
                    <th>Durum</th>
                    <th>Kayıt Tarihi</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
    `;
    services.forEach(service => {
        const createdAt = new Date(service.created_at * 1000).toLocaleString('tr-TR');
        html += `
            <tr>
                <td>${htmlspecialchars(service.type)}</td>
                <td>${htmlspecialchars(parseFloat(service.price).toFixed(2))}</td>
                <td>${htmlspecialchars(service.estimated_time || 'N/A')}</td>
                <td>${service.is_active == 1 ? 'Aktif' : 'Pasif'}</td>
                <td>${createdAt}</td>
                <td>
                    <button class="btn btn-secondary btn-sm" onclick="editService(${service.id})">Düzenle</button>
                    <button class="btn btn-danger btn-sm" onclick="toggleServiceActiveStatus(${service.id}, ${service.is_active})">
                        ${service.is_active == 1 ? 'Pasif Yap' : 'Aktif Yap'}
                    </button>
                    ${currentUser && currentUser.role === 'admin' ? `<button class="btn btn-danger btn-sm" onclick="deleteService(${service.id})">Sil</button>` : ''}
                </td>
            </tr>
        `;
    });
    html += '</tbody></table>';
    serviceList.innerHTML = html;
}

// Add these helper functions for service management (editService, toggleServiceActiveStatus)
// You would need to implement the backend API for these as well.
window.editService = function(serviceId) {
    showNotification("Hizmet düzenleme özelliği henüz aktif değil.", "info");
    console.log("Edit service: " + serviceId);
};

window.toggleServiceActiveStatus = async function(serviceId, currentStatus) {
    if (!currentUser || currentUser.role !== 'admin') {
        showNotification('Admin yetkisi gerekli', 'error');
        return;
    }
    // NOT: window.confirm() yerine özel bir modal pencere kullanmanız önerilir.
    // Ancak mevcut yapıyı korumak için burada window.confirm kullanılmıştır.
    if (!window.confirm('Hizmetin aktiflik durumunu değiştirmek istediğinizden emin misiniz?')) {
        return;
    }
    try {
        showNotification("Hizmet durumu güncelleniyor...", "info");
        const response = await fetch('./api/update_service_status.php', { // Bu API uç noktasını oluşturmanız gerekecek
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ service_id: serviceId, is_active: currentStatus == 1 ? 0 : 1 })
        });
        const result = await response.json();
        if (response.ok && result.success) {
            showNotification('Hizmet durumu güncellendi', 'success');
            await fetchServices();
        } else {
            console.error('Hizmet durumu güncelleme hatası (PHP):', result.message || 'Bilinmeyen Hata');
            showNotification('İşlem başarısız: ' + (result.message || 'Bilinmeyen hata'), 'error');
        }
    }
    // Catch fetch network errors
    catch (error) {
        console.error('PHP backend\'e bağlanırken hata oluştu (Hizmet Durumu):', error);
        showNotification('Sunucuya bağlanırken hata oluştu.', 'error');
    }
};

// Placeholder for reports generation (will be more complex with actual data)
export async function generateReports() {
    const reportTimeframe = document.getElementById('reportTimeframe')?.value || 'all';
    const reportBranchSelect = document.getElementById('reportBranchSelect')?.value || 'all';

    // Check if on reports page
    const reportsScreen = document.getElementById('reportsScreen');
    if (!reportsScreen || !reportsScreen.classList.contains('active')) {
        console.log("Not on reports screen, skipping report generation.");
        return;
    }

    const totalCarsEl = document.getElementById('totalCars');
    const activeCarsEl = document.getElementById('activeCars');
    const completedCarsEl = document.getElementById('completedCars');
    const totalRevenueEl = document.getElementById('totalRevenue');
    const avgRatingEl = document.getElementById('avgRating');
    const dailyCarsListEl = document.getElementById('dailyCarsList');
    const employeePerformanceListEl = document.getElementById('employeePerformanceList');
    const serviceProfitabilityListEl = document.getElementById('serviceProfitabilityList');

    // Clear previous data and show loading
    if (totalCarsEl) totalCarsEl.textContent = '0';
    if (activeCarsEl) activeCarsEl.textContent = '0';
    if (completedCarsEl) completedCarsEl.textContent = '0';
    if (totalRevenueEl) totalRevenueEl.textContent = '₺0';
    if (avgRatingEl) avgRatingEl.textContent = '0.0';

    if (dailyCarsListEl) dailyCarsListEl.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Raporlar yükleniyor...';
    if (employeePerformanceListEl) employeePerformanceListEl.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Yükleniyor...';
    if (serviceProfitabilityListEl) serviceProfitabilityListEl.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Yükleniyor...';


    // Fetch report data
    try {
        let url = './api/get_reports.php'; // Bu API uç noktasını oluşturmanız gerekecek
        const params = new URLSearchParams();
        params.append('timeframe', reportTimeframe);
        if (reportBranchSelect !== 'all') {
            params.append('branch_id', reportBranchSelect);
        }
        url += '?' + params.toString();

        const response = await fetch(url);
        const result = await response.json();

        if (response.ok && result.success) {
            const reports = result.data;
            if (totalCarsEl) totalCarsEl.textContent = reports.totalCars || 0;
            if (activeCarsEl) activeCarsEl.textContent = reports.activeCars || 0;
            if (completedCarsEl) completedCarsEl.textContent = reports.completedCars || 0;
            if (totalRevenueEl) totalRevenueEl.textContent = '₺' + (reports.totalRevenue ? parseFloat(reports.totalRevenue).toFixed(2) : '0.00');
            if (avgRatingEl) avgRatingEl.textContent = reports.avgRating ? parseFloat(reports.avgRating).toFixed(1) : '0.0';

            // Render detailed car records for reports
            if (dailyCarsListEl) {
                if (reports.detailedCars && reports.detailedCars.length > 0) {
                    // Reuse renderCarLists but without controls
                    renderCarLists(dailyCarsListEl, reports.detailedCars, false);
                } else {
                    dailyCarsListEl.innerHTML = '<p style="text-align: center; color: #777; padding: 20px;">Seçilen kriterlere uygun araç kaydı bulunamadı.</p>';
                }
            }

            // Render Employee Performance
            if (employeePerformanceListEl) {
                if (reports.employeePerformance && reports.employeePerformance.length > 0) {
                    let empHtml = '<table class="data-table"><thead><tr><th>Personel</th><th>Tamamlanan Araç</th><th>Ortalama Süre (dk)</th></tr></thead><tbody>';
                    reports.employeePerformance.forEach(emp => {
                        empHtml += `<tr><td>${htmlspecialchars(emp.employee_name)}</td><td>${emp.completed_cars}</td><td>${emp.avg_time ? emp.avg_time.toFixed(0) : 'N/A'}</td></tr>`;
                    });
                    empHtml += '</tbody></table>';
                    employeePerformanceListEl.innerHTML = empHtml;
                } else {
                    employeePerformanceListEl.innerHTML = '<p style="text-align: center; color: #777; padding: 20px;">Personel performans verisi bulunamadı.</p>';
                }
            }

            // Render Service Profitability
            if (serviceProfitabilityListEl) {
                if (reports.serviceProfitability && reports.serviceProfitability.length > 0) {
                    let serviceHtml = '<table class="data-table"><thead><tr><th>Hizmet Türü</th><th>Toplam Gelir (₺)</th><th>Ortalama Fiyat (₺)</th><th>Adet</th></tr></thead><tbody>';
                    reports.serviceProfitability.forEach(srv => {
                        serviceHtml += `<tr><td>${htmlspecialchars(srv.service_type)}</td><td>${srv.total_revenue ? srv.total_revenue.toFixed(2) : '0.00'}</td><td>${srv.avg_price ? srv.avg_price.toFixed(2) : '0.00'}</td><td>${srv.count}</td></tr>`;
                    });
                    serviceHtml += '</tbody></table>';
                    serviceProfitabilityListEl.innerHTML = serviceHtml;
                } else {
                    serviceProfitabilityListEl.innerHTML = '<p style="text-align: center; color: #777; padding: 20px;">Hizmet karlılık verisi bulunamadı.</p>';
                }
            }

            // Render Revenue Chart (requires Chart.js setup)
            const ctx = document.getElementById('revenueChart');
            if (ctx) {
                // Chart.js'in yüklenip yüklenmediğini kontrol et
                if (typeof Chart === 'undefined') {
                    console.error("Chart.js yüklenmemiş. Lütfen reports.php dosyanıza Chart.js CDN'ini ekleyin.");
                    // Kullanıcıya bildirim gösterebilirsiniz
                    showNotification("Grafik kütüphanesi yüklenemedi. Sayfayı yenileyin.", "error");
                    return;
                }
                // Var olan Chart instance'ı yok et
                if (window.revenueChartInstance) {
                    window.revenueChartInstance.destroy();
                }
                const labels = reports.revenueChartData?.labels || [];
                const data = reports.revenueChartData?.data || [];
                window.revenueChartInstance = new Chart(ctx, {
                    type: 'bar', // or 'line'
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Günlük Gelir (₺)',
                            data: data,
                            backgroundColor: 'rgba(102, 126, 234, 0.7)',
                            borderColor: 'rgba(102, 126, 234, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Gelir (₺)'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Tarih'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            label += new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(context.parsed.y);
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            }


            showNotification('Raporlar yüklendi', 'success');
        } else {
            console.error("Rapor verileri çekme hatası (PHP):", result.message || 'Bilinmeyen Hata');
            showNotification("Raporlar yüklenemedi: " + (result.message || 'Bilinmeyen hata'), "error");
            if (dailyCarsListEl) dailyCarsListEl.innerHTML = '<p style="text-align: center; color: #f44336; padding: 20px;">Raporlar yüklenirken bir hata oluştu.</p>';
            if (employeePerformanceListEl) employeePerformanceListEl.innerHTML = '<p style="text-align: center; color: #f44336; padding: 20px;">Raporlar yüklenirken bir hata oluştu.</p>';
            if (serviceProfitabilityListEl) serviceProfitabilityListEl.innerHTML = '<p style="text-align: center; color: #f44336; padding: 20px;">Raporlar yüklenirken bir hata oluştu.</p>';
        }
    }
    // Catch fetch network errors
    catch (error) {
        console.error("PHP backend'e bağlanırken hata oluştu (Raporlar):", error);
        showNotification('Sunucuya bağlanırken hata oluştu.', 'error');
        if (dailyCarsListEl) dailyCarsListEl.innerHTML = '<p style="text-align: center; color: #f44336; padding: 20px;">Sunucuya bağlanılamadı.</p>';
        if (employeePerformanceListEl) employeePerformanceListEl.innerHTML = '<p style="text-align: center; color: #f44336; padding: 20px;">Sunucuya bağlanılamadı.</p>';
        if (serviceProfitabilityListEl) serviceProfitabilityListEl.innerHTML = '<p style="text-align: center; color: #f44336; padding: 20px;">Sunucuya bağlanılamadı.</p>';
    }
}


// === UYGULAMA BAŞLANGICI ===

// Uygulama başlatıldığında Firebase'i ve event handler'ları başlat
async function initApp() {
    await initFirebase(); // Sadece Firebase Auth'u başlatacak

    // Sayfa yüklendiğinde mevcut URL'ye göre doğru ekranı göster
    const path = window.location.pathname.split('/').pop();
    const urlParams = new URLSearchParams(window.location.search);
    const screenParam = urlParams.get('screen');

    // index.php özelinde ekran geçişini yönet
    if (path === 'index.php' || path === '') { // path === '' durumu, anasayfaya direkt erişimde de index.php'nin yüklenmesi için
        if (screenParam === 'customer') {
            showScreen('customerScreen');
            // Customer screen için başlangıçta boş arama sonucu göster, veri çekme yok
        } else { // Default to login screen if no param or unknown param
            showScreen('loginScreen');
        }
    }
    // Diğer paneller kendi PHP dosyalarında aktif sınıfla yüklendiği için
    // burada showScreen çağırmaya gerek yok, sadece ilgili veri çekme işlemleri tetiklenecek.

    // İlk yüklemede tüm şubeleri ve hizmetleri çek (Admin ve Employee panellerindeki selectbox'lar için gerekli olabilir)
    // Bu, şube ve hizmet seçim kutularının doldurulması için önemlidir.
    await fetchBranches(); // Bu, updateBranchSelectOptions'ı çağırır
    await fetchServices(); // Bu, updateServiceSelectOptions'ı çağırır


    // Kullanıcının oturum durumu değiştiğinde ve currentUser objesi güncellendiğinde
    // ilgili sayfalardaki verileri çekme işlemini tetikle
    onAuthStateChanged(auth, async (user) => {
        console.log("Auth state changed in app.js:", user ? user.uid : "no user");
        if (user && currentUser) { // user ve currentUser'ın dolu olduğundan emin ol
            console.log("User logged in with role:", currentUser.role, "on path:", path);
            if (path === 'employee_panel.php') {
                await fetchCars(currentUser.branch_id);
            } else if (path === 'admin_panel.php') {
                await fetchFeedbacks();
                await fetchUsers();
                // fetchBranches ve fetchServices zaten global olarak çağrıldı. İsteğe bağlı olarak tekrar çağrılabilirler.
            } else if (path === 'reports.php') {
                await generateReports();
            }
        } else if (!user && currentUser === null) {
             // Kullanıcı çıkış yaptığında veya oturum yoksa
             console.log("No user or user logged out. Resetting UI.");
             // updateUI() ve showCustomerSearch() zaten firebase-config.js'de çağrılıyor.
        }
    });

    // Event listener'lar (eski kodunuzdan gelenler)
    document.addEventListener('click', function(e) {
        if (e.target.matches('#starRating .star')) {
            const rating = parseInt(e.target.getAttribute('data-rating'));
            setRating(rating);
        }
    });

    document.addEventListener('keypress', function(e) {
        // Sadece giriş ekranındayken Enter tuşu ile giriş yapma
        const loginScreen = document.getElementById('loginScreen');
        if (loginScreen && loginScreen.classList.contains('active') && e.key === 'Enter') {
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            if (email === document.activeElement || password === document.activeElement) {
                login();
            }
        }
    });
}

// Uygulamayı başlat (DOM içeriği yüklendikten sonra)
document.addEventListener('DOMContentLoaded', initApp);