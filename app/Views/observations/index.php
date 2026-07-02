<?php
// Observation / COT view for Process 9
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Observation / COT</title>
    <link rel="stylesheet" href="<?= $basePath ?>/css/app.css">
</head>
<body>
    <div class="container">
        <h1>Observation / Certificate of Transition</h1>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($cot)): ?>
            <h2>Existing COT</h2>
            <pre><?= htmlspecialchars(print_r($cot, true)) ?></pre>
        <?php endif; ?>

        <form method="post" action="<?= $basePath ?>/iep/<?= intval($iep['id']) ?>/observation/save">
            <div class="form-group">
                <label for="observation_notes">Observation Notes</label>
                <textarea id="observation_notes" name="observation_notes" class="form-control" rows="5"><?= htmlspecialchars($cot['observation_notes'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="recommendations">Recommendations</label>
                <textarea id="recommendations" name="recommendations" class="form-control" rows="4"><?= htmlspecialchars($cot['recommendations'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Save Observation</button>
        </form>
    </div>
</body>
</html>
