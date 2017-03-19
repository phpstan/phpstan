<?php declare(strict_types = 1);

namespace PHPStan\TypeX;

use PHPStan\Type\StaticResolvableType;
use PHPStan\Type\UnionTypeHelper;


class UnionType extends BaseTypeX implements StaticResolvableType
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
		$this->types = UnionTypeHelper::sortTypes($types);
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

		return implode('|', array_unique($typeNames));
	}

	public function acceptsX(TypeX $otherType): bool
	{
		if ($this->acceptsCompound($otherType)) {
			return true;
		}

		foreach ($this->types as $type) {
			if ($type->acceptsX($otherType)) {
				return true;
			}
		}

		return false;
	}

	public function isAssignable(): int
	{
		return $this->unionResults(function (TypeX $type): int {
			return $type->isCallable();
		});
	}

	public function isCallable(): int
	{
		return $this->unionResults(function (TypeX $type): int {
			return $type->isCallable();
		});
	}

//	public function getCallReturnType(TypeX ...$callArgsTypes): TypeX
//	{
//		return $this->unionTypes(function (TypeX $type): TypeX {
//			return $type->getCallReturnType();
//		});
//	}

	public function isIterable(): int
	{
		return $this->unionResults(function (TypeX $type): int {
			return $type->isIterable();
		});
	}

	public function getIterableKeyType(): TypeX
	{
		return $this->unionTypes(function (TypeX $type): TypeX {
			return $type->getIterableKeyType();
		});
	}

	public function getIterableValueType(): TypeX
	{
		return $this->unionTypes(function (TypeX $type): TypeX {
			return $type->getIterableValueType();
		});
	}

	public function canCallMethodsX(): int
	{
		return $this->unionResults(function (TypeX $type): int {
			return $type->canCallMethodsX();
		});
	}

	public function canAccessPropertiesX(): int
	{
		return $this->unionResults(function (TypeX $type): int {
			return $type->canAccessPropertiesX();
		});
	}

	public function canAccessOffset(): int
	{
		return $this->unionResults(function (TypeX $type): int {
			return $type->canAccessOffset();
		});
	}

	public function resolveStatic(string $className): \PHPStan\Type\Type
	{
		return $this->factory->createUnionType(...UnionTypeHelper::resolveStatic($className, $this->types));
	}

	public function changeBaseClass(string $className): StaticResolvableType
	{
		return $this->factory->createUnionType(...UnionTypeHelper::changeBaseClass($className, $this->types));
	}

	private function unionResults(callable $getResult): int
	{
		$result = null;

		foreach ($this->types as $type) {
			$subResult = $getResult($type);

			if ($result === null) {
				$result = $subResult;

			} elseif ($result !== $subResult) {
				$result = self::RESULT_MAYBE;
			}
		}

		return $result;
	}

	private function unionTypes(callable $getType): TypeX
	{
		$subTypes = [];

		foreach ($this->types as $type) {
			$subTypes[] = $getType($type);
		}

		return $this->factory->createUnionType(...$subTypes);
	}
}
