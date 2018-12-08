<?php declare(strict_types = 1);

namespace PHPStan\PhpDoc;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Reflection\Php\PhpPropertyReflection;

class PhpDocBlock
{

	/** @var string */
	private $docComment;

	/** @var string */
	private $file;

	/** @var string */
	private $class;

	private function __construct(
		string $docComment,
		string $file,
		string $class
	)
	{
		$this->docComment = $docComment;
		$this->file = $file;
		$this->class = $class;
	}

	public function getDocComment(): string
	{
		return $this->docComment;
	}

	public function getFile(): string
	{
		return $this->file;
	}

	public function getClass(): string
	{
		return $this->class;
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
			'hasNativeProperty',
			'getNativeProperty',
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
			'hasNativeMethod',
			'getNativeMethod',
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
				$parentClassReflection = $classReflection->getParentClass();
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
			}

			foreach ($classReflection->getInterfaces() as $interface) {
				$phpDocBlockFromClass = self::resolvePhpDocBlockFromClass(
					$broker,
					$interface,
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

		return new self($docComment, $file, $class);
	}

	private static function resolvePhpDocBlockFromClass(
		Broker $broker,
		ClassReflection $classReflection,
		string $name,
		string $hasMethodName,
		string $getMethodName,
		string $resolveMethodName
	): ?self
	{
		if ($classReflection->getFileName() !== false && $classReflection->$hasMethodName($name)) {
			/** @var \PHPStan\Reflection\PropertyReflection|\PHPStan\Reflection\MethodReflection $parentReflection */
			$parentReflection = $classReflection->$getMethodName($name);
			if (
				!$parentReflection instanceof PhpPropertyReflection
				&& !$parentReflection instanceof PhpMethodReflection
			) {
				return null;
			}
			if ($parentReflection->getDocComment() !== false) {
				return self::$resolveMethodName(
					$broker,
					$parentReflection->getDocComment(),
					$classReflection->getName(),
					$name,
					$classReflection->getFileName()
				);
			}
		}

		return null;
	}

}
