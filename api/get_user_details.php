<?php
// api/get_user_details.php
// app_signature_byeyn.1
require_once __DIR__ . '/_boilerplate.php';

// Sadece POST isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firebaseUid = isset($input['uid']) ? sanitizeInput($input['uid']) : null;
    $email = isset($input['email']) ? sanitizeInput($input['email']) : null;

    if (!$firebaseUid) {
        sendResponse(false, 'Firebase UID gerekli.');
    }

    // Auth sınıfını kullan (User değil)
    $auth = new Auth();
    $user = $auth->getUserByFirebaseUid($firebaseUid);

    if ($user) {
        // Kullanıcı bulundu, şube adını da ekleyelim
        if ($user['branch_id']) {
            $branchModel = new Branch();
            $branch = $branchModel->getById($user['branch_id']);
            $user['branch_name'] = $branch ? $branch['name'] : 'Bilinmiyor';
        } else {
            $user['branch_name'] = 'Genel Merkez'; // Şubesi olmayanlar için
        }
        sendResponse(true, 'Kullanıcı bilgileri başarıyla çekildi.', $user);
    } else {
        sendResponse(false, 'Kullanıcı veritabanında bulunamadı.');
    }
} else {
    sendResponse(false, 'Geçersiz istek metodu.');
}
?>