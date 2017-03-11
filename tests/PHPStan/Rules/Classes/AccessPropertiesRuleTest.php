<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PHPStan\Rules\RuleLevelHelper;

class AccessPropertiesRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

    /** @var bool */
    private $checkThisOnly;

    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new AccessPropertiesRule($this->createBroker(), new RuleLevelHelper(), $this->checkThisOnly);
    }

    public function testAccessProperties()
    {
        $this->checkThisOnly = false;
        $this->analyse(
            [__DIR__ . '/data/access-properties.php'],
            [
                [
                    'Access to an undefined property TestAccessProperties\BarAccessProperties::$loremipsum.',
                    20,
                ],
                [
                    'Access to private property $foo of parent class TestAccessProperties\FooAccessProperties.',
                    21,
                ],
                [
                    'Cannot access property $propertyOnString on string.',
                    28,
                ],
                [
                    'Access to private property $foo of class TestAccessProperties\FooAccessProperties.',
                    37,
                ],
                [
                    'Access to protected property $bar of class TestAccessProperties\FooAccessProperties.',
                    38,
                ],
                [
                    'Access to an undefined property TestAccessProperties\FooAccessProperties::$baz.',
                    44,
                ],
                [
                    'Access to an undefined property TestAccessProperties\FooAccessProperties::$nonexistent.',
                    47,
                ],
                [
                    'Access to private property $foo of class TestAccessProperties\FooAccessProperties.',
                    53,
                ],
                [
                    'Access to protected property $bar of class TestAccessProperties\FooAccessProperties.',
                    54,
                ],
                [
                    'Access to property $foo on an unknown class TestAccessProperties\UnknownClass.',
                    58,
                ],
                [
                    'Access to an undefined property TestAccessProperties\FooAccessProperties::$emptyBaz.',
                    63,
                ],
                [
                    'Access to an undefined property TestAccessProperties\FooAccessProperties::$emptyNonexistent.',
                    65,
                ],
                [
                    'Access to an undefined property TestAccessProperties\FooAccessProperties::$anotherNonexistent.',
                    71,
                ],
                [
                    'Access to an undefined property TestAccessProperties\FooAccessProperties::$anotherNonexistent.',
                    72,
                ],
                [
                    'Access to an undefined property TestAccessProperties\FooAccessProperties::$anotherEmptyNonexistent.',
                    75,
                ],
                [
                    'Access to an undefined property TestAccessProperties\FooAccessProperties::$anotherEmptyNonexistent.',
                    78,
                ],
            ]
        );
    }

    public function testAccessPropertiesOnThisOnly()
    {
        $this->checkThisOnly = true;
        $this->analyse(
            [__DIR__ . '/data/access-properties.php'],
            [
                [
                    'Access to an undefined property TestAccessProperties\BarAccessProperties::$loremipsum.',
                    20,
                ],
                [
                    'Access to private property $foo of parent class TestAccessProperties\FooAccessProperties.',
                    21,
                ],
            ]
        );
    }

    public function testAccessPropertiesAfterIsNullInBooleanOr()
    {
        $this->checkThisOnly = false;
        $this->analyse([__DIR__ . '/data/access-properties-after-isnull.php'], [
            [
                'Cannot access property $fooProperty on null.',
                14,
            ],
            [
                'Cannot access property $fooProperty on null.',
                20,
            ],
            [
                'Access to an undefined property AccessPropertiesAfterIsNull\Foo::$barProperty.',
                22,
            ],
            [
                'Access to an undefined property AccessPropertiesAfterIsNull\Foo::$barProperty.',
                24,
            ],
            [
                'Cannot access property $fooProperty on null.',
                27,
            ],
            [
                'Cannot access property $fooProperty on null.',
                33,
            ],
            [
                'Access to an undefined property AccessPropertiesAfterIsNull\Foo::$barProperty.',
                35,
            ],
            [
                'Access to an undefined property AccessPropertiesAfterIsNull\Foo::$barProperty.',
                37,
            ],
        ]);
    }

    public function testDateIntervalChildProperties()
    {
        $this->checkThisOnly = false;
        $this->analyse([__DIR__ . '/data/date-interval-child-properties.php'], [
            [
                'Access to an undefined property AccessPropertiesDateIntervalChild\DateIntervalChild::$nonexistent.',
                13,
            ],
        ]);
    }
}
