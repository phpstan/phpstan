<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PHPStan\Php\PhpVersion;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhp;
use function array_merge;
use const PHP_VERSION_ID;

/**
 * @extends RuleTestCase<AccessPropertiesRule>
 */
class AccessPropertiesRuleTest extends RuleTestCase
{

	private bool $checkThisOnly;

	private bool $checkUnionTypes;

	private bool $checkDynamicProperties;

	protected function getRule(): Rule
	{
		$reflectionProvider = self::createReflectionProvider();
		return new AccessPropertiesRule(new AccessPropertiesCheck($reflectionProvider, new RuleLevelHelper($reflectionProvider, true, $this->checkThisOnly, $this->checkUnionTypes, false, false, false, true), new PhpVersion(PHP_VERSION_ID), true, $this->checkDynamicProperties));
	}

	public function testAccessProperties(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = false;

		$tipText = 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>';
		$this->analyse(
			[__DIR__ . '/data/access-properties.php'],
			[
				[
					'Access to an undefined property TestAccessProperties\BarAccessProperties::$loremipsum.',
					24,
					$tipText,
				],
				[
					'Access to private property $foo of parent class TestAccessProperties\FooAccessProperties.',
					25,
				],
				[
					'Cannot access property $propertyOnString on string.',
					32,
				],
				[
					'Access to private property TestAccessProperties\FooAccessProperties::$foo.',
					43,
				],
				[
					'Access to protected property TestAccessProperties\FooAccessProperties::$bar.',
					44,
				],
				[
					'Access to an undefined property TestAccessProperties\FooAccessProperties::$baz.',
					50,
					$tipText,
				],
				[
					'Access to an undefined property TestAccessProperties\FooAccessProperties::$nonexistent.',
					53,
					$tipText,
				],
				[
					'Access to private property TestAccessProperties\FooAccessProperties::$foo.',
					59,
				],
				[
					'Access to protected property TestAccessProperties\FooAccessProperties::$bar.',
					60,
				],
				[
					'Access to property $foo on an unknown class TestAccessProperties\UnknownClass.',
					64,
					'Learn more at https://phpstan.org/user-guide/discovering-symbols',
				],
				[
					'Access to an undefined property TestAccessProperties\FooAccessProperties::$emptyBaz.',
					69,
					$tipText,
				],
				[
					'Access to an undefined property TestAccessProperties\FooAccessProperties::$emptyNonexistent.',
					71,
					$tipText,
				],
				[
					'Access to an undefined property TestAccessProperties\FooAccessProperties::$anotherNonexistent.',
					77,
					$tipText,
				],
				[
					'Access to an undefined property TestAccessProperties\FooAccessProperties::$anotherNonexistent.',
					78,
					$tipText,
				],
				[
					'Access to an undefined property TestAccessProperties\FooAccessProperties::$anotherEmptyNonexistent.',
					81,
					$tipText,
				],
				[
					'Access to an undefined property TestAccessProperties\FooAccessProperties::$anotherEmptyNonexistent.',
					84,
					$tipText,
				],
				[
					'Access to property $test on an unknown class TestAccessProperties\FirstUnknownClass.',
					147,
					'Learn more at https://phpstan.org/user-guide/discovering-symbols',
				],
				[
					'Access to property $test on an unknown class TestAccessProperties\SecondUnknownClass.',
					147,
					'Learn more at https://phpstan.org/user-guide/discovering-symbols',
				],
				[
					'Access to an undefined property TestAccessProperties\WithFooAndBarProperty|TestAccessProperties\WithFooProperty::$bar.',
					177,
					$tipText,
				],
				[
					'Access to an undefined property TestAccessProperties\SomeInterface&TestAccessProperties\WithFooProperty::$bar.',
					194,
					$tipText,
				],
				[
					'Cannot access property $ipsum on TestAccessProperties\FooAccessProperties|null.',
					208,
				],
				[
					'Cannot access property $foo on null.',
					221,
				],
				[
					'Access to an undefined property TestAccessProperties\FooAccessProperties::$lorem.',
					248,
					$tipText,
				],
				[
					'Access to an undefined property TestAccessProperties\FooAccessProperties::$dolor.',
					251,
					$tipText,
				],
				[
					'Cannot access property $bar on TestAccessProperties\NullCoalesce|null.',
					274,
				],
				[
					'Cannot access property $foo on TestAccessProperties\NullCoalesce|null.',
					274,
				],
				[
					'Cannot access property $foo on TestAccessProperties\NullCoalesce|null.',
					274,
				],
				[
					'Access to an undefined property class@anonymous/tests/PHPStan/Rules/Properties/data/access-properties.php:297::$barProperty.',
					302,
					$tipText,
				],
				[
					'Cannot access property $selfOrNull on TestAccessProperties\RevertNonNullabilityForIsset|null.',
					407,
				],
				[
					'Access to an undefined property object::$baz.',
					438,
					$tipText,
				],
			],
		);
	}

	public function testAccessPropertiesWithoutUnionTypes(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = false;
		$this->checkDynamicProperties = false;

		$tipText = 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>';
		$this->analyse(
			[__DIR__ . '/data/access-properties.php'],
			[
				[
					'Access to an undefined property TestAccessProperties\BarAccessProperties::$loremipsum.',
					24,
					$tipText,
				],
				[
					'Access to private property $foo of parent class TestAccessProperties\FooAccessProperties.',
					25,
				],
				[
					'Cannot access property $propertyOnString on string.',
					32,
				],
				[
					'Access to private property TestAccessProperties\FooAccessProperties::$foo.',
					43,
				],
				[
					'Access to protected property TestAccessProperties\FooAccessProperties::$bar.',
					44,
				],
				[
					'Access to an undefined property TestAccessProperties\FooAccessProperties::$baz.',
					50,
					$tipText,
				],
				[
					'Access to an undefined property TestAccessProperties\FooAccessProperties::$nonexistent.',
					53,
					$tipText,
				],
				[
					'Access to private property TestAccessProperties\FooAccessProperties::$foo.',
					59,
				],
				[
					'Access to protected property TestAccessProperties\FooAccessProperties::$bar.',
					60,
				],
				[
					'Access to property $foo on an unknown class TestAccessProperties\UnknownClass.',
					64,
					'Learn more at https://phpstan.org/user-guide/discovering-symbols',
				],
				[
					'Access to an undefined property TestAccessProperties\FooAccessProperties::$emptyBaz.',
					69,
					$tipText,
				],
				[
					'Access to an undefined property TestAccessProperties\FooAccessProperties::$emptyNonexistent.',
					71,
					$tipText,
				],
				[
					'Access to an undefined property TestAccessProperties\FooAccessProperties::$anotherNonexistent.',
					77,
					$tipText,
				],
				[
					'Access to an undefined property TestAccessProperties\FooAccessProperties::$anotherNonexistent.',
					78,
					$tipText,
				],
				[
					'Access to an undefined property TestAccessProperties\FooAccessProperties::$anotherEmptyNonexistent.',
					81,
					$tipText,
				],
				[
					'Access to an undefined property TestAccessProperties\FooAccessProperties::$anotherEmptyNonexistent.',
					84,
					$tipText,
				],
				[
					'Access to property $test on an unknown class TestAccessProperties\FirstUnknownClass.',
					147,
					'Learn more at https://phpstan.org/user-guide/discovering-symbols',
				],
				[
					'Access to property $test on an unknown class TestAccessProperties\SecondUnknownClass.',
					147,
					'Learn more at https://phpstan.org/user-guide/discovering-symbols',
				],
				[
					'Access to an undefined property TestAccessProperties\SomeInterface&TestAccessProperties\WithFooProperty::$bar.',
					194,
					$tipText,
				],
				[
					'Cannot access property $foo on null.',
					221,
				],
				[
					'Access to an undefined property TestAccessProperties\FooAccessProperties::$lorem.',
					248,
					$tipText,
				],
				[
					'Access to an undefined property TestAccessProperties\FooAccessProperties::$dolor.',
					251,
					$tipText,
				],
				[
					'Cannot access property $bar on TestAccessProperties\NullCoalesce|null.',
					274,
				],
				[
					'Access to an undefined property class@anonymous/tests/PHPStan/Rules/Properties/data/access-properties.php:297::$barProperty.',
					302,
					$tipText,
				],
			],
		);
	}

	public function testRuleAssignOp(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = false;

		$tipText = 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>';
		$this->analyse([__DIR__ . '/data/access-properties-assign-op.php'], [
			[
				'Access to an undefined property TestAccessProperties\AssignOpNonexistentProperty::$flags.',
				10,
				$tipText,
			],
		]);
	}

	public function testAccessPropertiesOnThisOnly(): void
	{
		$this->checkThisOnly = true;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = false;

		$tipText = 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>';
		$this->analyse(
			[__DIR__ . '/data/access-properties.php'],
			[
				[
					'Access to an undefined property TestAccessProperties\BarAccessProperties::$loremipsum.',
					24,
					$tipText,
				],
				[
					'Access to private property $foo of parent class TestAccessProperties\FooAccessProperties.',
					25,
				],
			],
		);
	}

	public function testBug12692(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = false;
		$this->checkDynamicProperties = false;
		$this->analyse([__DIR__ . '/data/bug-12692.php'], [[
			'Non-static access to static property Bug12692\Foo::$static.',
			14,
		]]);
	}

	public function testAccessPropertiesAfterIsNullInBooleanOr(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = false;

		$tipText = 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>';
		$this->analyse([__DIR__ . '/data/access-properties-after-isnull.php'], [
			[
				'Cannot access property $fooProperty on null.',
				16,
			],
			[
				'Cannot access property $fooProperty on null.',
				25,
			],
			[
				'Access to an undefined property AccessPropertiesAfterIsNull\Foo::$barProperty.',
				28,
				$tipText,
			],
			[
				'Access to an undefined property AccessPropertiesAfterIsNull\Foo::$barProperty.',
				31,
				$tipText,
			],
			[
				'Cannot access property $fooProperty on null.',
				35,
			],
			[
				'Cannot access property $fooProperty on null.',
				44,
			],
			[
				'Access to an undefined property AccessPropertiesAfterIsNull\Foo::$barProperty.',
				47,
				$tipText,
			],
			[
				'Access to an undefined property AccessPropertiesAfterIsNull\Foo::$barProperty.',
				50,
				$tipText,
			],
		]);
	}

	public function testDateIntervalChildProperties(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = false;

		$tipText = 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>';
		$this->analyse([__DIR__ . '/data/date-interval-child-properties.php'], [
			[
				'Access to an undefined property AccessPropertiesDateIntervalChild\DateIntervalChild::$nonexistent.',
				14,
				$tipText,
			],
		]);
	}

	public function testClassExists(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = false;

		$this->analyse([__DIR__ . '/data/access-properties-class-exists.php'], [
			[
				'Access to property $lorem on an unknown class AccessPropertiesClassExists\Bar.',
				15,
				'Learn more at https://phpstan.org/user-guide/discovering-symbols',
			],
			[
				'Access to property $lorem on an unknown class AccessPropertiesClassExists\Baz.',
				15,
				'Learn more at https://phpstan.org/user-guide/discovering-symbols',
			],
			[
				'Access to property $lorem on an unknown class AccessPropertiesClassExists\Baz.',
				18,
				'Learn more at https://phpstan.org/user-guide/discovering-symbols',
			],
			[
				'Access to property $lorem on an unknown class AccessPropertiesClassExists\Bar.',
				22,
				'Learn more at https://phpstan.org/user-guide/discovering-symbols',
			],
		]);
	}

	public function testMixin(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = false;

		$tipText = 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>';
		$this->analyse([__DIR__ . '/data/mixin.php'], [
			[
				'Access to an undefined property MixinProperties\GenericFoo<ReflectionClass>::$namee.',
				55,
				$tipText,
			],
		]);
	}

	public function testBug3947(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = false;
		$this->analyse([__DIR__ . '/data/bug-3947.php'], []);
	}

	public function testNullSafe(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = false;

		$tipText = 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>';
		$this->analyse([__DIR__ . '/data/nullsafe-property-fetch.php'], [
			[
				'Access to an undefined property NullsafePropertyFetch\Foo::$baz.',
				13,
				$tipText,
			],
			[
				'Cannot access property $bar on string.',
				18,
			],
			[
				'Cannot access property $bar on string.',
				19,
			],
			[
				'Cannot access property $bar on string.',
				21,
			],
			[
				'Cannot access property $bar on string.',
				22,
			],
			[
				'Cannot access property $foo on null.',
				28,
			],
			[
				'Cannot access property $foo on null.',
				29,
			],
		]);
	}

	public function testBug3371(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = false;
		$this->analyse([__DIR__ . '/data/bug-3371.php'], []);
	}

	public function testBug4527(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = false;
		$this->analyse([__DIR__ . '/data/bug-4527.php'], []);
	}

	public function testBug4808(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = false;
		$this->analyse([__DIR__ . '/data/bug-4808.php'], []);
	}

	#[RequiresPhp('>= 8.0')]
	public function testBug5868(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = false;
		$this->analyse([__DIR__ . '/data/bug-5868.php'], [
			[
				'Cannot access property $child on Bug5868PropertyFetch\Foo|null.',
				31,
			],
			[
				'Cannot access property $child on Bug5868PropertyFetch\Child|null.',
				32,
			],
			[
				'Cannot access property $existingChild on Bug5868PropertyFetch\Child|null.',
				33,
			],
			[
				'Cannot access property $existingChild on Bug5868PropertyFetch\Child|null.',
				34,
			],
		]);
	}

	#[RequiresPhp('>= 8.1')]
	public function testBug6385(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = false;

		$tipText = 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>';
		$this->analyse([__DIR__ . '/data/bug-6385.php'], [
			[
				'Access to an undefined property UnitEnum::$value.',
				43,
				$tipText,
			],
			[
				'Access to an undefined property Bug6385\ActualUnitEnum::$value.',
				47,
				$tipText,
			],
		]);
	}

	#[RequiresPhp('>= 8.0')]
	public function testBug6566(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = false;
		$this->analyse([__DIR__ . '/data/bug-6566.php'], []);
	}

	public function testBug6899(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = false;
		$errors = [
			[
				'Cannot access property $prop on string.',
				13,
			],
			[
				'Cannot access property $prop on string.',
				14,
			],
			[
				'Cannot access property $prop on string.',
				15,
			],
		];
		$this->analyse([__DIR__ . '/data/bug-6899.php'], $errors);
	}

	public function testBug6026(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = false;
		$this->analyse([__DIR__ . '/data/bug-6026.php'], []);
	}

	public function testBug3659(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = false;
		$errors = [];
		$this->analyse([__DIR__ . '/data/bug-3659.php'], $errors);
	}

	public static function dataDynamicProperties(): array
	{
		$tipText = 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>';
		$errors = [
			[
				'Access to an undefined property DynamicProperties\Bar::$dynamicProperty.',
				14,
				$tipText,
			],
			[
				'Access to an undefined property DynamicProperties\Bar::$dynamicProperty.',
				15,
				$tipText,
			],
			[
				'Access to an undefined property DynamicProperties\Bar::$dynamicProperty.',
				16,
				$tipText,
			],
		];

		$errorsWithMore = array_merge([
			[
				'Access to an undefined property DynamicProperties\Foo::$dynamicProperty.',
				9,
				$tipText,
			],
			[
				'Access to an undefined property DynamicProperties\Foo::$dynamicProperty.',
				10,
				$tipText,
			],
			[
				'Access to an undefined property DynamicProperties\Foo::$dynamicProperty.',
				11,
				$tipText,
			],
		], $errors);

		$errors[] = [
			'Access to an undefined property DynamicProperties\Baz::$dynamicProperty.',
			29,
			$tipText,
		];

		$errorsWithMore = array_merge($errorsWithMore, [
			[
				'Access to an undefined property DynamicProperties\Bar::$dynamicProperty.',
				20,
				$tipText,
			],
			[
				'Access to an undefined property DynamicProperties\Bar::$dynamicProperty.',
				21,
				$tipText,
			],
			[
				'Access to an undefined property DynamicProperties\Bar::$dynamicProperty.',
				22,
				$tipText,
			],
			[
				'Access to an undefined property DynamicProperties\Baz::$dynamicProperty.',
				29,
				$tipText,
			],
		]);

		$errorsWithMore = array_merge($errorsWithMore, [
			[
				'Access to an undefined property DynamicProperties\Baz::$dynamicProperty.',
				32,
				$tipText,
			],
			[
				'Access to an undefined property DynamicProperties\Baz::$dynamicProperty.',
				33,
				$tipText,
			],
			[
				'Access to an undefined property DynamicProperties\Baz::$dynamicProperty.',
				34,
				$tipText,
			],
		]);

		$otherErrors = [
			[
				'Access to an undefined property DynamicProperties\FinalFoo::$dynamicProperty.',
				42,
				$tipText,
			],
			[
				'Access to an undefined property DynamicProperties\FinalFoo::$dynamicProperty.',
				43,
				$tipText,
			],
			[
				'Access to an undefined property DynamicProperties\FinalFoo::$dynamicProperty.',
				44,
				$tipText,
			],
			[
				'Access to an undefined property DynamicProperties\FinalBar::$dynamicProperty.',
				47,
				$tipText,
			],
			[
				'Access to an undefined property DynamicProperties\FinalBar::$dynamicProperty.',
				48,
				$tipText,
			],
			[
				'Access to an undefined property DynamicProperties\FinalBar::$dynamicProperty.',
				49,
				$tipText,
			],
		];

		return [
			[false, PHP_VERSION_ID < 80200 ? [
				[
					'Access to an undefined property DynamicProperties\Baz::$dynamicProperty.',
					29,
					$tipText,
				],
			] : array_merge($errors, $otherErrors)],
			[true, array_merge($errorsWithMore, $otherErrors)],
		];
	}

	/**
	 * @param list<array{0: string, 1: int, 2?: string}> $errors
	 */
	#[DataProvider('dataDynamicProperties')]
	public function testDynamicProperties(bool $checkDynamicProperties, array $errors): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = $checkDynamicProperties;
		$this->analyse([__DIR__ . '/data/dynamic-properties.php'], $errors);
	}

	public function testBug4559(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = false;
		$errors = [];
		$this->analyse([__DIR__ . '/data/bug-4559.php'], $errors);
	}

	public function testBug3171(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = false;
		$this->analyse([__DIR__ . '/data/bug-3171.php'], []);
	}

	public function testBug3171OnDynamicProperties(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = true;
		$this->analyse([__DIR__ . '/data/bug-3171.php'], []);
	}

	public static function dataTrueAndFalse(): array
	{
		return [
			[true],
			[false],
		];
	}

	#[DataProvider('dataTrueAndFalse')]
	public function testPhp82AndDynamicProperties(bool $b): void
	{
		$errors = [];
		$tipText = 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>';
		if (PHP_VERSION_ID >= 80200) {
			$errors[] = [
				'Access to an undefined property Php82DynamicProperties\ClassA::$properties.',
				34,
				$tipText,
			];
			$errors[] = [
				'Access to an undefined property Php82DynamicProperties\HelloWorld::$world.',
				71,
				$tipText,
			];
			if ($b) {
				$errors[] = [
					'Access to an undefined property Php82DynamicProperties\HelloWorld::$world.',
					78,
					$tipText,
				];
			}
			$errors[] = [
				'Access to an undefined property Php82DynamicProperties\FinalHelloWorld::$world.',
				112,
				$tipText,
			];
		} elseif ($b) {
			$errors[] = [
				'Access to an undefined property Php82DynamicProperties\HelloWorld::$world.',
				71,
				$tipText,
			];
			$errors[] = [
				'Access to an undefined property Php82DynamicProperties\HelloWorld::$world.',
				78,
				$tipText,
			];
			$errors[] = [
				'Access to an undefined property Php82DynamicProperties\FinalHelloWorld::$world.',
				112,
				$tipText,
			];
		}
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = $b;
		$this->analyse([__DIR__ . '/data/php-82-dynamic-properties.php'], $errors);
	}

	#[DataProvider('dataTrueAndFalse')]
	public function testPhp82AndDynamicPropertiesAllow(bool $b): void
	{
		$errors = [];
		$tipText = 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>';
		if ($b) {
			$errors[] = [
				'Access to an undefined property Php82DynamicPropertiesAllow\HelloWorld::$world.',
				75,
				$tipText,
			];
		}
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = $b;
		$this->analyse([__DIR__ . '/data/php-82-dynamic-properties-allow.php'], $errors);
	}

	public function testBug2435(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = false;
		$this->analyse([__DIR__ . '/data/bug-2435.php'], []);
	}

	public function testBug7640(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = false;
		$this->analyse([__DIR__ . '/data/bug-7640.php'], []);
	}

	public function testBug3572(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = false;
		$this->analyse([__DIR__ . '/data/bug-3572.php'], []);
	}

	public function testBug393(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = false;
		$this->analyse([__DIR__ . '/data/bug-393.php'], []);
	}

	public function testObjectShapes(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = true;

		$tipText = 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>';
		$this->analyse([__DIR__ . '/data/properties-object-shapes.php'], [
			[
				'Access to an undefined property object{foo: int, bar?: string}::$bar.',
				15,
				$tipText,
			],
			[
				'Access to an undefined property object{foo: int, bar?: string}::$baz.',
				16,
				$tipText,
			],
		]);
	}

	public function testConflictingAnnotationProperty(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = true;
		$this->analyse([__DIR__ . '/data/conflicting-annotation-property.php'], []);
	}

	#[RequiresPhp('>= 8.1')]
	public function testBug8536(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = true;
		$this->analyse([__DIR__ . '/../Comparison/data/bug-8536.php'], []);
	}

	public function testRequireExtends(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = true;

		$tipText = 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>';
		$this->analyse([__DIR__ . '/data/require-extends.php'], [
			[
				'Access to an undefined property RequireExtends\MyInterface::$bar.',
				36,
				$tipText,
			],
		]);
	}

	public function testBug8629(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = true;
		$this->analyse([__DIR__ . '/data/bug-8629.php'], []);
	}

	#[RequiresPhp('>= 8.0')]
	public function testBug9694(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = true;
		$this->analyse([__DIR__ . '/data/bug-9694.php'], []);
	}

	public function testTraitMixin(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = true;
		$this->analyse([__DIR__ . '/data/trait-mixin.php'], []);
	}

	#[RequiresPhp('>= 8.4')]
	public function testAsymmetricVisibility(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = true;
		$this->analyse([__DIR__ . '/data/read-asymmetric-visibility.php'], []);
	}

	#[RequiresPhp('>= 8.2')]
	public function testNewIsAlwaysFinalClass(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = false;
		$this->analyse([__DIR__ . '/data/null-coalesce-new-is-always-final.php'], [
			[
				'Access to an undefined property NullCoalesceIsAlwaysFinal\Foo::$bar.',
				12,
				'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
			],
		]);
	}

	public function testPropertyExists(): void
	{
		$this->checkThisOnly = false;
		$this->checkUnionTypes = true;
		$this->checkDynamicProperties = true;
		$this->analyse([__DIR__ . '/data/property-exists.php'], [
			[
				'Access to an undefined property PropertyExists\Model::$getCreatedByColumn.',
				27,
				'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
			],
			[
				'Access to an undefined property PropertyExists\Model::$getUpdatedByColumn.',
				27,
				'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
			],
			[
				'Access to an undefined property PropertyExists\Model::$getDeletedByColumn.',
				27,
				'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
			],
			[
				'Access to an undefined property PropertyExists\Model::$getCreatedAtColumn.',
				27,
				'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
			],
			[
				'Access to an undefined property PropertyExists\Model::$getUpdatedAtColumn.',
				27,
				'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
			],
			[
				'Access to an undefined property PropertyExists\Model::$getDeletedAtColumn.',
				27,
				'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
			],
		]);
	}

}
