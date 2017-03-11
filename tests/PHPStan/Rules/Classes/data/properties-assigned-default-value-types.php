<?php

namespace PropertiesAssignedDefaultValuesTypes;

class Foo
{

    /** @var string */
    private $propertyWithoutDefaultValue;

    /** @var string */
    private $stringProperty = 'foo';

    /** @var string */
    private $stringPropertyWithWrongDefaultValue = 1;

    /** @var string */
    private static $staticStringPropertyWithWrongDefaultValue = 1;

    /** @var string */
    private $stringPropertyWithDefaultNullValue = null;
}
