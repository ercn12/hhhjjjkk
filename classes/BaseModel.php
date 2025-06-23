<?php
// classes/BaseModel.php
// app_signature_byeyn.1

// Veritabanı bağlantı fonksiyonunu dahil et
require_once __DIR__ . '/../config/database.php';

/**
 * Temel Model Sınıfı
 * Diğer tüm model sınıfları (Car, User, Feedback vb.) bu sınıftan türeyecektir.
 * Ortak veritabanı etkileşim fonksiyonlarını içerir.
 */
class BaseModel {
    protected $pdo;
    protected $table;

    public function __construct($table) {
        // connectDB fonksiyonunun bir PDOException fırlatabileceğini unutmayın
        try {
            $this->pdo = connectDB();
            $this->table = $table;
        } catch (PDOException $e) {
            // Veritabanı bağlantı hatasını burada ele alabiliriz
            // veya çağıran kodun ele alması için tekrar fırlatabiliriz.
            // API dosyalarındaki try-catch blokları bu exception'ı yakalayacaktır.
            throw $e;
        }
    }

    /**
     * Tüm kayıtları getirir.
     * @param string $orderBy Sıralama sütunu
     * @param string $order 'ASC' veya 'DESC'
     * @return array Kayıtlar dizisi
     */
    public function getAll($orderBy = 'id', $order = 'ASC') {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} ORDER BY {$orderBy} {$order}");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * ID'ye göre tek bir kayıt getirir.
     * @param int $id Kayıt ID'si
     * @return array|false Kayıt verisi veya false
     */
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Belirli bir sütuna göre kayıtları filtreler.
     * @param string $column Sütun adı
     * @param mixed $value Değer
     * @param string $orderBy Sıralama sütunu
     * @param string $order 'ASC' veya 'DESC'
     * @return array Kayıtlar dizisi
     */
    public function getByColumn($column, $value, $orderBy = 'id', $order = 'ASC') {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$column} = :value ORDER BY {$orderBy} {$order}");
        $stmt->execute([':value' => $value]);
        return $stmt->fetchAll();
    }

    /**
     * Yeni bir kayıt ekler.
     * @param array $data Eklenecek veriler (sütun_adı => değer)
     * @return int Eklenen kaydın ID'si
     */
    public function create($data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }

    /**
     * Mevcut bir kaydı günceller.
     * @param int $id Güncellenecek kaydın ID'si
     * @param array $data Güncellenecek veriler (sütun_adı => değer)
     * @return int Etkilenen satır sayısı
     */
    public function update($id, $data) {
        $setClauses = [];
        foreach ($data as $key => $value) {
            $setClauses[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $setClauses);
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $data[':id'] = $id; // ID'yi de parametrelere ekle
        $stmt->execute($data);
        return $stmt->rowCount();
    }

    /**
     * Bir kaydı siler.
     * @param int $id Silinecek kaydın ID'si
     * @return int Etkilenen satır sayısı
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }
}