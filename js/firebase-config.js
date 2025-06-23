// firebase-config.js
// app_signature_byeyn.1

// Firebase SDK'larından ihtiyacınız olan fonksiyonları içeri aktarın
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
import {
    getAuth,
    signInWithEmailAndPassword,
    createUserWithEmailAndPassword,
    signOut,
    onAuthStateChanged
} from "https://www.gstatic.com/firebasejs/10.12.2/firebase-auth.js";

// Sizin sağladığınız Firebase yapılandırma objesi
const firebaseConfig = {
    apiKey: "AIzaSyDj5s7kdG1ypyJGrRqmSgAZMpF3RIpwtv0",
    authDomain: "eynotoyikamamain.firebaseapp.com",
    projectId: "eynotoyikamamain",
    storageBucket: "eynotoyikamamain.firebasestorage.app",
    messagingSenderId: "1078531079980",
    appId: "1:1078531079980:web:ffc49a492fcead0a21b689",
    measurementId: "G-HVS683N1QB"
};

// Firebase uygulamasını başlat
export const app = initializeApp(firebaseConfig);
export const auth = getAuth(app); // Firebase Authentication servisini başlat

// Genel değişkenler
export let currentUser = null; // Giriş yapan kullanıcının bilgilerini tutar

// Firebase konfigürasyonunu doğrula ve hata mesajı göster
function validateFirebaseConfig() {
    if (!firebaseConfig.apiKey || !firebaseConfig.authDomain || !firebaseConfig.projectId) {
        return { isValid: false, message: 'Firebase yapılandırması eksik veya hatalı.' };
    }
    return { isValid: true };
}

// Konfigürasyon hatası durumunda kullanıcıya bilgi göster
function showConfigError(message) {
    document.body.innerHTML = '<div class="config-error">' +
        '<i class="fa-solid fa-triangle-exclamation error-icon"></i>' +
        '<h2>Sistem Yapılandırma Hatası!</h2>' +
        '<p>Lütfen Firebase projenizi doğru şekilde yapılandırdığınızdan emin olun.</p>' +
        '<div class="config-instructions">' +
        '<p><strong>Yapmanız Gerekenler:</strong></p>' +
        '<ul>' +
        '<li><i class="fa-solid fa-circle-info"></i> Firebase web konsoluna gidin: <a href="https://console.firebase.google.com" target="_blank">console.firebase.google.com</a></li>' +
        '<li><i class="fa-solid fa-circle-check"></i> Yeni proje oluşturun veya mevcut projenizi seçin</li>' +
        '<li><i class="fa-solid fa-gears"></i> Proje ayarları &rarr; Genel bölümüne gidin</li>' +
        '<li><i class="fa-solid fa-desktop"></i> "Uygulamalarınız" kısmında Web uygulaması ekleyin</li>' +
        '<li><i class="fa-solid fa-copy"></i> Firebase SDK snippetini kopyalayın</li>' +
        '<li><i class="fa-solid fa-envelope"></i> <strong>Authentication &rarr; Sign-in method</strong> kısmında Email/Password\'ü etkinleştirin</li>' +
        '<li><i class="fa-solid fa-database"></i> <s>Firestore Database oluşturun (Bu projede PHP/MySQL kullanılacaktır.)</s></li>' +
        '<li><i class="fa-solid fa-paste"></i> <strong>Kopyaladığınız config\'i firebase-config.js dosyasındaki firebaseConfig objesine yapıştırın</strong></li>' +
        '</ul>' +
        '</div>' +
        '<p style="color: #e74c3c; font-weight: bold;">Hata Mesajı: ' + message + '</p>' +
        '<button class="btn btn-primary" onclick="location.reload()">' +
        '<i class="fa-solid fa-refresh"></i> Sayfayı Yenile' +
        '</button>' +
        '</div>';
}

// Firebase'i başlat (sadece Auth için)
export async function initFirebase() {
    try {
        const configValidation = validateFirebaseConfig();
        if (!configValidation.isValid) {
            showConfigError(configValidation.message);
            return;
        }

        console.log("Firebase Auth başarıyla başlatıldı");

        onAuthStateChanged(auth, async (user) => {
            console.log("Auth state değişti:", user ? user.uid : "Çıkış yapıldı");
            if (user) {
                // Kullanıcı giriş yaptığında, PHP backend'den kullanıcı detaylarını çek
                // Bu kısım app.js'de tanımlanacak loadUserDataFromBackend fonksiyonunu çağıracak
                await window.loadUserDataFromBackend(user.uid, user.email);
            } else {
                currentUser = null;
                // updateUI ve showCustomerSearch fonksiyonlarının window objesinde tanımlı olduğundan emin olun
                if (typeof window.updateUI === 'function') window.updateUI();
                if (typeof window.showCustomerSearch === 'function') window.showCustomerSearch();
            }
        });

    } catch (error) {
        console.error("Firebase başlatma hatası:", error);
        let errorMessage = "Firebase bağlantı hatası";
        if (error.code === 'auth/api-key-not-valid') {
            errorMessage = "Geçersiz API anahtarı. Firebase konfigürasyonunu kontrol edin.";
        } else if (error.code === 'auth/invalid-api-key') {
            errorMessage = "Geçersiz API anahtarı formatı.";
        }
        showConfigError(errorMessage);
    }
}

// Firebase Auth fonksiyonlarını dışa aktar
export {
    signInWithEmailAndPassword,
    createUserWithEmailAndPassword,
    signOut,
    onAuthStateChanged
};

// Uygulamanın genel değişkenlerini ve fonksiyonlarını (şimdilik) window objesine ekleyelim
// Böylece app.js ve diğer modüller arasında iletişim sağlanır.
// Daha sonra daha düzenli bir olay dinleme/yönetim sistemi kurulabilir.
// Bu kontroller, fonksiyonların app.js'de tanımlanmadan önce çağrılması durumunda hata vermesini önler.
if (typeof window.showNotification === 'undefined') window.showNotification = () => console.log("showNotification not yet loaded");
if (typeof window.updateUI === 'undefined') window.updateUI = () => console.log("updateUI not yet loaded");
if (typeof window.showCustomerSearch === 'undefined') window.showCustomerSearch = () => console.log("showCustomerSearch not yet loaded");
if (typeof window.loadUserDataFromBackend === 'undefined') window.loadUserDataFromBackend = () => console.log("loadUserDataFromBackend not yet loaded");

// initFirebase(); // app.js'den çağrılacak