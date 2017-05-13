<?php declare(strict_types = 1);

namespace PHPStan\TypeX;

use PHPStan\Type\StaticResolvableType;
use PHPStan\Type\UnionTypeHelper;

class IntersectionType extends BaseTypeX implements StaticResolvableType
{
	/** @var TypeX[] */
	private $types;

	/**
	 * @param TypeXFactory $factory
	 * @param TypeX[]      $types
	 */
	public function __construct(TypeXFactory $factory, array $types)
	{
		parent::__construct($factory);

		assert(count($types) > 1);
		$this->types = $types;
	}

	/**
	 * @return TypeX[]
	 */
	public function getTypes(): array
	{
		return $this->types;
	}

	public function describe(): string
	{
		$typeNames = [];

		foreach ($this->types as $type) {
			$typeNames[] = $type->describe();
		}

		return '(' . implode('&', $typeNames) . ')'; // TODO: sort
	}

	public function acceptsX(TypeX $otherType): bool
	{
		foreach ($this->types as $type) {
			if (!$type->acceptsX($otherType)) {
				return false;
			}
		}

		return true;
	}

	public function isAssignable(): int
	{
		return $this->intersectResults(function (TypeX $type): int {
			return $type->isAssignable();
		});
	}

	public function isCallable(): int
	{
		return $this->intersectResults(function (TypeX $type): int {
			return $type->isCallable();
		});
	}

//	public function getCallReturnType(TypeX ...$callArgsTypes): TypeX
//	{
//		return $this->intersectTypes(function (TypeX $type): TypeX {
//			return $type->getCallReturnType();
//		});
//	}

	public function isIterable(): int
	{
		return $this->intersectResults(function (TypeX $type): int {
			return $type->isIterable();
		});
	}

	public function getIterableKeyType(): TypeX
	{
		return $this->intersectTypes(function (TypeX $type): TypeX {
			return $type->getIterableKeyType();
		});
	}

	public function getIterableValueType(): TypeX
	{
		return $this->intersectTypes(function (TypeX $type): TypeX {
			return $type->getIterableValueType();
		});
	}

	public function canCallMethodsX(): int
	{
		return $this->intersectResults(function (TypeX $type): int {
			return $type->canCallMethodsX();
		});
	}

	public function canAccessPropertiesX(): int
	{
		return $this->intersectResults(function (TypeX $type): int {
			return $type->canAccessPropertiesX();
		});
	}

	public function canAccessOffset(): int
	{
		return $this->intersectResults(function (TypeX $type): int {
			return $type->canAccessOffset();
		});
	}

	public function resolveStatic(string $className): \PHPStan\Type\Type
	{
		return $this->factory->createIntersectionType(...UnionTypeHelper::resolveStatic($className, $this->types));
	}

	public function changeBaseClass(string $className): StaticResolvableType
	{
		return $this->factory->createIntersectionType(...UnionTypeHelper::changeBaseClass($className, $this->types));
	}

	private function intersectResults(callable $getResult): int
	{
		$result = self::RESULT_NO;

		foreach ($this->types as $type) {
			$subResult = $getResult($type);
			if ($subResult < $result) {
				$result = $subResult;
			}
		}

		return $result;
	}

	private function intersectTypes(callable $getType): TypeX
	{
		$subTypes = [];

		foreach ($this->types as $type) {
			$subTypes[] = $getType($type);
		}

		return $this->factory->createIntersectionType(...$subTypes);
	}
}
