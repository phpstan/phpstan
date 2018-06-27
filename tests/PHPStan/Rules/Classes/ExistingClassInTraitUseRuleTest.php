<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\Rule;

class ExistingClassInTraitUseRuleTest extends \PHPStan\Testing\RuleTestCase
{

    protected function getRule(): Rule
    {
        $broker = $this->createBroker();
        return new ExistingClassInTraitUseRule(
            new ClassCaseSensitivityCheck($broker)
        );
    }

    public function testClassWithWrongCase(): void
    {
        $this->analyse([__DIR__ . '/data/trait-use.php'], [
            [
                'Trait TraitUseCase\FooTrait referenced with incorrect case: TraitUseCase\FOOTrait.',
                13,
            ],
        ]);
    }
}
