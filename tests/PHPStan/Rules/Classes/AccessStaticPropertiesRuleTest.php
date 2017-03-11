<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

class AccessStaticPropertiesRuleTest extends \PHPStan\Rules\AbstractRuleTest
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new AccessStaticPropertiesRule(
            $this->createBroker()
        );
    }

    public function testAccessStaticProperties()
    {
        $this->analyse([__DIR__ . '/data/access-static-properties.php'], [
            [
                'Access to an undefined static property FooAccessStaticProperties::$bar.',
                20,
            ],
            [
                'Access to an undefined static property BarAccessStaticProperties::$bar.',
                21,
            ],
            [
                'Access to an undefined static property FooAccessStaticProperties::$bar.',
                22,
            ],
            [
                'Static access to instance property FooAccessStaticProperties::$loremIpsum.',
                23,
            ],
            [
                'IpsumAccessStaticProperties::ipsum() accesses parent::$lorem but IpsumAccessStaticProperties does not extend any class.',
                37,
            ],
            [
                'Access to protected property $foo of class FooAccessStaticProperties.',
                39,
            ],
            [
                'Access to static property $test on an unknown class UnknownStaticProperties.',
                42,
            ],
            [
                'Access to an undefined static property IpsumAccessStaticProperties::$baz.',
                48,
            ],
            [
                'Access to an undefined static property IpsumAccessStaticProperties::$nonexistent.',
                50,
            ],
            [
                'Access to an undefined static property IpsumAccessStaticProperties::$emptyBaz.',
                58,
            ],
            [
                'Access to an undefined static property IpsumAccessStaticProperties::$emptyNonexistent.',
                60,
            ],
            [
                'Access to an undefined static property IpsumAccessStaticProperties::$anotherNonexistent.',
                66,
            ],
            [
                'Access to an undefined static property IpsumAccessStaticProperties::$anotherNonexistent.',
                67,
            ],
            [
                'Access to an undefined static property IpsumAccessStaticProperties::$anotherEmptyNonexistent.',
                70,
            ],
            [
                'Access to an undefined static property IpsumAccessStaticProperties::$anotherEmptyNonexistent.',
                73,
            ],
            [
                'Accessing self::$staticFooProperty outside of class scope.',
                78,
            ],
            [
                'Accessing static::$staticFooProperty outside of class scope.',
                79,
            ],
            [
                'Accessing parent::$staticFooProperty outside of class scope.',
                80,
            ],
            [
                'Access to protected property $foo of class FooAccessStaticProperties.',
                83,
            ],
            [
                'Static access to instance property FooAccessStaticProperties::$loremIpsum.',
                84,
            ],
        ]);
    }
}
