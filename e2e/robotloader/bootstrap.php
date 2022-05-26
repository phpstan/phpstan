<?php declare(strict_types = 1);

$loader = new Nette\Loaders\RobotLoader;

$loader->addDirectory(__DIR__ . '/src');
$loader->setTempDirectory(__DIR__ . '/tmp');
$loader->register();
