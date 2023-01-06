<?php

namespace Canvural\tests;

use PHPStan\Testing\TypeInferenceTestCase;

class FooTest extends TypeInferenceTestCase
{
    public function dataFileAsserts()
    {
        yield from $this->gatherAssertTypes(__DIR__ . '/data/bug2.php');
    }

    /**
     * @dataProvider dataFileAsserts
     */
    public function testFileAsserts(
        string $assertType,
        string $file,
        ...$args
    ): void {
        $this->assertFileAsserts($assertType, $file, ...$args);
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__.'/tests.neon'];
    }
}
