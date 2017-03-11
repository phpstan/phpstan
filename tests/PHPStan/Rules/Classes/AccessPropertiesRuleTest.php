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
                    23,
                ],
                [
                    'Access to private property $foo of parent class TestAccessProperties\FooAccessProperties.',
                    24,
                ],
                [
                    'Cannot access property $propertyOnString on string.',
                    31,
                ],
                [
                    'Access to private property $foo of class TestAccessProperties\FooAccessProperties.',
                    42,
                ],
                [
                    'Access to protected property $bar of class TestAccessProperties\FooAccessProperties.',
                    43,
                ],
                [
                    'Access to an undefined property TestAccessProperties\FooAccessProperties::$baz.',
                    49,
                ],
                [
                    'Access to an undefined property TestAccessProperties\FooAccessProperties::$nonexistent.',
                    52,
                ],
                [
                    'Access to private property $foo of class TestAccessProperties\FooAccessProperties.',
                    58,
                ],
                [
                    'Access to protected property $bar of class TestAccessProperties\FooAccessProperties.',
                    59,
                ],
                [
                    'Access to property $foo on an unknown class TestAccessProperties\UnknownClass.',
                    63,
                ],
                [
                    'Access to an undefined property TestAccessProperties\FooAccessProperties::$emptyBaz.',
                    68,
                ],
                [
                    'Access to an undefined property TestAccessProperties\FooAccessProperties::$emptyNonexistent.',
                    70,
                ],
                [
                    'Access to an undefined property TestAccessProperties\FooAccessProperties::$anotherNonexistent.',
                    76,
                ],
                [
                    'Access to an undefined property TestAccessProperties\FooAccessProperties::$anotherNonexistent.',
                    77,
                ],
                [
                    'Access to an undefined property TestAccessProperties\FooAccessProperties::$anotherEmptyNonexistent.',
                    80,
                ],
                [
                    'Access to an undefined property TestAccessProperties\FooAccessProperties::$anotherEmptyNonexistent.',
                    83,
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
                    23,
                ],
                [
                    'Access to private property $foo of parent class TestAccessProperties\FooAccessProperties.',
                    24,
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
                23,
            ],
            [
                'Access to an undefined property AccessPropertiesAfterIsNull\Foo::$barProperty.',
                26,
            ],
            [
                'Access to an undefined property AccessPropertiesAfterIsNull\Foo::$barProperty.',
                29,
            ],
            [
                'Cannot access property $fooProperty on null.',
                33,
            ],
            [
                'Cannot access property $fooProperty on null.',
                42,
            ],
            [
                'Access to an undefined property AccessPropertiesAfterIsNull\Foo::$barProperty.',
                45,
            ],
            [
                'Access to an undefined property AccessPropertiesAfterIsNull\Foo::$barProperty.',
                48,
            ],
        ]);
    }

    public function testDateIntervalChildProperties()
    {
        $this->checkThisOnly = false;
        $this->analyse([__DIR__ . '/data/date-interval-child-properties.php'], [
            [
                'Access to an undefined property AccessPropertiesDateIntervalChild\DateIntervalChild::$nonexistent.',
                14,
            ],
        ]);
    }
}
