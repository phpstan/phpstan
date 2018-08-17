<?php declare(strict_types = 1);

namespace PHPStan\PhpDoc\PhpUnit;

use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\PhpDoc\TypeNodeResolverAwareExtension;
use PHPStan\PhpDoc\TypeNodeResolverExtension;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;

class MockObjectTypeNodeResolverExtension implements TypeNodeResolverExtension, TypeNodeResolverAwareExtension
{

	/** @var TypeNodeResolver */
	private $typeNodeResolver;

	public function setTypeNodeResolver(TypeNodeResolver $typeNodeResolver): void
	{
		$this->typeNodeResolver = $typeNodeResolver;
	}

	public function getCacheKey(): string
	{
		return 'phpunit-v1';
	}

	public function resolve(TypeNode $typeNode, \PHPStan\Analyser\NameScope $nameScope): ?Type
	{
		if (!$typeNode instanceof UnionTypeNode) {
			return null;
		}

		static $mockClassNames = [
			'PHPUnit_Framework_MockObject_MockObject' => true,
			'PHPUnit\Framework\MockObject\MockObject' => true,
		];

		$types = $this->typeNodeResolver->resolveMultiple($typeNode->types, $nameScope);
		foreach ($types as $type) {
			if (!$type instanceof TypeWithClassName) {
				continue;
			}

			if (array_key_exists($type->getClassName(), $mockClassNames)) {
				return \PHPStan\Type\TypeCombinator::intersect(...$types);
			}
		}

		return null;
	}

}
