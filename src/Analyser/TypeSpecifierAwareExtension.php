<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

interface TypeSpecifierAwareExtension
{

	public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void;

}
