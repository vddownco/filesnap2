<?php

declare(strict_types=1);

use App\Kernel;

require_once sprintf('%s/vendor/autoload_runtime.php', dirname(__DIR__));

return static function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
