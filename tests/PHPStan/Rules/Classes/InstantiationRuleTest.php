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
                    13,
                ],
                [
                    'TestInstantiation\InstantiatingClass::doFoo() calls new parent but TestInstantiation\InstantiatingClass does not extend any class.',
                    16,
                ],
                [
                    'Class TestInstantiation\FooInstantiation does not have a constructor and must be instantiated without any parameters.',
                    23,
                ],
                [
                    'Instantiated class TestInstantiation\FooBarInstantiation not found.',
                    24,
                ],
                [
                    'Class TestInstantiation\BarInstantiation constructor invoked with 0 parameters, 1 required.',
                    25,
                ],
                [
                    'Instantiated class TestInstantiation\LoremInstantiation is abstract.',
                    26,
                ],
                [
                    'Cannot instantiate interface TestInstantiation\IpsumInstantiation.',
                    27,
                ],
                [
                    'Class DatePeriod constructor invoked with 0 parameters, 1-4 required.',
                    33,
                ],
                [
                    'Using self outside of class scope.',
                    36,
                ],
                [
                    'Using static outside of class scope.',
                    37,
                ],
                [
                    'Using parent outside of class scope.',
                    38,
                ],
                [
                    'Class TestInstantiation\InstantiatingClass constructor invoked with 0 parameters, 1 required.',
                    50,
                ],
            ]
        );
    }
}
