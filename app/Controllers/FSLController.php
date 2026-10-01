<?php
// Part of: SignED — FSL Vocabulary Controller & API
// Last modified: 2026-08-27

require_once __DIR__ . '/../Models/FSLModel.php';

class FSLController {
    private FSLModel $model;
    private string $basePath;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new FSLModel();
        $this->basePath = defined('BASE_PATH') ? BASE_PATH : '';
    }

    /**
     * AJAX Endpoint: /api/fsl/lookup?word=...
     */
    public function lookup(): void {
        header('Content-Type: application/json');
        $word = $_GET['word'] ?? '';

        if (empty($word)) {
            echo json_encode(['success' => false, 'message' => 'No word provided.']);
            exit;
        }

        $result = $this->model->lookup($word);

        $buildUrl = function(?string $path): ?string {
            if (empty($path)) return null;
            $trimmed = trim($path);
            if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://') || str_starts_with($trimmed, 'data:')) {
                return $trimmed;
            }
            $base = rtrim($this->basePath, '/');
            $cleanPath = '/' . ltrim($trimmed, '/');
            if (!empty($base) && str_starts_with($cleanPath, $base . '/')) {
                return $cleanPath;
            }
            return $base . $cleanPath;
        };

        if ($result) {
            echo json_encode([
                'success'     => true,
                'word'        => $result['word'],
                'category'    => $result['category'],
                'description' => $result['description'] ?? '',
                'video_path'  => $buildUrl($result['video_path'] ?? null),
                'gif_path'    => $buildUrl($result['gif_path'] ?? null),
                'base_path'   => $this->basePath
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'word'    => $word,
                'message' => 'Word not found in FSL vocabulary.'
            ]);
        }
        exit;
    }

    /**
     * AJAX Endpoint: /api/fsl/dictionary
     */
    public function dictionary(): void {
        header('Content-Type: application/json');
        $category = $_GET['category'] ?? null;
        $search = $_GET['search'] ?? '';

        $list = $this->model->getAll($category, $search);
        $categories = $this->model->getCategories();

        echo json_encode([
            'success'    => true,
            'categories' => $categories,
            'data'       => $list
        ]);
        exit;
    }

    /**
     * View FSL Teacher / Learner Dictionary Page
     */
    public function index(): void {
        $category = $_GET['category'] ?? null;
        $search = $_GET['search'] ?? '';

        $words = $this->model->getAll($category, $search);
        $categories = $this->model->getCategories();
        $basePath = $this->basePath;

        require __DIR__ . '/../Views/learning/fsl_dictionary.php';
    }
}
