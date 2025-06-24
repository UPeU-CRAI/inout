<?php
function require_vendor_autoload(string $baseDir): void
{
    $autoload = rtrim($baseDir, '/').'/vendor/autoload.php';
    if (!file_exists($autoload)) {
        throw new RuntimeException(
            'Vendor autoload not found. Please run "composer install".'
        );
    }
    require_once $autoload;
}
