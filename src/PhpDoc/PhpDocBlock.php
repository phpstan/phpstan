<?php declare(strict_types = 1);

namespace PHPStan\PhpDoc;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Reflection\ResolvedMethodReflection;
use PHPStan\Reflection\ResolvedPropertyReflection;

class PhpDocBlock
{

	/** @var string */
	private $docComment;

	/** @var string */
	private $file;

	/** @var ClassReflection */
	private $classReflection;

	/** @var string|null */
	private $trait;

	/** @var bool */
	private $explicit;

	private function __construct(
		string $docComment,
		string $file,
		ClassReflection $classReflection,
		?string $trait,
		bool $explicit
	)
	{
		$this->docComment = $docComment;
		$this->file = $file;
		$this->classReflection = $classReflection;
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

	public function getClassReflection(): ClassReflection
	{
		return $this->classReflection;
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
		?string $docComment,
		ClassReflection $classReflection,
		?string $trait,
		string $propertyName,
		string $file,
		?bool $explicit = null
	): ?self
	{
		return self::resolvePhpDocBlock(
			$docComment,
			$classReflection,
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
		?string $docComment,
		ClassReflection $classReflection,
		?string $trait,
		string $methodName,
		string $file,
		?bool $explicit = null
	): ?self
	{
		return self::resolvePhpDocBlock(
			$docComment,
			$classReflection,
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
		?string $docComment,
		ClassReflection $classReflection,
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
			$docComment === null
			|| preg_match('#@inheritdoc|\{@inheritdoc\}#i', $docComment) > 0
		) {
			if ($classReflection->getParentClass() !== false) {
				$parentClassReflection = $classReflection->getParentClass();
				$phpDocBlockFromClass = self::resolvePhpDocBlockRecursive(
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
			? new self($docComment, $file, $classReflection, $trait, $explicit ?? true)
			: null;
	}

	private static function resolvePhpDocBlockRecursive(
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
			if ($parentReflection->isPrivate()) {
				return null;
			}
			if (
				!$parentReflection->getDeclaringClass()->isTrait()
				&& $parentReflection->getDeclaringClass()->getName() !== $classReflection->getName()
			) {
				return null;
			}

			if ($parentReflection instanceof PhpPropertyReflection || $parentReflection instanceof ResolvedPropertyReflection || $parentReflection instanceof PhpMethodReflection || $parentReflection instanceof ResolvedMethodReflection) {
				$traitReflection = $parentReflection->getDeclaringTrait();
			} else {
				$traitReflection = null;
			}

			$trait = $traitReflection !== null
				? $traitReflection->getName()
				: null;

			if ($parentReflection->getDocComment() !== false) {
				return self::$resolveMethodName(
					$parentReflection->getDocComment(),
					$classReflection,
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
