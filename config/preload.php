<?php

declare(strict_types=1);

$filePath = sprintf('%s/var/cache/prod/App_KernelProdContainer.preload.php', dirname(__DIR__));

if (file_exists($filePath)) {
    require $filePath;
}
