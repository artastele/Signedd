<?php
// Class Placement Notice view for Process 13
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Class Placement Notice</title>
    <link rel="stylesheet" href="<?= $basePath ?>/css/app.css">
</head>
<body>
    <div class="container">
        <h1>Class Placement Notice</h1>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= $basePath ?>/iep/<?= intval($iep['id']) ?>/placement-notice/save">
            <div class="form-group">
                <label for="receiving_teacher_id">Receiving Teacher</label>
                <select id="receiving_teacher_id" name="receiving_teacher_id" class="form-control">
                    <option value="">Select a teacher</option>
                    <?php foreach ($teachers as $teacher): ?>
                        <option value="<?= intval($teacher['id']) ?>" <?= (isset($placement['receiving_teacher_id']) && $placement['receiving_teacher_id'] == $teacher['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($teacher['name'] ?? $teacher['email'] ?? '') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="target_grade_section">Target Grade / Section</label>
                <input id="target_grade_section" name="target_grade_section" type="text" class="form-control" value="<?= htmlspecialchars($placement['target_grade_section'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="effective_date">Effective Date</label>
                <input id="effective_date" name="effective_date" type="date" class="form-control" value="<?= htmlspecialchars($placement['effective_date'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="support_needed">Support Needed</label>
                <textarea id="support_needed" name="support_needed" class="form-control" rows="4"><?= htmlspecialchars($placement['support_needed'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="placement_status">Placement Status</label>
                <select id="placement_status" name="placement_status" class="form-control">
                    <?php foreach (['Draft', 'Notice Sent', 'Placed'] as $status): ?>
                        <option value="<?= $status ?>" <?= (($placement['placement_status'] ?? '') === $status) ? 'selected' : '' ?>><?= $status ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="approval_status">Approval Status</label>
                <select id="approval_status" name="approval_status" class="form-control">
                    <?php foreach (['draft', 'pending', 'approved', 'rejected'] as $status): ?>
                        <option value="<?= $status ?>" <?= (($placement['approval_status'] ?? '') === $status) ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Save Placement Notice</button>
        </form>
    </div>
</body>
</html>
