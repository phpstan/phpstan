<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use Nette\Utils\Strings;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\MagicConst;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\MagicConst\File;
use PhpParser\Node\Scalar\MagicConst\Line;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\ConstantResolver;
use PHPStan\Analyser\OutOfClassScope;
use PHPStan\DependencyInjection\AutowiredParameter;
use PHPStan\DependencyInjection\AutowiredService;
use PHPStan\DependencyInjection\Type\OperatorTypeSpecifyingExtensionRegistryProvider;
use PHPStan\Node\Expr\TypeExpr;
use PHPStan\Php\PhpVersion;
use PHPStan\Reflection\ReflectionProvider\ReflectionProviderProvider;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\Accessory\AccessoryArrayListType;
use PHPStan\Type\Accessory\AccessoryLiteralStringType;
use PHPStan\Type\Accessory\AccessoryLowercaseStringType;
use PHPStan\Type\Accessory\AccessoryNonEmptyStringType;
use PHPStan\Type\Accessory\AccessoryNonFalsyStringType;
use PHPStan\Type\Accessory\AccessoryNumericStringType;
use PHPStan\Type\Accessory\AccessoryUppercaseStringType;
use PHPStan\Type\Accessory\HasOffsetValueType;
use PHPStan\Type\Accessory\NonEmptyArrayType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BenevolentUnionType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\ClassStringType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantFloatType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Constant\OversizedArrayBuilder;
use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\ConstantTypeHelper;
use PHPStan\Type\Enum\EnumCaseObjectType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\FloatType;
use PHPStan\Type\GeneralizePrecision;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\Generic\TemplateType;
use PHPStan\Type\IntegerRangeType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StaticType;
use PHPStan\Type\StringType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypehintHelper;
use PHPStan\Type\TypeResult;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use function array_key_exists;
use function array_keys;
use function array_merge;
use function assert;
use function ceil;
use function count;
use function dirname;
use function floor;
use function in_array;
use function intval;
use function is_finite;
use function is_float;
use function is_int;
use function is_numeric;
use function max;
use function min;
use function sprintf;
use function strtolower;
use const INF;

#[AutowiredService]
final class InitializerExprTypeResolver
{

	public const CALCULATE_SCALARS_LIMIT = 128;

	/** @var array<string, true> */
	private array $currentlyResolvingClassConstant = [];

	public function __construct(
		private ConstantResolver $constantResolver,
		private ReflectionProviderProvider $reflectionProviderProvider,
		private PhpVersion $phpVersion,
		private OperatorTypeSpecifyingExtensionRegistryProvider $operatorTypeSpecifyingExtensionRegistryProvider,
		private OversizedArrayBuilder $oversizedArrayBuilder,
		#[AutowiredParameter]
		private bool $usePathConstantsAsConstantString,
	)
	{
	}

	/** @api */
	public function getType(Expr $expr, InitializerExprContext $context): Type
	{
		if ($expr instanceof TypeExpr) {
			return $expr->getExprType();
		}
		if ($expr instanceof Int_) {
			return new ConstantIntegerType($expr->value);
		}
		if ($expr instanceof Float_) {
			return new ConstantFloatType($expr->value);
		}
		if ($expr instanceof String_) {
			return new ConstantStringType($expr->value);
		}
		if ($expr instanceof ConstFetch) {
			$constName = (string) $expr->name;
			$loweredConstName = strtolower($constName);
			if ($loweredConstName === 'true') {
				return new ConstantBooleanType(true);
			} elseif ($loweredConstName === 'false') {
				return new ConstantBooleanType(false);
			} elseif ($loweredConstName === 'null') {
				return new NullType();
			}

			$constant = $this->constantResolver->resolveConstant($expr->name, $context);
			if ($constant !== null) {
				return $constant;
			}

			return new ErrorType();
		}
		if ($expr instanceof File) {
			$file = $context->getFile();
			if ($file === null) {
				return new StringType();
			}
			$stringType = new ConstantStringType($file);
			return $this->usePathConstantsAsConstantString ? $stringType : $stringType->generalize(GeneralizePrecision::moreSpecific());
		}
		if ($expr instanceof Dir) {
			$file = $context->getFile();
			if ($file === null) {
				return new StringType();
			}
			$stringType = new ConstantStringType(dirname($file));
			return $this->usePathConstantsAsConstantString ? $stringType : $stringType->generalize(GeneralizePrecision::moreSpecific());
		}
		if ($expr instanceof Line) {
			return new ConstantIntegerType($expr->getStartLine());
		}
		if ($expr instanceof Expr\New_) {
			if ($expr->class instanceof Name) {
				return new ObjectType((string) $expr->class);
			}

			return new ObjectWithoutClassType();
		}
		if ($expr instanceof Expr\Array_) {
			return $this->getArrayType($expr, fn (Expr $expr): Type => $this->getType($expr, $context));
		}
		if ($expr instanceof Expr\ArrayDimFetch && $expr->dim !== null) {
			$var = $this->getType($expr->var, $context);
			$dim = $this->getType($expr->dim, $context);
			return $var->getOffsetValueType($dim);
		}
		if ($expr instanceof ClassConstFetch && $expr->name instanceof Identifier) {
			return $this->getClassConstFetchType($expr->class, $expr->name->toString(), $context->getClassName(), fn (Expr $expr): Type => $this->getType($expr, $context));
		}
		if ($expr instanceof Expr\UnaryPlus) {
			return $this->getType($expr->expr, $context)->toNumber();
		}
		if ($expr instanceof Expr\UnaryMinus) {
			return $this->getUnaryMinusType($expr->expr, fn (Expr $expr): Type => $this->getType($expr, $context));
		}
		if ($expr instanceof Expr\BinaryOp\Coalesce) {
			$leftType = $this->getType($expr->left, $context);
			$rightType = $this->getType($expr->right, $context);

			return TypeCombinator::union(TypeCombinator::removeNull($leftType), $rightType);
		}

		if ($expr instanceof Expr\Ternary) {
			$condType = $this->getType($expr->cond, $context);
			$elseType = $this->getType($expr->else, $context);
			if ($expr->if === null) {
				return TypeCombinator::union(
					TypeCombinator::removeFalsey($condType),
					$elseType,
				);
			}

			$ifType = $this->getType($expr->if, $context);

			return TypeCombinator::union(
				TypeCombinator::removeFalsey($ifType),
				$elseType,
			);
		}

		if ($expr instanceof Expr\FuncCall && $expr->name instanceof Name && $expr->name->toLowerString() === 'constant') {
			$firstArg = $expr->args[0] ?? null;
			if ($firstArg instanceof Arg && $firstArg->value instanceof String_) {
				$constant = $this->constantResolver->resolvePredefinedConstant($firstArg->value->value);
				if ($constant !== null) {
					return $constant;
				}
			}
		}

		if ($expr instanceof Expr\BooleanNot) {
			$exprBooleanType = $this->getType($expr->expr, $context)->toBoolean();

			if ($exprBooleanType instanceof ConstantBooleanType) {
				return new ConstantBooleanType(!$exprBooleanType->getValue());
			}

			return new BooleanType();
		}

		if ($expr instanceof Expr\BitwiseNot) {
			return $this->getBitwiseNotType($expr->expr, fn (Expr $expr): Type => $this->getType($expr, $context));
		}

		if ($expr instanceof Expr\BinaryOp\Concat) {
			return $this->getConcatType($expr->left, $expr->right, fn (Expr $expr): Type => $this->getType($expr, $context));
		}

		if ($expr instanceof Expr\BinaryOp\BitwiseAnd) {
			return $this->getBitwiseAndType($expr->left, $expr->right, fn (Expr $expr): Type => $this->getType($expr, $context));
		}

		if ($expr instanceof Expr\BinaryOp\BitwiseOr) {
			return $this->getBitwiseOrType($expr->left, $expr->right, fn (Expr $expr): Type => $this->getType($expr, $context));
		}

		if ($expr instanceof Expr\BinaryOp\BitwiseXor) {
			return $this->getBitwiseXorType($expr->left, $expr->right, fn (Expr $expr): Type => $this->getType($expr, $context));
		}

		if ($expr instanceof Expr\BinaryOp\Spaceship) {
			return $this->getSpaceshipType($expr->left, $expr->right, fn (Expr $expr): Type => $this->getType($expr, $context));
		}

		if (
			$expr instanceof Expr\BinaryOp\BooleanAnd
			|| $expr instanceof Expr\BinaryOp\LogicalAnd
			|| $expr instanceof Expr\BinaryOp\BooleanOr
			|| $expr instanceof Expr\BinaryOp\LogicalOr
		) {
			return new BooleanType();
		}

		if ($expr instanceof Expr\BinaryOp\Div) {
			return $this->getDivType($expr->left, $expr->right, fn (Expr $expr): Type => $this->getType($expr, $context));
		}

		if ($expr instanceof Expr\BinaryOp\Mod) {
			return $this->getModType($expr->left, $expr->right, fn (Expr $expr): Type => $this->getType($expr, $context));
		}

		if ($expr instanceof Expr\BinaryOp\Plus) {
			return $this->getPlusType($expr->left, $expr->right, fn (Expr $expr): Type => $this->getType($expr, $context));
		}

		if ($expr instanceof Expr\BinaryOp\Minus) {
			return $this->getMinusType($expr->left, $expr->right, fn (Expr $expr): Type => $this->getType($expr, $context));
		}

		if ($expr instanceof Expr\BinaryOp\Mul) {
			return $this->getMulType($expr->left, $expr->right, fn (Expr $expr): Type => $this->getType($expr, $context));
		}

		if ($expr instanceof Expr\BinaryOp\Pow) {
			return $this->getPowType($expr->left, $expr->right, fn (Expr $expr): Type => $this->getType($expr, $context));
		}

		if ($expr instanceof Expr\BinaryOp\ShiftLeft) {
			return $this->getShiftLeftType($expr->left, $expr->right, fn (Expr $expr): Type => $this->getType($expr, $context));
		}

		if ($expr instanceof Expr\BinaryOp\ShiftRight) {
			return $this->getShiftRightType($expr->left, $expr->right, fn (Expr $expr): Type => $this->getType($expr, $context));
		}

		if ($expr instanceof BinaryOp\Identical) {
			return $this->resolveIdenticalType(
				$this->getType($expr->left, $context),
				$this->getType($expr->right, $context),
			)->type;
		}

		if ($expr instanceof BinaryOp\NotIdentical) {
			return $this->getType(new Expr\BooleanNot(new BinaryOp\Identical($expr->left, $expr->right)), $context);
		}

		if ($expr instanceof BinaryOp\Equal) {
			return $this->resolveEqualType(
				$this->getType($expr->left, $context),
				$this->getType($expr->right, $context),
			)->type;
		}

		if ($expr instanceof BinaryOp\NotEqual) {
			return $this->getType(new Expr\BooleanNot(new BinaryOp\Equal($expr->left, $expr->right)), $context);
		}

		if ($expr instanceof Expr\BinaryOp\Smaller) {
			return $this->getType($expr->left, $context)->isSmallerThan($this->getType($expr->right, $context), $this->phpVersion)->toBooleanType();
		}

		if ($expr instanceof Expr\BinaryOp\SmallerOrEqual) {
			return $this->getType($expr->left, $context)->isSmallerThanOrEqual($this->getType($expr->right, $context), $this->phpVersion)->toBooleanType();
		}

		if ($expr instanceof Expr\BinaryOp\Greater) {
			return $this->getType($expr->right, $context)->isSmallerThan($this->getType($expr->left, $context), $this->phpVersion)->toBooleanType();
		}

		if ($expr instanceof Expr\BinaryOp\GreaterOrEqual) {
			return $this->getType($expr->right, $context)->isSmallerThanOrEqual($this->getType($expr->left, $context), $this->phpVersion)->toBooleanType();
		}

		if ($expr instanceof Expr\BinaryOp\LogicalXor) {
			$leftBooleanType = $this->getType($expr->left, $context)->toBoolean();
			$rightBooleanType = $this->getType($expr->right, $context)->toBoolean();

			if (
				$leftBooleanType instanceof ConstantBooleanType
				&& $rightBooleanType instanceof ConstantBooleanType
			) {
				return new ConstantBooleanType(
					$leftBooleanType->getValue() xor $rightBooleanType->getValue(),
				);
			}

			return new BooleanType();
		}

		if ($expr instanceof MagicConst\Class_) {
			if ($context->getTraitName() !== null) {
				return TypeCombinator::intersect(
					new ClassStringType(),
					new AccessoryLiteralStringType(),
				);
			}

			if ($context->getClassName() === null) {
				return new ConstantStringType('');
			}

			return new ConstantStringType($context->getClassName(), true);
		}

		if ($expr instanceof MagicConst\Namespace_) {
			if ($context->getTraitName() !== null) {
				return TypeCombinator::intersect(
					new StringType(),
					new AccessoryLiteralStringType(),
				);
			}

			return new ConstantStringType($context->getNamespace() ?? '');
		}

		if ($expr instanceof MagicConst\Method) {
			return new ConstantStringType($context->getMethod() ?? '');
		}

		if ($expr instanceof MagicConst\Function_) {
			return new ConstantStringType($context->getFunction() ?? '');
		}

		if ($expr instanceof MagicConst\Trait_) {
			if ($context->getTraitName() === null) {
				return new ConstantStringType('');
			}

			return new ConstantStringType($context->getTraitName(), true);
		}

		if ($expr instanceof MagicConst\Property) {
			$contextProperty = $context->getProperty();
			if ($contextProperty === null) {
				return new ConstantStringType('');
			}

			return new ConstantStringType($contextProperty);
		}

		if ($expr instanceof PropertyFetch && $expr->name instanceof Identifier) {
			$fetchedOnType = $this->getType($expr->var, $context);
			if (!$fetchedOnType->hasProperty($expr->name->name)->yes()) {
				return new ErrorType();
			}

			return $fetchedOnType->getProperty($expr->name->name, new OutOfClassScope())->getReadableType();
		}

		return new MixedType();
	}

	/**
	 * @param callable(Expr): Type $getTypeCallback
	 */
	public function getConcatType(Expr $left, Expr $right, callable $getTypeCallback): Type
	{
		$leftType = $getTypeCallback($left);
		$rightType = $getTypeCallback($right);

		return $this->resolveConcatType($leftType, $rightType);
	}

	public function resolveConcatType(Type $left, Type $right): Type
	{
		$leftStringType = $left->toString();
		$rightStringType = $right->toString();
		if (TypeCombinator::union(
			$leftStringType,
			$rightStringType,
		) instanceof ErrorType) {
			return new ErrorType();
		}

		if ($leftStringType instanceof ConstantStringType && $leftStringType->getValue() === '') {
			return $rightStringType;
		}

		if ($rightStringType instanceof ConstantStringType && $rightStringType->getValue() === '') {
			return $leftStringType;
		}

		if ($leftStringType instanceof ConstantStringType && $rightStringType instanceof ConstantStringType) {
			return $leftStringType->append($rightStringType);
		}

		$leftConstantStrings = $leftStringType->getConstantStrings();
		$rightConstantStrings = $rightStringType->getConstantStrings();
		$combinedConstantStringsCount = count($leftConstantStrings) * count($rightConstantStrings);

		// we limit the number of union-types for performance reasons
		if ($combinedConstantStringsCount > 0 && $combinedConstantStringsCount <= 16) {
			$strings = [];

			foreach ($leftConstantStrings as $leftConstantString) {
				if ($leftConstantString->getValue() === '') {
					$strings = array_merge($strings, $rightConstantStrings);

					continue;
				}

				foreach ($rightConstantStrings as $rightConstantString) {
					if ($rightConstantString->getValue() === '') {
						$strings[] = $leftConstantString;

						continue;
					}

					$strings[] = $leftConstantString->append($rightConstantString);
				}
			}

			if (count($strings) > 0) {
				return TypeCombinator::union(...$strings);
			}
		}

		$accessoryTypes = [];
		if ($leftStringType->isNonEmptyString()->and($rightStringType->isNonEmptyString())->yes()) {
			$accessoryTypes[] = new AccessoryNonFalsyStringType();
		} elseif ($leftStringType->isNonFalsyString()->or($rightStringType->isNonFalsyString())->yes()) {
			$accessoryTypes[] = new AccessoryNonFalsyStringType();
		} elseif ($leftStringType->isNonEmptyString()->or($rightStringType->isNonEmptyString())->yes()) {
			$accessoryTypes[] = new AccessoryNonEmptyStringType();
		}

		if ($leftStringType->isLiteralString()->and($rightStringType->isLiteralString())->yes()) {
			$accessoryTypes[] = new AccessoryLiteralStringType();
		}

		if ($leftStringType->isLowercaseString()->and($rightStringType->isLowercaseString())->yes()) {
			$accessoryTypes[] = new AccessoryLowercaseStringType();
		}

		if ($leftStringType->isUppercaseString()->and($rightStringType->isUppercaseString())->yes()) {
			$accessoryTypes[] = new AccessoryUppercaseStringType();
		}

		$leftNumericStringNonEmpty = TypeCombinator::remove($leftStringType, new ConstantStringType(''));
		if ($leftNumericStringNonEmpty->isNumericString()->yes()) {
			$allRightConstantsZeroOrMore = false;
			foreach ($rightConstantStrings as $rightConstantString) {
				if ($rightConstantString->getValue() === '') {
					continue;
				}

				if (
					!is_numeric($rightConstantString->getValue())
					|| Strings::match($rightConstantString->getValue(), '#^[0-9]+$#') === null
				) {
					$allRightConstantsZeroOrMore = false;
					break;
				}

				$allRightConstantsZeroOrMore = true;
			}

			$zeroOrMoreInteger = IntegerRangeType::fromInterval(0, null);
			$nonNegativeRight = $allRightConstantsZeroOrMore || $zeroOrMoreInteger->isSuperTypeOf($right)->yes();
			if ($nonNegativeRight) {
				$accessoryTypes[] = new AccessoryNumericStringType();
			}
		}

		if (count($accessoryTypes) > 0) {
			$accessoryTypes[] = new StringType();
			return new IntersectionType($accessoryTypes);
		}

		return new StringType();
	}

	/**
	 * @param callable(Expr): Type $getTypeCallback
	 */
	public function getArrayType(Expr\Array_ $expr, callable $getTypeCallback): Type
	{
		if (count($expr->items) > ConstantArrayTypeBuilder::ARRAY_COUNT_LIMIT) {
			return $this->oversizedArrayBuilder->build($expr, $getTypeCallback);
		}

		$arrayBuilder = ConstantArrayTypeBuilder::createEmpty();
		$isList = null;
		foreach ($expr->items as $arrayItem) {
			$valueType = $getTypeCallback($arrayItem->value);
			if ($arrayItem->unpack) {
				$constantArrays = $valueType->getConstantArrays();
				if (count($constantArrays) === 1) {
					$constantArrayType = $constantArrays[0];
					$hasStringKey = false;
					foreach ($constantArrayType->getKeyTypes() as $keyType) {
						if ($keyType->isString()->yes()) {
							$hasStringKey = true;
							break;
						}
					}

					foreach ($constantArrayType->getValueTypes() as $i => $innerValueType) {
						if ($hasStringKey && $this->phpVersion->supportsArrayUnpackingWithStringKeys()) {
							$arrayBuilder->setOffsetValueType($constantArrayType->getKeyTypes()[$i], $innerValueType, $constantArrayType->isOptionalKey($i));
						} else {
							$arrayBuilder->setOffsetValueType(null, $innerValueType, $constantArrayType->isOptionalKey($i));
						}
					}
				} else {
					$arrayBuilder->degradeToGeneralArray();

					if ($this->phpVersion->supportsArrayUnpackingWithStringKeys() && !$valueType->getIterableKeyType()->isString()->no()) {
						$isList = false;
						$offsetType = $valueType->getIterableKeyType();
					} else {
						$isList ??= $arrayBuilder->isList();
						$offsetType = new IntegerType();
					}

					$arrayBuilder->setOffsetValueType($offsetType, $valueType->getIterableValueType(), !$valueType->isIterableAtLeastOnce()->yes());
				}
			} else {
				$arrayBuilder->setOffsetValueType(
					$arrayItem->key !== null ? $getTypeCallback($arrayItem->key) : null,
					$valueType,
				);
			}
		}

		$arrayType = $arrayBuilder->getArray();
		if ($isList === true) {
			return TypeCombinator::intersect($arrayType, new AccessoryArrayListType());
		}

		return $arrayType;
	}

	/**
	 * @param callable(Expr): Type $getTypeCallback
	 */
	public function getBitwiseAndType(Expr $left, Expr $right, callable $getTypeCallback): Type
	{
		$leftType = $getTypeCallback($left);
		$rightType = $getTypeCallback($right);

		if ($leftType instanceof NeverType || $rightType instanceof NeverType) {
			return $this->getNeverType($leftType, $rightType);
		}

		$leftTypes = $leftType->getConstantScalarTypes();
		$rightTypes = $rightType->getConstantScalarTypes();
		$leftTypesCount = count($leftTypes);
		$rightTypesCount = count($rightTypes);
		if ($leftTypesCount > 0 && $rightTypesCount > 0) {
			$resultTypes = [];
			$generalize = $leftTypesCount * $rightTypesCount > self::CALCULATE_SCALARS_LIMIT;
			if (!$generalize) {
				foreach ($leftTypes as $leftTypeInner) {
					foreach ($rightTypes as $rightTypeInner) {
						if ($leftTypeInner instanceof ConstantStringType && $rightTypeInner instanceof ConstantStringType) {
							$resultType = $this->getTypeFromValue($leftTypeInner->getValue() & $rightTypeInner->getValue());
						} else {
							$leftNumberType = $leftTypeInner->toNumber();
							$rightNumberType = $rightTypeInner->toNumber();

							if ($leftNumberType instanceof ErrorType || $rightNumberType instanceof ErrorType) {
								return new ErrorType();
							}

							if (!$leftNumberType instanceof ConstantScalarType || !$rightNumberType instanceof ConstantScalarType) {
								throw new ShouldNotHappenException();
							}

							$resultType = $this->getTypeFromValue($leftNumberType->getValue() & $rightNumberType->getValue());
						}
						$resultTypes[] = $resultType;
					}
				}
				return TypeCombinator::union(...$resultTypes);
			}

			$leftType = $this->optimizeScalarType($leftType);
			$rightType = $this->optimizeScalarType($rightType);
		}

		if ($leftType->isString()->yes() && $rightType->isString()->yes()) {
			return new StringType();
		}

		$leftNumberType = $leftType->toNumber();
		$rightNumberType = $rightType->toNumber();

		if ($leftNumberType instanceof ErrorType || $rightNumberType instanceof ErrorType) {
			return new ErrorType();
		}

		if ($rightNumberType instanceof ConstantIntegerType && $rightNumberType->getValue() >= 0) {
			return IntegerRangeType::fromInterval(0, $rightNumberType->getValue());
		}
		if ($leftNumberType instanceof ConstantIntegerType && $leftNumberType->getValue() >= 0) {
			return IntegerRangeType::fromInterval(0, $leftNumberType->getValue());
		}

		return new IntegerType();
	}

	/**
	 * @param callable(Expr): Type $getTypeCallback
	 */
	public function getBitwiseOrType(Expr $left, Expr $right, callable $getTypeCallback): Type
	{
		$leftType = $getTypeCallback($left);
		$rightType = $getTypeCallback($right);

		if ($leftType instanceof NeverType || $rightType instanceof NeverType) {
			return $this->getNeverType($leftType, $rightType);
		}

		$leftTypes = $leftType->getConstantScalarTypes();
		$rightTypes = $rightType->getConstantScalarTypes();
		$leftTypesCount = count($leftTypes);
		$rightTypesCount = count($rightTypes);
		if ($leftTypesCount > 0 && $rightTypesCount > 0) {
			$resultTypes = [];
			$generalize = $leftTypesCount * $rightTypesCount > self::CALCULATE_SCALARS_LIMIT;
			if (!$generalize) {
				foreach ($leftTypes as $leftTypeInner) {
					foreach ($rightTypes as $rightTypeInner) {
						if ($leftTypeInner instanceof ConstantStringType && $rightTypeInner instanceof ConstantStringType) {
							$resultType = $this->getTypeFromValue($leftTypeInner->getValue() | $rightTypeInner->getValue());
						} else {
							$leftNumberType = $leftTypeInner->toNumber();
							$rightNumberType = $rightTypeInner->toNumber();

							if ($leftNumberType instanceof ErrorType || $rightNumberType instanceof ErrorType) {
								return new ErrorType();
							}

							if (!$leftNumberType instanceof ConstantScalarType || !$rightNumberType instanceof ConstantScalarType) {
								throw new ShouldNotHappenException();
							}

							$resultType = $this->getTypeFromValue($leftNumberType->getValue() | $rightNumberType->getValue());
						}
						$resultTypes[] = $resultType;
					}
				}
				return TypeCombinator::union(...$resultTypes);
			}

			$leftType = $this->optimizeScalarType($leftType);
			$rightType = $this->optimizeScalarType($rightType);
		}

		if ($leftType->isString()->yes() && $rightType->isString()->yes()) {
			return new StringType();
		}

		if (TypeCombinator::union($leftType->toNumber(), $rightType->toNumber()) instanceof ErrorType) {
			return new ErrorType();
		}

		return new IntegerType();
	}

	/**
	 * @param callable(Expr): Type $getTypeCallback
	 */
	public function getBitwiseXorType(Expr $left, Expr $right, callable $getTypeCallback): Type
	{
		$leftType = $getTypeCallback($left);
		$rightType = $getTypeCallback($right);

		if ($leftType instanceof NeverType || $rightType instanceof NeverType) {
			return $this->getNeverType($leftType, $rightType);
		}

		$leftTypes = $leftType->getConstantScalarTypes();
		$rightTypes = $rightType->getConstantScalarTypes();
		$leftTypesCount = count($leftTypes);
		$rightTypesCount = count($rightTypes);
		if ($leftTypesCount > 0 && $rightTypesCount > 0) {
			$resultTypes = [];
			$generalize = $leftTypesCount * $rightTypesCount > self::CALCULATE_SCALARS_LIMIT;
			if (!$generalize) {
				foreach ($leftTypes as $leftTypeInner) {
					foreach ($rightTypes as $rightTypeInner) {
						if ($leftTypeInner instanceof ConstantStringType && $rightTypeInner instanceof ConstantStringType) {
							$resultType = $this->getTypeFromValue($leftTypeInner->getValue() ^ $rightTypeInner->getValue());
						} else {
							$leftNumberType = $leftTypeInner->toNumber();
							$rightNumberType = $rightTypeInner->toNumber();

							if ($leftNumberType instanceof ErrorType || $rightNumberType instanceof ErrorType) {
								return new ErrorType();
							}

							if (!$leftNumberType instanceof ConstantScalarType || !$rightNumberType instanceof ConstantScalarType) {
								throw new ShouldNotHappenException();
							}

							$resultType = $this->getTypeFromValue($leftNumberType->getValue() ^ $rightNumberType->getValue());
						}
						$resultTypes[] = $resultType;
					}
				}
				return TypeCombinator::union(...$resultTypes);
			}

			$leftType = $this->optimizeScalarType($leftType);
			$rightType = $this->optimizeScalarType($rightType);
		}

		if ($leftType->isString()->yes() && $rightType->isString()->yes()) {
			return new StringType();
		}

		if (TypeCombinator::union($leftType->toNumber(), $rightType->toNumber()) instanceof ErrorType) {
			return new ErrorType();
		}

		return new IntegerType();
	}

	/**
	 * @param callable(Expr): Type $getTypeCallback
	 */
	public function getSpaceshipType(Expr $left, Expr $right, callable $getTypeCallback): Type
	{
		$callbackLeftType = $getTypeCallback($left);
		$callbackRightType = $getTypeCallback($right);

		if ($callbackLeftType instanceof NeverType || $callbackRightType instanceof NeverType) {
			return $this->getNeverType($callbackLeftType, $callbackRightType);
		}

		$leftTypes = $callbackLeftType->getConstantScalarTypes();
		$rightTypes = $callbackRightType->getConstantScalarTypes();

		$leftTypesCount = count($leftTypes);
		$rightTypesCount = count($rightTypes);
		if ($leftTypesCount > 0 && $rightTypesCount > 0 && $leftTypesCount * $rightTypesCount <= self::CALCULATE_SCALARS_LIMIT) {
			$resultTypes = [];
			foreach ($leftTypes as $leftType) {
				foreach ($rightTypes as $rightType) {
					$leftValue = $leftType->getValue();
					$rightValue = $rightType->getValue();
					$resultType = $this->getTypeFromValue($leftValue <=> $rightValue);
					$resultTypes[] = $resultType;
				}
			}
			return TypeCombinator::union(...$resultTypes);
		}

		return IntegerRangeType::fromInterval(-1, 1);
	}

	/**
	 * @param callable(Expr): Type $getTypeCallback
	 */
	public function getDivType(Expr $left, Expr $right, callable $getTypeCallback): Type
	{
		$leftType = $getTypeCallback($left);
		$rightType = $getTypeCallback($right);

		$leftTypes = $leftType->getConstantScalarTypes();
		$rightTypes = $rightType->getConstantScalarTypes();
		$leftTypesCount = count($leftTypes);
		$rightTypesCount = count($rightTypes);
		if ($leftTypesCount > 0 && $rightTypesCount > 0) {
			$resultTypes = [];
			$generalize = $leftTypesCount * $rightTypesCount > self::CALCULATE_SCALARS_LIMIT;
			if (!$generalize) {
				foreach ($leftTypes as $leftTypeInner) {
					foreach ($rightTypes as $rightTypeInner) {
						$leftNumberType = $leftTypeInner->toNumber();
						$rightNumberType = $rightTypeInner->toNumber();

						if ($leftNumberType instanceof ErrorType || $rightNumberType instanceof ErrorType) {
							return new ErrorType();
						}

						if (!$leftNumberType instanceof ConstantScalarType || !$rightNumberType instanceof ConstantScalarType) {
							throw new ShouldNotHappenException();
						}

						if (in_array($rightNumberType->getValue(), [0, 0.0], true)) {
							return new ErrorType();
						}

						$resultType = $this->getTypeFromValue($leftNumberType->getValue() / $rightNumberType->getValue()); // @phpstan-ignore binaryOp.invalid
						$resultTypes[] = $resultType;
					}
				}
				return TypeCombinator::union(...$resultTypes);
			}

			$leftType = $this->optimizeScalarType($leftType);
			$rightType = $this->optimizeScalarType($rightType);
		}

		$rightScalarValues = $rightType->toNumber()->getConstantScalarValues();
		foreach ($rightScalarValues as $scalarValue) {
			if (in_array($scalarValue, [0, 0.0], true)) {
				return new ErrorType();
			}
		}

		return $this->resolveCommonMath(new BinaryOp\Div($left, $right), $leftType, $rightType);
	}

	/**
	 * @param callable(Expr): Type $getTypeCallback
	 */
	public function getModType(Expr $left, Expr $right, callable $getTypeCallback): Type
	{
		$leftType = $getTypeCallback($left);
		$rightType = $getTypeCallback($right);

		if ($leftType instanceof NeverType || $rightType instanceof NeverType) {
			return $this->getNeverType($leftType, $rightType);
		}

		$extensionSpecified = $this->callOperatorTypeSpecifyingExtensions(new BinaryOp\Mod($left, $right), $leftType, $rightType);
		if ($extensionSpecified !== null) {
			return $extensionSpecified;
		}

		if ($leftType->toNumber() instanceof ErrorType || $rightType->toNumber() instanceof ErrorType) {
			return new ErrorType();
		}

		$leftTypes = $leftType->getConstantScalarTypes();
		$rightTypes = $rightType->getConstantScalarTypes();
		$leftTypesCount = count($leftTypes);
		$rightTypesCount = count($rightTypes);
		if ($leftTypesCount > 0 && $rightTypesCount > 0) {
			$resultTypes = [];
			$generalize = $leftTypesCount * $rightTypesCount > self::CALCULATE_SCALARS_LIMIT;
			if (!$generalize) {
				foreach ($leftTypes as $leftTypeInner) {
					foreach ($rightTypes as $rightTypeInner) {
						$leftNumberType = $leftTypeInner->toNumber();
						$rightNumberType = $rightTypeInner->toNumber();

						if ($leftNumberType instanceof ErrorType || $rightNumberType instanceof ErrorType) {
							return new ErrorType();
						}

						if (!$leftNumberType instanceof ConstantScalarType || !$rightNumberType instanceof ConstantScalarType) {
							throw new ShouldNotHappenException();
						}

						$rightIntegerValue = (int) $rightNumberType->getValue();
						if ($rightIntegerValue === 0) {
							return new ErrorType();
						}

						$resultType = $this->getTypeFromValue((int) $leftNumberType->getValue() % $rightIntegerValue);
						$resultTypes[] = $resultType;
					}
				}
				return TypeCombinator::union(...$resultTypes);
			}

			$leftType = $this->optimizeScalarType($leftType);
			$rightType = $this->optimizeScalarType($rightType);
		}

		$integerType = $rightType->toInteger();
		if ($integerType instanceof ConstantIntegerType && $integerType->getValue() === 1) {
			return new ConstantIntegerType(0);
		}

		$rightScalarValues = $rightType->toNumber()->getConstantScalarValues();
		foreach ($rightScalarValues as $scalarValue) {

			if (in_array($scalarValue, [0, 0.0], true)) {
				return new ErrorType();
			}
		}

		$positiveInt = IntegerRangeType::fromInterval(0, null);
		if ($rightType->isInteger()->yes()) {
			$rangeMin = null;
			$rangeMax = null;

			if ($rightType instanceof IntegerRangeType) {
				$rangeMax = $rightType->getMax() !== null ? $rightType->getMax() - 1 : null;
			} elseif ($rightType instanceof ConstantIntegerType) {
				$rangeMax = $rightType->getValue() - 1;
			} elseif ($rightType instanceof UnionType) {
				foreach ($rightType->getTypes() as $type) {
					if ($type instanceof IntegerRangeType) {
						if ($type->getMax() === null) {
							$rangeMax = null;
						} else {
							$rangeMax = max($rangeMax, $type->getMax());
						}
					} elseif ($type instanceof ConstantIntegerType) {
						$rangeMax = max($rangeMax, $type->getValue() - 1);
					}
				}
			}

			if ($positiveInt->isSuperTypeOf($leftType)->yes()) {
				$rangeMin = 0;
			} elseif ($rangeMax !== null) {
				$rangeMin = $rangeMax * -1;
			}

			return IntegerRangeType::fromInterval($rangeMin, $rangeMax);
		} elseif ($positiveInt->isSuperTypeOf($leftType)->yes()) {
			return IntegerRangeType::fromInterval(0, null);
		}

		return new IntegerType();
	}

	/**
	 * @param callable(Expr): Type $getTypeCallback
	 */
	public function getPlusType(Expr $left, Expr $right, callable $getTypeCallback): Type
	{
		$leftType = $getTypeCallback($left);
		$rightType = $getTypeCallback($right);

		if ($leftType instanceof NeverType || $rightType instanceof NeverType) {
			return $this->getNeverType($leftType, $rightType);
		}

		$leftTypes = $leftType->getConstantScalarTypes();
		$rightTypes = $rightType->getConstantScalarTypes();
		$leftTypesCount = count($leftTypes);
		$rightTypesCount = count($rightTypes);
		if ($leftTypesCount > 0 && $rightTypesCount > 0) {
			$resultTypes = [];
			$generalize = $leftTypesCount * $rightTypesCount > self::CALCULATE_SCALARS_LIMIT;
			if (!$generalize) {
				foreach ($leftTypes as $leftTypeInner) {
					foreach ($rightTypes as $rightTypeInner) {
						$leftNumberType = $leftTypeInner->toNumber();
						$rightNumberType = $rightTypeInner->toNumber();

						if ($leftNumberType instanceof ErrorType || $rightNumberType instanceof ErrorType) {
							return new ErrorType();
						}

						if (!$leftNumberType instanceof ConstantScalarType || !$rightNumberType instanceof ConstantScalarType) {
							throw new ShouldNotHappenException();
						}

						$resultType = $this->getTypeFromValue($leftNumberType->getValue() + $rightNumberType->getValue());
						$resultTypes[] = $resultType;
					}
				}

				return TypeCombinator::union(...$resultTypes);
			}

			$leftType = $this->optimizeScalarType($leftType);
			$rightType = $this->optimizeScalarType($rightType);
		}

		$leftConstantArrays = $leftType->getConstantArrays();
		$rightConstantArrays = $rightType->getConstantArrays();

		$leftCount = count($leftConstantArrays);
		$rightCount = count($rightConstantArrays);
		if ($leftCount > 0 && $rightCount > 0
			&& ($leftCount + $rightCount < ConstantArrayTypeBuilder::ARRAY_COUNT_LIMIT)) {
			$resultTypes = [];
			foreach ($rightConstantArrays as $rightConstantArray) {
				foreach ($leftConstantArrays as $leftConstantArray) {
					$newArrayBuilder = ConstantArrayTypeBuilder::createFromConstantArray($rightConstantArray);
					foreach ($leftConstantArray->getKeyTypes() as $i => $leftKeyType) {
						$optional = $leftConstantArray->isOptionalKey($i);
						$valueType = $leftConstantArray->getOffsetValueType($leftKeyType);
						if (!$optional) {
							if ($rightConstantArray->hasOffsetValueType($leftKeyType)->maybe()) {
								$valueType = TypeCombinator::union($valueType, $rightConstantArray->getOffsetValueType($leftKeyType));
							}
						}
						$newArrayBuilder->setOffsetValueType(
							$leftKeyType,
							$valueType,
							$optional,
						);
					}
					$resultTypes[] = $newArrayBuilder->getArray();
				}
			}
			return TypeCombinator::union(...$resultTypes);
		}

		$leftIsArray = $leftType->isArray();
		$rightIsArray = $rightType->isArray();
		if ($leftIsArray->yes() && $rightIsArray->yes()) {
			if ($leftType->getIterableKeyType()->equals($rightType->getIterableKeyType())) {
				// to preserve BenevolentUnionType
				$keyType = $leftType->getIterableKeyType();
			} else {
				$keyTypes = [];
				foreach ([
					$leftType->getIterableKeyType(),
					$rightType->getIterableKeyType(),
				] as $keyType) {
					$keyTypes[] = $keyType;
				}
				$keyType = TypeCombinator::union(...$keyTypes);
			}

			$arrayType = new ArrayType(
				$keyType,
				TypeCombinator::union($leftType->getIterableValueType(), $rightType->getIterableValueType()),
			);

			foreach ($leftType->getConstantArrays() as $type) {
				foreach ($type->getKeyTypes() as $i => $offsetType) {
					if ($type->isOptionalKey($i)) {
						continue;
					}
					$valueType = $type->getValueTypes()[$i];
					$arrayType = TypeCombinator::intersect($arrayType, new HasOffsetValueType($offsetType, $valueType));
				}
			}

			if ($leftType->isIterableAtLeastOnce()->yes() || $rightType->isIterableAtLeastOnce()->yes()) {
				$arrayType = TypeCombinator::intersect($arrayType, new NonEmptyArrayType());
			}
			if ($leftType->isList()->yes() && $rightType->isList()->yes()) {
				$arrayType = TypeCombinator::intersect($arrayType, new AccessoryArrayListType());
			}

			return $arrayType;
		}

		if ($leftType instanceof MixedType && $rightType instanceof MixedType) {
			if (
				($leftIsArray->no() && $rightIsArray->no())
			) {
				return new BenevolentUnionType([
					new FloatType(),
					new IntegerType(),
				]);
			}
			return new BenevolentUnionType([
				new FloatType(),
				new IntegerType(),
				new ArrayType(new MixedType(), new MixedType()),
			]);
		}

		if (
			($leftIsArray->yes() && $rightIsArray->no())
			|| ($leftIsArray->no() && $rightIsArray->yes())
		) {
			return new ErrorType();
		}

		if (
			($leftIsArray->yes() && $rightIsArray->maybe())
			|| ($leftIsArray->maybe() && $rightIsArray->yes())
		) {
			$resultType = new ArrayType(new MixedType(), new MixedType());
			if ($leftType->isIterableAtLeastOnce()->yes() || $rightType->isIterableAtLeastOnce()->yes()) {
				return TypeCombinator::intersect($resultType, new NonEmptyArrayType());
			}

			return $resultType;
		}

		if ($leftIsArray->maybe() && $rightIsArray->maybe()) {
			$plusable = new UnionType([
				new StringType(),
				new FloatType(),
				new IntegerType(),
				new ArrayType(new MixedType(), new MixedType()),
				new BooleanType(),
			]);

			$plusableSuperTypeOfLeft = $plusable->isSuperTypeOf($leftType)->yes();
			$plusableSuperTypeOfRight = $plusable->isSuperTypeOf($rightType)->yes();
			if ($plusableSuperTypeOfLeft && $plusableSuperTypeOfRight) {
				return TypeCombinator::union($leftType, $rightType);
			}
			if ($plusableSuperTypeOfLeft && $rightType instanceof MixedType) {
				return $leftType;
			}
			if ($plusableSuperTypeOfRight && $leftType instanceof MixedType) {
				return $rightType;
			}
		}

		return $this->resolveCommonMath(new BinaryOp\Plus($left, $right), $leftType, $rightType);
	}

	/**
	 * @param callable(Expr): Type $getTypeCallback
	 */
	public function getMinusType(Expr $left, Expr $right, callable $getTypeCallback): Type
	{
		$leftType = $getTypeCallback($left);
		$rightType = $getTypeCallback($right);

		$leftTypes = $leftType->getConstantScalarTypes();
		$rightTypes = $rightType->getConstantScalarTypes();
		$leftTypesCount = count($leftTypes);
		$rightTypesCount = count($rightTypes);
		if ($leftTypesCount > 0 && $rightTypesCount > 0) {
			$resultTypes = [];
			$generalize = $leftTypesCount * $rightTypesCount > self::CALCULATE_SCALARS_LIMIT;
			if (!$generalize) {
				foreach ($leftTypes as $leftTypeInner) {
					foreach ($rightTypes as $rightTypeInner) {
						$leftNumberType = $leftTypeInner->toNumber();
						$rightNumberType = $rightTypeInner->toNumber();

						if ($leftNumberType instanceof ErrorType || $rightNumberType instanceof ErrorType) {
							return new ErrorType();
						}

						if (!$leftNumberType instanceof ConstantScalarType || !$rightNumberType instanceof ConstantScalarType) {
							throw new ShouldNotHappenException();
						}

						$resultType = $this->getTypeFromValue($leftNumberType->getValue() - $rightNumberType->getValue());
						$resultTypes[] = $resultType;
					}
				}

				return TypeCombinator::union(...$resultTypes);
			}

			$leftType = $this->optimizeScalarType($leftType);
			$rightType = $this->optimizeScalarType($rightType);
		}

		return $this->resolveCommonMath(new BinaryOp\Minus($left, $right), $leftType, $rightType);
	}

	/**
	 * @param callable(Expr): Type $getTypeCallback
	 */
	public function getMulType(Expr $left, Expr $right, callable $getTypeCallback): Type
	{
		$leftType = $getTypeCallback($left);
		$rightType = $getTypeCallback($right);

		$leftTypes = $leftType->getConstantScalarTypes();
		$rightTypes = $rightType->getConstantScalarTypes();
		$leftTypesCount = count($leftTypes);
		$rightTypesCount = count($rightTypes);
		if ($leftTypesCount > 0 && $rightTypesCount > 0) {
			$resultTypes = [];
			$generalize = $leftTypesCount * $rightTypesCount > self::CALCULATE_SCALARS_LIMIT;
			if (!$generalize) {
				foreach ($leftTypes as $leftTypeInner) {
					foreach ($rightTypes as $rightTypeInner) {
						$leftNumberType = $leftTypeInner->toNumber();
						$rightNumberType = $rightTypeInner->toNumber();

						if ($leftNumberType instanceof ErrorType || $rightNumberType instanceof ErrorType) {
							return new ErrorType();
						}

						if (!$leftNumberType instanceof ConstantScalarType || !$rightNumberType instanceof ConstantScalarType) {
							throw new ShouldNotHappenException();
						}

						$resultType = $this->getTypeFromValue($leftNumberType->getValue() * $rightNumberType->getValue());
						$resultTypes[] = $resultType;
					}
				}

				return TypeCombinator::union(...$resultTypes);
			}

			$leftType = $this->optimizeScalarType($leftType);
			$rightType = $this->optimizeScalarType($rightType);
		}

		$leftNumberType = $leftType->toNumber();
		if ($leftNumberType instanceof ConstantIntegerType && $leftNumberType->getValue() === 0) {
			if ($rightType->isFloat()->yes()) {
				return new ConstantFloatType(0.0);
			}
			return new ConstantIntegerType(0);
		}
		$rightNumberType = $rightType->toNumber();
		if ($rightNumberType instanceof ConstantIntegerType && $rightNumberType->getValue() === 0) {
			if ($leftType->isFloat()->yes()) {
				return new ConstantFloatType(0.0);
			}
			return new ConstantIntegerType(0);
		}

		return $this->resolveCommonMath(new BinaryOp\Mul($left, $right), $leftType, $rightType);
	}

	/**
	 * @param callable(Expr): Type $getTypeCallback
	 */
	public function getPowType(Expr $left, Expr $right, callable $getTypeCallback): Type
	{
		$leftType = $getTypeCallback($left);
		$rightType = $getTypeCallback($right);

		$extensionSpecified = $this->callOperatorTypeSpecifyingExtensions(new BinaryOp\Pow($left, $right), $leftType, $rightType);
		if ($extensionSpecified !== null) {
			return $extensionSpecified;
		}

		$exponentiatedTyped = $leftType->exponentiate($rightType);
		if (!$exponentiatedTyped instanceof ErrorType) {
			return $exponentiatedTyped;
		}

		return new ErrorType();
	}

	/**
	 * @param callable(Expr): Type $getTypeCallback
	 */
	public function getShiftLeftType(Expr $left, Expr $right, callable $getTypeCallback): Type
	{
		$leftType = $getTypeCallback($left);
		$rightType = $getTypeCallback($right);

		if ($leftType instanceof NeverType || $rightType instanceof NeverType) {
			return $this->getNeverType($leftType, $rightType);
		}

		$leftTypes = $leftType->getConstantScalarTypes();
		$rightTypes = $rightType->getConstantScalarTypes();
		$leftTypesCount = count($leftTypes);
		$rightTypesCount = count($rightTypes);
		if ($leftTypesCount > 0 && $rightTypesCount > 0) {
			$resultTypes = [];
			$generalize = $leftTypesCount * $rightTypesCount > self::CALCULATE_SCALARS_LIMIT;
			if (!$generalize) {
				foreach ($leftTypes as $leftTypeInner) {
					foreach ($rightTypes as $rightTypeInner) {
						$leftNumberType = $leftTypeInner->toNumber();
						$rightNumberType = $rightTypeInner->toNumber();

						if ($leftNumberType instanceof ErrorType || $rightNumberType instanceof ErrorType) {
							return new ErrorType();
						}

						if (!$leftNumberType instanceof ConstantScalarType || !$rightNumberType instanceof ConstantScalarType) {
							throw new ShouldNotHappenException();
						}

						if ($rightNumberType->getValue() < 0) {
							return new ErrorType();
						}

						$resultType = $this->getTypeFromValue(intval($leftNumberType->getValue()) << intval($rightNumberType->getValue()));
						$resultTypes[] = $resultType;
					}
				}

				return TypeCombinator::union(...$resultTypes);
			}

			$leftType = $this->optimizeScalarType($leftType);
			$rightType = $this->optimizeScalarType($rightType);
		}

		$leftNumberType = $leftType->toNumber();
		$rightNumberType = $rightType->toNumber();

		if ($leftNumberType instanceof ErrorType || $rightNumberType instanceof ErrorType) {
			return new ErrorType();
		}

		return $this->resolveCommonMath(new Expr\BinaryOp\ShiftLeft($left, $right), $leftType, $rightType);
	}

	/**
	 * @param callable(Expr): Type $getTypeCallback
	 */
	public function getShiftRightType(Expr $left, Expr $right, callable $getTypeCallback): Type
	{
		$leftType = $getTypeCallback($left);
		$rightType = $getTypeCallback($right);

		if ($leftType instanceof NeverType || $rightType instanceof NeverType) {
			return $this->getNeverType($leftType, $rightType);
		}

		$leftTypes = $leftType->getConstantScalarTypes();
		$rightTypes = $rightType->getConstantScalarTypes();
		$leftTypesCount = count($leftTypes);
		$rightTypesCount = count($rightTypes);
		if ($leftTypesCount > 0 && $rightTypesCount > 0) {
			$resultTypes = [];
			$generalize = $leftTypesCount * $rightTypesCount > self::CALCULATE_SCALARS_LIMIT;
			if (!$generalize) {
				foreach ($leftTypes as $leftTypeInner) {
					foreach ($rightTypes as $rightTypeInner) {
						$leftNumberType = $leftTypeInner->toNumber();
						$rightNumberType = $rightTypeInner->toNumber();

						if ($leftNumberType instanceof ErrorType || $rightNumberType instanceof ErrorType) {
							return new ErrorType();
						}

						if (!$leftNumberType instanceof ConstantScalarType || !$rightNumberType instanceof ConstantScalarType) {
							throw new ShouldNotHappenException();
						}

						if ($rightNumberType->getValue() < 0) {
							return new ErrorType();
						}

						$resultType = $this->getTypeFromValue(intval($leftNumberType->getValue()) >> intval($rightNumberType->getValue()));
						$resultTypes[] = $resultType;
					}
				}

				return TypeCombinator::union(...$resultTypes);
			}

			$leftType = $this->optimizeScalarType($leftType);
			$rightType = $this->optimizeScalarType($rightType);
		}

		$leftNumberType = $leftType->toNumber();
		$rightNumberType = $rightType->toNumber();

		if ($leftNumberType instanceof ErrorType || $rightNumberType instanceof ErrorType) {
			return new ErrorType();
		}

		return $this->resolveCommonMath(new Expr\BinaryOp\ShiftRight($left, $right), $leftType, $rightType);
	}

	private function optimizeScalarType(Type $type): Type
	{
		$types = [];
		if ($type->isInteger()->yes()) {
			$types[] = new IntegerType();
		}
		if ($type->isString()->yes()) {
			$types[] = new StringType();
		}
		if ($type->isFloat()->yes()) {
			$types[] = new FloatType();
		}
		if ($type->isNull()->yes()) {
			$types[] = new NullType();
		}

		if (count($types) === 0) {
			return new ErrorType();
		}

		if (count($types) === 1) {
			return $types[0];
		}

		return new UnionType($types);
	}

	/**
	 * @return TypeResult<BooleanType>
	 */
	public function resolveIdenticalType(Type $leftType, Type $rightType): TypeResult
	{
		if ($leftType instanceof NeverType || $rightType instanceof NeverType) {
			return new TypeResult(new ConstantBooleanType(false), []);
		}

		if ($leftType instanceof ConstantScalarType && $rightType instanceof ConstantScalarType) {
			return new TypeResult(new ConstantBooleanType($leftType->getValue() === $rightType->getValue()), []);
		}

		$leftTypeFiniteTypes = $leftType->getFiniteTypes();
		$rightTypeFiniteType = $rightType->getFiniteTypes();
		if (count($leftTypeFiniteTypes) === 1 && count($rightTypeFiniteType) === 1) {
			return new TypeResult(new ConstantBooleanType($leftTypeFiniteTypes[0]->equals($rightTypeFiniteType[0])), []);
		}

		$leftIsSuperTypeOfRight = $leftType->isSuperTypeOf($rightType);
		$rightIsSuperTypeOfLeft = $rightType->isSuperTypeOf($leftType);
		if ($leftIsSuperTypeOfRight->no() && $rightIsSuperTypeOfLeft->no()) {
			return new TypeResult(new ConstantBooleanType(false), array_merge($leftIsSuperTypeOfRight->reasons, $rightIsSuperTypeOfLeft->reasons));
		}

		if ($leftType instanceof ConstantArrayType && $rightType instanceof ConstantArrayType) {
			return $this->resolveConstantArrayTypeComparison($leftType, $rightType, fn ($leftValueType, $rightValueType): TypeResult => $this->resolveIdenticalType($leftValueType, $rightValueType));
		}

		return new TypeResult(new BooleanType(), []);
	}

	/**
	 * @return TypeResult<BooleanType>
	 */
	public function resolveEqualType(Type $leftType, Type $rightType): TypeResult
	{
		if (
			($leftType->isEnum()->yes() && $rightType->isTrue()->no())
			|| ($rightType->isEnum()->yes() && $leftType->isTrue()->no())
		) {
			return $this->resolveIdenticalType($leftType, $rightType);
		}

		if ($leftType instanceof ConstantArrayType && $rightType instanceof ConstantArrayType) {
			return $this->resolveConstantArrayTypeComparison($leftType, $rightType, fn ($leftValueType, $rightValueType): TypeResult => $this->resolveEqualType($leftValueType, $rightValueType));
		}

		return new TypeResult($leftType->looseCompare($rightType, $this->phpVersion), []);
	}

	/**
	 * @param callable(Type, Type): TypeResult<BooleanType> $valueComparisonCallback
	 * @return TypeResult<BooleanType>
	 */
	private function resolveConstantArrayTypeComparison(ConstantArrayType $leftType, ConstantArrayType $rightType, callable $valueComparisonCallback): TypeResult
	{
		$leftKeyTypes = $leftType->getKeyTypes();
		$rightKeyTypes = $rightType->getKeyTypes();
		$leftValueTypes = $leftType->getValueTypes();
		$rightValueTypes = $rightType->getValueTypes();

		$resultType = new ConstantBooleanType(true);

		foreach ($leftKeyTypes as $i => $leftKeyType) {
			$leftOptional = $leftType->isOptionalKey($i);
			if ($leftOptional) {
				$resultType = new BooleanType();
			}

			if (count($rightKeyTypes) === 0) {
				if (!$leftOptional) {
					return new TypeResult(new ConstantBooleanType(false), []);
				}
				continue;
			}

			$found = false;
			foreach ($rightKeyTypes as $j => $rightKeyType) {
				unset($rightKeyTypes[$j]);

				if ($leftKeyType->equals($rightKeyType)) {
					$found = true;
					break;
				} elseif (!$rightType->isOptionalKey($j)) {
					return new TypeResult(new ConstantBooleanType(false), []);
				}
			}

			if (!$found) {
				if (!$leftOptional) {
					return new TypeResult(new ConstantBooleanType(false), []);
				}
				continue;
			}

			if (!isset($j)) {
				throw new ShouldNotHappenException();
			}

			$rightOptional = $rightType->isOptionalKey($j);
			if ($rightOptional) {
				$resultType = new BooleanType();
				if ($leftOptional) {
					continue;
				}
			}

			$leftIdenticalToRightResult = $valueComparisonCallback($leftValueTypes[$i], $rightValueTypes[$j]);
			$leftIdenticalToRight = $leftIdenticalToRightResult->type;
			if ($leftIdenticalToRight->isFalse()->yes()) {
				return $leftIdenticalToRightResult;
			}
			$resultType = TypeCombinator::union($resultType, $leftIdenticalToRight);
		}

		foreach (array_keys($rightKeyTypes) as $j) {
			if (!$rightType->isOptionalKey($j)) {
				return new TypeResult(new ConstantBooleanType(false), []);
			}
			$resultType = new BooleanType();
		}

		return new TypeResult($resultType->toBoolean(), []);
	}

	private function callOperatorTypeSpecifyingExtensions(Expr\BinaryOp $expr, Type $leftType, Type $rightType): ?Type
	{
		$operatorSigil = $expr->getOperatorSigil();
		$operatorTypeSpecifyingExtensions = $this->operatorTypeSpecifyingExtensionRegistryProvider->getRegistry()->getOperatorTypeSpecifyingExtensions($operatorSigil, $leftType, $rightType);

		/** @var Type[] $extensionTypes */
		$extensionTypes = [];

		foreach ($operatorTypeSpecifyingExtensions as $extension) {
			$extensionTypes[] = $extension->specifyType($operatorSigil, $leftType, $rightType);
		}

		if (count($extensionTypes) > 0) {
			return TypeCombinator::union(...$extensionTypes);
		}

		return null;
	}

	/**
	 * @param BinaryOp\Plus|BinaryOp\Minus|BinaryOp\Mul|BinaryOp\Div|BinaryOp\ShiftLeft|BinaryOp\ShiftRight $expr
	 */
	private function resolveCommonMath(Expr\BinaryOp $expr, Type $leftType, Type $rightType): Type
	{
		$types = TypeCombinator::union($leftType, $rightType);
		$leftNumberType = $leftType->toNumber();
		$rightNumberType = $rightType->toNumber();

		if (
			!$types instanceof MixedType
			&& (
				$rightNumberType instanceof IntegerRangeType
				|| $rightNumberType instanceof ConstantIntegerType
				|| $rightNumberType instanceof UnionType
			)
		) {
			if ($leftNumberType instanceof IntegerRangeType || $leftNumberType instanceof ConstantIntegerType) {
				return $this->integerRangeMath(
					$leftNumberType,
					$expr,
					$rightNumberType,
				);
			} elseif ($leftNumberType instanceof UnionType) {
				$unionParts = [];

				foreach ($leftNumberType->getTypes() as $type) {
					$numberType = $type->toNumber();
					if ($numberType instanceof IntegerRangeType || $numberType instanceof ConstantIntegerType) {
						$unionParts[] = $this->integerRangeMath($numberType, $expr, $rightNumberType);
					} else {
						$unionParts[] = $numberType;
					}
				}

				$union = TypeCombinator::union(...$unionParts);
				if ($leftNumberType instanceof BenevolentUnionType) {
					return TypeUtils::toBenevolentUnion($union)->toNumber();
				}

				return $union->toNumber();
			}
		}

		$specifiedTypes = $this->callOperatorTypeSpecifyingExtensions($expr, $leftType, $rightType);
		if ($specifiedTypes !== null) {
			return $specifiedTypes;
		}

		if (
			$leftType->isArray()->yes()
			|| $rightType->isArray()->yes()
			|| $types->isArray()->yes()
		) {
			return new ErrorType();
		}

		if ($leftNumberType instanceof ErrorType || $rightNumberType instanceof ErrorType) {
			return new ErrorType();
		}
		if ($leftNumberType instanceof NeverType || $rightNumberType instanceof NeverType) {
			return $this->getNeverType($leftNumberType, $rightNumberType);
		}

		if (
			$leftNumberType->isFloat()->yes()
			|| $rightNumberType->isFloat()->yes()
		) {
			if ($expr instanceof Expr\BinaryOp\ShiftLeft || $expr instanceof Expr\BinaryOp\ShiftRight) {
				return new IntegerType();
			}
			return new FloatType();
		}

		$resultType = TypeCombinator::union($leftNumberType, $rightNumberType);
		if ($expr instanceof Expr\BinaryOp\Div) {
			if ($types instanceof MixedType || $resultType->isInteger()->yes()) {
				return new BenevolentUnionType([new IntegerType(), new FloatType()]);
			}

			return new UnionType([new IntegerType(), new FloatType()]);
		}

		if ($types instanceof MixedType
			|| $leftType instanceof BenevolentUnionType
			|| $rightType instanceof BenevolentUnionType
		) {
			return TypeUtils::toBenevolentUnion($resultType);
		}

		return $resultType;
	}

	/**
	 * @param ConstantIntegerType|IntegerRangeType $range
	 * @param BinaryOp\Div|BinaryOp\Minus|BinaryOp\Mul|BinaryOp\Plus|BinaryOp\ShiftLeft|BinaryOp\ShiftRight $node
	 */
	private function integerRangeMath(Type $range, BinaryOp $node, Type $operand): Type
	{
		if ($range instanceof IntegerRangeType) {
			$rangeMin = $range->getMin();
			$rangeMax = $range->getMax();
		} else {
			$rangeMin = $range->getValue();
			$rangeMax = $rangeMin;
		}

		if ($operand instanceof UnionType) {

			$unionParts = [];

			foreach ($operand->getTypes() as $type) {
				$numberType = $type->toNumber();
				if ($numberType instanceof IntegerRangeType || $numberType instanceof ConstantIntegerType) {
					$unionParts[] = $this->integerRangeMath($range, $node, $numberType);
				} else {
					$unionParts[] = $type->toNumber();
				}
			}

			$union = TypeCombinator::union(...$unionParts);
			if ($operand instanceof BenevolentUnionType) {
				return TypeUtils::toBenevolentUnion($union)->toNumber();
			}

			return $union->toNumber();
		}

		$operand = $operand->toNumber();
		if ($operand instanceof IntegerRangeType) {
			$operandMin = $operand->getMin();
			$operandMax = $operand->getMax();
		} elseif ($operand instanceof ConstantIntegerType) {
			$operandMin = $operand->getValue();
			$operandMax = $operand->getValue();
		} else {
			return $operand;
		}

		if ($node instanceof BinaryOp\Plus) {
			if ($operand instanceof ConstantIntegerType) {
				/** @var int|float|null $min */
				$min = $rangeMin !== null ? $rangeMin + $operand->getValue() : null;

				/** @var int|float|null $max */
				$max = $rangeMax !== null ? $rangeMax + $operand->getValue() : null;
			} else {
				/** @var int|float|null $min */
				$min = $rangeMin !== null && $operand->getMin() !== null ? $rangeMin + $operand->getMin() : null;

				/** @var int|float|null $max */
				$max = $rangeMax !== null && $operand->getMax() !== null ? $rangeMax + $operand->getMax() : null;
			}
		} elseif ($node instanceof BinaryOp\Minus) {
			if ($operand instanceof ConstantIntegerType) {
				/** @var int|float|null $min */
				$min = $rangeMin !== null ? $rangeMin - $operand->getValue() : null;

				/** @var int|float|null $max */
				$max = $rangeMax !== null ? $rangeMax - $operand->getValue() : null;
			} else {
				if ($rangeMin === $rangeMax && $rangeMin !== null
					&& ($operand->getMin() === null || $operand->getMax() === null)) {
					$min = null;
					$max = $rangeMin;
				} else {
					if ($operand->getMin() === null) {
						$min = null;
					} elseif ($rangeMin !== null) {
						if ($operand->getMax() !== null) {
							/** @var int|float $min */
							$min = $rangeMin - $operand->getMax();
						} else {
							/** @var int|float $min */
							$min = $rangeMin - $operand->getMin();
						}
					} else {
						$min = null;
					}

					if ($operand->getMax() === null) {
						$min = null;
						$max = null;
					} elseif ($rangeMax !== null) {
						if ($rangeMin !== null && $operand->getMin() === null) {
							/** @var int|float $min */
							$min = $rangeMin - $operand->getMax();
							$max = null;
						} elseif ($operand->getMin() !== null) {
							/** @var int|float $max */
							$max = $rangeMax - $operand->getMin();
						} else {
							$max = null;
						}
					} else {
						$max = null;
					}

					if ($min !== null && $max !== null && $min > $max) {
						[$min, $max] = [$max, $min];
					}
				}
			}
		} elseif ($node instanceof Expr\BinaryOp\Mul) {
			$min1 = $rangeMin === 0 || $operandMin === 0 ? 0 : ($rangeMin ?? -INF) * ($operandMin ?? -INF);
			$min2 = $rangeMin === 0 || $operandMax === 0 ? 0 : ($rangeMin ?? -INF) * ($operandMax ?? INF);
			$max1 = $rangeMax === 0 || $operandMin === 0 ? 0 : ($rangeMax ?? INF) * ($operandMin ?? -INF);
			$max2 = $rangeMax === 0 || $operandMax === 0 ? 0 : ($rangeMax ?? INF) * ($operandMax ?? INF);

			$min = min($min1, $min2, $max1, $max2);
			$max = max($min1, $min2, $max1, $max2);

			if (!is_finite($min)) {
				$min = null;
			}
			if (!is_finite($max)) {
				$max = null;
			}
		} elseif ($node instanceof Expr\BinaryOp\Div) {
			if ($operand instanceof ConstantIntegerType) {
				$min = $rangeMin !== null && $operand->getValue() !== 0 ? $rangeMin / $operand->getValue() : null;
				$max = $rangeMax !== null && $operand->getValue() !== 0 ? $rangeMax / $operand->getValue() : null;
			} else {
				// Avoid division by zero when looking for the min and the max by using the closest int
				$operandMin = $operandMin !== 0 ? $operandMin : 1;
				$operandMax = $operandMax !== 0 ? $operandMax : -1;

				if (
					($operandMin < 0 || $operandMin === null)
					&& ($operandMax > 0 || $operandMax === null)
				) {
					$negativeOperand = IntegerRangeType::fromInterval($operandMin, 0);
					assert($negativeOperand instanceof IntegerRangeType);
					$positiveOperand = IntegerRangeType::fromInterval(0, $operandMax);
					assert($positiveOperand instanceof IntegerRangeType);

					$result = TypeCombinator::union(
						$this->integerRangeMath($range, $node, $negativeOperand),
						$this->integerRangeMath($range, $node, $positiveOperand),
					)->toNumber();

					if ($result->equals(new UnionType([new IntegerType(), new FloatType()]))) {
						return new BenevolentUnionType([new IntegerType(), new FloatType()]);
					}

					return $result;
				}
				if (
					($rangeMin < 0 || $rangeMin === null)
					&& ($rangeMax > 0 || $rangeMax === null)
				) {
					$negativeRange = IntegerRangeType::fromInterval($rangeMin, 0);
					assert($negativeRange instanceof IntegerRangeType);
					$positiveRange = IntegerRangeType::fromInterval(0, $rangeMax);
					assert($positiveRange instanceof IntegerRangeType);

					$result = TypeCombinator::union(
						$this->integerRangeMath($negativeRange, $node, $operand),
						$this->integerRangeMath($positiveRange, $node, $operand),
					)->toNumber();

					if ($result->equals(new UnionType([new IntegerType(), new FloatType()]))) {
						return new BenevolentUnionType([new IntegerType(), new FloatType()]);
					}

					return $result;
				}

				$rangeMinSign = ($rangeMin ?? -INF) <=> 0;
				$rangeMaxSign = ($rangeMax ?? INF) <=> 0;

				$min1 = $operandMin !== null ? ($rangeMin ?? -INF) / $operandMin : $rangeMinSign * -0.1;
				$min2 = $operandMax !== null ? ($rangeMin ?? -INF) / $operandMax : $rangeMinSign * 0.1;
				$max1 = $operandMin !== null ? ($rangeMax ?? INF) / $operandMin : $rangeMaxSign * -0.1;
				$max2 = $operandMax !== null ? ($rangeMax ?? INF) / $operandMax : $rangeMaxSign * 0.1;

				$min = min($min1, $min2, $max1, $max2);
				$max = max($min1, $min2, $max1, $max2);

				if ($min === -INF) {
					$min = null;
				}
				if ($max === INF) {
					$max = null;
				}
			}

			if ($min !== null && $max !== null && $min > $max) {
				[$min, $max] = [$max, $min];
			}

			if (is_float($min)) {
				$min = (int) ceil($min);
			}
			if (is_float($max)) {
				$max = (int) floor($max);
			}

			// invert maximas on division with negative constants
			if ((($range instanceof ConstantIntegerType && $range->getValue() < 0)
					|| ($operand instanceof ConstantIntegerType && $operand->getValue() < 0))
				&& ($min === null || $max === null)) {
				[$min, $max] = [$max, $min];
			}

			if ($min === null && $max === null) {
				return new BenevolentUnionType([new IntegerType(), new FloatType()]);
			}

			return TypeCombinator::union(IntegerRangeType::fromInterval($min, $max), new FloatType());
		} elseif ($node instanceof Expr\BinaryOp\ShiftLeft) {
			if (!$operand instanceof ConstantIntegerType) {
				return new IntegerType();
			}
			if ($operand->getValue() < 0) {
				return new ErrorType();
			}
			$min = $rangeMin !== null ? intval($rangeMin) << $operand->getValue() : null;
			$max = $rangeMax !== null ? intval($rangeMax) << $operand->getValue() : null;
		} elseif ($node instanceof Expr\BinaryOp\ShiftRight) {
			if (!$operand instanceof ConstantIntegerType) {
				return new IntegerType();
			}
			if ($operand->getValue() < 0) {
				return new ErrorType();
			}
			$min = $rangeMin !== null ? intval($rangeMin) >> $operand->getValue() : null;
			$max = $rangeMax !== null ? intval($rangeMax) >> $operand->getValue() : null;
		} else {
			throw new ShouldNotHappenException();
		}

		if (is_float($min)) {
			$min = null;
		}
		if (is_float($max)) {
			$max = null;
		}

		return IntegerRangeType::fromInterval($min, $max);
	}

	/**
	 * @param callable(Expr): Type $getTypeCallback
	 */
	public function getClassConstFetchTypeByReflection(Name|Expr $class, string $constantName, ?ClassReflection $classReflection, callable $getTypeCallback): Type
	{
		$isObject = false;
		if ($class instanceof Name) {
			$constantClass = (string) $class;
			$constantClassType = new ObjectType($constantClass);
			$namesToResolve = [
				'self',
				'parent',
			];
			if ($classReflection !== null) {
				if ($classReflection->isFinal()) {
					$namesToResolve[] = 'static';
				} elseif (strtolower($constantClass) === 'static') {
					if (strtolower($constantName) === 'class') {
						return new GenericClassStringType(new StaticType($classReflection));
					}

					$namesToResolve[] = 'static';
					$isObject = true;
				}
			}
			if (in_array(strtolower($constantClass), $namesToResolve, true)) {
				$resolvedName = $this->resolveName($class, $classReflection);
				if (strtolower($resolvedName) === 'parent' && strtolower($constantName) === 'class') {
					return new ClassStringType();
				}
				$constantClassType = $this->resolveTypeByName($class, $classReflection);
			}

			if (strtolower($constantName) === 'class') {
				return new ConstantStringType($constantClassType->getClassName(), true);
			}
		} elseif ($class instanceof String_ && strtolower($constantName) === 'class') {
			return new ConstantStringType($class->value, true);
		} else {
			$constantClassType = $getTypeCallback($class);
			$isObject = true;
		}

		if (strtolower($constantName) === 'class') {
			return TypeTraverser::map(
				$constantClassType,
				function (Type $type, callable $traverse): Type {
					if ($type instanceof UnionType || $type instanceof IntersectionType) {
						return $traverse($type);
					}

					if ($type instanceof NullType) {
						return $type;
					}

					if ($type instanceof EnumCaseObjectType) {
						return TypeCombinator::intersect(
							new GenericClassStringType(new ObjectType($type->getClassName())),
							new AccessoryLiteralStringType(),
						);
					}

					$objectClassNames = $type->getObjectClassNames();
					if (count($objectClassNames) > 1) {
						throw new ShouldNotHappenException();
					}

					if ($type instanceof TemplateType && $objectClassNames === []) {
						return TypeCombinator::intersect(
							new GenericClassStringType($type),
							new AccessoryLiteralStringType(),
						);
					} elseif ($objectClassNames !== [] && $this->getReflectionProvider()->hasClass($objectClassNames[0])) {
						$reflection = $this->getReflectionProvider()->getClass($objectClassNames[0]);
						if ($reflection->isFinalByKeyword()) {
							return new ConstantStringType($reflection->getName(), true);
						}

						return TypeCombinator::intersect(
							new GenericClassStringType($type),
							new AccessoryLiteralStringType(),
						);
					} elseif ($type->isObject()->yes()) {
						return TypeCombinator::intersect(
							new ClassStringType(),
							new AccessoryLiteralStringType(),
						);
					}

					return new ErrorType();
				},
			);
		}

		if ($constantClassType->isClassString()->yes()) {
			if ($constantClassType->isConstantScalarValue()->yes()) {
				$isObject = false;
			}
			$constantClassType = $constantClassType->getClassStringObjectType();
		}

		$types = [];
		foreach ($constantClassType->getObjectClassNames() as $referencedClass) {
			if (!$this->getReflectionProvider()->hasClass($referencedClass)) {
				continue;
			}

			$constantClassReflection = $this->getReflectionProvider()->getClass($referencedClass);
			if (!$constantClassReflection->hasConstant($constantName)) {
				continue;
			}

			if ($constantClassReflection->isEnum() && $constantClassReflection->hasEnumCase($constantName)) {
				$types[] = new EnumCaseObjectType($constantClassReflection->getName(), $constantName);
				continue;
			}

			$resolvingName = sprintf('%s::%s', $constantClassReflection->getName(), $constantName);
			if (array_key_exists($resolvingName, $this->currentlyResolvingClassConstant)) {
				$types[] = new MixedType();
				continue;
			}

			$this->currentlyResolvingClassConstant[$resolvingName] = true;

			if (!$isObject) {
				$reflectionConstant = $constantClassReflection->getNativeReflection()->getReflectionConstant($constantName);
				if ($reflectionConstant === false) {
					unset($this->currentlyResolvingClassConstant[$resolvingName]);
					continue;
				}
				$reflectionConstantDeclaringClass = $reflectionConstant->getDeclaringClass();
				$constantType = $this->getType($reflectionConstant->getValueExpression(), InitializerExprContext::fromClass($reflectionConstantDeclaringClass->getName(), $reflectionConstantDeclaringClass->getFileName() ?: null));
				$nativeType = null;
				if ($reflectionConstant->getType() !== null) {
					$nativeType = TypehintHelper::decideTypeFromReflection($reflectionConstant->getType(), selfClass: $constantClassReflection);
				}
				$types[] = $this->constantResolver->resolveClassConstantType(
					$constantClassReflection->getName(),
					$constantName,
					$constantType,
					$nativeType,
				);
				unset($this->currentlyResolvingClassConstant[$resolvingName]);
				continue;
			}

			$constantReflection = $constantClassReflection->getConstant($constantName);
			if (
				!$constantClassReflection->isFinal()
				&& !$constantReflection->isFinal()
				&& !$constantReflection->hasPhpDocType()
				&& !$constantReflection->hasNativeType()
			) {
				unset($this->currentlyResolvingClassConstant[$resolvingName]);
				return new MixedType();
			}

			if (!$constantClassReflection->isFinal()) {
				$constantType = $constantReflection->getValueType();
			} else {
				$constantType = $this->getType($constantReflection->getValueExpr(), InitializerExprContext::fromClassReflection($constantReflection->getDeclaringClass()));
			}

			$nativeType = $constantReflection->getNativeType();
			$constantType = $this->constantResolver->resolveClassConstantType(
				$constantClassReflection->getName(),
				$constantName,
				$constantType,
				$nativeType,
			);
			unset($this->currentlyResolvingClassConstant[$resolvingName]);
			$types[] = $constantType;
		}

		if (count($types) > 0) {
			return TypeCombinator::union(...$types);
		}

		if (!$constantClassType->hasConstant($constantName)->yes()) {
			return new ErrorType();
		}

		return $constantClassType->getConstant($constantName)->getValueType();
	}

	/**
	 * @param callable(Expr): Type $getTypeCallback
	 */
	public function getClassConstFetchType(Name|Expr $class, string $constantName, ?string $className, callable $getTypeCallback): Type
	{
		$classReflection = null;
		if ($className !== null && $this->getReflectionProvider()->hasClass($className)) {
			$classReflection = $this->getReflectionProvider()->getClass($className);
		}

		return $this->getClassConstFetchTypeByReflection($class, $constantName, $classReflection, $getTypeCallback);
	}

	/**
	 * @param callable(Expr): Type $getTypeCallback
	 */
	public function getUnaryMinusType(Expr $expr, callable $getTypeCallback): Type
	{
		$type = $getTypeCallback($expr)->toNumber();
		$scalarValues = $type->getConstantScalarValues();

		if (count($scalarValues) > 0) {
			$newTypes = [];
			foreach ($scalarValues as $scalarValue) {
				if (is_int($scalarValue)) {
					/** @var int|float $newValue */
					$newValue = -$scalarValue;
					if (!is_int($newValue)) {
						return $type;
					}
					$newTypes[] = new ConstantIntegerType($newValue);
				} elseif (is_float($scalarValue)) {
					$newTypes[] = new ConstantFloatType(-$scalarValue);
				}
			}

			return TypeCombinator::union(...$newTypes);
		}

		if ($type instanceof IntegerRangeType) {
			return $getTypeCallback(new Expr\BinaryOp\Mul($expr, new Int_(-1)));
		}

		return $type;
	}

	/**
	 * @param callable(Expr): Type $getTypeCallback
	 */
	public function getBitwiseNotType(Expr $expr, callable $getTypeCallback): Type
	{
		$exprType = $getTypeCallback($expr);
		return TypeTraverser::map($exprType, static function (Type $type, callable $traverse): Type {
			if ($type instanceof UnionType || $type instanceof IntersectionType) {
				return $traverse($type);
			}
			if ($type instanceof ConstantStringType) {
				return new ConstantStringType(~$type->getValue());
			}
			if ($type->isString()->yes()) {
				$accessories = [
					new StringType(),
				];
				if ($type->isNonEmptyString()->yes()) {
					$accessories[] = new AccessoryNonEmptyStringType();
				}
				// it is not useful to apply numeric and literal strings here.
				// numeric string isn't certainly kept numeric: 3v4l.org/JERDB

				return TypeCombinator::intersect(...$accessories);
			}
			if ($type->isInteger()->yes() || $type->isFloat()->yes()) {
				return new IntegerType(); //no const types here, result depends on PHP_INT_SIZE
			}
			return new ErrorType();
		});
	}

	private function resolveName(Name $name, ?ClassReflection $classReflection): string
	{
		$originalClass = (string) $name;
		if ($classReflection !== null) {
			$lowerClass = strtolower($originalClass);

			if (in_array($lowerClass, [
				'self',
				'static',
			], true)) {
				return $classReflection->getName();
			} elseif ($lowerClass === 'parent') {
				if ($classReflection->getParentClass() !== null) {
					return $classReflection->getParentClass()->getName();
				}
			}
		}

		return $originalClass;
	}

	private function resolveTypeByName(Name $name, ?ClassReflection $classReflection): TypeWithClassName
	{
		if ($name->toLowerString() === 'static' && $classReflection !== null) {
			return new StaticType($classReflection);
		}

		$originalClass = $this->resolveName($name, $classReflection);
		if ($classReflection !== null) {
			$thisType = new ThisType($classReflection);
			$ancestor = $thisType->getAncestorWithClassName($originalClass);
			if ($ancestor !== null) {
				return $ancestor;
			}
		}

		return new ObjectType($originalClass);
	}

	/**
	 * @param mixed $value
	 */
	private function getTypeFromValue($value): Type
	{
		return ConstantTypeHelper::getTypeFromValue($value);
	}

	private function getReflectionProvider(): ReflectionProvider
	{
		return $this->reflectionProviderProvider->getReflectionProvider();
	}

	private function getNeverType(Type $leftType, Type $rightType): Type
	{
		// make sure we don't lose the explicit flag in the process
		if ($leftType instanceof NeverType && $leftType->isExplicit()) {
			return $leftType;
		}
		if ($rightType instanceof NeverType && $rightType->isExplicit()) {
			return $rightType;
		}
		return new NeverType();
	}

}
