<?php declare(strict_types = 1);

namespace PHPStan\PhpDoc;

use PHPStan\Analyser\NameScope;
use PHPStan\Broker\Broker;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\CallableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\CallableTypeParameterNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\ThisTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\Reflection\Native\NativeParameterReflection;
use PHPStan\Reflection\PassedByReference;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CallableType;
use PHPStan\Type\ClosureType;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\NonexistentParentClassType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\ResourceType;
use PHPStan\Type\StaticType;
use PHPStan\Type\StringType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use PHPStan\Type\VoidType;

class TypeNodeResolver
{

	/** @var TypeNodeResolverExtension[] */
	private $extensions;

	/**
	 * @param TypeNodeResolverExtension[] $extensions
	 */
	public function __construct(array $extensions)
	{
		foreach ($extensions as $extension) {
			if (!$extension instanceof TypeNodeResolverAwareExtension) {
				continue;
			}

			$extension->setTypeNodeResolver($this);
		}

		$this->extensions = $extensions;
	}

	public function getCacheKey(): string
	{
		$key = 'v59-explicit-never';
		foreach ($this->extensions as $extension) {
			$key .= sprintf('-%s', $extension->getCacheKey());
		}

		return $key;
	}

	public function resolve(TypeNode $typeNode, NameScope $nameScope): Type
	{
		foreach ($this->extensions as $extension) {
			$type = $extension->resolve($typeNode, $nameScope);
			if ($type !== null) {
				return $type;
			}
		}

		if ($typeNode instanceof IdentifierTypeNode) {
			return $this->resolveIdentifierTypeNode($typeNode, $nameScope);

		} elseif ($typeNode instanceof ThisTypeNode) {
			return $this->resolveThisTypeNode($typeNode, $nameScope);

		} elseif ($typeNode instanceof NullableTypeNode) {
			return $this->resolveNullableTypeNode($typeNode, $nameScope);

		} elseif ($typeNode instanceof UnionTypeNode) {
			return $this->resolveUnionTypeNode($typeNode, $nameScope);

		} elseif ($typeNode instanceof IntersectionTypeNode) {
			return $this->resolveIntersectionTypeNode($typeNode, $nameScope);

		} elseif ($typeNode instanceof ArrayTypeNode) {
			return $this->resolveArrayTypeNode($typeNode, $nameScope);

		} elseif ($typeNode instanceof GenericTypeNode) {
			return $this->resolveGenericTypeNode($typeNode, $nameScope);

		} elseif ($typeNode instanceof CallableTypeNode) {
			return $this->resolveCallableTypeNode($typeNode, $nameScope);
		} elseif ($typeNode instanceof ArrayShapeNode) {
			return $this->resolveArrayShapeNode($typeNode, $nameScope);
		}

		return new ErrorType();
	}

	private function resolveIdentifierTypeNode(IdentifierTypeNode $typeNode, NameScope $nameScope): Type
	{
		switch (strtolower($typeNode->name)) {
			case 'int':
			case 'integer':
				return new IntegerType();

			case 'string':
				return new StringType();

			case 'bool':
			case 'boolean':
				return new BooleanType();

			case 'true':
				return new ConstantBooleanType(true);

			case 'false':
				return new ConstantBooleanType(false);

			case 'null':
				return new NullType();

			case 'float':
			case 'double':
				return new FloatType();

			case 'array':
				return new ArrayType(new MixedType(true), new MixedType(true));

			case 'scalar':
				return new UnionType([
					new IntegerType(),
					new FloatType(),
					new StringType(),
					new BooleanType(),
				]);

			case 'number':
				return new UnionType([
					new IntegerType(),
					new FloatType(),
				]);

			case 'iterable':
				return new IterableType(new MixedType(true), new MixedType(true));

			case 'callable':
				return new CallableType();

			case 'resource':
				return new ResourceType();

			case 'mixed':
				return new MixedType(true);

			case 'void':
				return new VoidType();

			case 'object':
				return new ObjectWithoutClassType();

			case 'never':
				return new NeverType(true);
		}

		if ($nameScope->getClassName() !== null) {
			switch (strtolower($typeNode->name)) {
				case 'self':
					return new ObjectType($nameScope->getClassName());

				case 'static':
					return new StaticType($nameScope->getClassName());

				case 'parent':
					$broker = Broker::getInstance();
					if ($broker->hasClass($nameScope->getClassName())) {
						$classReflection = $broker->getClass($nameScope->getClassName());
						if ($classReflection->getParentClass() !== false) {
							return new ObjectType($classReflection->getParentClass()->getName());
						}
					}

					return new NonexistentParentClassType();
			}
		}

		return new ObjectType($nameScope->resolveStringName($typeNode->name));
	}

	private function resolveThisTypeNode(ThisTypeNode $typeNode, NameScope $nameScope): Type
	{
		if ($nameScope->getClassName() !== null) {
			return new ThisType($nameScope->getClassName());
		}

		return new ErrorType();
	}

	private function resolveNullableTypeNode(NullableTypeNode $typeNode, NameScope $nameScope): Type
	{
		return TypeCombinator::addNull($this->resolve($typeNode->type, $nameScope));
	}

	private function resolveUnionTypeNode(UnionTypeNode $typeNode, NameScope $nameScope): Type
	{
		$iterableTypeNodes = [];
		$otherTypeNodes = [];

		foreach ($typeNode->types as $innerTypeNode) {
			if ($innerTypeNode instanceof ArrayTypeNode) {
				$iterableTypeNodes[] = $innerTypeNode->type;
			} else {
				$otherTypeNodes[] = $innerTypeNode;
			}
		}

		$otherTypeTypes = $this->resolveMultiple($otherTypeNodes, $nameScope);
		if (count($iterableTypeNodes) > 0) {
			$arrayTypeTypes = $this->resolveMultiple($iterableTypeNodes, $nameScope);
			$arrayTypeType = TypeCombinator::union(...$arrayTypeTypes);
			$addArray = true;

			foreach ($otherTypeTypes as &$type) {
				if (!$type->isIterable()->yes() || !$type->getIterableValueType()->isSuperTypeOf($arrayTypeType)->yes()) {
					continue;
				}

				if ($type instanceof ObjectType) {
					$type = new IntersectionType([$type, new IterableType(new MixedType(), $arrayTypeType)]);
				} elseif ($type instanceof ArrayType) {
					$type = new ArrayType(new MixedType(), $arrayTypeType);
				} elseif ($type instanceof IterableType) {
					$type = new IterableType(new MixedType(), $arrayTypeType);
				} else {
					continue;
				}

				$addArray = false;
			}

			if ($addArray) {
				$otherTypeTypes[] = new ArrayType(new MixedType(), $arrayTypeType);
			}
		}

		return TypeCombinator::union(...$otherTypeTypes);
	}

	private function resolveIntersectionTypeNode(IntersectionTypeNode $typeNode, NameScope $nameScope): Type
	{
		$types = $this->resolveMultiple($typeNode->types, $nameScope);
		return TypeCombinator::intersect(...$types);
	}

	private function resolveArrayTypeNode(ArrayTypeNode $typeNode, NameScope $nameScope): Type
	{
		$itemType = $this->resolve($typeNode->type, $nameScope);
		return new ArrayType(new MixedType(), $itemType);
	}

	private function resolveGenericTypeNode(GenericTypeNode $typeNode, NameScope $nameScope): Type
	{
		$mainTypeName = strtolower($typeNode->type->name);
		$genericTypes = $this->resolveMultiple($typeNode->genericTypes, $nameScope);

		if ($mainTypeName === 'array') {
			if (count($genericTypes) === 1) { // array<ValueType>
				return new ArrayType(new MixedType(true), $genericTypes[0]);

			}

			if (count($genericTypes) === 2) { // array<KeyType, ValueType>
				return new ArrayType($genericTypes[0], $genericTypes[1]);
			}

		} elseif ($mainTypeName === 'iterable') {
			if (count($genericTypes) === 1) { // iterable<ValueType>
				return new IterableType(new MixedType(true), $genericTypes[0]);

			}

			if (count($genericTypes) === 2) { // iterable<KeyType, ValueType>
				return new IterableType($genericTypes[0], $genericTypes[1]);
			}
		}

		$mainType = $this->resolveIdentifierTypeNode($typeNode->type, $nameScope);
		if ($mainType->isIterable()->yes()) {
			if (count($genericTypes) === 1) { // Foo<ValueType>
				return TypeCombinator::intersect(
					$mainType,
					new IterableType(new MixedType(true), $genericTypes[0])
				);
			}

			if (count($genericTypes) === 2) { // Foo<KeyType, ValueType>
				return TypeCombinator::intersect(
					$mainType,
					new IterableType($genericTypes[0], $genericTypes[1])
				);
			}
		}

		return new ErrorType();
	}

	private function resolveCallableTypeNode(CallableTypeNode $typeNode, NameScope $nameScope): Type
	{
		$mainType = $this->resolve($typeNode->identifier, $nameScope);
		$isVariadic = false;
		$parameters = array_map(
			function (CallableTypeParameterNode $parameterNode) use ($nameScope, &$isVariadic): NativeParameterReflection {
				$isVariadic = $isVariadic || $parameterNode->isVariadic;
				return new NativeParameterReflection(
					$parameterNode->parameterName,
					$parameterNode->isOptional,
					$this->resolve($parameterNode->type, $nameScope),
					$parameterNode->isReference ? PassedByReference::createCreatesNewVariable() : PassedByReference::createNo(),
					$parameterNode->isVariadic
				);
			},
			$typeNode->parameters
		);
		$returnType = $this->resolve($typeNode->returnType, $nameScope);

		if ($mainType instanceof CallableType) {
			return new CallableType($parameters, $returnType, $isVariadic);

		} elseif (
			$mainType instanceof ObjectType
			&& $mainType->getClassName() === \Closure::class
		) {
			return new ClosureType($parameters, $returnType, $isVariadic);
		}

		return new ErrorType();
	}

	private function resolveArrayShapeNode(ArrayShapeNode $typeNode, NameScope $nameScope): Type
	{
		$requiredItems = [];
		$optionalItems = [];
		foreach ($typeNode->items as $itemNode) {
			if ($itemNode->optional) {
				$optionalItems[] = $itemNode;
				continue;
			}

			$requiredItems[] = $itemNode;
		}

		$builder = ConstantArrayTypeBuilder::createEmpty();

		$addToBuilder = function (ConstantArrayTypeBuilder $builder, array $items) use ($nameScope): void {
			foreach ($items as $itemNode) {
				$offsetType = null;
				if ($itemNode->keyName instanceof ConstExprIntegerNode) {
					$offsetType = new ConstantIntegerType((int) $itemNode->keyName->value);
				} elseif ($itemNode->keyName instanceof IdentifierTypeNode) {
					$offsetType = new ConstantStringType($itemNode->keyName->name);
				}
				$builder->setOffsetValueType($offsetType, $this->resolve($itemNode->valueType, $nameScope));
			}
		};

		$arrays = [];
		$addToBuilder($builder, $requiredItems);
		$arrays[] = $builder->getArray();

		if (count($optionalItems) === 0) {
			return $arrays[0];
		}

		$addToBuilder($builder, $optionalItems);
		$arrays[] = $builder->getArray();

		return TypeCombinator::union(...$arrays);
	}

	/**
	 * @param TypeNode[] $typeNodes
	 * @param NameScope $nameScope
	 * @return Type[]
	 */
	public function resolveMultiple(array $typeNodes, NameScope $nameScope): array
	{
		$types = [];
		foreach ($typeNodes as $typeNode) {
			$types[] = $this->resolve($typeNode, $nameScope);
		}

		return $types;
	}

}
