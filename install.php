<?php
$host = '127.0.0.1';
$user = 'root';
$pass = 'root';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create DB
    $pdo->exec("CREATE DATABASE IF NOT EXISTS bracket_list_db");
    $pdo->exec("USE bracket_list_db");

    // Tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100),
        username VARCHAR(50) UNIQUE,
        email VARCHAR(100) UNIQUE,
        password VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE,
        password VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS tournaments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255),
        round_size INT,
        status ENUM('Upcoming', 'Live', 'Completed') DEFAULT 'Upcoming',
        champion_team_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS teams (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tournament_id INT,
        team_name VARCHAR(100),
        team_logo VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS matches (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tournament_id INT,
        round_no INT,
        match_no INT,
        team1_id INT NULL,
        team2_id INT NULL,
        winner_team_id INT NULL,
        next_match_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE
    )");

    // Insert Default Admin
    $stmt = $pdo->prepare("SELECT id FROM admin WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        $hashed = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO admin (username, password) VALUES ('admin', '$hashed')");
    }

    // Create upload folder
    if (!is_dir(__DIR__ . '/upload')) {
        mkdir(__DIR__ . '/upload', 0777, true);
    }

    echo "<div style='background:#111; color:#0f0; padding:20px; font-family:monospace;'>";
    echo "<h2>Installation Complete</h2>";
    echo "<p>Database and tables created successfully.</p>";
    echo "<p>Admin: admin / admin123</p>";
    echo "<a href='admin/login.php' style='color:#0ff;'>Go to Admin Login</a>";
    echo "</div>";

} catch(PDOException $e) {
    die("Installation Failed: " . $e->getMessage());
}
?>
