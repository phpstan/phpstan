<?php declare(strict_types = 1);

namespace PHPStan\PhpDoc;

use PHPStan\Broker\Broker;

class PhpDocBlock
{

	/** @var string */
	private $docComment;

	/** @var string */
	private $file;

	private function __construct(string $docComment, string $file)
	{
		$this->docComment = $docComment;
		$this->file = $file;
	}

	public function getDocComment(): string
	{
		return $this->docComment;
	}

	public function getFile(): string
	{
		return $this->file;
	}

	public static function resolvePhpDocBlock(
		Broker $broker,
		string $docComment,
		string $class,
		string $methodName,
		string $file
	): self
	{
		if (
			preg_match('#\{@inheritdoc\}#i', $docComment) > 0
			&& $broker->hasClass($class)
		) {
			$classReflection = $broker->getClass($class);
			if ($classReflection->getParentClass() !== false) {
				$parentClassReflection = $classReflection->getParentClass()->getNativeReflection();
				if ($parentClassReflection->getFileName() !== false && $parentClassReflection->hasMethod($methodName)) {
					$parentMethodReflection = $parentClassReflection->getMethod($methodName);
					if ($parentMethodReflection->getDocComment() !== false) {
						return self::resolvePhpDocBlock(
							$broker,
							$parentMethodReflection->getDocComment(),
							$parentClassReflection->getName(),
							$methodName,
							$parentClassReflection->getFileName()
						);
					}
				}
			}
		}

		return new self($docComment, $file);
	}

}
