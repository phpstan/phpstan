---
title: Core Concepts
---

Abstract Syntax Tree
-----------------

The way analysed source code is represented in the static analyser so that it can be queried for useful information. [Learn more »](/developing-extensions/abstract-syntax-tree)

Scope
-----------------

The Scope object can be used to get more information about the code, like types of variables, or current file and namespace. [Learn more »](/developing-extensions/scope)

Type System
-----------------

PHPStan's type system is a collection of classes implementing the common `PHPStan\Type\Type` interface to inform the analyser about relationships between types, and their behaviour. [Learn more »](/developing-extensions/type-system)

Trinary Logic
-----------------

Many methods in PHPStan do not return a two-state boolean, but a three-state `PHPStan\TrinaryLogic` object. [Learn more »](/developing-extensions/trinary-logic)

Reflection
-----------------

PHPStan has its own reflection layer for asking about functions, classes, properties, methods, and constants. [Learn more »](/developing-extensions/reflection)

Dependency Injection & Configuration
-----------------

Dependency injection controls the way how extension objects are constructed for usage by PHPStan. [Learn more »](/developing-extensions/dependency-injection-configuration)
