<?php
// Transition Readiness view for Process 10
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transition Readiness</title>
    <link rel="stylesheet" href="<?= $basePath ?>/css/app.css">
</head>
<body>
    <div class="container">
        <h1>Transition Readiness</h1>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= $basePath ?>/iep/<?= intval($iep['id']) ?>/transition-readiness/save">
            <div class="form-group">
                <label for="readiness_summary">Readiness Summary</label>
                <textarea id="readiness_summary" name="readiness_summary" class="form-control" rows="5"><?= htmlspecialchars($readiness['readiness_summary'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="transition_recommendations">Recommendations</label>
                <textarea id="transition_recommendations" name="transition_recommendations" class="form-control" rows="4"><?= htmlspecialchars($readiness['transition_recommendations'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Save Readiness</button>
        </form>
    </div>
</body>
</html>
