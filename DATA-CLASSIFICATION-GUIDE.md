# 📊 Data Classification Guide - SPED LMS

## What is Data Classification?

Data classification is the process of organizing data into categories based on sensitivity level. This helps determine:
- Who can access the data
- How the data should be protected
- How long to retain the data
- What security controls to apply

---

## Data Classification Levels for SPED LMS

### 🔴 **Level 1: HIGHLY CONFIDENTIAL** (Red)
**Definition:** Data that if disclosed could cause severe harm to individuals or the organization.

**Examples in SPED LMS:**
- Student medical records
- Disability diagnoses
- PWD ID numbers
- PSA birth certificate numbers
- IEP assessment details
- Student psychological evaluations
- Parent/guardian financial information (4Ps household ID)
- Login passwords (hashed)
- Encryption keys

**Access:** Restricted to authorized personnel only (SPED Teacher, Guidance, Principal)

**Protection:**
- ✅ Encrypt in database
- ✅ Encrypt in transit (HTTPS)
- ✅ Access logging required
- ✅ DLP watermarking
- ✅ No screenshots allowed
- ✅ Audit trail required

---

### 🟡 **Level 2: CONFIDENTIAL** (Yellow)
**Definition:** Data that if disclosed could cause moderate harm.

**Examples in SPED LMS:**
- Student names and LRN
- Parent/guardian contact information
- Student addresses
- Enrollment documents (non-medical)
- IEP meeting schedules
- Learning modality preferences
- Grade levels
- School attendance records

**Access:** Accessible to relevant staff (Teachers, Guidance, Principal, Admin)

**Protection:**
- ✅ Access control (RBAC)
- ✅ HTTPS required
- ✅ Access logging
- ✅ Regular backups
- ⚠️ Watermarking recommended

---

### 🟢 **Level 3: INTERNAL USE** (Green)
**Definition:** Data for internal use only, minimal harm if disclosed.

**Examples in SPED LMS:**
- User roles (teacher, parent, etc.)
- System activity logs
- Login attempt logs
- Notification messages
- Dashboard statistics
- School year information
- Grade level lists
- Learning modality options

**Access:** All authenticated users

**Protection:**
- ✅ Access control (login required)
- ✅ HTTPS recommended
- ⚠️ Basic logging

---

### ⚪ **Level 4: PUBLIC** (White)
**Definition:** Data that can be publicly shared.

**Examples in SPED LMS:**
- School name and logo
- Public announcements
- General SPED program information
- Contact information (general school email/phone)
- System version information

**Access:** Anyone (no login required)

**Protection:**
- ⚠️ Basic security
- ⚠️ No sensitive data

---

## Where to Implement Data Classification

### 1. **Database Level** (Schema)

Add a `data_classification` column to sensitive tables:

```sql
-- Add to student_records table
ALTER TABLE student_records 
ADD COLUMN data_classification ENUM('public', 'internal', 'confidential', 'highly_confidential') 
DEFAULT 'confidential';

-- Add to enrollment_documents table
ALTER TABLE enrollment_documents 
ADD COLUMN data_classification ENUM('public', 'internal', 'confidential', 'highly_confidential') 
DEFAULT 'highly_confidential';

-- Add to iep_documents table
ALTER TABLE iep_documents 
ADD COLUMN data_classification ENUM('public', 'internal', 'confidential', 'highly_confidential') 
DEFAULT 'highly_confidential';
```

**File:** `config/schema.sql` (add as Migration v20)

---

### 2. **Model Level** (Data Access)

Create a `DataClassificationHelper` to enforce access rules:

```php
// app/Helpers/DataClassificationHelper.php

class DataClassificationHelper {
    const PUBLIC = 'public';
    const INTERNAL = 'internal';
    const CONFIDENTIAL = 'confidential';
    const HIGHLY_CONFIDENTIAL = 'highly_confidential';
    
    /**
     * Check if user can access data with given classification
     */
    public static function canAccess($userRole, $dataClassification) {
        $permissions = [
            'admin' => ['public', 'internal', 'confidential', 'highly_confidential'],
            'principal' => ['public', 'internal', 'confidential', 'highly_confidential'],
            'guidance' => ['public', 'internal', 'confidential', 'highly_confidential'],
            'sped_teacher' => ['public', 'internal', 'confidential', 'highly_confidential'],
            'master_teacher' => ['public', 'internal', 'confidential'],
            'parent' => ['public', 'internal', 'confidential'], // Only their own child's data
            'learner' => ['public', 'internal'], // Only their own data
            'user' => ['public']
        ];
        
        return in_array($dataClassification, $permissions[$userRole] ?? []);
    }
    
    /**
     * Get classification badge HTML
     */
    public static function getBadge($classification) {
        $badges = [
            'public' => '<span class="badge bg-secondary">Public</span>',
            'internal' => '<span class="badge bg-success">Internal</span>',
            'confidential' => '<span class="badge bg-warning">Confidential</span>',
            'highly_confidential' => '<span class="badge bg-danger">Highly Confidential</span>'
        ];
        
        return $badges[$classification] ?? '';
    }
    
    /**
     * Log data access for audit trail
     */
    public static function logAccess($userId, $dataType, $dataId, $classification) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO data_access_log 
            (user_id, data_type, data_id, classification, accessed_at, ip_address)
            VALUES (:user_id, :data_type, :data_id, :classification, NOW(), :ip_address)
        ");
        
        $stmt->execute([
            'user_id' => $userId,
            'data_type' => $dataType,
            'data_id' => $dataId,
            'classification' => $classification,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }
}
```

**File:** `app/Helpers/DataClassificationHelper.php` (create new)

---

### 3. **View Level** (Display)

Show classification badges on sensitive pages:

```php
<!-- In enrollment/view.php -->
<div class="card-header">
    <h5>
        Student Information 
        <?php echo DataClassificationHelper::getBadge('confidential'); ?>
    </h5>
</div>

<!-- In iep/view.php -->
<div class="card-header">
    <h5>
        IEP Document 
        <?php echo DataClassificationHelper::getBadge('highly_confidential'); ?>
    </h5>
</div>
```

**Files to update:**
- `app/Views/enrollment/view.php`
- `app/Views/verification/show.php`
- `app/Views/iep/p2_review.php`
- `app/Views/iep/p3_sign.php`
- `app/Views/assessment/view.php`

---

### 4. **Controller Level** (Access Control)

Check classification before allowing access:

```php
// In VerificationController->show()
public function show($id) {
    $enrollment = $this->enrollmentModel->findById($id);
    
    // Check if user can access this classification level
    if (!DataClassificationHelper::canAccess($_SESSION['role'], 'confidential')) {
        http_response_code(403);
        echo "Access Denied: Insufficient permissions for this data classification level";
        return;
    }
    
    // Log access for audit trail
    DataClassificationHelper::logAccess(
        $_SESSION['user_id'],
        'enrollment',
        $id,
        'confidential'
    );
    
    // ... rest of code
}
```

**Files to update:**
- `app/Controllers/VerificationController.php`
- `app/Controllers/IEPDocumentController.php`
- `app/Controllers/AssessmentController.php`
- `app/Controllers/EnrollmentController.php`

---

### 5. **Database Audit Log**

Create a table to track access to classified data:

```sql
-- Migration v21: Data Access Audit Log
CREATE TABLE IF NOT EXISTS data_access_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    data_type VARCHAR(50) NOT NULL,
    data_id INT NOT NULL,
    classification ENUM('public', 'internal', 'confidential', 'highly_confidential') NOT NULL,
    accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_data_type (data_type, data_id),
    INDEX idx_classification (classification),
    INDEX idx_accessed_at (accessed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**File:** `config/schema.sql` (add as Migration v21)

---

### 6. **Admin Dashboard** (Monitoring)

Create a page to view data access by classification:

```php
// app/Controllers/AdminController.php

public function dataAccessLogs() {
    $classification = $_GET['classification'] ?? 'all';
    $limit = (int)($_GET['limit'] ?? 100);
    
    $sql = "
        SELECT dal.*, u.name as user_name, u.email as user_email, u.role
        FROM data_access_log dal
        LEFT JOIN users u ON dal.user_id = u.id
        WHERE 1=1
    ";
    
    if ($classification !== 'all') {
        $sql .= " AND dal.classification = :classification";
    }
    
    $sql .= " ORDER BY dal.accessed_at DESC LIMIT :limit";
    
    // ... execute query and load view
}
```

**Files to create:**
- `app/Views/admin/data_access_logs.php` (new view)
- Add route in `routes/web.php`
- Add sidebar link in admin navigation

---

## Implementation Priority

### Phase 1: Foundation (Week 1)
1. ✅ Create `DataClassificationHelper.php`
2. ✅ Add `data_access_log` table (Migration v21)
3. ✅ Add classification columns to sensitive tables (Migration v20)

### Phase 2: Core Features (Week 2)
4. ✅ Implement access control in controllers
5. ✅ Add classification badges to views
6. ✅ Log access to highly confidential data

### Phase 3: Monitoring (Week 3)
7. ✅ Create admin data access logs view
8. ✅ Add classification filters
9. ✅ Generate classification reports

---

## Visual Indicators

### Classification Badges

```html
<!-- Public -->
<span class="badge bg-secondary">
    <i class="bi bi-globe"></i> Public
</span>

<!-- Internal -->
<span class="badge bg-success">
    <i class="bi bi-building"></i> Internal Use
</span>

<!-- Confidential -->
<span class="badge bg-warning text-dark">
    <i class="bi bi-lock"></i> Confidential
</span>

<!-- Highly Confidential -->
<span class="badge bg-danger">
    <i class="bi bi-shield-lock"></i> Highly Confidential
</span>
```

### Watermarks

For highly confidential documents, add watermark:

```css
.highly-confidential::after {
    content: 'HIGHLY CONFIDENTIAL';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-45deg);
    font-size: 4rem;
    font-weight: bold;
    color: rgba(220, 53, 69, 0.1);
    pointer-events: none;
}
```

---

## Compliance & Best Practices

### Data Protection Act (Philippines)
- ✅ Classify personal data appropriately
- ✅ Implement access controls
- ✅ Maintain audit logs
- ✅ Encrypt sensitive data
- ✅ Regular security reviews

### DepEd Guidelines
- ✅ Protect student privacy
- ✅ Secure medical records
- ✅ Control access to IEP documents
- ✅ Maintain confidentiality

---

## Summary

**Where to put data classification:**

1. **Database:** Add classification columns (schema.sql)
2. **Helper:** Create DataClassificationHelper.php
3. **Controllers:** Check access before showing data
4. **Views:** Display classification badges
5. **Audit:** Log access to classified data
6. **Admin:** Monitor data access by classification

**Start with:** Create the helper class and add it to highly confidential pages (IEP, medical records, assessments).

