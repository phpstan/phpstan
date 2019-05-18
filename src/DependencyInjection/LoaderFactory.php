<?php declare(strict_types = 1);

namespace PHPStan\DependencyInjection;

use Nette\DI\Config\Adapters\NeonAdapter;
use Nette\DI\Config\Loader;

class LoaderFactory
{

	/** @var string */
	private $rootDir;

	/** @var string */
	private $currentWorkingDirectory;

	public function __construct(
		string $rootDir,
		string $currentWorkingDirectory
	)
	{
		$this->rootDir = $rootDir;
		$this->currentWorkingDirectory = $currentWorkingDirectory;
	}

	public function createLoader(): Loader
	{
		$loader = new Loader();
		$loader->addAdapter('dist', NeonAdapter::class);
		$loader->setParameters([
			'rootDir' => $this->rootDir,
			'currentWorkingDirectory' => $this->currentWorkingDirectory,
		]);

		return $loader;
	}

}
