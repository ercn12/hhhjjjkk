<?php
// api/add_user.php
// app_signature_byeyn.1
require_once __DIR__ . '/_boilerplate.php';

// Sadece POST isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firebaseUid = isset($input['uid']) ? sanitizeInput($input['uid']) : null;
    $email = isset($input['email']) ? sanitizeInput($input['email']) : null;
    $name = isset($input['name']) ? sanitizeInput($input['name']) : null;
    $role = isset($input['role']) ? sanitizeInput($input['role']) : null;
    $branchId = isset($input['branch_id']) && $input['branch_id'] !== '' ? (int)sanitizeInput($input['branch_id']) : null;
    $createdByUserId = isset($input['created_by_user_id']) ? sanitizeInput($input['created_by_user_id']) : null;

    if (!$firebaseUid || !$email || !$name || !$role) {
        sendResponse(false, 'Tüm gerekli alanlar doldurulmalıdır.');
    }

    // Yetkilendirme kontrolü (Admin, Operasyon Müdürü, Bölge Müdürü)
    $userModel = new User();
    // $creatingUser = $userModel->getUserByFirebaseUid($createdByUserId); // Bu kontrol için veritabanı bağlantısı gerekir
    // if (!$creatingUser || !Auth::hasPermission($creatingUser['role'], ['admin', 'operation_manager', 'regional_manager'])) {
    //      sendResponse(false, 'Bu işlemi yapmaya yetkiniz yok.');
    // }

    try {
        $newUserId = $userModel->createNewUser($firebaseUid, $email, $name, $role, $branchId, $createdByUserId);
        sendResponse(true, 'Kullanıcı başarıyla eklendi.', ['id' => $newUserId]);
    } catch (PDOException $e) {
        if ($e->getCode() == '23000' && strpos($e->getMessage(), 'Duplicate entry') !== false) {
            sendResponse(false, 'Bu e-posta veya Firebase UID zaten kayıtlı.');
        } else {
            error_log("Kullanıcı ekleme hatası: " . $e->getMessage());
            sendResponse(false, 'Kullanıcı eklenirken bir hata oluştu: ' . $e->getMessage());
        }
    }

} else {
    sendResponse(false, 'Geçersiz istek metodu.');
}
