<?php

declare(strict_types=1);

namespace Example\Tests;

use Example\ClassA;
use Example\ClassAHelperTrait;

class ClassATestWithUse
{
    public function test(): void
    {
        $anonStub = new class() extends ClassA
        {
            use ClassAHelperTrait;
        };
    }
}
