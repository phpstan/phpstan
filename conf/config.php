<?php
use Interop\Container\ContainerInterface;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;

$obj = function (array $parameters, string $class = '') {
    $obj = $class ? DI\object($class) : DI\object();

    foreach ($parameters as $key => $value) {
        if (is_int($key)) {
            $key = $value;
        }

        if (is_string($value)) {
            $value = $value[0] == '#' ? substr($value, 1) : DI\get($value);
        }

        $obj->constructorParameter($key, $value);
    }

    return $obj;
};

$parameters = [
    'tmpDir' => sys_get_temp_dir(),
    'bootstrap' => null,
    'bootstrapFile' => null,
    'excludes_analyse' => [],
    'ignorePathPatterns' => [],
    'fileExtensions' => [ 'php', ],
    'checkFunctionArgumentTypes' => false,
    'enableUnionTypes' => false,
    'polluteScopeWithLoopInitialAssignments' => false,
    'polluteCatchScopeWithTryAssignments' => false,
    'defineVariablesWithoutDefaultBranch' => false,
    'ignoreErrors' => [],
    'reportUnmatchedIgnoredErrors' =>  true,
    'universalObjectCratesClasses' => [
        'stdClass',
        'SimpleXMLElement',
    ],
    'earlyTerminatingMethodCalls' => [],
    'customRulesetUsed' => false,
    'checkThisOnly' => true,
    'checkFunctionArgumentTypes' => true,
    'enableUnionTypes' => true,
    'memoryLimitFile' => DI\string('{tmpDir}/.memory_limit'),
    'cacheOptions' => [
        'path' => DI\string('{tmpDir}/phpstan'),
    ],
    'customRulesetUsed' => false,
    'checkThisOnly' => true,
    'checkFunctionArgumentTypes' => true,
    'enableUnionTypes' => true,
];

$services = [
    PhpParser\NodeTraverser::class => function (PhpParser\NodeVisitor\NameResolver $nameResolver) {
        $nodeTraverser = new PhpParser\NodeTraverser;
        $nodeTraverser->addVisitor($nameResolver);

        return $nodeTraverser;
    },

    PhpParser\Parser::class => DI\object(PhpParser\Parser\Php7::class),

    PHPStan\Analyser\Analyser::class => $obj([
        'ignoreErrors',
        'reportUnmatchedIgnoredErrors',
        'bootstrapFile',
    ]),

    PHPStan\Analyser\NodeScopeResolver::class => $obj([
        'polluteScopeWithLoopInitialAssignments',
        'polluteCatchScopeWithTryAssignments',
        'defineVariablesWithoutDefaultBranch',
        'earlyTerminatingMethodCalls',
    ]),

    PHPStan\Command\AnalyseApplication::class => $obj([
        'memoryLimitFile',
        'fileExtensions',
        'ignorePathPatterns',
    ]),

    PHPStan\File\FileHelper::class => $obj([
        'workingDirectory' => 'currentWorkingDirectory',
    ]),

    PHPStan\File\FileExcluder::class => $obj([
        'analyseExcludes' => 'excludes_analyse',
    ]),

    PHPStan\Parser\CachedParser::class => $obj([
        'originalParser' => PHPStan\Parser\Parser::class,
    ]),

    PHPStan\Reflection\Php\UniversalObjectCratesClassReflectionExtension::class => $obj([
        'classes' => 'universalObjectCratesClasses',
    ]),

    PHPStan\Reflection\Php\PhpMethodReflectionFactory::class => DI\object(PHPStan\Reflection\Php\PhpMethodReflectionFactoryDI::class),

    PHPStan\Reflection\FunctionReflectionFactory::class => DI\object(PHPStan\Reflection\FunctionReflectionFactoryDI::class),

    PHPStan\Reflection\PropertiesClassReflectionExtension::class => [
        DI\get(PHPStan\Reflection\Php\PhpClassReflectionExtension::class),
        DI\get(PHPStan\Reflection\Annotations\AnnotationsPropertiesClassReflectionExtension::class),
        DI\get(PHPStan\Reflection\PhpDefect\PhpDefectClassReflectionExtension::class),
    ],

    PHPStan\Reflection\MethodsClassReflectionExtension::class => [
        DI\get(PHPStan\Reflection\Php\PhpClassReflectionExtension::class),
        DI\get(PHPStan\Reflection\Annotations\AnnotationsMethodsClassReflectionExtension::class),
    ],

    PHPStan\Type\DynamicMethodReturnTypeExtension::class => [
    ],

    PHPStan\Type\DynamicStaticMethodReturnTypeExtension::class => [
    ],

    PHPStan\Rules\FunctionCallParametersCheck::class => $obj([
        'checkArgumentTypes' => 'checkFunctionArgumentTypes',
    ]),

    PHPStan\Type\FileTypeMapper::class => $obj([
        'enableUnionTypes',
    ]),

    PHPStan\Broker\Broker::class=> $obj([
        'propertiesClassReflectionExtensions' => DI\get(PropertiesClassReflectionExtension::class),
        'methodsClassReflectionExtensions' => DI\get(MethodsClassReflectionExtension::class),
        'dynamicMethodReturnTypeExtensions' => DI\get(DynamicMethodReturnTypeExtension::class),
        'dynamicStaticMethodReturnTypeExtensions' => DI\get(DynamicStaticMethodReturnTypeExtension::class),
    ]),

    Stash\Interfaces\DriverInterface::class => $obj([
        'options' => 'cacheOptions',
    ], Stash\Driver\FileSystem::class),

    Psr\Cache\CacheItemPoolInterface::class => DI\object(Stash\Pool::class),

    PHPStan\Parser\Parser::class => DI\object(PHPStan\Parser\DirectParser::class),

    PHPStan\Rules\Registry::class => DI\factory([PHPStan\Rules\RegistryFactory::class, 'create']),

    // rules
    PHPStan\Rules\Classes\AccessPropertiesRule::class => $obj([
        'checkThisOnly',
    ]),
    PHPStan\Rules\Methods\CallMethodsRule::class => $obj([
        'checkThisOnly',
    ])
];

return $parameters + $services;
