---
title: "Using PHPStan to Extract Data About Your Codebase"
date: 2026-03-26
tags: guides
---

PHPStan is known for finding bugs in your code. But that's not all it can do. When PHPStan analyses your codebase, it builds a detailed model of every class, method, property, type, and relationship. All of that knowledge is accessible through [Scope](/developing-extensions/scope) and [Reflection](/developing-extensions/reflection). It'd be a shame to only use it for error reporting.

In this article, I'm going to show you how to use PHPStan as a data extraction tool — to query your codebase and produce machine-readable output you can use for documentation, visualization, or any other purpose.

The general approach
--------------------

The idea relies on three PHPStan extension points working together:

1. **[Collectors](/developing-extensions/collectors)** gather data about your codebase during analysis. They visit AST nodes, query the Scope and Reflection, and return structured data.
2. **A [rule](/developing-extensions/rules)** that processes `CollectedDataNode` — a special node that gives you access to all collected data after the analysis is complete. Instead of reporting actual errors, it packages the collected data as metadata attached to rule errors.
3. **A custom [error formatter](/developing-extensions/error-formatters)** that reads the metadata from these errors and outputs machine-readable data (like JSON) instead of the usual error table.

This is a bit of a creative hack — we're using PHPStan's error reporting pipeline as a data transport mechanism. The "errors" aren't real errors at all, they're just carriers for the data we extracted.

All of this is tied together with a [configuration file](/config-reference) that registers the collectors, the rule, and the error formatter, and points PHPStan at the code you want to analyse.

Let's walk through a real-world example.

Example: Generating a call map
--------------------

[stella-maris/callmap](https://gitlab.com/stella-maris/callmap) by [Andreas Heigl](https://github.com/heiglandreas) is a PHPStan extension that maps every method call in your codebase — which method calls which other method, and in which class. The result is a JSON file you can feed into [CallMap CLI](https://gitlab.com/stella-maris/callmapcli) to generate a call graph.

### The Collector

The collector visits every `MethodCall` node in the AST and records four things: the calling class, the calling method, the called class, and the called method.

```php
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;

/**
 * @implements Collector<MethodCall, array{callingClass?: string, callingMethod: string, calledClass: string, calledMethod: string}>
 */
class MethodCallCollector implements Collector
{
    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope)
    {
        $methodName = $node->name;
        if (!$methodName instanceof Node\Identifier) {
            return null;
        }

        // Find the class that declares the called method
        $type = $scope->getType($node->var);
        $methodReflection = $scope->getMethodReflection($type, $methodName->name);
        if ($methodReflection === null) {
            return null;
        }

        $calledClass = $methodReflection->getDeclaringClass()->getName();

        // Find the calling context
        $callingClass = '';
        if ($scope->isInClass()) {
            $callingClass = $scope->getClassReflection()->getName();
        }

        return [
            'callingClass' => $callingClass,
            'callingMethod' => $scope->getFunction()?->getName(),
            'calledClass' => $calledClass,
            'calledMethod' => $methodName->name,
        ];
    }
}
```

The key thing here is that we're using `$scope->getMethodReflection()` to resolve the *declaring* class of the called method. This means if you call `$user->save()` and `save()` is declared on a parent `Model` class, the collector records `Model` as the called class, not the concrete type. This is the kind of insight that would be difficult to get from simple text-based analysis.

### The Rule

The rule processes `CollectedDataNode` and wraps each row of collected data as a rule error with metadata:

```php
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements \PHPStan\Rules\Rule<CollectedDataNode>
 */
class Rule implements \PHPStan\Rules\Rule
{
    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];

        foreach ($node->get(MethodCallCollector::class) as $rows) {
            foreach ($rows as $row) {
                $errors[] = RuleErrorBuilder::message('Metadata')
                    ->identifier('callmapFormatter.data')
                    ->metadata($row)
                    ->build();
            }
        }

        return $errors;
    }
}
```

The `$node->get(MethodCallCollector::class)` call retrieves all data collected by `MethodCallCollector` across the entire codebase. Each entry is wrapped as a `RuleError` with a specific identifier and the original data stashed in the metadata.

### The Error Formatter

The error formatter reads these metadata-carrying errors and outputs them as JSON:

```php
use PHPStan\Command\AnalysisResult;
use PHPStan\Command\ErrorFormatter\ErrorFormatter;
use PHPStan\Command\ErrorFormatter\TableErrorFormatter;
use PHPStan\Command\Output;

class CallmapFormatter implements ErrorFormatter
{
    public function __construct(
        private TableErrorFormatter $tableErrorFormatter,
    ) {}

    public function formatErrors(
        AnalysisResult $analysisResult,
        Output $output,
    ): int
    {
        if ($analysisResult->hasInternalErrors()) {
            // fall back to table output
            return $this->tableErrorFormatter->formatErrors(
                $analysisResult,
                $output,
            );
        }

        $json = [];
        foreach ($analysisResult->getFileSpecificErrors() as $error) {
            if ($error->getIdentifier() !== 'callmapFormatter.data') {
                // An unexpected real error — fall back to table output
                return $this->tableErrorFormatter->formatErrors(
                    $analysisResult,
                    $output,
                );
            }

            $metadata = $error->getMetadata();
            $json[] = [
                'callingClass' => $metadata['callingClass'],
                'callingMethod' => $metadata['callingMethod'],
                'calledClass' => $metadata['calledClass'],
                'calledMethod' => $metadata['calledMethod'],
            ];
        }

        file_put_contents('callmap.json', json_encode(['data' => $json]));

        return 0;
    }
}
```

The formatter checks for the specific error identifier. If it encounters any unexpected errors (real PHPStan errors from the analysed code), it falls back to the standard table output. This is a good safety measure — you want to know if something went wrong during the analysis instead of silently producing incomplete data.

### The Configuration

All of this is tied together with a NEON configuration file:

```yaml
parameters:
    errorFormat: callmap
    paths:
        - src

    # Makes PHPStan not require a `--level` for `analyse` command.
    # Our rule will be the only one that runs
    customRulesetUsed: true

rules:
    - StellaMaris\Callmap\Rule

services:
    errorFormatter.callmap:
        class: StellaMaris\Callmap\CallmapFormatter
    -
        class: StellaMaris\Callmap\MethodCallCollector
        tags:
            - phpstan.collector
```

You run it like any other PHPStan analysis:

```bash
vendor/bin/phpstan analyse -c callmap.neon
```

And the output is a `callmap.json` file with every method call in your codebase:

```json
{
    "data": [
        {
            "callingClass": "App\\Service\\UserService",
            "callingMethod": "getUser",
            "calledClass": "App\\Repository\\UserRepository",
            "calledMethod": "find"
        }
    ]
}
```

A more complex example: Extracting error identifiers
--------------------

I use this same pattern myself to [generate the error identifier documentation](/error-identifiers) on this website. Every PHPStan error has a [unique identifier](/blog/phpstan-1-11-errors-identifiers-phpstan-pro-reboot) like `argument.type` or `deadCode.unreachable`. The [identifier extractor](https://github.com/phpstan/phpstan/tree/2.2.x/identifier-extractor) scans the PHPStan source code and all 1st-party extensions to find where these identifiers are defined.

It's more complex than the callmap example because it uses four different collectors, each targeting a different code pattern:

* `RuleErrorBuilderCollector` — finds calls to `RuleErrorBuilder::message()->identifier('...')`
* `ErrorWithIdentifierCollector` — finds calls to `Error::withIdentifier('...')`
* `RestrictedUsageCollector` — finds calls to `RestrictedUsage::create()`
* `RuleConstructorParameterCollector` — maps rule-to-rule injection dependencies

The last one is interesting — some rules delegate error reporting to helper classes. The extractor traces these dependency chains to figure out which top-level rule ultimately emits a given identifier.

The output is a JSON file that gets [merged from multiple repositories](https://github.com/phpstan/phpstan/tree/2.2.x/identifier-extractor/merge.php), transformed, and used to generate the "Rules that report this error" section you can see at the bottom of each [error identifier detail page](/error-identifiers/argument.type).

Build your own
--------------------

If you want to extract data from your codebase, the pattern is always the same:

1. Write one or more [collectors](/developing-extensions/collectors) that implement `PHPStan\Collectors\Collector` and gather the data you're interested in.
2. Write a [rule](/developing-extensions/rules) for `CollectedDataNode` that packages collected data as error metadata.
3. Write a custom [error formatter](/developing-extensions/error-formatters) that reads the metadata and produces the output you need.
4. Create a NEON configuration file with `customRulesetUsed: true` and your custom `errorFormat`.

The possibilities are endless. You could generate dependency graphs between namespaces, find all usages of a deprecated API, catalogue all database queries, or map out event listener registrations. Anything PHPStan can see through its type inference and reflection, you can extract.

---

Do you like PHPStan and use it every day? [**Consider supporting further development of PHPStan**](/sponsor). I'd really appreciate it!
