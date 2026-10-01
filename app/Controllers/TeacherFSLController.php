<?php
// Part of: SignED — Teacher FSL Training & Guides Controller
// Last modified: 2026-08-27

require_once __DIR__ . '/../Models/TeacherFSLModel.php';

class TeacherFSLController {
    private TeacherFSLModel $model;
    private int $userId;
    private string $userRole;
    private string $basePath;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login');
            exit;
        }

        $this->userId = (int)$_SESSION['user_id'];
        $this->userRole = $_SESSION['role'] ?? 'user';
        $this->basePath = defined('BASE_PATH') ? BASE_PATH : '';
        $this->model = new TeacherFSLModel();
    }

    /**
     * Teacher FSL Training Portal
     */
    public function index(): void {
        $category = $_GET['category'] ?? null;
        $search = $_GET['search'] ?? '';

        $modules = $this->model->getModules($category, $search);
        $categories = $this->model->getCategories();
        $basePath = $this->basePath;
        $role = $this->userRole;

        require __DIR__ . '/../Views/training/fsl_index.php';
    }

    /**
     * Printable FSL Classroom Reference Cards
     */
    public function printGuide(): void {
        $modules = $this->model->getModules();
        $basePath = $this->basePath;
        require __DIR__ . '/../Views/training/printable_guide.php';
    }
}
