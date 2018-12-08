<?php declare(strict_types = 1);

namespace PHPStan\PhpDoc;

use PHPStan\Analyser\NameScope;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Type;

interface TypeNodeResolverExtension
{

	public function getCacheKey(): string;

	public function resolve(TypeNode $typeNode, NameScope $nameScope): ?Type;

}
