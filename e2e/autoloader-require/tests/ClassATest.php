<?php

declare(strict_types=1);

namespace Example\Tests;

class ClassATest
{
    public function test(): void
    {
        $anonStub = new class() extends \Example\ClassA
        {
            use \Example\ClassAHelperTrait;
        };
    }
}
