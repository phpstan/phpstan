<?php declare(strict_types = 1);

namespace PHPStan\DependencyInjection;

use Nette\Configurator;
use Nette\DI\Extensions\PhpExtension;
use PHPStan\File\FileHelper;

class ContainerFactory
{

	/**
	 * @var string
	 */
	private $currentWorkingDirectory;

	/**
	 * @var string
	 */
	private $rootDirectory;

	/**
	 * @var string
	 */
	private $configDirectory;

	public function __construct(string $currentWorkingDirectory)
	{
		$this->currentWorkingDirectory = $currentWorkingDirectory;
		$fileHelper = new FileHelper($currentWorkingDirectory);
		$this->rootDirectory = $fileHelper->normalizePath(__DIR__ . '/../..');
		$this->configDirectory = $this->rootDirectory . '/conf';
	}

	public function create(
		string $tempDirectory,
		array $additionalConfigFiles
	): \Nette\DI\Container
	{
		$configurator = new Configurator();
		$configurator->defaultExtensions = [
			'php' => PhpExtension::class,
			'extensions' => \Nette\DI\Extensions\ExtensionsExtension::class,
		];
		$configurator->setDebugMode(true);
		$configurator->setTempDirectory($tempDirectory);
		$configurator->addParameters([
			'rootDir' => $this->rootDirectory,
			'currentWorkingDirectory' => $this->currentWorkingDirectory,
			'cliArgumentsVariablesRegistered' => ini_get('register_argc_argv') === '1',
			'tmpDir' => $tempDirectory,
		]);
		$configurator->addConfig($this->configDirectory . '/config.neon');
		foreach ($additionalConfigFiles as $additionalConfigFile) {
			$configurator->addConfig($additionalConfigFile);
		}

		return $configurator->createContainer();
	}

	public function getCurrentWorkingDirectory(): string
	{
		return $this->currentWorkingDirectory;
	}

	public function getRootDirectory(): string
	{
		return $this->rootDirectory;
	}

	public function getConfigDirectory(): string
	{
		return $this->configDirectory;
	}

}
