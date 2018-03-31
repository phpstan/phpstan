<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PHPStan\Reflection\ClassReflection;

class ScopeContext
{

	/** @var string */
	private $file;

	/** @var ClassReflection|null */
	private $classReflection;

	/** @var ClassReflection|null */
	private $traitReflection;

	private function __construct(
		string $file,
		?ClassReflection $classReflection,
		?ClassReflection $traitReflection
	)
	{
		$this->file = $file;
		$this->classReflection = $classReflection;
		$this->traitReflection = $traitReflection;
	}

	public static function create(string $file): self
	{
		return new self($file, null, null);
	}

	public function beginFile(): self
	{
		return new self($this->file, null, null);
	}

	public function enterClass(ClassReflection $classReflection): self
	{
		if ($this->classReflection !== null && !$classReflection->isAnonymous()) {
			throw new \PHPStan\ShouldNotHappenException();
		}
		if ($classReflection->isTrait()) {
			throw new \PHPStan\ShouldNotHappenException();
		}
		return new self($this->file, $classReflection, null);
	}

	public function enterTrait(ClassReflection $traitReflection): self
	{
		if ($this->classReflection === null) {
			throw new \PHPStan\ShouldNotHappenException();
		}
		if (!$traitReflection->isTrait()) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		return new self($this->file, $this->classReflection, $traitReflection);
	}

	public function getFile(): string
	{
		return $this->file;
	}

	public function getClassReflection(): ?ClassReflection
	{
		return $this->classReflection;
	}

	public function getTraitReflection(): ?ClassReflection
	{
		return $this->traitReflection;
	}

}
