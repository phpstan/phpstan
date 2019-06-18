<?php declare(strict_types = 1);

require_once __DIR__ . '/PHPStan/Analyser/functions.php';

class_alias(\ReturnTypes\Foo::class, \ReturnTypes\FooAlias::class, true);
class_alias(\TestAccessProperties\FooAccessProperties::class, \TestAccessProperties\FooAccessPropertiesAlias::class, true);
