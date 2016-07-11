<?php

if ($definedLater) {
	$definedLater = 1;
	$definedInIfOnly = foo();
}

$definedInIfOnly->foo();

switch (foo()) {
	case 1:
		$definedInCases = foo();
		break;
	case 2:
		$definedInCases = bar();
		break;
}

$definedInCases->foo();


do {
	$doWhileVar = 1;
} while ($doWhileVar > 1);


foo($fooParameterBeforeDeclaration, $fooParameterBeforeDeclaration = 1);
bar($barParameter = 1, $barParameter);

preg_match('#.*#', 'foo', $matches);
parse_str(
	$parseStrParameter,
	$parseStrParameter,
	$parseStrParameter
);

$foo ?? $foo; // $foo undefined (once - after ??)
$bar[0] ?? null; // OK

$willBeUnset = 'foo';
unset($willBeUnset);
$willBeUnset;

$arrayVariableCannotBeUnsetByDimFetch = ['foo' => 1];
unset($arrayVariableCannotBeUnsetByDimFetch['foo']);
$arrayVariableCannotBeUnsetByDimFetch;

$mustAlreadyExistWhenDividing /= 5;

$anonymousClassObject = new class {};

$newArrayCreatedByDimFetch[] = 'foo';
echo $newArrayCreatedByDimFetch[0];

$arrayDoesNotExist['foo'];

$undefinedVariable;

$containerBuilder = getContainer();
$serviceDefinition = $containerBuilder->addDefinition($serviceName = prefix('cache'))
	->setAutowired(false);

instantiate($serviceName);
