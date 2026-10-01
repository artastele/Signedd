<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 6
// Last modified: 2026-06-01
// Part of: SPED LMS — IEP Implementation Controller

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../Models/LessonPlanModel.php';
require_once __DIR__ . '/../Models/NotificationModel.php';
require_once __DIR__ . '/../Models/IEPModel.php';

class IEPImplementationController {

    private LessonPlanModel   $model;
    private NotificationModel $notifModel;
    private int    $userId;
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
        $this->userId     = (int) $_SESSION['user_id'];
        $this->userRole   = $_SESSION['role'] ?? '';
        $this->basePath   = defined('BASE_PATH') ? BASE_PATH : '';
        $this->model      = new LessonPlanModel();
        $this->notifModel = new NotificationModel();
    }

    // ============================================================
    // INDEX — list students with signed IEPs
    // ============================================================

    public function index() {
        $students      = $this->model->getSignedIEPsForTeacher($this->userId);
        $totalStudents = $this->model->countStudentsForTeacher($this->userId);
        $published     = $this->model->countPublishedForTeacher($this->userId);
        $draftCount    = $this->model->countDraftForTeacher($this->userId);
        $pending       = $this->model->countPendingSubmissionsForTeacher($this->userId);
        $basePath      = $this->basePath;

        require_once __DIR__ . '/../Views/iep_implementation/index.php';
    }

    // ============================================================
    // WORKSPACE — full workspace for one IEP
    // ============================================================

    public function workspace($iepId) {
        $iepId = (int) $iepId;
        $iep   = $this->model->getIepById($iepId);

        if (!$iep) {
            $_SESSION['error'] = 'IEP record not found.';
            header('Location: ' . $this->basePath . '/iep/implementation');
            exit;
        }

        if (!in_array($iep['status'] ?? '', ['signed', 'locked'], true)) {
            $_SESSION['error'] = 'Cannot open teaching workspace: IEP must be signed and finalized (Process 5) before implementation begins.';
            header('Location: ' . $this->basePath . '/iep');
            exit;
        }

        $lessonPlans = $this->model->getByIepId($iepId);
        $materials   = $this->model->getMaterialsByIepId($iepId);
        $activities  = $this->model->getActivitiesByIepId($iepId);
        $signatories = $this->model->getSignatories($iepId);
        
        $submissionsByLp = [];
        foreach ($lessonPlans as $lp) {
            $submissionsByLp[$lp['id']] = $this->model->getSubmissionsForLessonPlan($lp['id']);
        }

        $iepModel       = new IEPModel();
        $pdspDomainRows = $iepModel->getPdspDomainRows((int) ($iep['pdsp_id'] ?? 0));
        $iepLinkedLessonPlanIds = $iepModel->getLessonPlanIdsLinkedToIep($iepId);
        $iepFull = $iepModel->findById($iepId);
        if ($iepFull) {
            $iep['pdsp_signed_document_path'] = $iepFull['pdsp_signed_document_path'] ?? null;
        }

        // Fetch student's learning track and traditional records (Stage 2)
        $db = Database::getInstance()->getConnection();
        $studentId = (int)($iep['student_id'] ?? 0);
        $stmtTrack = $db->prepare("SELECT learning_track, lms_invite_status FROM student_records WHERE id = :sid LIMIT 1");
        $stmtTrack->execute(['sid' => $studentId]);
        $studentTrackInfo = $stmtTrack->fetch(PDO::FETCH_ASSOC) ?: ['learning_track' => 'unassigned', 'lms_invite_status' => 'none'];

        // Fetch traditional documents (DLL, physical IEP, progress notes)
        $stmtTrad = $db->prepare("
            SELECT tid.*, u.name as uploaded_by_name 
            FROM traditional_iep_documents tid
            LEFT JOIN users u ON tid.uploaded_by = u.id
            WHERE tid.student_id = :sid
            ORDER BY tid.created_at DESC
        ");
        $stmtTrad->execute(['sid' => $studentId]);
        $traditionalDocs = $stmtTrad->fetchAll(PDO::FETCH_ASSOC);

        $basePath    = $this->basePath;

        require_once __DIR__ . '/../Views/iep_implementation/workspace.php';
    }

    // ============================================================
    // CREATE LESSON PLAN (POST AJAX JSON)
    // ============================================================

    public function createLessonPlan() {
        header('Content-Type: application/json');
        try {
            $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            $title          = trim($body['title']           ?? '');
            $pdspDomain     = trim($body['pdsp_domain']     ?? '');
            $assignmentType = trim($body['assignment_type'] ?? '');
            $iepId          = (int) ($body['iep_id']        ?? 0);
            $studentId      = !empty($body['student_id']) ? (int) $body['student_id'] : null;

            if (!$title || !$pdspDomain || !$assignmentType || !$iepId) {
                echo json_encode(['success' => false, 'message' => 'Title, PDSP domain, assignment type, and IEP are required.']);
                exit;
            }

            $validDomains = [
                'perceptuo_cognitive', 'psychosocial', 'socio_emotional',
                'psychomotor', 'daily_living_skills', 'communication_language'
            ];
            if (!in_array($pdspDomain, $validDomains)) {
                echo json_encode(['success' => false, 'message' => 'Invalid PDSP domain.']);
                exit;
            }

            if (!in_array($assignmentType, ['individual', 'shared'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid assignment type.']);
                exit;
            }

            $lpId = $this->model->create([
                'iep_id'          => $iepId,
                'student_id'      => $assignmentType === 'individual' ? $studentId : null,
                'created_by'      => $this->userId,
                'title'           => $title,
                'pdsp_domain'     => $pdspDomain,
                'assignment_type' => $assignmentType,
            ]);

            if (!$lpId) {
                echo json_encode(['success' => false, 'message' => 'Failed to create lesson plan.']);
                exit;
            }

            // Assign to student(s)
            if ($assignmentType === 'individual' && $studentId) {
                $this->model->assignToStudent($lpId, $studentId, $this->userId);
            } else {
                $this->model->assignToAllLearners($lpId, $iepId, $this->userId);
            }

            echo json_encode(['success' => true, 'lesson_plan_id' => $lpId]);
        } catch (Throwable $e) {
            error_log('createLessonPlan error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }

    // ============================================================
    // UPLOAD LESSON DOCUMENT (POST AJAX multipart)
    // ============================================================

    public function uploadLessonDoc() {
        header('Content-Type: application/json');
        try {
            $iepId        = (int) ($_POST['iep_id']        ?? 0);
            $lessonPlanId = (int) ($_POST['lesson_plan_id'] ?? 0);

            if (!$iepId || !$lessonPlanId) {
                echo json_encode(['success' => false, 'message' => 'Missing iep_id or lesson_plan_id.']);
                exit;
            }

            $lp = $this->model->findById($lessonPlanId);
            if (!$lp) {
                echo json_encode(['success' => false, 'message' => 'Lesson plan not found.']);
                exit;
            }

            if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error.']);
                exit;
            }

            $file    = $_FILES['document'];
            $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'pdf'];

            if (!in_array($ext, $allowed)) {
                echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, and PDF files are allowed.']);
                exit;
            }

            if ($file['size'] > 10 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'File must be under 10MB.']);
                exit;
            }

            $studentId = $lp['student_id'] ?? $iepId;
            $uploadDir = function_exists('public_path') ? public_path('uploads/lesson_plans/' . $studentId . '/') : (__DIR__ . '/../../public/uploads/lesson_plans/' . $studentId . '/');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = 'lp_' . $lessonPlanId . '_' . time() . '.' . $ext;
            $fullPath = $uploadDir . $fileName;

            if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
                echo json_encode(['success' => false, 'message' => 'Failed to save file.']);
                exit;
            }

            $relativePath = 'uploads/lesson_plans/' . $studentId . '/' . $fileName;
            $this->model->update($lessonPlanId, ['document_path' => $relativePath]);

            echo json_encode(['success' => true, 'path' => $relativePath]);
        } catch (Throwable $e) {
            error_log('uploadLessonDoc error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }

    // ============================================================
    // INTERACTIVE LESSON PAGES (VLE / Moodle Multi-Page)
    // ============================================================

    public function getLessonPagesJson($lessonPlanId): void {
        header('Content-Type: application/json');
        try {
            $lpId = (int)$lessonPlanId;
            $pages = $this->model->getPagesByLessonPlan($lpId);
            echo json_encode(['success' => true, 'pages' => $pages]);
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function addLessonPage($lessonPlanId): void {
        header('Content-Type: application/json');
        try {
            $lpId = (int)$lessonPlanId;
            if (!$lpId) {
                echo json_encode(['success' => false, 'message' => 'Invalid lesson plan ID.']);
                exit;
            }

            $title          = trim($_POST['title'] ?? '');
            $content        = trim($_POST['content'] ?? '');
            $guideQuestions = trim($_POST['guide_questions'] ?? '');
            $mediaType      = trim($_POST['media_type'] ?? 'none');
            $mediaPath      = trim($_POST['media_path'] ?? $_POST['media_url'] ?? '');

            if (empty($title)) {
                echo json_encode(['success' => false, 'message' => 'Page title is required.']);
                exit;
            }

            // Handle file upload if provided
            if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['media_file'];
                $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'mp4', 'webm', 'ogg', 'mov'];
                if (in_array($ext, $allowed)) {
                    $uploadDir = function_exists('public_path') ? public_path('uploads/lesson_pages/' . $lpId . '/') : (__DIR__ . '/../../public/uploads/lesson_pages/' . $lpId . '/');
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $fileName = 'page_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                    move_uploaded_file($file['tmp_name'], $uploadDir . $fileName);
                    $mediaPath = 'uploads/lesson_pages/' . $lpId . '/' . $fileName;

                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        $mediaType = 'image';
                    } elseif (in_array($ext, ['mp4', 'webm', 'ogg', 'mov'])) {
                        $mediaType = 'video';
                    } else {
                        $mediaType = 'file';
                    }
                }
            }

            $pageId = $this->model->createPage([
                'lesson_plan_id'  => $lpId,
                'title'           => $title,
                'content'         => $content,
                'guide_questions' => !empty($guideQuestions) ? $guideQuestions : null,
                'media_type'      => $mediaType,
                'media_path'      => !empty($mediaPath) ? $mediaPath : null
            ]);

            $page = $this->model->getPageById($pageId);
            echo json_encode(['success' => true, 'page_id' => $pageId, 'page' => $page, 'message' => 'Slide created successfully!']);
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function updateLessonPage($pageId): void {
        header('Content-Type: application/json');
        try {
            $pId = (int)$pageId;
            $existing = $this->model->getPageById($pId);
            if (!$existing) {
                echo json_encode(['success' => false, 'message' => 'Page not found.']);
                exit;
            }

            $title          = trim($_POST['title'] ?? '');
            $content        = trim($_POST['content'] ?? '');
            $guideQuestions = trim($_POST['guide_questions'] ?? '');
            $mediaType      = trim($_POST['media_type'] ?? $existing['media_type'] ?? 'none');
            $mediaPath      = trim($_POST['media_path'] ?? $_POST['media_url'] ?? $existing['media_path'] ?? '');

            if (empty($title)) {
                echo json_encode(['success' => false, 'message' => 'Page title is required.']);
                exit;
            }

            // Handle file upload if provided
            if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['media_file'];
                $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'mp4', 'webm', 'ogg', 'mov'];
                if (in_array($ext, $allowed)) {
                    $lpId = (int)$existing['lesson_plan_id'];
                    $uploadDir = function_exists('public_path') ? public_path('uploads/lesson_pages/' . $lpId . '/') : (__DIR__ . '/../../public/uploads/lesson_pages/' . $lpId . '/');
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $fileName = 'page_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                    move_uploaded_file($file['tmp_name'], $uploadDir . $fileName);
                    $mediaPath = 'uploads/lesson_pages/' . $lpId . '/' . $fileName;

                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        $mediaType = 'image';
                    } elseif (in_array($ext, ['mp4', 'webm', 'ogg', 'mov'])) {
                        $mediaType = 'video';
                    } else {
                        $mediaType = 'file';
                    }
                }
            }

            $ok = $this->model->updatePage($pId, [
                'title'           => $title,
                'content'         => $content,
                'guide_questions' => !empty($guideQuestions) ? $guideQuestions : null,
                'media_type'      => $mediaType,
                'media_path'      => !empty($mediaPath) ? $mediaPath : null
            ]);

            $page = $this->model->getPageById($pId);
            echo json_encode(['success' => true, 'page' => $page, 'message' => 'Slide updated successfully!']);
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function deleteLessonPage($pageId): void {
        header('Content-Type: application/json');
        try {
            $pId = (int)$pageId;
            $res = $this->model->deletePage($pId);
            echo json_encode(['success' => (bool)$res, 'message' => $res ? 'Slide deleted successfully.' : 'Failed to delete slide.']);
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function clearLessonPages($lessonPlanId): void {
        header('Content-Type: application/json');
        try {
            $lpId = (int)$lessonPlanId;
            $res = $this->model->deleteAllPagesByLessonPlan($lpId);
            echo json_encode(['success' => (bool)$res, 'message' => 'Lahat ng slides ay na-clear na.']);
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Dedicated Full-Page Lesson Slides Studio & FSL Interactive Aids Builder
     */
    public function lessonSlideBuilder($lessonPlanId): void {
        $lpId = (int)$lessonPlanId;
        $lessonPlan = $this->model->findById($lpId);

        if (!$lessonPlan) {
            $_SESSION['error'] = 'Lesson plan not found.';
            header('Location: ' . $this->basePath . '/iep/implementation');
            exit;
        }

        $iepId = (int)$lessonPlan['iep_id'];
        $iep   = $this->model->getIepById($iepId);
        $pages = $this->model->getPagesByLessonPlan($lpId);

        // FSL Vocabulary for interactive sign insertion (filter out any signs without real videos)
        require_once __DIR__ . '/../Models/FSLModel.php';
        $fslModel = new FSLModel();
        $fslSigns = $fslModel->getAll(null, '', true);
        $fslCategories = $fslModel->getCategories(true);

        $pageTitle = 'Lesson Studio: ' . ($lessonPlan['title'] ?? 'Slides Builder');
        $basePath  = $this->basePath;

        require_once __DIR__ . '/../Views/iep_implementation/lesson_slide_builder.php';
    }

    /**
     * Teacher custom FSL video upload
     */
    public function uploadCustomFslSign(): void {
        header('Content-Type: application/json');
        try {
            $word = trim($_POST['word'] ?? '');
            $category = trim($_POST['category'] ?? 'Custom Signs');
            $description = trim($_POST['description'] ?? '');

            if (empty($word)) {
                echo json_encode(['success' => false, 'message' => 'Mangyaring ilagay ang salita / tawag sa senyas (word is required).']);
                exit;
            }

            if (!isset($_FILES['video_file']) || $_FILES['video_file']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Mangyaring mag-upload ng video file para sa senyas na ito.']);
                exit;
            }

            $file = $_FILES['video_file'];
            if ($file['size'] > 10 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'Laki ng video ay dapat mas mababa sa 10MB (Max 10MB).']);
                exit;
            }
            $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['mp4', 'webm', 'mov', 'ogg', 'm4v'];

            if (!in_array($ext, $allowed)) {
                echo json_encode(['success' => false, 'message' => 'Hindi wastong format ng video. Pwede: MP4, WebM, MOV, OGG.']);
                exit;
            }

            $uploadDir = function_exists('public_path') ? public_path('uploads/fsl_custom_videos/') : (__DIR__ . '/../../public/uploads/fsl_custom_videos/');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $cleanSlug = preg_replace('/[^a-z0-9_-]/i', '_', strtolower($word));
            $fileName = 'fsl_' . $cleanSlug . '_' . time() . '.' . $ext;
            $destPath = $uploadDir . $fileName;

            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                echo json_encode(['success' => false, 'message' => 'Nabigong i-save ang video file sa server.']);
                exit;
            }

            $relPath = 'uploads/fsl_custom_videos/' . $fileName;

            require_once __DIR__ . '/../Models/FSLModel.php';
            $fslModel = new FSLModel();
            $signId = $fslModel->addWord([
                'word'        => $word,
                'category'    => !empty($category) ? $category : 'Custom Signs',
                'video_path'  => $relPath,
                'gif_path'    => null,
                'description' => !empty($description) ? $description : 'Gawa ng guro para sa ' . $word
            ]);

            $newSign = $fslModel->getById($signId);

            echo json_encode([
                'success' => true,
                'message' => 'Matagumpay na na-upload ang iyong bagong FSL Video!',
                'sign'    => $newSign
            ]);
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }



    public function uploadSlideImage(): void {
        header('Content-Type: application/json');
        try {
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'No image file uploaded or upload error.']);
                exit;
            }

            $file = $_FILES['image'];
            $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
            if (!in_array($ext, $allowed)) {
                echo json_encode(['success' => false, 'message' => 'Invalid image format. Allowed: JPG, PNG, GIF, WebP, SVG.']);
                exit;
            }

            $uploadDir = function_exists('public_path') ? public_path('uploads/lesson_editor/') : (__DIR__ . '/../../public/uploads/lesson_editor/');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = 'editor_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
                $basePath = defined('BASE_PATH') ? BASE_PATH : '';
                $url = $basePath . '/uploads/lesson_editor/' . $fileName;
                echo json_encode(['success' => true, 'url' => $url]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to save uploaded image.']);
            }
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ============================================================
    // ADD MATERIAL (POST AJAX JSON)
    // ============================================================

    public function addMaterial() {
        header('Content-Type: application/json');
        try {
            // Support both JSON body and multipart (for file uploads)
            $isMultipart = !empty($_FILES);
            if ($isMultipart) {
                $body = $_POST;
            } else {
                $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            }

            $lessonPlanId = (int) ($body['lesson_plan_id'] ?? 0);
            $materialType = trim($body['material_type']    ?? '');
            $title        = trim($body['title']            ?? '');

            if (!$lessonPlanId || !$materialType || !$title) {
                echo json_encode(['success' => false, 'message' => 'lesson_plan_id, material_type, and title are required.']);
                exit;
            }

            if (!in_array($materialType, ['file', 'link', 'embed'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid material type.']);
                exit;
            }

            $filePath    = null;
            $externalUrl = trim($body['external_url'] ?? '');
            $embedType   = trim($body['embed_type']   ?? '');

            // Handle file upload
            if ($materialType === 'file') {
                if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                    echo json_encode(['success' => false, 'message' => 'File is required for file material type.']);
                    exit;
                }

                $file    = $_FILES['file'];
                $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'mp4'];
                $maxSize = in_array($ext, ['mp4']) ? 50 * 1024 * 1024 : 10 * 1024 * 1024;

                if (!in_array($ext, $allowed)) {
                    echo json_encode(['success' => false, 'message' => 'Allowed file types: JPG, PNG, PDF, MP4.']);
                    exit;
                }
                if ($file['size'] > $maxSize) {
                    $limit = in_array($ext, ['mp4']) ? '50MB' : '10MB';
                    echo json_encode(['success' => false, 'message' => "File must be under $limit."]);
                    exit;
                }

                $uploadDir = function_exists('public_path') ? public_path('uploads/materials/' . $lessonPlanId . '/') : (__DIR__ . '/../../public/uploads/materials/' . $lessonPlanId . '/');
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $fileName = 'mat_' . time() . '_' . uniqid() . '.' . $ext;
                $fullPath = $uploadDir . $fileName;

                if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
                    echo json_encode(['success' => false, 'message' => 'Failed to save file.']);
                    exit;
                }

                $filePath = 'uploads/materials/' . $lessonPlanId . '/' . $fileName;
            }

            // Auto-detect embed type
            if ($materialType === 'embed' && $externalUrl) {
                if (strpos($externalUrl, 'youtube.com') !== false || strpos($externalUrl, 'youtu.be') !== false) {
                    $embedType = 'youtube';
                } elseif (strpos($externalUrl, 'drive.google.com') !== false) {
                    $embedType = 'gdrive';
                } elseif (empty($embedType)) {
                    $embedType = 'other';
                }
            }

            // Get current max display_order
            $existing     = $this->model->getMaterials($lessonPlanId);
            $displayOrder = count($existing);

            $materialId = $this->model->addMaterial([
                'lesson_plan_id' => $lessonPlanId,
                'material_type'  => $materialType,
                'title'          => $title,
                'file_path'      => $filePath,
                'external_url'   => $externalUrl ?: null,
                'embed_type'     => $embedType   ?: null,
                'display_order'  => $displayOrder,
            ]);

            if (!$materialId) {
                echo json_encode(['success' => false, 'message' => 'Failed to save material.']);
                exit;
            }

            $material = [
                'id'             => $materialId,
                'lesson_plan_id' => $lessonPlanId,
                'material_type'  => $materialType,
                'title'          => $title,
                'file_path'      => $filePath,
                'external_url'   => $externalUrl ?: null,
                'embed_type'     => $embedType   ?: null,
                'display_order'  => $displayOrder,
            ];

            echo json_encode(['success' => true, 'material' => $material]);
        } catch (Throwable $e) {
            error_log('addMaterial error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }

    // ============================================================
    // DELETE MATERIAL (POST AJAX JSON) — new route
    // ============================================================

    public function deleteMaterialNew() {
        header('Content-Type: application/json');
        try {
            $body       = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $rawId      = $body['material_id'] ?? '';

            if (!$rawId) {
                echo json_encode(['success' => false, 'message' => 'material_id is required.']);
                exit;
            }

            if (is_string($rawId) && strpos($rawId, 'interactive_') === 0) {
                $lpId = (int)str_replace('interactive_', '', $rawId);
                $this->model->deleteAllPagesByLessonPlan($lpId);
                echo json_encode(['success' => true, 'message' => 'Interactive lesson slides deleted.']);
                exit;
            }

            $materialId = (int)$rawId;
            $material = $this->model->getMaterialById($materialId);
            if (!$material) {
                echo json_encode(['success' => false, 'message' => 'Material not found.']);
                exit;
            }

            // Delete physical file if it exists
            if (!empty($material['file_path'])) {
                $fullPath = __DIR__ . '/../../public/' . $material['file_path'];
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
            }

            $this->model->deleteMaterial($materialId);
            echo json_encode(['success' => true, 'message' => 'Material deleted.']);
        } catch (Throwable $e) {
            error_log('deleteMaterialNew error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }

    // ============================================================
    // ADD ACTIVITY (POST AJAX JSON)
    // ============================================================

    public function addActivity() {
        header('Content-Type: application/json');
        try {
            $body = json_decode(file_get_contents('php://input'), true);
            if (!is_array($body)) {
                $body = $_POST;
            }

            $lessonPlanId = (int) ($body['lesson_plan_id'] ?? 0);
            $title        = trim($body['title']            ?? '');
            $instructions = trim($body['instructions']     ?? '');
            $activityType = trim($body['activity_type']    ?? '');
            $activityData = $body['activity_data']         ?? null;
            $isF2F        = isset($body['is_f2f']) ? (int)$body['is_f2f'] : 0;
            $maxScore     = $isF2F ? 0 : (int) ($body['max_score'] ?? 0);
            $dueDate      = !empty($body['due_date']) ? $body['due_date'] : null;

            if (!$lessonPlanId || !$title || !$activityType) {
                echo json_encode(['success' => false, 'message' => 'lesson_plan_id, title, and activity_type are required.']);
                exit;
            }

            $validTypes = [
                'multiple_choice', 'true_false', 'fill_in_blanks', 'matching',
                'drag_drop_sort', 'image_label', 'flashcards', 'sequencing'
            ];
            if (!in_array($activityType, $validTypes)) {
                echo json_encode(['success' => false, 'message' => 'Invalid activity type.']);
                exit;
            }

            // Ensure activity_data is an array for manipulation
            $activityDataArr = [];
            if (is_array($activityData)) {
                $activityDataArr = $activityData;
            } elseif (is_string($activityData)) {
                $activityDataArr = json_decode($activityData, true) ?? [];
            }

            // Upload image file for image_label (scoring/validation)
            if ($activityType === 'image_label') {
                $imagePath = null;
                if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
                    $file    = $_FILES['image_file'];
                    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png'];
                    if (!in_array($ext, $allowed)) {
                        echo json_encode(['success' => false, 'message' => 'Allowed image types: JPG, PNG.']);
                        exit;
                    }
                    if ($file['size'] > 5 * 1024 * 1024) {
                        echo json_encode(['success' => false, 'message' => 'Image file must be under 5MB.']);
                        exit;
                    }
                    $uploadDir = function_exists('public_path') ? public_path('uploads/activities/' . $lessonPlanId . '/') : (__DIR__ . '/../../public/uploads/activities/' . $lessonPlanId . '/');
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $fileName = 'act_' . time() . '_' . uniqid() . '.' . $ext;
                    $fullPath = $uploadDir . $fileName;
                    if (move_uploaded_file($file['tmp_name'], $fullPath)) {
                        $imagePath = 'uploads/activities/' . $lessonPlanId . '/' . $fileName;
                    }
                }

                if ($imagePath) {
                    $activityDataArr['image_path'] = $imagePath;
                }

                if (empty($activityDataArr['image_path'])) {
                    echo json_encode(['success' => false, 'message' => 'Please upload an image before saving this activity.']);
                    exit;
                }
            }

            $activityDataJson = json_encode($activityDataArr);

            $existing     = $this->model->getActivities($lessonPlanId);
            $displayOrder = count($existing);

            $activityId = $this->model->addActivity([
                'lesson_plan_id' => $lessonPlanId,
                'title'          => $title,
                'instructions'   => $instructions,
                'activity_type'  => $activityType,
                'activity_data'  => $activityDataJson,
                'max_score'      => $maxScore,
                'due_date'       => $dueDate,
                'display_order'  => $displayOrder,
                'is_f2f'         => $isF2F,
            ]);

            if (!$activityId) {
                echo json_encode(['success' => false, 'message' => 'Failed to save activity.']);
                exit;
            }

            echo json_encode(['success' => true, 'activity_id' => $activityId]);
        } catch (Throwable $e) {
            error_log('addActivity error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }

    // ============================================================
    // DELETE ACTIVITY (POST AJAX JSON)
    // ============================================================

    public function deleteActivity() {
        header('Content-Type: application/json');
        try {
            $body       = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $activityId = (int) ($body['activity_id'] ?? 0);

            if (!$activityId) {
                echo json_encode(['success' => false, 'message' => 'activity_id is required.']);
                exit;
            }

            $activity = $this->model->getActivityById($activityId);
            if (!$activity) {
                echo json_encode(['success' => false, 'message' => 'Activity not found.']);
                exit;
            }

            $this->model->deleteActivity($activityId);
            echo json_encode(['success' => true, 'message' => 'Activity deleted.']);
        } catch (Throwable $e) {
            error_log('deleteActivity error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }

    // ============================================================
    // PUBLISH LESSON PLAN (POST AJAX JSON)
    // ============================================================

    public function publishLessonPlan() {
        header('Content-Type: application/json');
        try {
            $body         = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $lessonPlanId = (int) ($body['lesson_plan_id'] ?? 0);

            if (!$lessonPlanId) {
                echo json_encode(['success' => false, 'message' => 'lesson_plan_id is required.']);
                exit;
            }

            $lp = $this->model->findById($lessonPlanId);
            if (!$lp) {
                echo json_encode(['success' => false, 'message' => 'Lesson plan not found.']);
                exit;
            }

            // Check if associated IEP is signed and finalized
            $iepId = $lp['iep_id'];
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT status FROM iep_records WHERE id = :iep_id LIMIT 1");
            $stmt->execute(['iep_id' => $iepId]);
            $iep = $stmt->fetch();
            if (!$iep || !in_array($iep['status'], ['signed', 'finalized'])) {
                echo json_encode(['success' => false, 'message' => 'Cannot publish lesson plan: Associated IEP must be signed and finalized first.']);
                exit;
            }

            $this->model->publish($lessonPlanId);

            // Send in-system notifications to assigned learners/parents
            $users = $this->model->getAssignedUserAccounts($lessonPlanId);
            foreach ($users as $user) {
                $this->notifModel->create(
                    $user['user_id'],
                    'lesson_published',
                    'New Lesson Plan Published',
                    'Your teacher published: ' . $lp['title'],
                    ['lesson_plan_id' => $lessonPlanId]
                );
            }

            echo json_encode(['success' => true, 'message' => 'Lesson plan published successfully.']);
        } catch (Throwable $e) {
            error_log('publishLessonPlan error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }

    // ============================================================
    // DOWNLOAD TEMPLATE (GET — serves DLL or DLP .docx)
    // ============================================================

    public function downloadTemplate($type) {
        $allowed = ['dll' => 'DLL_template.docx', 'dlp' => 'DLP_template.docx'];
        $type    = strtolower(trim($type));

        if (!array_key_exists($type, $allowed)) {
            http_response_code(404);
            echo 'Template not found.';
            exit;
        }

        $filename = $allowed[$type];
        $filepath = __DIR__ . '/../../public/templates/' . $filename;

        if (!file_exists($filepath)) {
            http_response_code(404);
            echo 'Template file not yet uploaded. Please contact the administrator.';
            exit;
        }

        $downloadName = $type === 'dll' ? 'DepEd_DLL_Template.docx' : 'DepEd_DLP_Template.docx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $downloadName . '"; filename*=UTF-8\'\'' . rawurlencode($downloadName));
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('X-Content-Type-Options: nosniff');
        readfile($filepath);
        exit;
    }

    // ============================================================
    // DELETE LESSON PLAN (POST AJAX JSON)
    // ============================================================

    public function deleteLessonPlan($lessonPlanId) {
        header('Content-Type: application/json');
        try {
            $lessonPlanId = (int) $lessonPlanId;
            if (!$lessonPlanId) {
                echo json_encode(['success' => false, 'message' => 'Invalid lesson plan ID.']);
                exit;
            }

            $lp = $this->model->findById($lessonPlanId);
            if (!$lp) {
                echo json_encode(['success' => false, 'message' => 'Lesson plan not found.']);
                exit;
            }

            // Only the creator can delete
            if ((int)$lp['created_by'] !== $this->userId && $this->userRole !== 'admin') {
                echo json_encode(['success' => false, 'message' => 'You can only delete lesson plans you created.']);
                exit;
            }

            // Delete physical document file if exists
            if (!empty($lp['document_path'])) {
                $fullPath = __DIR__ . '/../../public/' . $lp['document_path'];
                if (file_exists($fullPath)) @unlink($fullPath);
            }

            $this->model->deleteLessonPlan($lessonPlanId);
            echo json_encode(['success' => true, 'message' => 'Lesson plan deleted.']);
        } catch (Throwable $e) {
            error_log('deleteLessonPlan error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }

    // ============================================================
    // LEGACY / STUB METHODS (route compatibility)
    // ============================================================

    /** Legacy route stub — use addMaterial instead */
    public function uploadFile() {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'This endpoint is deprecated. Use /iep/implementation/add-material instead.']);
        exit;
    }

    /** Legacy route stub — use addActivity instead */
    public function saveActivity() {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'This endpoint is deprecated. Use /iep/implementation/add-activity instead.']);
        exit;
    }

    /** Legacy delete-material route (old route: POST /iep/implementation/delete-material/{id}) */
    public function deleteMaterial($materialId) {
        header('Content-Type: application/json');
        try {
            $materialId = (int) $materialId;
            if (!$materialId) {
                echo json_encode(['success' => false, 'message' => 'Invalid material ID.']);
                exit;
            }

            $material = $this->model->getMaterialById($materialId);
            if (!$material) {
                echo json_encode(['success' => false, 'message' => 'Material not found.']);
                exit;
            }

            if (!empty($material['file_path'])) {
                $fullPath = __DIR__ . '/../../public/' . $material['file_path'];
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
            }

            $this->model->deleteMaterial($materialId);
            echo json_encode(['success' => true, 'message' => 'Material deleted.']);
        } catch (Throwable $e) {
            error_log('deleteMaterial error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }

    /** Stub — redirect to index */
    public function progress($id) {
        header('Location: ' . $this->basePath . '/iep/implementation');
        exit;
    }

    /** Stub — redirect to index */
    public function showAssign() {
        header('Location: ' . $this->basePath . '/iep/implementation');
        exit;
    }

    /** Stub — redirect to index */
    public function assign() {
        header('Location: ' . $this->basePath . '/iep/implementation');
        exit;
    }

    /** Stub — redirect to workspace if id given, else index */
    public function showCreateActivity($id = null) {
        if ($id) {
            header('Location: ' . $this->basePath . '/iep/implementation/workspace/' . (int) $id);
        } else {
            header('Location: ' . $this->basePath . '/iep/implementation');
        }
        exit;
    }

    /** Stub — redirect to index */
    public function materials($id) {
        header('Location: ' . $this->basePath . '/iep/implementation');
        exit;
    }

    // ============================================================
    // IMPORT ACTIVITIES FROM CSV (POST AJAX multipart)
    // ============================================================

    public function importActivitiesCSV() {
        header('Content-Type: application/json');
        try {
            $lessonPlanId = (int) ($_POST['lesson_plan_id'] ?? 0);
            $iepId        = (int) ($_POST['iep_id']         ?? 0);

            if (!$lessonPlanId || !$iepId) {
                echo json_encode(['success' => false, 'message' => 'lesson_plan_id and iep_id are required.']);
                exit;
            }

            if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'No CSV file uploaded or upload error.']);
                exit;
            }

            $file = $_FILES['csv_file'];
            $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($ext !== 'csv') {
                echo json_encode(['success' => false, 'message' => 'Only CSV files are allowed.']);
                exit;
            }

            $handle = fopen($file['tmp_name'], 'r');
            if (!$handle) {
                echo json_encode(['success' => false, 'message' => 'Could not read the CSV file.']);
                exit;
            }

            // Read header row
            $headers = fgetcsv($handle);
            if (!$headers) {
                fclose($handle);
                echo json_encode(['success' => false, 'message' => 'CSV is empty or unreadable.']);
                exit;
            }

            $headers = array_map('trim', $headers);

            $imported = 0;
            $errors   = [];
            $rowNum   = 1;

            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                if (empty(array_filter($row))) continue; // skip blank rows

                $rowPadded = array_slice(array_pad($row, count($headers), ''), 0, count($headers));
                $data = array_combine($headers, $rowPadded);
                if ($data === false) {
                    $errors[] = "Row $rowNum: Column count mismatch.";
                    continue;
                }

                $title        = trim($data['title']         ?? $data['Title']         ?? '');
                $instructions = trim($data['instructions']  ?? $data['Instructions']  ?? '');
                $type         = strtolower(trim($data['type'] ?? $data['Type'] ?? 'multiple_choice'));
                $maxScore     = (int) ($data['max_score']   ?? $data['Max Score']     ?? 1);
                $dueDate      = trim($data['due_date']      ?? $data['Due Date']      ?? '');

                if (empty($title)) {
                    $errors[] = "Row $rowNum: 'title' is required.";
                    continue;
                }

                $validTypes = [
                    'multiple_choice', 'true_false', 'fill_in_blanks', 'matching',
                    'drag_drop_sort', 'image_label', 'flashcards', 'sequencing'
                ];
                if (!in_array($type, $validTypes)) {
                    $type = 'multiple_choice';
                }

                // Build activity_data from CSV columns
                $activityData = [];
                switch ($type) {
                    case 'multiple_choice':
                        $questions = [];
                        for ($q = 1; $q <= 5; $q++) {
                            $qText = trim($data["question$q"] ?? $data["Question$q"] ?? '');
                            if (empty($qText)) continue;
                            $opts = [];
                            for ($o = 1; $o <= 4; $o++) {
                                $opt = trim($data["q{$q}_option$o"] ?? '');
                                if ($opt !== '') {
                                    $isCorrect = (string)$o === trim($data["q{$q}_correct"] ?? '1');
                                    $opts[] = ['text' => $opt, 'is_correct' => $isCorrect];
                                }
                            }
                            if (!empty($opts)) {
                                $questions[] = ['text' => $qText, 'options' => $opts, 'points' => 1];
                            }
                        }
                        $activityData = ['questions' => $questions, 'points' => 1];
                        break;

                    case 'true_false':
                        $questions = [];
                        for ($q = 1; $q <= 5; $q++) {
                            $qText = trim($data["question$q"] ?? $data["Question$q"] ?? ($q === 1 ? ($data['statement'] ?? '') : ''));
                            if ($qText === '') {
                                continue;
                            }
                            $answer = strtolower(trim($data["q{$q}_correct"] ?? ($q === 1 ? ($data['correct_answer'] ?? 'true') : 'true')));
                            $questions[] = [
                                'statement' => $qText,
                                'answer' => $answer === 'false' ? 'false' : 'true',
                                'points' => 1,
                            ];
                        }
                        $firstQuestion = $questions[0] ?? ['statement' => '', 'answer' => 'true'];
                        $activityData = [
                            'questions' => $questions,
                            'statement' => $firstQuestion['statement'],
                            'answer' => $firstQuestion['answer'],
                            'correct_answer' => $firstQuestion['answer'],
                            'points' => 1,
                        ];
                        break;

                    case 'fill_in_blanks':
                        $sentences = [];
                        for ($s = 1; $s <= 5; $s++) {
                            $text    = trim($data["sentence$s"] ?? '');
                            $answers = trim($data["answer$s"]   ?? '');
                            if ($text) {
                                $sentences[] = [
                                    'text'    => $text,
                                    'answers' => array_map('trim', explode('|', $answers)),
                                ];
                            }
                        }
                        $activityData = ['sentences' => $sentences, 'points' => 1];
                        break;

                    case 'matching':
                        $pairs = [];
                        for ($p = 1; $p <= 8; $p++) {
                            $left  = trim($data["left$p"]  ?? '');
                            $right = trim($data["right$p"] ?? '');
                            if ($left && $right) {
                                $pairs[] = ['left' => $left, 'right' => $right];
                            }
                        }
                        $activityData = ['pairs' => $pairs, 'points' => 1];
                        break;

                    case 'drag_drop_sort':
                        $items = [];
                        for ($i = 1; $i <= 10; $i++) {
                            $text = trim($data["item$i"] ?? $data["Item$i"] ?? '');
                            if ($text !== '') {
                                $items[] = ['text' => $text, 'order' => count($items) + 1];
                            }
                        }
                        $activityData = ['items' => $items, 'points' => 1];
                        break;

                    case 'sequencing':
                        $steps = [];
                        for ($i = 1; $i <= 10; $i++) {
                            $text = trim($data["step$i"] ?? $data["Step$i"] ?? '');
                            if ($text !== '') {
                                $steps[] = ['text' => $text, 'order' => count($steps) + 1];
                            }
                        }
                        $activityData = ['steps' => $steps, 'points' => 1];
                        break;

                    case 'flashcards':
                        $cards = [];
                        for ($i = 1; $i <= 10; $i++) {
                            $front = trim($data["front$i"] ?? $data["Front$i"] ?? '');
                            $back = trim($data["back$i"] ?? $data["Back$i"] ?? '');
                            if ($front !== '' && $back !== '') {
                                $cards[] = ['front' => $front, 'back' => $back];
                            }
                        }
                        $activityData = ['cards' => $cards];
                        break;

                    case 'image_label':
                        $activityData = ['labels' => [], 'points' => 1];
                        break;

                    default:
                        $activityData = [];
                }

                $existing     = $this->model->getActivities($lessonPlanId);
                $displayOrder = count($existing);

                $actId = $this->model->addActivity([
                    'lesson_plan_id' => $lessonPlanId,
                    'title'          => $title,
                    'instructions'   => $instructions,
                    'activity_type'  => $type,
                    'activity_data'  => json_encode($activityData),
                    'max_score'      => $maxScore > 0 ? $maxScore : 1,
                    'due_date'       => !empty($dueDate) ? $dueDate : null,
                    'display_order'  => $displayOrder,
                ]);

                if ($actId) {
                    $imported++;
                } else {
                    $errors[] = "Row $rowNum: Failed to save '$title'.";
                }
            }

            fclose($handle);

            if ($imported === 0 && !empty($errors)) {
                echo json_encode(['success' => false, 'message' => 'No activities imported. Errors: ' . implode('; ', $errors)]);
                exit;
            }

            echo json_encode([
                'success'  => true,
                'imported' => $imported,
                'errors'   => $errors,
                'message'  => "$imported activit" . ($imported !== 1 ? 'ies' : 'y') . " imported successfully." . (!empty($errors) ? ' Some rows had errors.' : ''),
            ]);

        } catch (Throwable $e) {
            error_log('importActivitiesCSV error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }

    // ============================================================
    // TEACHER PROGRESS TRACKER (GET)
    // ============================================================

    public function progressTracker() {
        $basePath = $this->basePath;

        // Get all learners for this teacher
        $learners = $this->model->getLearnersForTeacher($this->userId);

        require_once __DIR__ . '/../Views/iep_implementation/progress_tracker.php';
    }

    // ============================================================
    // EDIT MATERIAL (POST AJAX)
    // ============================================================

    public function editMaterial($materialId) {
        header('Content-Type: application/json');
        try {
            $materialId = (int) $materialId;
            if (!$materialId) {
                echo json_encode(['success' => false, 'message' => 'Invalid material ID.']);
                exit;
            }

            $material = $this->model->getMaterialById($materialId);
            if (!$material) {
                echo json_encode(['success' => false, 'message' => 'Material not found.']);
                exit;
            }

            $isMultipart = !empty($_FILES);
            $body        = $isMultipart ? $_POST : (json_decode(file_get_contents('php://input'), true) ?? []);

            $title       = trim($body['title'] ?? '');
            if (!$title) {
                echo json_encode(['success' => false, 'message' => 'Title is required.']);
                exit;
            }

            $updateData = ['title' => $title];

            // Handle URL update (for link / embed materials)
            if (!empty($body['external_url'])) {
                $updateData['external_url'] = trim($body['external_url']);
            }

            // Handle file replacement
            if ($isMultipart && isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $file    = $_FILES['file'];
                $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'mp4'];
                $maxSize = in_array($ext, ['mp4']) ? 50 * 1024 * 1024 : 10 * 1024 * 1024;

                if (!in_array($ext, $allowed)) {
                    echo json_encode(['success' => false, 'message' => 'Allowed: JPG, PNG, PDF, MP4.']);
                    exit;
                }
                if ($file['size'] > $maxSize) {
                    echo json_encode(['success' => false, 'message' => 'File too large.']);
                    exit;
                }

                $lpId      = (int) $material['lesson_plan_id'];
                $uploadDir = function_exists('public_path') ? public_path('uploads/materials/' . $lpId . '/') : (__DIR__ . '/../../public/uploads/materials/' . $lpId . '/');
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $fileName = 'mat_' . time() . '_' . uniqid() . '.' . $ext;
                if (!move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
                    echo json_encode(['success' => false, 'message' => 'Failed to save file.']);
                    exit;
                }

                // Delete old file
                if (!empty($material['file_path'])) {
                    $old = __DIR__ . '/../../public/' . $material['file_path'];
                    if (file_exists($old)) @unlink($old);
                }

                $updateData['file_path'] = 'uploads/materials/' . $lpId . '/' . $fileName;
            }

            $this->model->updateMaterial($materialId, $updateData);
            $updated = $this->model->getMaterialById($materialId);
            echo json_encode(['success' => true, 'material' => $updated]);
        } catch (Throwable $e) {
            error_log('editMaterial error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }

    // ============================================================
    // EDIT ACTIVITY (POST AJAX JSON)
    // ============================================================

    public function editActivity($activityId) {
        header('Content-Type: application/json');
        try {
            $activityId = (int) $activityId;
            if (!$activityId) {
                echo json_encode(['success' => false, 'message' => 'Invalid activity ID.']);
                exit;
            }

            $activity = $this->model->getActivityById($activityId);
            if (!$activity) {
                echo json_encode(['success' => false, 'message' => 'Activity not found.']);
                exit;
            }

            $body = json_decode(file_get_contents('php://input'), true);
            if (!is_array($body)) {
                $body = $_POST;
            }

            $title = trim($body['title'] ?? '');
            if (!$title) {
                echo json_encode(['success' => false, 'message' => 'Title is required.']);
                exit;
            }

            $lessonPlanId = isset($body['lesson_plan_id']) ? (int) $body['lesson_plan_id'] : (int) $activity['lesson_plan_id'];
            $activityType = trim($body['activity_type'] ?? $activity['activity_type']);
            $instructions = isset($body['instructions']) ? trim($body['instructions']) : ($activity['instructions'] ?? '');
            $isF2F        = isset($body['is_f2f']) ? (int) $body['is_f2f'] : (int) ($activity['is_f2f'] ?? 0);
            $maxScore     = $isF2F ? 0 : (isset($body['max_score']) ? (int) $body['max_score'] : (int) $activity['max_score']);
            $dueDate      = !empty($body['due_date']) ? $body['due_date'] : null;

            $updateData = [
                'title'          => $title,
                'instructions'   => $instructions,
                'lesson_plan_id' => $lessonPlanId,
                'activity_type'  => $activityType,
                'max_score'      => $maxScore,
                'due_date'       => $dueDate,
                'is_f2f'         => $isF2F,
            ];

            if (isset($body['activity_data'])) {
                $activityData = $body['activity_data'];
                $activityDataArr = [];
                if (is_array($activityData)) {
                    $activityDataArr = $activityData;
                } elseif (is_string($activityData)) {
                    $activityDataArr = json_decode($activityData, true) ?? [];
                }

                // If image_label, check for new image upload or keep old
                if ($activityType === 'image_label') {
                    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
                        $file    = $_FILES['image_file'];
                        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                        $allowed = ['jpg', 'jpeg', 'png'];
                        if (!in_array($ext, $allowed)) {
                            echo json_encode(['success' => false, 'message' => 'Allowed image types: JPG, PNG.']);
                            exit;
                        }
                        if ($file['size'] > 5 * 1024 * 1024) {
                            echo json_encode(['success' => false, 'message' => 'Image file must be under 5MB.']);
                            exit;
                        }
                        $uploadDir = function_exists('public_path') ? public_path('uploads/activities/' . $lessonPlanId . '/') : (__DIR__ . '/../../public/uploads/activities/' . $lessonPlanId . '/');
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }
                        $fileName = 'act_' . time() . '_' . uniqid() . '.' . $ext;
                        $fullPath = $uploadDir . $fileName;
                        if (move_uploaded_file($file['tmp_name'], $fullPath)) {
                            $activityDataArr['image_path'] = 'uploads/activities/' . $lessonPlanId . '/' . $fileName;
                        }
                    }
                }

                $updateData['activity_data'] = json_encode($activityDataArr);
            }

            $this->model->updateActivity($activityId, $updateData);
            $updated = $this->model->getActivityById($activityId);
            echo json_encode(['success' => true, 'activity' => $updated]);
        } catch (Throwable $e) {
            error_log('editActivity error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }

    // ============================================================
    // VIEW SUBMISSION — teacher read-only review of a learner's
    // submitted activity (GET /iep/implementation/submission/{id})
    // ============================================================

    public function viewSubmission($activityId) {
        $activityId = (int) $activityId;
        $studentId  = isset($_GET['student_id']) ? (int) $_GET['student_id'] : 0;
        $basePath   = $this->basePath;

        if (!$activityId || !$studentId) {
            $_SESSION['error'] = 'Invalid activity or student.';
            header('Location: ' . $this->basePath . '/iep/implementation');
            exit;
        }

        $db = Database::getInstance()->getConnection();

        // Load activity (with lesson plan + IEP for navigation / grading)
        $stmt = $db->prepare("
            SELECT a.*, lp.iep_id, lp.created_by AS lp_created_by
            FROM lms_activities a
            JOIN lesson_plans lp ON a.lesson_plan_id = lp.id
            WHERE a.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $activityId]);
        $activity = $stmt->fetch();

        if (!$activity) {
            $_SESSION['error'] = 'Activity not found.';
            header('Location: ' . $this->basePath . '/iep/implementation');
            exit;
        }

        $activity['activity_data'] = json_decode($activity['activity_data'] ?? '{}', true) ?? [];

        $iepIdForAct = (int) ($activity['iep_id'] ?? 0);
        $stmtIep = $db->prepare('SELECT drafted_by FROM iep_records WHERE id = :id LIMIT 1');
        $stmtIep->execute(['id' => $iepIdForAct]);
        $iepRow = $stmtIep->fetch();
        $canGrade = in_array($this->userRole, ['sped_teacher', 'admin'], true)
            && $iepRow
            && ((int) $iepRow['drafted_by'] === (int) $this->userId || $this->userRole === 'admin');

        require_once __DIR__ . '/../Models/LessonPlanModel.php';
        $displayMaxScore = LessonPlanModel::displayMaxScoreForActivity($activity);

        // Load student info
        $stmt = $db->prepare("SELECT id, student_name, lrn FROM student_records WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $studentId]);
        $student = $stmt->fetch();

        if (!$student) {
            $_SESSION['error'] = 'Student not found.';
            header('Location: ' . $this->basePath . '/iep/implementation');
            exit;
        }

        // Load submission + grade
        $stmt = $db->prepare("
            SELECT sub.*, g.score, g.max_score AS grade_max_score, g.is_complete, g.remarks
            FROM lms_submissions sub
            LEFT JOIN lms_grades g ON g.submission_id = sub.id
            WHERE sub.activity_id = :activity_id AND sub.student_id = :student_id
            LIMIT 1
        ");
        $stmt->execute(['activity_id' => $activityId, 'student_id' => $studentId]);
        $submission = $stmt->fetch();

        $basePath = $this->basePath;
        $iep_id   = $iepIdForAct;
        $student_id = $studentId;

        require_once __DIR__ . '/../Views/iep_implementation/submission_review.php';
    }

    /**
     * Confirm auto-score (or posted score) as official grade — POST.
     */
    public function confirmSubmissionGrade($activityId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->basePath . '/iep/implementation');
            exit;
        }
        $activityId = (int) $activityId;
        $studentId  = (int) ($_POST['student_id'] ?? 0);
        if (!$activityId || !$studentId) {
            $_SESSION['error'] = 'Invalid request.';
            header('Location: ' . $this->basePath . '/iep/implementation');
            exit;
        }

        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
            SELECT a.*, lp.iep_id, lp.created_by AS lp_created_by
            FROM lms_activities a
            JOIN lesson_plans lp ON a.lesson_plan_id = lp.id
            WHERE a.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $activityId]);
        $activity = $stmt->fetch();
        if (!$activity) {
            $_SESSION['error'] = 'Activity not found.';
            header('Location: ' . $this->basePath . '/iep/implementation');
            exit;
        }

        $iepId = (int) ($activity['iep_id'] ?? 0);
        $stmtIep = $db->prepare('SELECT drafted_by FROM iep_records WHERE id = :id LIMIT 1');
        $stmtIep->execute(['id' => $iepId]);
        $iepRow = $stmtIep->fetch();
        if (!$iepRow
            || !in_array($this->userRole, ['sped_teacher', 'admin'], true)
            || ((int) $iepRow['drafted_by'] !== (int) $this->userId && $this->userRole !== 'admin')) {
            $_SESSION['error'] = 'Access denied.';
            header('Location: ' . $this->basePath . '/iep/implementation');
            exit;
        }

        require_once __DIR__ . '/../Models/LessonPlanModel.php';
        $activity['activity_data'] = json_decode($activity['activity_data'] ?? '{}', true) ?? [];
        $maxScore = LessonPlanModel::displayMaxScoreForActivity($activity);

        // Find targeted PDSP skill
        $stmtSkill = $db->prepare("
            SELECT s.pdsp_indicator_text 
            FROM iep_steps s
            JOIN iep_step_lesson_plans lp ON lp.iep_step_id = s.id
            WHERE lp.lesson_plan_id = :lp_id AND s.pdsp_indicator_text IS NOT NULL AND s.pdsp_indicator_text != ''
            LIMIT 1
        ");
        $stmtSkill->execute(['lp_id' => $activity['lesson_plan_id']]);
        $targetedSkill = $stmtSkill->fetchColumn() ?: null;

        $quarter = isset($_POST['quarter']) ? (int) $_POST['quarter'] : 1;
        $rating = isset($_POST['rating']) ? trim((string)$_POST['rating']) : null;
        $observation = isset($_POST['observation']) ? trim((string)$_POST['observation']) : null;

        if (!empty($activity['is_f2f'])) {
            // FACE-TO-FACE ACTIVITY PATH
            if (!$targetedSkill) {
                $_SESSION['error'] = 'This activity is not linked to any targeted PDSP skill.';
                header('Location: ' . $this->basePath . '/iep/implementation/submission/' . $activityId . '?student_id=' . $studentId);
                exit;
            }
            if (!$rating) {
                $_SESSION['error'] = 'Rating is required for Face-to-Face activity.';
                header('Location: ' . $this->basePath . '/iep/implementation/submission/' . $activityId . '?student_id=' . $studentId);
                exit;
            }
            if (!$observation) {
                $_SESSION['error'] = 'Observation notes are required for Face-to-Face activity.';
                header('Location: ' . $this->basePath . '/iep/implementation/submission/' . $activityId . '?student_id=' . $studentId);
                exit;
            }

            // Find PDSP record for student
            $stmtPdsp = $db->prepare("SELECT id FROM pdsp_records WHERE student_id = :sid AND status IN ('signed', 'complete') LIMIT 1");
            $stmtPdsp->execute(['sid' => $studentId]);
            $pdspRecordId = $stmtPdsp->fetchColumn();
            if (!$pdspRecordId) {
                $stmtPdsp = $db->prepare("SELECT id FROM pdsp_records WHERE student_id = :sid LIMIT 1");
                $stmtPdsp->execute(['sid' => $studentId]);
                $pdspRecordId = $stmtPdsp->fetchColumn();
            }
            if (!$pdspRecordId) {
                $_SESSION['error'] = 'No signed PDSP record found for this student. Please sign a PDSP first.';
                header('Location: ' . $this->basePath . '/iep/implementation/submission/' . $activityId . '?student_id=' . $studentId);
                exit;
            }

            // Upsert rating & observation
            $stmtCheck = $db->prepare("
                SELECT id FROM student_quarterly_ratings 
                WHERE student_id = :sid AND pdsp_record_id = :pid AND indicator_text = :ind AND quarter = :q
                LIMIT 1
            ");
            $stmtCheck->execute([
                'sid' => $studentId,
                'pid' => $pdspRecordId,
                'ind' => $targetedSkill,
                'q'   => $quarter
            ]);
            $existingRatingId = $stmtCheck->fetchColumn();

            if ($existingRatingId) {
                $stmtUpdate = $db->prepare("
                    UPDATE student_quarterly_ratings 
                    SET rating = :r, observation = :obs, source = 'f2f', updated_at = NOW()
                    WHERE id = :id
                ");
                $stmtUpdate->execute([
                    'r'   => $rating,
                    'obs' => $observation,
                    'id'  => $existingRatingId
                ]);
            } else {
                $stmtDom = $db->prepare("
                    SELECT domain_name FROM pdsp_domains 
                    WHERE pdsp_id = :pid AND (skills_description = :ind OR sub_domain = :ind)
                    LIMIT 1
                ");
                $stmtDom->execute(['pid' => $pdspRecordId, 'ind' => $targetedSkill]);
                $dName = $stmtDom->fetchColumn() ?: 'Cognitive';
                
                $map = [
                    'perceptuo-cognitive' => 'Cognitive',
                    'cognitive' => 'Cognitive',
                    'psychosocial' => 'Behavioral Development',
                    'behavioral development' => 'Behavioral Development',
                    'socio-emotional' => 'Socio-Emotional',
                    'psychomotor' => 'Psychomotor',
                    'daily living skills' => 'Daily Living Skills',
                    'communication and language' => 'Language Development',
                    'language development' => 'Language Development',
                    'communication' => 'Language Development',
                    'aesthetic/creative' => 'Aesthetic/Creative',
                    'orientation and mobility' => 'Orientation and Mobility'
                ];
                $mappedDom = $map[strtolower(trim($dName))] ?? 'Cognitive';

                $stmtInsert = $db->prepare("
                    INSERT INTO student_quarterly_ratings (student_id, pdsp_record_id, domain, indicator_text, quarter, rating, observation, source)
                    VALUES (:sid, :pid, :dom, :ind, :q, :r, :obs, 'f2f')
                ");
                $stmtInsert->execute([
                    'sid' => $studentId,
                    'pid' => $pdspRecordId,
                    'dom' => $mappedDom,
                    'ind' => $targetedSkill,
                    'q'   => $quarter,
                    'r'   => $rating,
                    'obs' => $observation
                ]);
            }

            // Save observation notes to existing iep_steps.observation field if a step is linked
            $stmtStep = $db->prepare("
                SELECT s.id 
                FROM iep_steps s
                JOIN iep_step_lesson_plans lp ON lp.iep_step_id = s.id
                WHERE lp.lesson_plan_id = :lp_id
                LIMIT 1
            ");
            $stmtStep->execute(['lp_id' => $activity['lesson_plan_id']]);
            $linkedStepId = $stmtStep->fetchColumn();
            if ($linkedStepId) {
                $stmtUpStep = $db->prepare("UPDATE iep_steps SET observation = :obs WHERE id = :id");
                $stmtUpStep->execute(['obs' => $observation, 'id' => $linkedStepId]);
            }

            $_SESSION['success'] = 'F2F rating and observation recorded successfully.';
            header('Location: ' . $this->basePath . '/iep/implementation/submission/' . $activityId . '?student_id=' . $studentId);
            exit;
        } else {
            // DIGITAL PATH
            if (is_string($activity['activity_data'])) {
                require_once __DIR__ . '/../Models/LessonPlanModel.php';
                $activity['activity_data'] = json_decode($activity['activity_data'] ?? '{}', true) ?? [];
                $maxScore = LessonPlanModel::displayMaxScoreForActivity($activity);
            }

            $stmt = $db->prepare('SELECT id, auto_score FROM lms_submissions WHERE activity_id = :a AND student_id = :s LIMIT 1');
            $stmt->execute(['a' => $activityId, 's' => $studentId]);
            $sub = $stmt->fetch();
            if (!$sub) {
                $_SESSION['error'] = 'No submission found.';
                header('Location: ' . $this->basePath . '/iep/implementation/submission/' . $activityId . '?student_id=' . $studentId);
                exit;
            }

            $posted = isset($_POST['score']) ? (int) $_POST['score'] : null;
            $score  = $posted !== null ? $posted : (int) ($sub['auto_score'] ?? 0);
            if ($maxScore > 0 && $score > $maxScore) {
                $score = $maxScore;
            }
            if ($score < 0) {
                $score = 0;
            }

            // Save to lms_grades
            $ins = $db->prepare("
                INSERT INTO lms_grades (submission_id, graded_by, score, max_score, is_complete, remarks)
                VALUES (:sid, :uid, :sc, :mx, 1, :rm)
                ON DUPLICATE KEY UPDATE
                    score = VALUES(score),
                    max_score = VALUES(max_score),
                    is_complete = 1,
                    graded_by = VALUES(graded_by),
                    remarks = VALUES(remarks),
                    graded_at = CURRENT_TIMESTAMP
            ");
            $ins->execute([
                'sid' => (int) $sub['id'],
                'uid' => (int) $this->userId,
                'sc'  => $score,
                'mx'  => $maxScore > 0 ? $maxScore : (int) ($activity['max_score'] ?? 0),
                'rm'  => $observation ?: (trim((string) ($_POST['remarks'] ?? '')) ?: null),
            ]);

            // Save to student_quarterly_ratings if rating and targeted skill exist
            if ($rating && $targetedSkill) {
                $stmtPdsp = $db->prepare("SELECT id FROM pdsp_records WHERE student_id = :sid AND status IN ('signed', 'complete') LIMIT 1");
                $stmtPdsp->execute(['sid' => $studentId]);
                $pdspRecordId = $stmtPdsp->fetchColumn();
                if (!$pdspRecordId) {
                    $stmtPdsp = $db->prepare("SELECT id FROM pdsp_records WHERE student_id = :sid LIMIT 1");
                    $stmtPdsp->execute(['sid' => $studentId]);
                    $pdspRecordId = $stmtPdsp->fetchColumn();
                }

                if ($pdspRecordId) {
                    $stmtCheck = $db->prepare("
                        SELECT id FROM student_quarterly_ratings 
                        WHERE student_id = :sid AND pdsp_record_id = :pid AND indicator_text = :ind AND quarter = :q
                        LIMIT 1
                    ");
                    $stmtCheck->execute([
                        'sid' => $studentId,
                        'pid' => $pdspRecordId,
                        'ind' => $targetedSkill,
                        'q'   => $quarter
                    ]);
                    $existingRatingId = $stmtCheck->fetchColumn();

                    if ($existingRatingId) {
                        $stmtUpdate = $db->prepare("
                            UPDATE student_quarterly_ratings 
                            SET rating = :r, observation = :obs, source = 'digital', updated_at = NOW()
                            WHERE id = :id
                        ");
                        $stmtUpdate->execute([
                            'r'   => $rating,
                            'obs' => $observation,
                            'id'  => $existingRatingId
                        ]);
                    } else {
                        $stmtDom = $db->prepare("
                            SELECT domain_name FROM pdsp_domains 
                            WHERE pdsp_id = :pid AND (skills_description = :ind OR sub_domain = :ind)
                            LIMIT 1
                        ");
                        $stmtDom->execute(['pid' => $pdspRecordId, 'ind' => $targetedSkill]);
                        $dName = $stmtDom->fetchColumn() ?: 'Cognitive';
                        
                        $map = [
                            'perceptuo-cognitive' => 'Cognitive',
                            'cognitive' => 'Cognitive',
                            'psychosocial' => 'Behavioral Development',
                            'behavioral development' => 'Behavioral Development',
                            'socio-emotional' => 'Socio-Emotional',
                            'psychomotor' => 'Psychomotor',
                            'daily living skills' => 'Daily Living Skills',
                            'communication and language' => 'Language Development',
                            'language development' => 'Language Development',
                            'communication' => 'Language Development',
                            'aesthetic/creative' => 'Aesthetic/Creative',
                            'orientation and mobility' => 'Orientation and Mobility'
                        ];
                        $mappedDom = $map[strtolower(trim($dName))] ?? 'Cognitive';

                        $stmtInsert = $db->prepare("
                            INSERT INTO student_quarterly_ratings (student_id, pdsp_record_id, domain, indicator_text, quarter, rating, observation, source)
                            VALUES (:sid, :pid, :dom, :ind, :q, :r, :obs, 'digital')
                        ");
                        $stmtInsert->execute([
                            'sid' => $studentId,
                            'pid' => $pdspRecordId,
                            'dom' => $mappedDom,
                            'ind' => $targetedSkill,
                            'q'   => $quarter,
                            'r'   => $rating,
                            'obs' => $observation
                        ]);
                    }
                }
            }

            $_SESSION['success'] = 'Grade and rating confirmed successfully.';
            header('Location: ' . $this->basePath . '/iep/implementation/submission/' . $activityId . '?student_id=' . $studentId);
            exit;
        }
    }

    /**
     * Upload Traditional IEP Record Document (DLL, Physical IEP, Progress Note)
     */
    public function uploadTraditionalDoc() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->basePath . '/iep/implementation');
            exit;
        }

        $iepId = (int)($_POST['iep_id'] ?? 0);
        $studentId = (int)($_POST['student_id'] ?? 0);
        $docType = trim($_POST['document_type'] ?? 'dll');
        $title = trim($_POST['title'] ?? '');
        $quarter = trim($_POST['quarter'] ?? 'Q1');
        $notes = trim($_POST['notes'] ?? '');

        if (empty($title) || empty($_FILES['doc_file']['tmp_name'])) {
            $_SESSION['error'] = 'Title and document file are required.';
            header('Location: ' . $this->basePath . '/iep/implementation/workspace/' . $iepId);
            exit;
        }

        $uploadDir = function_exists('public_path') ? public_path('uploads/traditional_iep/') : (__DIR__ . '/../../public/uploads/traditional_iep/');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $file = $_FILES['doc_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $newFilename = 'trad_' . $studentId . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . $newFilename;
        $relPath = 'uploads/traditional_iep/' . $newFilename;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO traditional_iep_documents (student_id, uploaded_by, document_type, title, file_path, file_size, quarter, notes)
                VALUES (:student_id, :uploaded_by, :document_type, :title, :file_path, :file_size, :quarter, :notes)
            ");
            $stmt->execute([
                'student_id'    => $studentId,
                'uploaded_by'   => $this->userId,
                'document_type' => $docType,
                'title'         => $title,
                'file_path'     => $relPath,
                'file_size'     => (int)$file['size'],
                'quarter'       => $quarter,
                'notes'         => $notes
            ]);

            $_SESSION['success'] = "Traditional IEP Document '{$title}' uploaded successfully!";
        } else {
            $_SESSION['error'] = 'Failed to upload document file.';
        }

        header('Location: ' . $this->basePath . '/iep/implementation/workspace/' . $iepId);
        exit;
    }

    /**
     * Delete Traditional IEP Document
     */
    public function deleteTraditionalDoc($docId) {
        $docId = (int)$docId;
        $iepId = (int)($_POST['iep_id'] ?? 0);

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM traditional_iep_documents WHERE id = :id AND uploaded_by = :uid");
        $stmt->execute(['id' => $docId, 'uid' => $this->userId]);

        $_SESSION['success'] = "Document removed successfully.";
        header('Location: ' . $this->basePath . '/iep/implementation/workspace/' . $iepId);
        exit;
    }
}


