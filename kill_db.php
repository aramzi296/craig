<?php
$host = '127.0.0.1';
$db   = 'craig_db';
$user = 'craig_user';
$pass = 'secret';
$port = 5434;

$dsn = "pgsql:host=$host;port=$port;dbname=$db";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    // Terminate the blocking queries
    $stmt = $pdo->query("SELECT pid, query FROM pg_stat_activity WHERE state = 'active' AND query ILIKE '%SELECT id, email, whatsa%'");
    $count = 0;
    while ($row = $stmt->fetch()) {
        echo "Killing PID {$row['pid']}: {$row['query']}\n";
        $pdo->exec("SELECT pg_terminate_backend({$row['pid']})");
        $count++;
    }
    
    // Fallback: Terminate all connections to this DB except ours
    if ($count == 0) {
        $stmt2 = $pdo->query("SELECT pid, query FROM pg_stat_activity WHERE datname = '$db' AND pid <> pg_backend_pid()");
        while ($row = $stmt2->fetch()) {
            echo "Killing generic PID {$row['pid']}: {$row['query']}\n";
            $pdo->exec("SELECT pg_terminate_backend({$row['pid']})");
            $count++;
        }
    }
    
    echo "Killed $count connections.\n";
} catch (PDOException $e) {
    echo "PDO Error: " . $e->getMessage() . "\n";
}
