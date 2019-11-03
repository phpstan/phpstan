<?php declare(strict_types = 1);

namespace PHPStan\PhpDoc;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Parser\Parser;
use PHPStan\Type\FileTypeMapper;

class StubPhpDocProvider
{

	/** @var \PHPStan\Parser\Parser */
	private $parser;

	/** @var \PHPStan\Type\FileTypeMapper */
	private $fileTypeMapper;

	/** @var \PHPStan\PhpDoc\TypeNodeResolver */
	private $typeNodeResolver;

	/** @var string[] */
	private $stubFiles;

	/** @var array<string, ResolvedPhpDocBlock>|null */
	private $classMap;

	/** @var array<string, array<string, ResolvedPhpDocBlock>>|null */
	private $propertyMap;

	/** @var array<string, array<string, ResolvedPhpDocBlock>>|null */
	private $methodMap;

	/** @var bool */
	private $initialized = false;

	/** @var bool */
	private $initializing = false;

	/**
	 * @param \PHPStan\Parser\Parser $parser
	 * @param string[] $stubFiles
	 */
	public function __construct(
		Parser $parser,
		FileTypeMapper $fileTypeMapper,
		TypeNodeResolver $typeNodeResolver,
		array $stubFiles
	)
	{
		$this->parser = $parser;
		$this->fileTypeMapper = $fileTypeMapper;
		$this->typeNodeResolver = $typeNodeResolver;
		$this->stubFiles = $stubFiles;
	}

	public function findClassPhpDoc(string $className): ?ResolvedPhpDocBlock
	{
		if (!$this->initialized) {
			$this->initialize();
		}

		if (isset($this->classMap[$className])) {
			return $this->classMap[$className];
		}

		return null;
	}

	public function findPropertyPhpDoc(string $className, string $propertyName): ?ResolvedPhpDocBlock
	{
		if (!$this->initialized) {
			$this->initialize();
		}

		if (isset($this->propertyMap[$className][$propertyName])) {
			return $this->propertyMap[$className][$propertyName];
		}

		return null;
	}

	public function findMethodPhpDoc(string $className, string $methodName): ?ResolvedPhpDocBlock
	{
		if (!$this->initialized) {
			$this->initialize();
		}

		if (isset($this->methodMap[$className][$methodName])) {
			return $this->methodMap[$className][$methodName];
		}

		return null;
	}

	private function initialize(): void
	{
		if ($this->initializing) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$this->initializing = true;
		$this->typeNodeResolver->disableIterableGenericFallback();

		foreach ($this->stubFiles as $stubFile) {
			$nodes = $this->parser->parseFile($stubFile);
			$this->processNodes($stubFile, $nodes);
		}

		$this->initializing = false;
		$this->initialized = true;
		$this->typeNodeResolver->enableIterableGenericFallback();
	}

	/**
	 * @param string $stubFile
	 * @param \PhpParser\Node[] $nodes
	 */
	private function processNodes(string $stubFile, array $nodes): void
	{
		foreach ($nodes as $node) {
			$this->processNode($stubFile, $node);
		}
	}

	private function processNode(string $stubFile, Node $node): void
	{
		if (!$node instanceof Class_ && !$node instanceof Interface_ && !$node instanceof Trait_) {
			return;
		}
		if (!isset($node->namespacedName)) {
			return;
		}

		$className = (string) $node->namespacedName;
		if ($node->getDocComment() !== null) {
			$this->classMap[$className] = $this->fileTypeMapper->getResolvedPhpDoc(
				$stubFile,
				$className,
				null,
				null,
				$node->getDocComment()->getText()
			);
		}

		foreach ($node->stmts as $stmt) {
			if ($stmt->getDocComment() === null) {
				continue;
			}

			if ($stmt instanceof Node\Stmt\Property) {
				$resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
					$stubFile,
					$className,
					null,
					null,
					$stmt->getDocComment()->getText()
				);

				foreach ($stmt->props as $property) {
					$this->propertyMap[$className][$property->name->toString()] = $resolvedPhpDoc;
				}
				continue;
			} elseif ($stmt instanceof Node\Stmt\ClassMethod) {
				$methodName = $stmt->name->toString();
				$resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
					$stubFile,
					$className,
					null,
					$methodName,
					$stmt->getDocComment()->getText()
				);
				$this->methodMap[$className][$methodName] = $resolvedPhpDoc;
			}
		}
	}

}
