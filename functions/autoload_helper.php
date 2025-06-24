<?php
function require_vendor_autoload(string $baseDir): void
{
    $autoload = rtrim($baseDir, '/').'/vendor/autoload.php';
    if (!file_exists($autoload)) {
        echo 'Vendor autoload not found. Please run "composer install".';
        exit;
    }
    require_once $autoload;
}
