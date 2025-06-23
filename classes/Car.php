<?php
// classes/Car.php
// app_signature_byeyn.1

require_once __DIR__ . '/BaseModel.php';

/**
 * Araç Yönetimi Sınıfı
 * 'cars' tablosu üzerindeki CRUD işlemlerini yönetir.
 */
class Car extends BaseModel {
    public function __construct() {
        parent::__construct('cars');
    }

    /**
     * Yeni bir araç kaydı oluşturur.
     * @param array $data Araç verileri (plate, customer_name, service_type, price, branch_id vb.)
     * @return int Eklenen aracın ID'si
     */
    public function createNewCar($data) {
        $data['created_at'] = time(); // Unix timestamp
        return $this->create($data);
    }

    /**
     * Bir aracın durumunu günceller.
     * @param int $carId Araç ID'si
     * @param string $newStatus Yeni durum
     * @param string $updatedByUserId Güncelleyen kullanıcının Firebase UID'si
     * @return int Etkilenen satır sayısı
     */
    public function updateCarStatus($carId, $newStatus, $updatedByUserId) {
        $data = [
            'status' => $newStatus,
            'updated_at' => time(), // Unix timestamp
            'updated_by_user_id' => $updatedByUserId
        ];
        return $this->update($carId, $data);
    }

    /**
     * Plakaya göre araçları arar.
     * @param string $plateSearch Plaka arama terimi
     * @param int|null $branchId Şube ID'si (opsiyonel)
     * @return array Araçlar dizisi
     */
    public function searchCarsByPlate($plateSearch, $branchId = null) {
        $sql = "SELECT c.*, b.name AS branch_name, u.name AS created_by_name
                FROM cars c
                LEFT JOIN branches b ON c.branch_id = b.id
                LEFT JOIN users u ON c.created_by_user_id = u.firebase_uid
                WHERE c.plate LIKE :plate_search
                ORDER BY c.created_at DESC";
        $params = [':plate_search' => '%' . $plateSearch . '%'];

        if ($branchId !== null) {
            $sql = "SELECT c.*, b.name AS branch_name, u.name AS created_by_name
                    FROM cars c
                    LEFT JOIN branches b ON c.branch_id = b.id
                    LEFT JOIN users u ON c.created_by_user_id = u.firebase_uid
                    WHERE c.plate LIKE :plate_search AND c.branch_id = :branch_id
                    ORDER BY c.created_at DESC";
            $params[':branch_id'] = $branchId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Tüm araçları getirir, şube ve oluşturan/güncelleyen kullanıcı adı ile birlikte.
     * @param int|null $branchId Şube ID'si (opsiyonel)
     * @param string $orderBy Sıralama sütunu
     * @param string $order 'ASC' veya 'DESC'
     * @return array Araçlar dizisi
     */
    public function getAllCarsWithDetails($branchId = null, $orderBy = 'c.created_at', $order = 'DESC') {
        $sql = "SELECT c.*, b.name AS branch_name, u_created.name AS created_by_name, u_updated.name AS updated_by_name
                FROM cars c
                LEFT JOIN branches b ON c.branch_id = b.id
                LEFT JOIN users u_created ON c.created_by_user_id = u_created.firebase_uid
                LEFT JOIN users u_updated ON c.updated_by_user_id = u_updated.firebase_uid";

        $params = [];
        if ($branchId !== null && $branchId !== 'all') { // 'all' seçeneğini de kontrol et
            $sql .= " WHERE c.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }
        $sql .= " ORDER BY {$orderBy} {$order}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        // Durum etiketlerini ekle
        $cars = $stmt->fetchAll();
        foreach ($cars as &$car) {
            $car['status_label'] = $this->getStatusLabel($car['status']);
        }
        return $cars;
    }

    /**
     * Durum etiketlerini döndürür.
     * @param string $status
     * @return string
     */
    private function getStatusLabel($status) {
        switch ($status) {
            case 'waiting': return 'Sırada';
            case 'washing': return 'Yıkanıyor';
            case 'drying': return 'Kurutuluyor';
            case 'ready': return 'Hazır';
            case 'completed': return 'Tamamlandı';
            default: return 'Bilinmiyor';
        }
    }

    /**
     * Eski araç kayıtlarını (örn: belirli bir süreden daha eski olanları) siler.
     * @param int $timestamp Silinecek kayıtların maksimum tarihi (Unix timestamp)
     * @return int Silinen kayıt sayısı
     */
    public function deleteOldCars($timestamp) {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE created_at < :timestamp");
        $stmt->execute([':timestamp' => $timestamp]);
        return $stmt->rowCount();
    }

    /**
     * Raporlama için araç verilerini zaman aralığına ve şubeye göre getirir.
     * @param string $timeframe Günlük, haftalık, aylık, yıllık, tüm zamanlar
     * @param int|null $branchId Şube ID'si
     * @return array
     */
    public function getReportData($timeframe, $branchId = null) {
        $currentTime = time();
        $startTime = 0;

        switch ($timeframe) {
            case 'daily':
                $startTime = strtotime('today', $currentTime);
                break;
            case 'weekly':
                $startTime = strtotime('last monday', $currentTime);
                break;
            case 'monthly':
                $startTime = strtotime('first day of this month', $currentTime);
                break;
            case 'yearly':
                $startTime = strtotime('first day of january this year', $currentTime);
                break;
            case 'all':
            default:
                $startTime = 0; // Tüm zamanlar
                break;
        }

        $sql = "SELECT c.*, b.name AS branch_name, u_created.name AS created_by_name, u_updated.name AS updated_by_name
                FROM cars c
                LEFT JOIN branches b ON c.branch_id = b.id
                LEFT JOIN users u_created ON c.created_by_user_id = u_created.firebase_uid
                LEFT JOIN users u_updated ON c.updated_by_user_id = u_updated.firebase_uid
                WHERE c.created_at >= :start_time";

        $params = [':start_time' => $startTime];

        if ($branchId !== null && $branchId !== 'all') {
            $sql .= " AND c.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }

        $sql .= " ORDER BY c.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $detailedCars = $stmt->fetchAll();

        $totalCars = count($detailedCars);
        $activeCars = 0;
        $completedCars = 0;
        $totalRevenue = 0;
        $revenueChartData = []; // Tarihe göre gelir
        $employeePerformance = []; // Personel bazında tamamlanan araç ve süre
        $serviceProfitability = []; // Hizmet bazında gelir ve adet

        foreach ($detailedCars as $car) {
            if ($car['status'] !== 'completed') {
                $activeCars++;
            } else {
                $completedCars++;
                if (isset($car['price'])) {
                    $totalRevenue += (float)$car['price'];

                    // Gelir Grafiği Verisi
                    $date = date('Y-m-d', $car['created_at']);
                    if (!isset($revenueChartData[$date])) {
                        $revenueChartData[$date] = 0;
                    }
                    $revenueChartData[$date] += (float)$car['price'];
                }

                // Personel Performansı
                if ($car['created_by_name']) {
                    $employeeName = $car['created_by_name'];
                    if (!isset($employeePerformance[$employeeName])) {
                        $employeePerformance[$employeeName] = ['completed_cars' => 0, 'total_time' => 0];
                    }
                    $employeePerformance[$employeeName]['completed_cars']++;
                    $employeePerformance[$employeeName]['total_time'] += $car['estimated_time'] ?? 0;
                }

                // Hizmet Karlılık Analizi
                if ($car['service_type']) {
                    $serviceType = $car['service_type'];
                    if (!isset($serviceProfitability[$serviceType])) {
                        $serviceProfitability[$serviceType] = ['total_revenue' => 0, 'total_price_sum' => 0, 'count' => 0];
                    }
                    $serviceProfitability[$serviceType]['total_revenue'] += (float)$car['price'];
                    $serviceProfitability[$serviceType]['total_price_sum'] += (float)$car['price']; // Ortalama için
                    $serviceProfitability[$serviceType]['count']++;
                }
            }
        }

        // Personel performansını sonlandır
        $finalEmployeePerformance = [];
        foreach ($employeePerformance as $name => $data) {
            $finalEmployeePerformance[] = [
                'employee_name' => $name,
                'completed_cars' => $data['completed_cars'],
                'avg_time' => $data['completed_cars'] > 0 ? $data['total_time'] / $data['completed_cars'] : 0,
            ];
        }

        // Hizmet karlılık analizini sonlandır
        $finalServiceProfitability = [];
        foreach ($serviceProfitability as $type => $data) {
            $finalServiceProfitability[] = [
                'service_type' => $type,
                'total_revenue' => $data['total_revenue'],
                'avg_price' => $data['count'] > 0 ? $data['total_price_sum'] / $data['count'] : 0,
                'count' => $data['count'],
            ];
        }


        // Gelir grafiği verisini tarihe göre sırala
        ksort($revenueChartData);
        $chartLabels = array_keys($revenueChartData);
        $chartValues = array_values($revenueChartData);

        // Ortalama puan (Feedback tablosundan çekilmeli, şimdilik placeholder)
        $avgRating = 0; // Bu kısım Feedback modelinden çekilmeli

        return [
            'totalCars' => $totalCars,
            'activeCars' => $activeCars,
            'completedCars' => $completedCars,
            'totalRevenue' => $totalRevenue,
            'avgRating' => $avgRating,
            'detailedCars' => $detailedCars,
            'revenueChartData' => ['labels' => $chartLabels, 'data' => $chartValues],
            'employeePerformance' => $finalEmployeePerformance,
            'serviceProfitability' => $finalServiceProfitability,
        ];
    }
}