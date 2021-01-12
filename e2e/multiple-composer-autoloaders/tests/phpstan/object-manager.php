<?php

declare(strict_types=1);

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;

require dirname(__DIR__, 2).'/vendor/autoload.php';

umask(0000);

if (class_exists(Debug::class)) {
    Debug::enable();
}

$kernel = new Kernel('test', true);
$kernel->boot();

return $kernel->getContainer()->get('doctrine')->getManager();
