<?php declare(strict_types = 1);

namespace PHPStan\DependencyInjection;

use Nette\DI\Config\Loader;

class Configurator extends \Nette\Configurator
{

	/** @var LoaderFactory */
	private $loaderFactory;

	public function __construct(LoaderFactory $loaderFactory)
	{
		$this->loaderFactory = $loaderFactory;

		parent::__construct();
	}

	protected function createLoader(): Loader
	{
		return $this->loaderFactory->createLoader();
	}

	/**
	 * @return mixed[]
	 */
	protected function getDefaultParameters(): array
	{
		return [];
	}

}
