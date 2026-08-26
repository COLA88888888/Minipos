<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>1. PHP Version & Extensions Check</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "PDO Installed: " . (extension_loaded('pdo') ? 'YES' : 'NO') . "<br>";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? 'YES' : 'NO') . "<br>";

echo "<h2>2. Testing Database Connection</h2>";
if (file_exists(__DIR__ . '/config/db.php')) {
    require_once __DIR__ . '/config/db.php';
    echo "config/db.php loaded successfully!<br>";
    if (isset($pdo)) {
        echo "Database connected OK!<br>";
    } else {
        echo "<b>Error:</b> \$pdo variable is not set!<br>";
    }
} else {
    echo "<b>Error:</b> config/db.php not found!<br>";
}

echo "<h2>3. Testing Dashboard Includes</h2>";
if (file_exists(__DIR__ . '/home/dashboard.php')) {
    echo "home/dashboard.php exists!<br>";
} else {
    echo "home/dashboard.php NOT found!<br>";
}