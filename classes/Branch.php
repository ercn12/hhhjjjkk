<?php
// classes/Branch.php
// app_signature_byeyn.1

require_once __DIR__ . '/BaseModel.php';

/**
 * Şube Yönetimi Sınıfı
 * 'branches' tablosu üzerindeki CRUD işlemlerini yönetir.
 */
class Branch extends BaseModel {
    public function __construct() {
        parent::__construct('branches');
    }

    /**
     * Yeni bir şube oluşturur.
     * @param string $name Şube adı
     * @param string|null $address Şube adresi
     * @param string|null $phone Şube telefon numarası
     * @return int Eklenen şubenin ID'si
     */
    public function createNewBranch($name, $address = null, $phone = null) {
        $data = [
            'name' => $name,
            'address' => $address,
            'phone' => $phone,
            'created_at' => time() // Mevcut Unix timestamp
        ];
        return $this->create($data);
    }

    /**
     * Bir şubeyi siler.
     * @param int $branchId Şube ID'si
     * @return int Etkilenen satır sayısı
     */
    public function deleteBranch($branchId) {
        return $this->delete($branchId);
    }

    /**
     * Bir şubenin aktiflik durumunu günceller.
     * @param int $branchId Şube ID'si
     * @param int $isActive Yeni aktiflik durumu (0 veya 1)
     * @return int Etkilenen satır sayısı
     */
    public function updateBranchStatus($branchId, $isActive) {
        return $this->update($branchId, ['is_active' => $isActive]);
    }
}