<?php
$autoloader = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloader)) {
    // Create a minimal stub so files expecting the autoloader do not fail
    if (!is_dir(dirname($autoloader))) {
        mkdir(dirname($autoloader), 0775, true);
    }
    file_put_contents($autoloader, "<?php\n// stub autoloader for tests\n");
}
require_once $autoloader;
