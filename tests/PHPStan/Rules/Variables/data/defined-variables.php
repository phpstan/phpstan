<?php

namespace DefinedVariables;

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

$anonymousClassObject = new class {
};

$newArrayCreatedByDimFetch[] = 'foo';
echo $newArrayCreatedByDimFetch[0];

$arrayDoesNotExist['foo'];

$undefinedVariable;

$containerBuilder = getContainer();
$serviceDefinition = $containerBuilder->addDefinition($serviceName = prefix('cache'))
    ->setAutowired(false);

instantiate($serviceName);

function () use (&$errorHandler) {
    $errorHandler->handle(); // variable is fine here
};

$refObject = &refFunction();
$refObject->foo;

funcWithSpecialParameter(1, 2, $variableDefinedInsideTheFunction);
echo $variableDefinedInsideTheFunction;

$fooObject = new Foo();
$fooObject->doFoo(1, 2, $anotherVariableDefinedInsideTheFunction);
echo $anotherVariableDefinedInsideTheFunction;

if ($fooInCondition = doFoo()) {
    $fooInCondition->foo();
} elseif ($barInCondition = $fooInCondition) {
    $barInCondition->bar();
} elseif (doBar()) {
    $barInCondition->differentBar();
} else {
    $fooInCondition->differentFoo();
    $barInCondition->totallyDifferentBar();
}

\Closure::bind(function () {
    $this->doFoo();
}, $fooObject);
\Closure::bind(function () {
    $this->doFoo(); // $this undefined
});
\Closure::bind(function () {
    $this->doFoo(); // $this undefined
}, null);

$someArray = [1, 2, [3, 4]];
list($variableInList, $anotherVariableInList, list($yetAnotherVariableInList, $yetAnotherAnotherVariableInList)) = $someArray;

foreach ($someArray as list($destructuredA, $destructuredB, list($destructuredC, $destructuredD))) {
}

$str = '12';
$resource = fopen();
sscanf($str, '%d%d', $sscanfArgument, $anotherSscanfArgument);
fscanf($resource, '%d%d', $fscanfArgument, $anotherFscanfArgument);
doFoo($sscanfArgument, $anotherSscanfArgument, $fscanfArgument, $anotherFscanfArgument);

Foo::doStaticFoo(1, 2, $variableDefinedInStaticMethodPassedByReference);
echo $variableDefinedInStaticMethodPassedByReference;

echo $echoedVariable = 1;
echo $echoedVariable;

print $printedVariable = 2;
print $printedVariable;

foreach ($variableAssignedInForeach = [] as $v) {
    echo $variableAssignedInForeach;
}
echo $variableAssignedInForeach;

$someArray[$variableDefinedInDimFetch = 1];

if (isset($_[$variableAssignedInIsset = 123]) && $variableAssignedInIsset > 0) {
    doFoo($variableAssignedInIsset); // defined here
}
doFoo($variableAssignedInIsset);

unset($unsettingUndefinedVariable); // it's fine from PHP POV

($variableInBooleanAnd = 123) && $variableInBooleanAnd;

function () use (&$variablePassedByReferenceToClosure) {
};
echo $variablePassedByReferenceToClosure;
if (empty($variableInEmpty) && empty($anotherVariableInEmpty['foo'])) {
    echo $variableInEmpty; // does not exist here
    return;
} else {
    //echo $variableInEmpty; // exists here - not yet supported
}

if (!empty($negatedVariableInEmpty)) {
    echo $negatedVariableInEmpty; // exists here
}

echo $variableInEmpty; // exists here
echo $negatedVariableInEmpty; // does not exist here

if (isset($variableInIsset) && isset($anotherVariableInIsset['foo'])) {
    echo $variableInIsset;
} else {
    echo $variableInIsset; // does not exist
}

switch ('foo') {
    case 1:
        $variableInSwitchWithEarlyTerminatingStatement = 'foo';
        break;
    case 2:
        $variableInSwitchWithEarlyTerminatingStatement = 'bar';
        break;
    default:
        return 'test';
}

echo $variableInSwitchWithEarlyTerminatingStatement;

foreach ($someArray as $someArrayKey => &$valueByReference) {
    if (is_array($valueByReference)) {
        $valueByReference = implode(',', $valueByReference);
    }
}
unset($valueByReference);

function () {
    var_dump($http_response_header);
    fopen('http://www.google.com', 'r');
    var_dump($http_response_header);
};

function () {
    var_dump($http_response_header);
    file_get_contents('http://www.google.com');
    var_dump($http_response_header);
};

($variableDefinedInTernary = doFoo()) ? ('foo' . $variableDefinedInTernary): 'bar';
echo $variableDefinedInTernary;

$fooObject->select($parameterValue = 'test')->from($parameterValue);
echo $parameterValue;

$arrayWithAssignmentInKey = [
    $assignedInKey => 'baz',
    'baz' => $assignedInKey,
    $assignedInKey = 'foo' => $assignedInKey . 'bar' . ($assignedInValue = 'foo'),
    $assignedInKey . $assignedInValue => $assignedInKey . $assignedInValue,
];
echo $assignedInKey;

if (($isInstanceOf = $fooObject) instanceof Foo && $isInstanceOf) {
}
echo $isInstanceOf;

isset($nonexistentVariableInIsset);
