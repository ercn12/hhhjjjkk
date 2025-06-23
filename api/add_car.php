<?php
// api/add_car.php
// app_signature_byeyn.1
require_once __DIR__ . '/_boilerplate.php';

// Sadece POST isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plate = isset($input['plate']) ? sanitizeInput($input['plate']) : null;
    $customerName = isset($input['customer_name']) ? sanitizeInput($input['customer_name']) : null;
    $customerPhone = isset($input['customer_phone']) ? sanitizeInput($input['customer_phone']) : null;
    $serviceType = isset($input['service_type']) ? sanitizeInput($input['service_type']) : null;
    $estimatedTime = isset($input['estimated_time']) ? (int)sanitizeInput($input['estimated_time']) : null;
    $price = isset($input['price']) ? (float)sanitizeInput($input['price']) : null;
    $status = isset($input['status']) ? sanitizeInput($input['status']) : 'waiting'; // Varsayılan
    $createdByUserId = isset($input['created_by_user_id']) ? sanitizeInput($input['created_by_user_id']) : null;
    $branchId = isset($input['branch_id']) ? (int)sanitizeInput($input['branch_id']) : null; // Şube ID'si

    if (!$plate || !$serviceType || !$branchId || !$createdByUserId) {
        sendResponse(false, 'Plaka, hizmet türü, şube ve oluşturan kullanıcı bilgisi gerekli.');
    }

    // Yetkilendirme kontrolü
    // Frontend'den gelen 'created_by_user_id'nin gerçekten yetkili olduğunu doğrulamak önemlidir.
    // if (!isset($_SESSION['user_role']) || !Auth::hasPermission($_SESSION['user_role'], ['employee', 'branch_manager', 'operation_manager', 'admin'])) {
    //      sendResponse(false, 'Bu işlemi yapmaya yetkiniz yok.');
    // }

    try {
        $carModel = new Car();
        // Plakanın zaten aktif olup olmadığını kontrol et (isteğe bağlı)
        // Eğer aynı şubede aynı plaka aktif olamaz kuralı varsa burada kontrol edilebilir.
        // $existingCar = $carModel->searchCarsByPlate($plate, $branchId);
        // if ($existingCar && !empty($existingCar) && $existingCar[0]['status'] !== 'completed') {
        //     sendResponse(false, 'Bu plaka numarası zaten aktif bir işlemde.');
        // }

        $carData = [
            'plate' => $plate,
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'service_type' => $serviceType,
            'estimated_time' => $estimatedTime,
            'price' => $price,
            'status' => $status,
            'created_by_user_id' => $createdByUserId,
            'branch_id' => $branchId
        ];

        $newCarId = $carModel->createNewCar($carData);
        sendResponse(true, 'Araç başarıyla eklendi.', ['id' => $newCarId]);
    } catch (PDOException $e) {
        error_log("Araç ekleme hatası: " . $e->getMessage());
        sendResponse(false, 'Araç eklenirken bir hata oluştu: ' . $e->getMessage());
    }
} else {
    sendResponse(false, 'Geçersiz istek metodu.');
}
