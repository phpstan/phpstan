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
				$phpDocBlockFromClass = self::resolvePhpDocBlockFromClass($broker, $parentClassReflection, $methodName);
				if ($phpDocBlockFromClass !== null) {
					return $phpDocBlockFromClass;
				}
			} else {
				foreach ($classReflection->getInterfaces() as $interface) {
					$phpDocBlockFromClass = self::resolvePhpDocBlockFromClass($broker, $interface->getNativeReflection(), $methodName);
					if ($phpDocBlockFromClass !== null) {
						return $phpDocBlockFromClass;
					}
				}
			}
		}

		return new self($docComment, $file);
	}

	/**
	 * @param \PHPStan\Broker\Broker $broker
	 * @param \ReflectionClass $classReflection
	 * @param string $methodName
	 * @return self|null
	 */
	private static function resolvePhpDocBlockFromClass(
		Broker $broker,
		\ReflectionClass $classReflection,
		string $methodName
	)
	{
		if ($classReflection->getFileName() !== false && $classReflection->hasMethod($methodName)) {
			$parentMethodReflection = $classReflection->getMethod($methodName);
			if ($parentMethodReflection->getDocComment() !== false) {
				return self::resolvePhpDocBlock(
					$broker,
					$parentMethodReflection->getDocComment(),
					$classReflection->getName(),
					$methodName,
					$classReflection->getFileName()
				);
			}
		}

		return null;
	}

}
