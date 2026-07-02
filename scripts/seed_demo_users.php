<?php
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/db.php';

$db = Database::getInstance()->getConnection();

$roles = ['user','parent','sped_teacher','guidance','principal','master_teacher','learner','admin'];
$password = 'password';

foreach ($roles as $role) {
    $email = "demo+" . $role . "@example.local";

    // check existing
    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $exists = $stmt->fetch();
    if ($exists) {
        echo "Skipping existing user: $email\n";
        continue;
    }

    $name = ucfirst(str_replace('_',' ',$role)) . " Demo";
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    $insert = $db->prepare("INSERT INTO users (name, first_name, last_name, email, password_hash, role, status, email_verified) VALUES (:name, :first_name, :last_name, :email, :password_hash, :role, 'active', 1)");
    $insert->execute([
        'name' => $name,
        'first_name' => ucfirst($role),
        'last_name' => 'Demo',
        'email' => $email,
        'password_hash' => $passwordHash,
        'role' => $role
    ]);

    echo "Created user: $email with role $role\n";
}

echo "Demo seeding complete.\n";
