<?php
// Inclusive IEP + ITGP view for Process 12
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inclusive IEP and ITGP</title>
    <link rel="stylesheet" href="<?= $basePath ?>/css/app.css">
</head>
<body>
    <div class="container">
        <h1>Inclusive IEP & ITGP</h1>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= $basePath ?>/iep/<?= intval($iep['id']) ?>/inclusive-iep-itgp/save">
            <div class="form-group">
                <label for="disability">Disability</label>
                <input id="disability" name="disability" type="text" class="form-control" value="<?= htmlspecialchars($_POST['disability'] ?? ($inclusive['disability'] ?? '')) ?>">
            </div>
            <div class="form-group">
                <label for="entry_point">Entry Point</label>
                <input id="entry_point" name="entry_point" type="text" class="form-control" value="<?= htmlspecialchars($inclusive['entry_point'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="generated_summary">Generated Summary</label>
                <textarea id="generated_summary" name="generated_summary" class="form-control" rows="4"><?= htmlspecialchars($inclusive['generated_summary'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="itgp_goal">ITGP Goal</label>
                <textarea id="itgp_goal" name="itgp_goal" class="form-control" rows="3"><?= htmlspecialchars($itgp['goal'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="learning_packages">Learning Packages</label>
                <input id="learning_packages" name="learning_packages" type="text" class="form-control" value="<?= htmlspecialchars($itgp['learning_packages'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="competency_skill">Competency / Skill</label>
                <input id="competency_skill" name="competency_skill" type="text" class="form-control" value="<?= htmlspecialchars($itgp['competency_skill'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="activities">Activities</label>
                <textarea id="activities" name="activities" class="form-control" rows="4"><?= htmlspecialchars($itgp['activities'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="time_frame">Time Frame</label>
                <input id="time_frame" name="time_frame" type="text" class="form-control" value="<?= htmlspecialchars($itgp['time_frame'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="person_responsible">Person Responsible</label>
                <input id="person_responsible" name="person_responsible" type="text" class="form-control" value="<?= htmlspecialchars($itgp['person_responsible'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="itgp_remarks">Remarks</label>
                <textarea id="itgp_remarks" name="itgp_remarks" class="form-control" rows="3"><?= htmlspecialchars($itgp['remarks'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="itgp_recommendations">Recommendations</label>
                <textarea id="itgp_recommendations" name="itgp_recommendations" class="form-control" rows="4"><?= htmlspecialchars($itgp['recommendations'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Save Inclusive IEP & ITGP</button>
        </form>
    </div>
</body>
</html>
