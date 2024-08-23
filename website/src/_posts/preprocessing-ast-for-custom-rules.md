---
title: "Preprocessing the AST for custom rules"
date: 2022-04-15
tags: guides
---

When PHPStan 1.6.0 is released, your [custom rules](/developing-extensions/rules) that use the `parent`/`previous`/`next` attributes from the [`NodeConnectingVisitor`](https://apiref.phpstan.org/1.12.x/PhpParser.NodeVisitor.NodeConnectingVisitor.html) will no longer work for users that have enabled the [Bleeding Edge](/blog/what-is-bleeding-edge) feature toggle.

```php
public function processNode(Node $node, Scope $scope): array
{
    // will be null in PHPStan 1.6.0 + Bleeding Edge!
    $parentNode = $node->getAttribute('parent');
}
```

PHPStan 1.6.0 focuses on reducing the consumed memory by the analysis, but it does so at the cost of backward compatibility break. That's why the saved resources will be enjoyed only by the [Bleeding Edge](/blog/what-is-bleeding-edge) users before everyone else gets them in the next major version. Without Bleeding Edge, everything continues to work as expected, but the memory savings aren't going to be as big. With Bleeding Edge, some custom rules will going to need adjustments.

The consumed memory is reduced by breaking up reference cycles between objects and avoiding memory leaks. This is how the default AST of a try-catch block looks like:

{% mermaid %}
    flowchart LR;
    TryCatch== stmts ==>array
    array== 0 ==>Stmt1
    array== 1 ==>Stmt2
    array== 2 ==>Stmt3
{% endmermaid %}

In comparison the object graph of AST nodes with `parent`/`previous`/`next` attributes looks like this:

{% mermaid %}
    flowchart LR;
    TryCatch== stmts ==>array
    array== 0 ==>Stmt1
    array== 1 ==>Stmt2
    array== 2 ==>Stmt3
    Stmt1== parent ==>TryCatch
    Stmt2== parent ==>TryCatch
    Stmt3== parent ==>TryCatch
    Stmt1== next ==>Stmt2
    Stmt2== next ==>Stmt3
    Stmt2== prev ==>Stmt1
    Stmt3== prev ==>Stmt2
{% endmermaid %}

What a mess, right? No wonder the PHP runtime cannot free this memory once the AST is no longer used. So our goal is to have the AST object graph to look like the first picture, but we don't want to lose any features.

If you just want to bring back the `parent`/`previous`/`next` attributes even for PHPStan 1.6.0 users with Bleeding Edge enabled to buy some time to rewrite your rules, you can do so with the following configuration added to your `phpstan.neon` (or the `.neon` file of your package with custom rules). This configuration can be used since [PHPStan 1.5.6](https://github.com/phpstan/phpstan/releases/tag/1.5.6) and will make your code forward-compatible even for PHPStan 1.6.0 with Bleeding Edge:

```yaml
conditionalTags:
    PhpParser\NodeVisitor\NodeConnectingVisitor:
        phpstan.parser.richParserNodeVisitor: true
```

But of course this goes against the spirit of the changes made and will make PHPStan consume more memory again.

Let's see how to do it right on an example. A bunch of rules in PHPStan itself are interested in whether the current AST node is inside a try-catch block or not and what are the caught exception types.

```php
try {
    // Rule registered for MethodCall node
    // might be interested in whether the call
    // is surrounded by a try-catch and
    // what are the caught exceptions here
    $this->doFoo();
} catch (AppException) {

}
```

The previous version of the code did the check using the `parent` node attribute:

```php
private function isUnhandledMatchErrorCaught(Node $node): bool
{
    /** @var Node|null $tryCatchNode */
    $tryCatchNode = $node->getAttribute('parent');
    while (
        $tryCatchNode !== null &&
        !$tryCatchNode instanceof Node\FunctionLike &&
        !$tryCatchNode instanceof Node\Stmt\TryCatch
    ) {
        $tryCatchNode = $tryCatchNode->getAttribute('parent');
    }

    if ($tryCatchNode === null || $tryCatchNode instanceof Node\FunctionLike) {
        // no try-catch
        return false;
    }

    foreach ($tryCatchNode->catches as $catch) {
        // create PHPStan's Type object from the AST caught Name nodes
        $catchType = TypeCombinator::union(
            ...array_map(static fn (Node\Name $class): ObjectType =>
                new ObjectType($class->toString()), $catch->types)
        );
        if ($catchType
            ->isSuperTypeOf(new ObjectType(UnhandledMatchError::class))->yes()) {
            // yes, UnhandledMatchError is handled in a surrounding try-catch block
            return true;
        }
    }

    // check if we're in a try-catch block in a try-catch block recursively
    return $this->isUnhandledMatchErrorCaught($tryCatchNode);
}
```

The new version looks at the new custom `tryCatchTypes` node attribute:

```php
private function isUnhandledMatchErrorCaught(Node $node): bool
{
    /** @var string[]|null $tryCatchTypes */
    $tryCatchTypes = $node->getAttribute(TryCatchTypeVisitor::ATTRIBUTE_NAME); // 'tryCatchTypes'
    if ($tryCatchTypes === null) {
        return false;
    }

    $tryCatchType = TypeCombinator::union(
        ...array_map(static fn (string $class) =>
            new ObjectType($class), $tryCatchTypes)
    );

    return $tryCatchType
        ->isSuperTypeOf(new ObjectType(UnhandledMatchError::class))->yes();
}
```

We also need to write a [custom node visitor](https://github.com/nikic/PHP-Parser/blob/v4.13.2/doc/component/Walking_the_AST.markdown#node-visitors) to set the `tryCatchTypes` attribute and pass the information for the rule. The methods of the node visitor are called on each node during the AST traversal right after parsing. Our goal is to pass along specific information for the rule to use. Keep in mind to only **keep the specific and relevant data** to the current rule you're developing and write the custom node visitor only for this specific use-case. Otherwise you're risking to fill the memory with too much data and cause memory leaks again.

```php
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class TryCatchTypeVisitor extends NodeVisitorAbstract
{
    public const ATTRIBUTE_NAME = 'tryCatchTypes';

    /** @var array<int, array<int, string>|null> */
    private array $typeStack = [];

    public function beforeTraverse(array $nodes): ?array
    {
        $this->typeStack = [];
        return null;
    }

    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof Node\Stmt || $node instanceof Node\Expr\Match_) {
            if (count($this->typeStack) > 0) {
                // set the attribute for each statement
                // and match expression inside a try-catch block
                $node->setAttribute(
                    self::ATTRIBUTE_NAME,
                    $this->typeStack[count($this->typeStack) - 1],
                );
            }
        }

        if ($node instanceof Node\FunctionLike) {
            // we're entering a function boundary (including closures)
            // the stack needs to be reset - we're not in a try-catch
            $this->typeStack[] = null;
        }

        if ($node instanceof Node\Stmt\TryCatch) {
            // we're entering a new try-catch block
            $types = [];

            // reverse the stack because we're interested in the closest try-catch, not the top-most one
            foreach (array_reverse($this->typeStack) as $stackTypes) {
                if ($stackTypes === null) {
                    // there's a function boundary, we can stop
                    break;
                }

                // add information from outer try-catch blocks to this one too
                foreach ($stackTypes as $type) {
                    $types[] = $type;
                }
            }

            // go through the catch blocks of the current try-catch
            // and add each caught exception type name
            foreach ($node->catches as $catch) {
                foreach ($catch->types as $type) {
                    $types[] = $type->toString();
                }
            }

            $this->typeStack[] = $types;
        }

        return null;
    }

    public function leaveNode(Node $node): ?Node
    {
        if (
            !$node instanceof Node\Stmt\TryCatch
            && !$node instanceof Node\FunctionLike
        ) {
            return null;
        }

        // pop the stack - we're leaving TryCatch and FunctionLike
        // which are the two node types that are pushing items
        // to the stack in enterNode()
        array_pop($this->typeStack);

        return null;
    }

}
```

The custom node visitor needs to be registered in your configuration file like this:

```yaml
services:
    -
        # Sets custom 'tryCatchTypes' node attribute
        class: App\PHPStan\TryCatchTypeVisitor
        tags:
            - phpstan.parser.richParserNodeVisitor
```

---

Do you like PHPStan and use it every day? [**Consider supporting further development of PHPStan on GitHub Sponsors**](https://github.com/sponsors/ondrejmirtes/). I’d really appreciate it!
