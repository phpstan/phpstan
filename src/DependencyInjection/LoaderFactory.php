<?php declare(strict_types = 1);

namespace PHPStan\DependencyInjection;

use Nette\DI\Config\Adapters\NeonAdapter;
use Nette\DI\Config\Loader;

class LoaderFactory
{

	public function createLoader(): Loader
	{
		$loader = new Loader();
		$loader->addAdapter('dist', NeonAdapter::class);

		return $loader;
	}

}
