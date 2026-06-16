<?php
// Individualized Transition Plan view for Process 11
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Individualized Transition Plan</title>
    <link rel="stylesheet" href="<?= $basePath ?>/css/app.css">
</head>
<body>
    <div class="container">
        <h1>Individualized Transition Plan</h1>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= $basePath ?>/iep/<?= intval($iep['id']) ?>/itp/save">
            <div class="form-group">
                <label for="itp_summary">ITP Summary</label>
                <textarea id="itp_summary" name="itp_summary" class="form-control" rows="5"><?= htmlspecialchars($itp['itp_summary'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="entry_point">Entry Point</label>
                <input id="entry_point" name="entry_point" type="text" class="form-control" value="<?= htmlspecialchars($itp['entry_point'] ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-primary">Save ITP</button>
        </form>
    </div>
</body>
</html>
