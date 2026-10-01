<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 1
// Last modified: 2026-05-01
// Part of: SPED LMS — Location Controller (Philippine Locations)

class LocationController {
    private $locations;

    public function __construct() {
        // Load Philippine location data (supports standard and flat hosting structures)
        $possiblePaths = [
            __DIR__ . '/../../public/data/philippines.json',
            __DIR__ . '/../../data/philippines.json',
            __DIR__ . '/../data/philippines.json',
            dirname(__DIR__, 2) . '/data/philippines.json',
            dirname(__DIR__, 2) . '/public/data/philippines.json',
        ];

        $jsonPath = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $jsonPath = $path;
                break;
            }
        }

        if ($jsonPath && ($data = file_get_contents($jsonPath))) {
            $decoded = json_decode($data, true);
            $this->locations = is_array($decoded) ? $decoded : $this->getDefaultLocations();
        } else {
            $this->locations = $this->getDefaultLocations();
        }
    }

    /**
     * Get all provinces
     */
    public function getProvinces() {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'provinces' => array_keys($this->locations)
        ]);
    }

    /**
     * Get cities by province
     */
    public function getCities($province = null) {
        header('Content-Type: application/json');
        
        // Get province from parameter or URL
        if (!$province && isset($_GET['province'])) {
            $province = $_GET['province'];
        }
        
        $province = trim(urldecode($province ?? ''));

        // Match province (case-insensitive fallback)
        $targetProvinceKey = null;
        if (isset($this->locations[$province])) {
            $targetProvinceKey = $province;
        } else {
            foreach ($this->locations as $pKey => $cities) {
                if (strcasecmp(trim($pKey), $province) === 0) {
                    $targetProvinceKey = $pKey;
                    break;
                }
            }
        }
        
        if ($targetProvinceKey !== null && isset($this->locations[$targetProvinceKey])) {
            echo json_encode([
                'success' => true,
                'province' => $targetProvinceKey,
                'cities' => array_keys($this->locations[$targetProvinceKey])
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Province not found',
                'province' => $province,
                'available' => array_keys($this->locations)
            ]);
        }
    }

    /**
     * Get barangays by city
     */
    public function getBarangays($province = null, $city = null) {
        header('Content-Type: application/json');
        
        // Get parameters from URL if not provided
        if (!$province && isset($_GET['province'])) {
            $province = $_GET['province'];
        }
        if (!$city && isset($_GET['city'])) {
            $city = $_GET['city'];
        }
        
        $province = trim(urldecode($province ?? ''));
        $city = trim(urldecode($city ?? ''));

        // Match province (case-insensitive fallback)
        $targetProvinceKey = null;
        if (isset($this->locations[$province])) {
            $targetProvinceKey = $province;
        } else {
            foreach ($this->locations as $pKey => $cities) {
                if (strcasecmp(trim($pKey), $province) === 0) {
                    $targetProvinceKey = $pKey;
                    break;
                }
            }
        }

        // Match city (case-insensitive fallback)
        $targetCityKey = null;
        if ($targetProvinceKey !== null && isset($this->locations[$targetProvinceKey])) {
            if (isset($this->locations[$targetProvinceKey][$city])) {
                $targetCityKey = $city;
            } else {
                foreach ($this->locations[$targetProvinceKey] as $cKey => $brgys) {
                    if (strcasecmp(trim($cKey), $city) === 0) {
                        $targetCityKey = $cKey;
                        break;
                    }
                }
            }
        }
        
        if ($targetProvinceKey !== null && $targetCityKey !== null && isset($this->locations[$targetProvinceKey][$targetCityKey])) {
            echo json_encode([
                'success' => true,
                'province' => $targetProvinceKey,
                'city' => $targetCityKey,
                'barangays' => $this->locations[$targetProvinceKey][$targetCityKey]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'City or Barangay list not found for selected location',
                'province' => $province,
                'city' => $city
            ]);
        }
    }

    /**
     * Default location data (sample - can be expanded)
     */
    private function getDefaultLocations() {
        return [
            'Cebu' => [
                'Cebu City' => [
                    'Apas', 'Banilad', 'Basak', 'Busay', 'Guadalupe', 'Lahug', 
                    'Mabolo', 'Talamban', 'Tisa', 'Zapatera'
                ],
                'Mandaue City' => [
                    'Alang-alang', 'Bakilid', 'Banilad', 'Basak', 'Cabancalan',
                    'Centro', 'Guizo', 'Ibabao-Estancia', 'Looc', 'Mantuyong'
                ],
                'Lapu-Lapu City' => [
                    'Agus', 'Babag', 'Bankal', 'Basak', 'Buaya', 'Canjulao',
                    'Caubian', 'Gun-ob', 'Ibo', 'Looc', 'Mactan', 'Maribago'
                ],
                'Talisay City' => [
                    'Biasong', 'Bulacao', 'Cadulawan', 'Camp IV', 'Cansojong',
                    'Dumlog', 'Jaclupan', 'Lagtang', 'Lawaan', 'Linao'
                ]
            ],
            'Metro Manila' => [
                'Manila' => [
                    'Ermita', 'Intramuros', 'Malate', 'Paco', 'Pandacan',
                    'Port Area', 'Quiapo', 'Sampaloc', 'San Miguel', 'Tondo'
                ],
                'Quezon City' => [
                    'Bagong Pag-asa', 'Batasan Hills', 'Commonwealth', 'Cubao',
                    'Diliman', 'Fairview', 'Kamuning', 'Novaliches', 'Project 4', 'Tandang Sora'
                ],
                'Makati City' => [
                    'Bel-Air', 'Carmona', 'Dasmariñas', 'Forbes Park', 'Guadalupe Nuevo',
                    'Magallanes', 'Poblacion', 'Rockwell', 'San Lorenzo', 'Urdaneta'
                ],
                'Pasig City' => [
                    'Bagong Ilog', 'Kapitolyo', 'Manggahan', 'Maybunga', 'Oranbo',
                    'Pinagbuhatan', 'Rosario', 'San Joaquin', 'Santolan', 'Ugong'
                ]
            ],
            'Davao del Sur' => [
                'Davao City' => [
                    'Agdao', 'Buhangin', 'Bunawan', 'Calinan', 'Matina',
                    'Paquibato', 'Poblacion', 'Talomo', 'Toril', 'Tugbok'
                ],
                'Digos City' => [
                    'Aplaya', 'Balabag', 'Cogon', 'Dawis', 'Goma',
                    'Kiagot', 'Lungag', 'Rizal', 'San Jose', 'Zone I'
                ]
            ]
        ];
    }
}
