<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use function class_exists;
use function gettype;
use function is_float;
use function is_string;
use function preg_match_all;

class DOMXPathDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	public function getClass(): string
	{
		return DOMXPath::class;
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === 'evaluate';
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): Type
	{
		if (!class_exists(DOMDocument::class)) {
			return new MixedType();
		}

		$expressionArg = $methodCall->args[0]->value ?? null;

		if ($expressionArg === null) {
			return new ConstantBooleanType(false);
		}

		return $this->determineReturnType($scope->getType($expressionArg), $methodReflection);
	}

	private function determineReturnType(Type $type, MethodReflection $methodReflection): Type
	{
		if ($type instanceof ConstantStringType) {
			return $this->determineXpathReturnType($type);
		}

		if ($type instanceof StringType || $type instanceof MixedType) {
			return ParametersAcceptorSelector::combineAcceptors($methodReflection->getVariants())->getReturnType();
		}

		if ($type instanceof UnionType) {
			return TypeCombinator::union(
				...array_map(
					function (Type $type) use ($methodReflection): Type {
						return $this->determineReturnType($type, $methodReflection);
					},
					$type->getTypes()
				)
			);
		}

		return new ConstantBooleanType(false);
	}

	private function determineXpathReturnType(ConstantStringType $expression): Type
	{
		libxml_clear_errors();
		libxml_use_internal_errors(true);

		preg_match_all('~([^\/\s:]+?):[^\/\s:]+?~', $expression->getValue(), $namespaces);

		$doc = new DOMDocument();
		$doc->loadXML('<dummy/>');
		$xpath = new DOMXPath($doc);

		foreach ($namespaces[1] ?? [] as $prefix) {
			$xpath->registerNamespace($prefix, 'http://example.com');
		}

		$result = $xpath->evaluate($expression->getValue());

		if ($result === false) {
			$errors = libxml_get_errors();
			libxml_clear_errors();

			if (count($errors) > 0) {
				return new ConstantBooleanType(false);
			}
		}

		if ($result instanceof DOMNodeList) {
			return new IntersectionType(
				[
					new ObjectType(DOMNodeList::class),
					new IterableType(new IntegerType(), new ObjectType(DOMNode::class)),
				]
			);
		} elseif (is_bool($result)) {
			return new BooleanType();
		} elseif (is_float($result)) {
			return new FloatType();
		} elseif (is_string($result)) {
			return new StringType();
		}

		throw new \PHPStan\ShouldNotHappenException('Unexpected type ' . gettype($result));
	}

}
