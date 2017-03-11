<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PHPStan\Broker\Broker;

class UniversalObjectCratesClassReflectionExtensionTest extends \PHPStan\TestCase
{
    public function testNonexistentClass()
    {
        $broker = $this->getContainer()->getByType(Broker::class);
        $extension = new UniversalObjectCratesClassReflectionExtension([
            'NonexistentClass',
            'stdClass',
        ]);
        $this->assertTrue($extension->hasProperty($broker->getClass(\stdClass::class), 'foo'));
    }
}
