<?php // lint >= 7.1

namespace TestClosureFunctionTypehintsPhp71;

class FooFunctionTypehints
{

}

function (): void
{

};

function (): iterable
{

};

function (): ?iterable
{

};

function (): ?string
{

};

function (?FooFunctionTypehints $foo): ?FooFunctionTypehints
{

};

function (?NonexistentClass $bar): ?NonexistentClass
{

};
