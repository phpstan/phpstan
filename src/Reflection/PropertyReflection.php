<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;

interface PropertyReflection extends ClassMemberReflection
{

	public function getReadableType(): Type;

	public function getWritableType(): Type;

	public function canChangeTypeAfterAssignment(): bool;

	public function isReadable(): bool;

	public function isWritable(): bool;

	public function isDeprecated(): TrinaryLogic;

	public function getDeprecatedDescription(): ?string;

	public function isInternal(): TrinaryLogic;

}
