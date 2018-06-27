<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\Rule;

class ExistingClassInClassExtendsRuleTest extends \PHPStan\Testing\RuleTestCase
{

    protected function getRule(): Rule
    {
        $broker = $this->createBroker();
        return new ExistingClassInClassExtendsRule(
            new ClassCaseSensitivityCheck($broker)
        );
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/extends-implements.php'], [
            [
                'Class ExtendsImplements\Foo referenced with incorrect case: ExtendsImplements\FOO.',
                15,
            ],
        ]);
    }
}
