<?php

namespace ClosureReturnTypeExtensionsNamespace;

use function PHPStan\Analyser\assertType;

$predicate = function (object $thing): bool { return true; };

$closure = \Closure::fromCallable($predicate);
assertType('Closure(object): bool', $closure);

$newThis = new class {};
$boundClosure = $closure->bindTo($newThis);
assertType('Closure(object): bool', $boundClosure);

$staticallyBoundClosure = \Closure::bind($closure, $newThis);
assertType('Closure(object): bool', $staticallyBoundClosure);

$returnType = $closure->call($newThis, new class {});
assertType('bool', $returnType);
