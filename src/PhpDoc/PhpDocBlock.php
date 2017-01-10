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

	public static function resolvePhpDocBlockForProperty(
		Broker $broker,
		string $docComment,
		string $class,
		string $propertyName,
		string $file
	): self
	{
		return self::resolvePhpDocBlock(
			$broker,
			$docComment,
			$class,
			$propertyName,
			$file,
			'hasProperty',
			'getProperty',
			__FUNCTION__
		);
	}

	public static function resolvePhpDocBlockForMethod(
		Broker $broker,
		string $docComment,
		string $class,
		string $methodName,
		string $file
	): self
	{
		return self::resolvePhpDocBlock(
			$broker,
			$docComment,
			$class,
			$methodName,
			$file,
			'hasMethod',
			'getMethod',
			__FUNCTION__
		);
	}

	private static function resolvePhpDocBlock(
		Broker $broker,
		string $docComment,
		string $class,
		string $name,
		string $file,
		string $hasMethodName,
		string $getMethodName,
		string $resolveMethodName
	): self
	{
		if (
			preg_match('#\{@inheritdoc\}#i', $docComment) > 0
			&& $broker->hasClass($class)
		) {
			$classReflection = $broker->getClass($class);
			if ($classReflection->getParentClass() !== false) {
				$parentClassReflection = $classReflection->getParentClass()->getNativeReflection();
				$phpDocBlockFromClass = self::resolvePhpDocBlockFromClass(
					$broker,
					$parentClassReflection,
					$name,
					$hasMethodName,
					$getMethodName,
					$resolveMethodName
				);
				if ($phpDocBlockFromClass !== null) {
					return $phpDocBlockFromClass;
				}
			} else {
				foreach ($classReflection->getInterfaces() as $interface) {
					$phpDocBlockFromClass = self::resolvePhpDocBlockFromClass(
						$broker,
						$interface->getNativeReflection(),
						$name,
						$hasMethodName,
						$getMethodName,
						$resolveMethodName
					);
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
	 * @param string $name
	 * @param string $hasMethodName
	 * @param string $getMethodName
	 * @param string $resolveMethodName
	 * @return self|null
	 */
	private static function resolvePhpDocBlockFromClass(
		Broker $broker,
		\ReflectionClass $classReflection,
		string $name,
		string $hasMethodName,
		string $getMethodName,
		string $resolveMethodName
	)
	{
		if ($classReflection->getFileName() !== false && $classReflection->$hasMethodName($name)) {
			$parentMethodReflection = $classReflection->$getMethodName($name);
			if ($parentMethodReflection->getDocComment() !== false) {
				return self::$resolveMethodName(
					$broker,
					$parentMethodReflection->getDocComment(),
					$classReflection->getName(),
					$name,
					$classReflection->getFileName()
				);
			}
		}

		return null;
	}

}
