<?php
// classes/Service.php
// app_signature_byeyn.1

require_once __DIR__ . '/BaseModel.php';

/**
 * Hizmet Yönetimi Sınıfı
 * 'services' tablosu üzerindeki CRUD işlemlerini yönetir.
 */
class Service extends BaseModel {
    public function __construct() {
        parent::__construct('services');
    }

    /**
     * Yeni bir hizmet oluşturur.
     * @param string $type Hizmet adı
     * @param float $price Fiyat
     * @param int|null $estimatedTime Tahmini süre (dakika)
     * @return int Eklenen hizmetin ID'si
     */
    public function createNewService($type, $price, $estimatedTime = null) {
        $data = [
            'type' => $type,
            'price' => $price,
            'estimated_time' => $estimatedTime,
            'created_at' => time() // Mevcut Unix timestamp
        ];
        return $this->create($data);
    }

    /**
     * Bir hizmeti siler.
     * @param int $serviceId Hizmet ID'si
     * @return int Etkilenen satır sayısı
     */
    public function deleteService($serviceId) {
        return $this->delete($serviceId);
    }

    /**
     * Bir hizmetin aktiflik durumunu günceller.
     * @param int $serviceId Hizmet ID'si
     * @param int $isActive Yeni aktiflik durumu (0 veya 1)
     * @return int Etkilenen satır sayısı
     */
    public function updateServiceStatus($serviceId, $isActive) {
        return $this->update($serviceId, ['is_active' => $isActive]);
    }
}
