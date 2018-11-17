<?php declare(strict_types = 1);

namespace PHPStan\Dependency;

use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\ScopeContext;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\File\FileHelper;
use PHPStan\Parser\Parser;

class DependencyDumper
{

	/** @var DependencyResolver */
	private $dependencyResolver;

	/** @var NodeScopeResolver */
	private $nodeScopeResolver;

	/** @var FileHelper */
	private $fileHelper;

	/** @var Parser */
	private $parser;

	/** @var ScopeFactory */
	private $scopeFactory;

	public function __construct(
		DependencyResolver $dependencyResolver,
		NodeScopeResolver $nodeScopeResolver,
		FileHelper $fileHelper,
		Parser $parser,
		ScopeFactory $scopeFactory
	)
	{
		$this->dependencyResolver = $dependencyResolver;
		$this->nodeScopeResolver = $nodeScopeResolver;
		$this->fileHelper = $fileHelper;
		$this->parser = $parser;
		$this->scopeFactory = $scopeFactory;
	}

	/**
	 * @param string[] $files
	 * @param callable(int $count): void $countCallback
	 * @param callable(): void $progressCallback
	 * @return string[][]
	 */
	public function dumpDependencies(
		array $files,
		callable $countCallback,
		callable $progressCallback
	): array
	{
		$this->nodeScopeResolver->setAnalysedFiles($files);
		$analysedFiles = array_fill_keys($files, true);

		$dependencies = [];
		$countCallback(count($files));
		foreach ($files as $file) {
			$fileDependencies = [];
			$this->nodeScopeResolver->processNodes(
				$this->parser->parseFile($file),
				$this->scopeFactory->create(ScopeContext::create($file)),
				function (\PhpParser\Node $node, Scope $scope) use ($analysedFiles, &$fileDependencies): void {
					$fileDependencies = array_merge(
						$fileDependencies,
						$this->resolveDependencies($node, $scope, $analysedFiles)
					);
				}
			);

			foreach (array_unique($fileDependencies) as $fileDependency) {
				$relativeDependencyFile = $fileDependency;
				$dependencies[$relativeDependencyFile][] = $file;
			}

			$progressCallback();
		}

		return $dependencies;
	}

	/**
	 * @param \PhpParser\Node $node
	 * @param Scope $scope
	 * @param array<string, true> $analysedFiles
	 * @return string[]
	 */
	private function resolveDependencies(
		\PhpParser\Node $node,
		Scope $scope,
		array $analysedFiles
	): array
	{
		$dependencies = [];

		foreach ($this->dependencyResolver->resolveDependencies($node, $scope) as $dependencyReflection) {
			$dependencyFile = $dependencyReflection->getFileName();
			if ($dependencyFile === false) {
				continue;
			}
			$dependencyFile = $this->fileHelper->normalizePath($dependencyFile);

			if ($scope->getFile() === $dependencyFile) {
				continue;
			}

			if (!isset($analysedFiles[$dependencyFile])) {
				continue;
			}

			$dependencies[$dependencyFile] = $dependencyFile;
		}

		return array_values($dependencies);
	}

}
