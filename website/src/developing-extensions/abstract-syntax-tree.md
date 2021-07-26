---
title: Abstract Syntax Tree
---

Abstract Syntax Tree (AST for short) is the way analysed source code is represented in the static analyser so that it can be queried for useful information.

*[AST]: Abstract Syntax Tree

PHPStan uses the popular [PHP Parser](https://github.com/nikic/php-parser) library from [Nikita Popov](https://twitter.com/nikita_ppv) to obtain and traverse the AST.

AST represents the parsed source code as a hierarchy of objects. Let's say we have the following PHP code:

```php
return $this->foo && self::bar();
```

This is how the AST for this code looks like:

```
PhpParser\Node\Stmt\Return_
└─ expr: PhpParser\Node\Expr\BinaryOp\BooleanAnd
   ├─ left:  PhpParser\Node\Expr\PropertyFetch
   |         ├─ var:  PhpParser\Node\Expr\Variable
   |         └─ name: PhpParser\Node\Identifier
   └─ right: PhpParser\Node\Expr\StaticCall
             ├─ class: PhpParser\Node\Name
             ├─ name:  PhpParser\Node\Identifier
             └─ args:  array()
```

The PHP Parser library contains dozens of node types for all possible constructs that can be written in PHP code.

The AST doesn't care how is the code formatted - it throws away information about whitespace, and some extra parentheses.

Statements vs. expressions
-----------------

There are two main categories of nodes in the AST: statements and expressions.

Statements are usually controlling the flow of the program, and declare new symbols. They usually come with their own keyword. Statements themselves cannot have a type. Examples of statements are:

* PhpParser\Node\Stmt\Return_ (`return` keyword)
* PhpParser\Node\Stmt\Switch_ (`switch` keyword)
* PhpParser\Node\Stmt\For_ (`for` loop)

Expressions usually consist of other expressions, and can be resolved to a type. Examples of expressions are:

* PhpParser\Node\Expr\Variable (variable like `$foo`, type is the type of the variable)
* PhpParser\Node\Expr\PropertyFetch (accessing a property: `$this->foo`, type is the type of the property)
* PhpParser\Node\Expr\Instanceof_ (the `instanceof` keyword, type is either `true` or `false`)

To retrieve the type of an expression, you need to call the `getType()` method on the [Scope](/developing-extensions/scope) object. You'll obtain an object implementing the `PHPStan\Type\Type` interface. See the article about [the type system](/developing-extensions/type-system) to learn how to use it.
