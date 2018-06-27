<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PHPStan\Broker\Broker;

class UniversalObjectCratesClassReflectionExtensionTest extends \PHPStan\Testing\TestCase
{

    public function testNonexistentClass(): void
    {
        $broker = self::getContainer()->getByType(Broker::class);
        $extension = new UniversalObjectCratesClassReflectionExtension([
            'NonexistentClass',
            'stdClass',
        ]);
        $extension->setBroker($broker);
        $this->assertTrue($extension->hasProperty($broker->getClass(\stdClass::class), 'foo'));
    }
}
