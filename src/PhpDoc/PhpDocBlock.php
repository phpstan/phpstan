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

	/** @var string|null */
	private $trait;

	/** @var bool */
	private $explicit;

	private function __construct(
		string $docComment,
		string $file,
		string $class,
		?string $trait,
		bool $explicit
	)
	{
		$this->docComment = $docComment;
		$this->file = $file;
		$this->class = $class;
		$this->trait = $trait;
		$this->explicit = $explicit;
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

	public function getTrait(): ?string
	{
		return $this->trait;
	}

	public function isExplicit(): bool
	{
		return $this->explicit;
	}

	public static function resolvePhpDocBlockForProperty(
		Broker $broker,
		?string $docComment,
		string $class,
		?string $trait,
		string $propertyName,
		string $file,
		?bool $explicit = null
	): ?self
	{
		return self::resolvePhpDocBlock(
			$broker,
			$docComment,
			$class,
			$trait,
			$propertyName,
			$file,
			'hasNativeProperty',
			'getNativeProperty',
			__FUNCTION__,
			$explicit
		);
	}

	public static function resolvePhpDocBlockForMethod(
		Broker $broker,
		?string $docComment,
		string $class,
		?string $trait,
		string $methodName,
		string $file,
		?bool $explicit = null
	): ?self
	{
		return self::resolvePhpDocBlock(
			$broker,
			$docComment,
			$class,
			$trait,
			$methodName,
			$file,
			'hasNativeMethod',
			'getNativeMethod',
			__FUNCTION__,
			$explicit
		);
	}

	private static function resolvePhpDocBlock(
		Broker $broker,
		?string $docComment,
		string $class,
		?string $trait,
		string $name,
		string $file,
		string $hasMethodName,
		string $getMethodName,
		string $resolveMethodName,
		?bool $explicit
	): ?self
	{
		if (
			(
				$docComment === null
				|| preg_match('#@inheritdoc|\{@inheritdoc\}#i', $docComment) > 0
			)
			&& $broker->hasClass($class)
		) {
			$classReflection = $broker->getClass($class);
			if ($classReflection->getParentClass() !== false) {
				$parentClassReflection = $classReflection->getParentClass();
				$phpDocBlockFromClass = self::resolvePhpDocBlockRecursive(
					$broker,
					$parentClassReflection,
					$trait,
					$name,
					$hasMethodName,
					$getMethodName,
					$resolveMethodName,
					$explicit ?? $docComment !== null
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
					$resolveMethodName,
					$explicit ?? $docComment !== null
				);
				if ($phpDocBlockFromClass !== null) {
					return $phpDocBlockFromClass;
				}
			}
		}

		return $docComment !== null
			? new self($docComment, $file, $class, $trait, $explicit ?? true)
			: null;
	}

	private static function resolvePhpDocBlockRecursive(
		Broker $broker,
		ClassReflection $classReflection,
		?string $trait,
		string $name,
		string $hasMethodName,
		string $getMethodName,
		string $resolveMethodName,
		bool $explicit
	): ?self
	{
		$phpDocBlockFromClass = self::resolvePhpDocBlockFromClass(
			$broker,
			$classReflection,
			$name,
			$hasMethodName,
			$getMethodName,
			$resolveMethodName,
			$explicit
		);

		if ($phpDocBlockFromClass !== null) {
			return $phpDocBlockFromClass;
		}

		$parentClassReflection = $classReflection->getParentClass();
		if ($parentClassReflection !== false) {
			return self::resolvePhpDocBlockRecursive(
				$broker,
				$parentClassReflection,
				$trait,
				$name,
				$hasMethodName,
				$getMethodName,
				$resolveMethodName,
				$explicit
			);
		}

		return null;
	}

	private static function resolvePhpDocBlockFromClass(
		Broker $broker,
		ClassReflection $classReflection,
		string $name,
		string $hasMethodName,
		string $getMethodName,
		string $resolveMethodName,
		bool $explicit
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
			if ($parentReflection->isPrivate()) {
				return null;
			}
			if (
				!$parentReflection->getDeclaringClass()->isTrait()
				&& $parentReflection->getDeclaringClass()->getName() !== $classReflection->getName()
			) {
				return null;
			}

			$traitReflection = $parentReflection instanceof PhpMethodReflection
				? $parentReflection->getDeclaringTrait()
				: null;

			$trait = $traitReflection !== null
				? $traitReflection->getName()
				: null;

			if ($parentReflection->getDocComment() !== false) {
				return self::$resolveMethodName(
					$broker,
					$parentReflection->getDocComment(),
					$classReflection->getName(),
					$trait,
					$name,
					$classReflection->getFileName(),
					$explicit
				);
			}
		}

		return null;
	}

}
