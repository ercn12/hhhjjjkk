<?php
// api/get_cars.php
// app_signature_byeyn.1
require_once __DIR__ . '/_boilerplate.php';

// Sadece GET isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $plateSearch = isset($_GET['plate']) ? sanitizeInput($_GET['plate']) : null;
    $branchId = isset($_GET['branch_id']) ? (int)sanitizeInput($_GET['branch_id']) : null;

    $carModel = new Car();

    if ($plateSearch) {
        $cars = $carModel->searchCarsByPlate($plateSearch, $branchId);
    } else {
        // Tüm araçları getir (admin/yönetici rolleri için tüm şubeler, diğerleri için kendi şubeleri)
        // Gerçekte, bu isteği yapan kullanıcının yetkisine göre filtreleme yapılmalıdır.
        // Örneğin:
        // if ($currentUserRole === 'employee' || $currentUserRole === 'branch_manager') {
        //     $cars = $carModel->getAllCarsWithDetails($currentUserBranchId);
        // } else {
        //     $cars = $carModel->getAllCarsWithDetails($branchId); // branchId null ise tüm şubeler
        // }
        $cars = $carModel->getAllCarsWithDetails($branchId);
    }

    sendResponse(true, 'Araçlar başarıyla çekildi.', $cars);
} else {
    sendResponse(false, 'Geçersiz istek metodu.');
}
?>
