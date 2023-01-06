<?php

namespace Canvural\Bug;

use PHPStan\Analyser\NameScope;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;

class FooTypeNodeResolverExtension implements \PHPStan\PhpDoc\TypeNodeResolverExtension
{
    protected bool $active;

    protected TypeNodeResolver $baseResolver;

    public function __construct(TypeNodeResolver $baseResolver, bool $active)
    {
        $this->baseResolver = $baseResolver;
        $this->active = $active;
    }

    public function resolve(TypeNode $typeNode, NameScope $nameScope): ?Type
    {
        if ($typeNode instanceof IdentifierTypeNode && $typeNode->name === 'foo-bar') {
            return $this->active ? new IntegerType() : new StringType();
        }

        return null;
    }
}
