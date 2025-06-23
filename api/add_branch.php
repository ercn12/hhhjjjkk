<?php
// api/add_branch.php
// app_signature_byeyn.1
require_once __DIR__ . '/_boilerplate.php';

// Sadece POST isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($input['name']) ? sanitizeInput($input['name']) : null;
    $address = isset($input['address']) ? sanitizeInput($input['address']) : null;
    $phone = isset($input['phone']) ? sanitizeInput($input['phone']) : null;

    if (!$name) {
        sendResponse(false, 'Şube adı gerekli.');
    }

    // Yetkilendirme kontrolü (Sadece admin yetkisine sahip kullanıcılar ekleyebilir)
    // if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    //      sendResponse(false, 'Bu işlemi yapmaya yetkiniz yok.');
    // }

    try {
        $branchModel = new Branch();
        $newBranchId = $branchModel->createNewBranch($name, $address, $phone);
        sendResponse(true, 'Şube başarıyla eklendi.', ['id' => $newBranchId]);
    } catch (PDOException $e) {
        if ($e->getCode() == '23000' && strpos($e->getMessage(), 'Duplicate entry') !== false) {
            sendResponse(false, 'Bu şube adı zaten mevcut.');
        } else {
            error_log("Şube ekleme hatası: " . $e->getMessage());
            sendResponse(false, 'Şube eklenirken bir hata oluştu: ' . $e->getMessage());
        }
    }
} else {
    sendResponse(false, 'Geçersiz istek metodu.');
}
