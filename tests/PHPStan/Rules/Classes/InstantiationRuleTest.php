<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PHPStan\Rules\FunctionCallParametersCheck;

class InstantiationRuleTest extends \PHPStan\Rules\AbstractRuleTest
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        $broker = $this->createBroker();
        return new InstantiationRule(
            $broker,
            new FunctionCallParametersCheck($broker, true)
        );
    }

    public function testInstantiation()
    {
        $this->analyse(
            [__DIR__ . '/data/instantiation.php'],
            [
                [
                    'Class TestInstantiation\InstantiatingClass constructor invoked with 0 parameters, 1 required.',
                    15,
                ],
                [
                    'TestInstantiation\InstantiatingClass::doFoo() calls new parent but TestInstantiation\InstantiatingClass does not extend any class.',
                    18,
                ],
                [
                    'Class TestInstantiation\FooInstantiation does not have a constructor and must be instantiated without any parameters.',
                    26,
                ],
                [
                    'Instantiated class TestInstantiation\FooBarInstantiation not found.',
                    27,
                ],
                [
                    'Class TestInstantiation\BarInstantiation constructor invoked with 0 parameters, 1 required.',
                    28,
                ],
                [
                    'Instantiated class TestInstantiation\LoremInstantiation is abstract.',
                    29,
                ],
                [
                    'Cannot instantiate interface TestInstantiation\IpsumInstantiation.',
                    30,
                ],
                [
                    'Class DatePeriod constructor invoked with 0 parameters, 1-4 required.',
                    36,
                ],
                [
                    'Using self outside of class scope.',
                    39,
                ],
                [
                    'Using static outside of class scope.',
                    40,
                ],
                [
                    'Using parent outside of class scope.',
                    41,
                ],
                [
                    'Class TestInstantiation\InstantiatingClass constructor invoked with 0 parameters, 1 required.',
                    54,
                ],
            ]
        );
    }
}
