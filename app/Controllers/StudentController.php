<?php
// DO NOT ALTER WITHOUT APPROVAL — Student Records Management
// Last modified: 2026-06-28
// Part of: SPED LMS — Student Records

require_once __DIR__ . '/../Models/StudentModel.php';
require_once __DIR__ . '/../Models/EnrollmentModel.php';
require_once __DIR__ . '/../Helpers/CSRFHelper.php';

class StudentController {
    private $studentModel;
    private $enrollmentModel;
    private $basePath;

    public function __construct() {
        $this->studentModel = new StudentModel();
        $this->enrollmentModel = new EnrollmentModel();
        $this->basePath = defined('BASE_PATH') ? BASE_PATH : '';
    }

    private function requireStaff(): void {
        if (!isset($_SESSION['user_id']) || in_array($_SESSION['role'], ['parent', 'user', 'learner'])) {
            $_SESSION['error'] = 'Access denied. Staff only.';
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }
    }

    public function index() {
        $this->requireStaff();
        $students = $this->studentModel->getAllStudents();
        $basePath = $this->basePath;
        require_once __DIR__ . '/../Views/students/index.php';
    }

    public function view($studentId) {
        $this->requireStaff();

        $student = $this->studentModel->findById($studentId);

        if (!$student) {
            $_SESSION['error'] = 'Student not found';
            header('Location: ' . $this->basePath . '/students');
            exit;
        }

        $enrollments = $this->studentModel->getEnrollmentsByStudentRecordId($studentId);

        $allDocuments = [];
        foreach ($enrollments as $enrollment) {
            $docs = $this->enrollmentModel->getDocuments($enrollment['id']);
            foreach ($docs as $doc) {
                $doc['enrollment_year'] = $enrollment['school_year'];
                $doc['enrollment_type'] = $enrollment['enrollment_type'];
                $doc['doc_source'] = 'enrollment';
                $allDocuments[] = $doc;
            }
        }

        // Also fetch traditional / uploaded IEP and assessment documents
        try {
            $db = Database::getInstance()->getConnection();
            $stmtTrad = $db->prepare("
                SELECT tid.id, tid.student_id, tid.title, tid.file_path, tid.document_type,
                       tid.school_year AS enrollment_year, 'traditional' AS enrollment_type,
                       'approved' AS status, tid.created_at AS uploaded_at,
                       u.name AS reviewer_name, tid.notes, 'traditional' as doc_source
                FROM traditional_iep_documents tid
                LEFT JOIN users u ON tid.uploaded_by = u.id
                WHERE tid.student_id = :sid
                ORDER BY tid.created_at DESC
            ");
            $stmtTrad->execute(['sid' => $studentId]);
            $tradDocs = $stmtTrad->fetchAll(PDO::FETCH_ASSOC);
            foreach ($tradDocs as $td) {
                $allDocuments[] = $td;
            }
        } catch (\Throwable $e) {
            error_log('StudentController: traditional docs query error - ' . $e->getMessage());
        }

        $basePath = $this->basePath;
        require_once __DIR__ . '/../Views/students/view.php';
    }

    public function edit($studentId) {
        $this->requireStaff();

        $student = $this->studentModel->findById($studentId);

        if (!$student) {
            $_SESSION['error'] = 'Student not found';
            header('Location: ' . $this->basePath . '/students');
            exit;
        }

        $basePath = $this->basePath;
        require_once __DIR__ . '/../Views/students/edit.php';
    }

    public function update($studentId) {
        $this->requireStaff();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->basePath . '/students/edit/' . (int)$studentId);
            exit;
        }

        try {
            CSRFHelper::verify();
        } catch (Exception $e) {
            $_SESSION['error'] = 'Security validation failed.';
            header('Location: ' . $this->basePath . '/students/edit/' . (int)$studentId);
            exit;
        }

        $student = $this->studentModel->findById($studentId);
        if (!$student) {
            $_SESSION['error'] = 'Student not found';
            header('Location: ' . $this->basePath . '/students');
            exit;
        }

        $lrn = trim($_POST['lrn'] ?? '');
        $lrn = $lrn !== '' ? $lrn : null;

        try {
            $this->studentModel->update($studentId, ['lrn' => $lrn]);
            $_SESSION['success'] = 'Student profile updated.';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Failed to update student profile.';
        }

        header('Location: ' . $this->basePath . '/students/view/' . (int)$studentId);
        exit;
    }

    /**
     * Upload document for a student (Physical IEP, Medical Record, PSA, Assessment, etc.)
     * Accessible by SPED Teachers, Staff, Admin, and verified Parents
     */
    public function uploadDocument() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . $this->basePath . '/login');
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $role   = $_SESSION['role'] ?? 'user';
        $studentId = (int)($_POST['student_id'] ?? 0);
        $returnTo  = trim($_POST['return_to'] ?? '');

        $defaultRedirect = $this->basePath . ($studentId > 0 ? '/students/view/' . $studentId : '/masterlist');
        $redirectUrl = !empty($returnTo) ? $this->basePath . $returnTo : $defaultRedirect;

        $student = $this->studentModel->findById($studentId);
        if (!$student) {
            $_SESSION['error'] = 'Learner record not found.';
            header('Location: ' . $redirectUrl);
            exit;
        }

        // Permission check: parents can only upload for their linked child
        if ($role === 'parent') {
            $db = Database::getInstance()->getConnection();
            $stmtP = $db->prepare("
                SELECT COUNT(*) FROM student_records sr
                JOIN enrollment_submissions es ON sr.enrollment_id = es.id
                WHERE sr.id = :sid AND es.parent_id = :pid
            ");
            $stmtP->execute(['sid' => $studentId, 'pid' => $userId]);
            if ((int)$stmtP->fetchColumn() === 0) {
                $_SESSION['error'] = 'You can only upload documents for your own registered children.';
                header('Location: ' . $this->basePath . '/dashboard');
                exit;
            }
        } elseif (!in_array($role, ['sped_teacher', 'master_teacher', 'principal', 'admin', 'guidance'])) {
            $_SESSION['error'] = 'Access denied.';
            header('Location: ' . $this->basePath . '/dashboard');
            exit;
        }

        // Normalize files from either doc_files[] or doc_file
        $rawFiles = [];
        if (!empty($_FILES['doc_files']) && is_array($_FILES['doc_files']['name'])) {
            $totalCount = count($_FILES['doc_files']['name']);
            for ($i = 0; $i < $totalCount; $i++) {
                if (!empty($_FILES['doc_files']['name'][$i]) && $_FILES['doc_files']['error'][$i] === UPLOAD_ERR_OK) {
                    $rawFiles[] = [
                        'name'     => $_FILES['doc_files']['name'][$i],
                        'type'     => $_FILES['doc_files']['type'][$i] ?? '',
                        'tmp_name' => $_FILES['doc_files']['tmp_name'][$i],
                        'error'    => $_FILES['doc_files']['error'][$i],
                        'size'     => $_FILES['doc_files']['size'][$i]
                    ];
                }
            }
        } elseif (!empty($_FILES['doc_file']) && $_FILES['doc_file']['error'] === UPLOAD_ERR_OK) {
            $rawFiles[] = $_FILES['doc_file'];
        }

        if (empty($rawFiles)) {
            $_SESSION['error'] = 'Palihug pagpili og bisan usa o daghang balido nga dokumento nga i-upload.';
            header('Location: ' . $redirectUrl);
            exit;
        }

        $allowedExts = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        $maxSize = 15 * 1024 * 1024; // 15MB per file

        $selectedDocType = trim($_POST['document_type'] ?? 'physical_iep');
        $rawTitle        = trim($_POST['title'] ?? '');
        $schoolYear      = trim($_POST['school_year'] ?? '2026-2027');
        $quarter         = !empty($_POST['quarter']) ? trim($_POST['quarter']) : null;
        $notes           = !empty($_POST['notes']) ? trim($_POST['notes']) : null;

        // Auto-generate title if blank
        $typeLabels = [
            'physical_iep'      => 'Physical / Existing IEP Document',
            'assessment_report' => 'Assessment / Diagnostic Evaluation Report',
            'psa_birth_cert'    => 'PSA Birth Certificate',
            'pwd_id'            => 'Person with Disability (PWD) ID',
            'medical_record'    => 'Medical Certificate / Diagnosis',
            'dll'               => 'Daily Lesson Log (DLL)',
            'other'             => 'School Record / SF10 / General Document'
        ];

        // Determine destination folder
        $uploadDir = __DIR__ . '/../../public/uploads/student_documents/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $db = Database::getInstance()->getConnection();
        $uploadedCount = 0;
        $failedCount = 0;
        $hasIepOrAssessment = false;
        $lastTitle = '';
        $newIepId = null;

        foreach ($rawFiles as $file) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExts) || $file['size'] > $maxSize) {
                $failedCount++;
                continue;
            }

            // Detect or apply docType
            $docType = $selectedDocType;
            $fnameLower = strtolower($file['name']);
            if ($docType === 'auto' || empty($docType)) {
                if (strpos($fnameLower, 'pwd') !== false) {
                    $docType = 'pwd_id';
                } elseif (strpos($fnameLower, 'psa') !== false || strpos($fnameLower, 'birth') !== false) {
                    $docType = 'psa_birth_cert';
                } elseif (strpos($fnameLower, 'med') !== false || strpos($fnameLower, 'clinic') !== false || strpos($fnameLower, 'diagnos') !== false) {
                    $docType = 'medical_record';
                } elseif (strpos($fnameLower, 'assess') !== false || strpos($fnameLower, 'eval') !== false) {
                    $docType = 'assessment_report';
                } elseif (strpos($fnameLower, 'dll') !== false || strpos($fnameLower, 'lesson') !== false) {
                    $docType = 'dll';
                } elseif (strpos($fnameLower, 'iep') !== false) {
                    $docType = 'physical_iep';
                } else {
                    $docType = 'other';
                }
            }

            // Determine title
            $origBaseName = pathinfo($file['name'], PATHINFO_FILENAME);
            if (!empty($rawTitle)) {
                $title = count($rawFiles) === 1 ? $rawTitle : "{$rawTitle} - {$origBaseName}";
            } else {
                $title = ($typeLabels[$docType] ?? 'Uploaded Document') . (count($rawFiles) > 1 ? " ({$origBaseName})" : "");
            }
            $lastTitle = $title;

            $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $origBaseName);
            $newFilename = 'doc_' . $studentId . '_' . time() . '_' . substr(md5(uniqid()), 0, 6) . '_' . substr($safeName, 0, 25) . '.' . $ext;
            $destPath = $uploadDir . $newFilename;
            $relPath = 'uploads/student_documents/' . $newFilename;

            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                // Map docType for traditional_iep_documents enum ('dll', 'physical_iep', 'assessment_report', 'progress_note', 'other')
                $tradType = in_array($docType, ['physical_iep', 'assessment_report', 'dll', 'progress_note'], true) ? $docType : 'other';

                // Insert into traditional_iep_documents
                $stmtIns = $db->prepare("
                    INSERT INTO traditional_iep_documents (student_id, uploaded_by, document_type, title, file_path, file_size, school_year, quarter, notes, created_at)
                    VALUES (:student_id, :uploaded_by, :document_type, :title, :file_path, :file_size, :school_year, :quarter, :notes, NOW())
                ");
                $stmtIns->execute([
                    'student_id'    => $studentId,
                    'uploaded_by'   => $userId,
                    'document_type' => $tradType,
                    'title'         => $title,
                    'file_path'     => $relPath,
                    'file_size'     => (int)$file['size'],
                    'school_year'   => $schoolYear,
                    'quarter'       => $quarter,
                    'notes'         => $notes
                ]);

                // If it corresponds to standard enrollment documents, also record into enrollment_documents
                if (!empty($student['enrollment_id']) && in_array($docType, ['psa_birth_cert', 'pwd_id', 'medical_record'], true)) {
                    try {
                        $stmtEd = $db->prepare("
                            INSERT INTO enrollment_documents (enrollment_id, document_type, file_path, status, reviewed_by, uploaded_at, reviewed_at)
                            VALUES (:eid, :dtype, :fpath, 'approved', :uid, NOW(), NOW())
                            ON DUPLICATE KEY UPDATE file_path = VALUES(file_path), status = 'approved', reviewed_by = VALUES(reviewed_by), reviewed_at = NOW()
                        ");
                        $stmtEd->execute([
                            'eid'   => $student['enrollment_id'],
                            'dtype' => $docType,
                            'fpath' => $relPath,
                            'uid'   => $userId
                        ]);
                    } catch (\Throwable $e) {
                        error_log('StudentController: enrollment_documents sync error - ' . $e->getMessage());
                    }
                }

                // If physical IEP or assessment report is uploaded by staff, ensure signed IEP record exists so workspace is accessible
                if (in_array($docType, ['physical_iep', 'assessment_report'], true) && in_array($role, ['sped_teacher', 'admin', 'principal', 'master_teacher'], true)) {
                    $hasIepOrAssessment = true;
                    try {
                        require_once __DIR__ . '/../Models/IEPModel.php';
                        $iepModel = new IEPModel();
                        $pdsp = $iepModel->ensureBaselinePdspForStudent($studentId, $userId);
                        
                        // Check if signed IEP already exists
                        $stmtCheckIep = $db->prepare("SELECT id FROM iep_records WHERE student_id = :sid AND school_year = :sy LIMIT 1");
                        $stmtCheckIep->execute(['sid' => $studentId, 'sy' => $schoolYear]);
                        $existingIepId = $stmtCheckIep->fetchColumn();
                        
                        if (!$existingIepId) {
                            $stmtInsIep = $db->prepare("
                                INSERT INTO iep_records (student_id, pdsp_id, drafted_by, school_year, status, signed_document_path, re_evaluation_date, created_at, updated_at)
                                VALUES (:sid, :pid, :uid, :sy, 'signed', :doc, DATE_ADD(CURDATE(), INTERVAL 1 YEAR), NOW(), NOW())
                            ");
                            $stmtInsIep->execute([
                                'sid' => $studentId,
                                'pid' => $pdsp['id'],
                                'uid' => $userId,
                                'sy'  => $schoolYear,
                                'doc' => $relPath,
                            ]);
                            $newIepId = (int)$db->lastInsertId();
                        } else {
                            $newIepId = (int)$existingIepId;
                            $db->prepare("UPDATE iep_records SET status = 'signed', signed_document_path = IFNULL(signed_document_path, :doc) WHERE id = :id")
                               ->execute(['doc' => $relPath, 'id' => $newIepId]);
                        }
                    } catch (\Throwable $e) {
                        error_log('StudentController IEP ensure error: ' . $e->getMessage());
                    }
                }

                $uploadedCount++;
            } else {
                $failedCount++;
            }
        }

        // Parent notification if uploaded by parent
        if ($role === 'parent' && !empty($student['assigned_teacher_id']) && $uploadedCount > 0) {
            try {
                require_once __DIR__ . '/../Models/NotificationModel.php';
                $notifModel = new NotificationModel();
                $notifModel->create(
                    (int)$student['assigned_teacher_id'],
                    'document_uploaded',
                    'New Document(s) Uploaded 📄',
                    "Parent uploaded {$uploadedCount} document(s) for {$student['student_name']}.",
                    ['student_id' => $studentId]
                );
            } catch (\Throwable $e) {}
        }

        if ($uploadedCount > 0) {
            $msg = $uploadedCount === 1 
                ? "Dokumento '{$lastTitle}' malampusong na-upload alang kang {$student['student_name']}!"
                : "Malampusong na-upload ang {$uploadedCount} ka mga dokumento alang kang {$student['student_name']}!";
            if ($hasIepOrAssessment && $newIepId) {
                $msg .= " Na-ablihan na usab ang IEP Workspace! <a href='{$this->basePath}/iep/implementation/workspace/{$newIepId}' class='btn btn-sm btn-primary ms-2' style='border-radius:6px;font-size:0.8rem;padding:2px 10px;'><i class='bi bi-layout-dashboard me-1'></i>Ablihi ang Workspace</a>";
            }
            $_SESSION['success'] = $msg;
        } else {
            $_SESSION['error'] = 'Wala nay na-upload nga dokumento. Palihug susiha ang file format o gidak-on (kutob 15MB).';
        }

        header('Location: ' . $redirectUrl);
        exit;
    }
}
