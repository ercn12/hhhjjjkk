<?php
// api/get_users.php
// app_signature_byeyn.1
require_once __DIR__ . '/_boilerplate.php';

// Sadece GET isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Yetkilendirme kontrolü
    // Bu API uç noktasına erişen kullanıcının rolünü kontrol etmelisiniz.
    // Örneğin, oturum açmış kullanıcının rolünü alıp buna göre izin vermek gibi.
    // Şimdilik basitlik adına kontrol yok ama gerçek projede olmalı.

    $userModel = new User();
    $users = $userModel->getAllUsersWithBranchName(); // Şube adları ile birlikte getir

    sendResponse(true, 'Kullanıcılar başarıyla çekildi.', $users);
} else {
    sendResponse(false, 'Geçersiz istek metodu.');
}
?>
