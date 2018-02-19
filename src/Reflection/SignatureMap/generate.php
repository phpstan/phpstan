<?php declare(strict_types = 1);

namespace PHPStan\Reflection\SignatureMap;

use PHPStan\Broker\Broker;
use PHPStan\DependencyInjection\ContainerFactory;

require __DIR__ . '/../../../vendor/autoload.php';

$rootDir = __DIR__ . '/../../..';
$containerFactory = new ContainerFactory($rootDir);
$container = $containerFactory->create($rootDir . '/tmp', []);
$container->getByType(Broker::class); // so that Broker::getInstance() works

try {
	\PHPStan\Type\TypeCombinator::setUnionTypesEnabled(true);
} catch (\PHPStan\ShouldNotHappenException $e) {
	return; // PHPUnit tries to run this script again ¯\_(ツ)_/¯
}

/** @var SignatureMapParser $parser */
$parser = $container->getByType(SignatureMapParser::class);
$functionSignatures = $parser->getFunctions(require __DIR__ . '/functionMap.php');

$progressBar = new \Symfony\Component\Console\Helper\ProgressBar(new \Symfony\Component\Console\Output\ConsoleOutput());
$progressBar->start(count($functionSignatures));

/** @var FunctionDumper $dumper */
$dumper = $container->getByType(FunctionDumper::class);
foreach ($functionSignatures as $name => $functionSignature) {
	$dumper->dump($name, $functionSignature);
	$progressBar->advance();
}

$progressBar->finish();
