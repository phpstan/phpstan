<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\FunctionCallParametersCheck;
use PHPStan\Rules\RuleLevelHelper;

class InstantiationRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		$broker = $this->createBroker();
		return new InstantiationRule(
			$broker,
			new FunctionCallParametersCheck(new RuleLevelHelper($broker, true, false, true), true, true, true),
			new ClassCaseSensitivityCheck($broker)
		);
	}

	public function testInstantiation(): void
	{
		if (PHP_VERSION_ID >= 70400) {
			$this->markTestSkipped('Test does not run on PHP 7.4 because of referencing parent:: without parent class.');
		}
		$this->analyse(
			[__DIR__ . '/data/instantiation.php'],
			[
				[
					'Class TestInstantiation\InstantiatingClass constructor invoked with 0 parameters, 1 required.',
					15,
				],
				[
					'TestInstantiation\InstantiatingClass::doFoo() calls new parent but TestInstantiation\InstantiatingClass does not extend any class.',
					18,
				],
				[
					'Class TestInstantiation\FooInstantiation does not have a constructor and must be instantiated without any parameters.',
					26,
				],
				[
					'Instantiated class TestInstantiation\FooBarInstantiation not found.',
					27,
				],
				[
					'Class TestInstantiation\BarInstantiation constructor invoked with 0 parameters, 1 required.',
					28,
				],
				[
					'Instantiated class TestInstantiation\LoremInstantiation is abstract.',
					29,
				],
				[
					'Cannot instantiate interface TestInstantiation\IpsumInstantiation.',
					30,
				],
				[
					'Instantiated class Test not found.',
					33,
				],
				[
					'Class DatePeriod constructor invoked with 0 parameters, 1-4 required.',
					36,
				],
				[
					'Using self outside of class scope.',
					39,
				],
				[
					'Using static outside of class scope.',
					40,
				],
				[
					'Using parent outside of class scope.',
					41,
				],
				[
					'Class TestInstantiation\BarInstantiation constructor invoked with 0 parameters, 1 required.',
					44,
				],
				[
					'Class TestInstantiation\InstantiatingClass constructor invoked with 0 parameters, 1 required.',
					57,
				],
				[
					'Class TestInstantiation\FooInstantiation referenced with incorrect case: TestInstantiation\FOOInstantiation.',
					64,
				],
				[
					'Class TestInstantiation\FooInstantiation does not have a constructor and must be instantiated without any parameters.',
					64,
				],
				[
					'Class TestInstantiation\BarInstantiation referenced with incorrect case: TestInstantiation\BARInstantiation.',
					65,
				],
				[
					'Class TestInstantiation\BarInstantiation constructor invoked with 0 parameters, 1 required.',
					65,
				],
				[
					'Class TestInstantiation\BarInstantiation referenced with incorrect case: TestInstantiation\BARInstantiation.',
					66,
				],
				[
					'Class TestInstantiation\ClassExtendsProtectedConstructorClass constructor invoked with 0 parameters, 1 required.',
					94,
				],
				[
					'Cannot instantiate class TestInstantiation\ExtendsPrivateConstructorClass via private constructor TestInstantiation\PrivateConstructorClass::__construct().',
					104,
				],
				[
					'Class TestInstantiation\ExtendsPrivateConstructorClass constructor invoked with 0 parameters, 1 required.',
					104,
				],
				[
					'Cannot instantiate class TestInstantiation\PrivateConstructorClass via private constructor TestInstantiation\PrivateConstructorClass::__construct().',
					110,
				],
				[
					'Cannot instantiate class TestInstantiation\ProtectedConstructorClass via protected constructor TestInstantiation\ProtectedConstructorClass::__construct().',
					111,
				],
				[
					'Cannot instantiate class TestInstantiation\ClassExtendsProtectedConstructorClass via protected constructor TestInstantiation\ProtectedConstructorClass::__construct().',
					112,
				],
				[
					'Cannot instantiate class TestInstantiation\ExtendsPrivateConstructorClass via private constructor TestInstantiation\PrivateConstructorClass::__construct().',
					113,
				],
				[
					'Parameter #1 $message of class Exception constructor expects string, int given.',
					117,
				],
				[
					'Parameter #2 $code of class Exception constructor expects int, string given.',
					117,
				],
				[
					'Class TestInstantiation\NoConstructor referenced with incorrect case: TestInstantiation\NOCONSTRUCTOR.',
					127,
				],
				[
					DIRECTORY_SEPARATOR === '/' ? 'Class class@anonymous/tests/PHPStan/Rules/Classes/data/instantiation.php:137 constructor invoked with 3 parameters, 1 required.' : 'Class class@anonymous/tests\PHPStan\Rules\Classes\data\instantiation.php:137 constructor invoked with 3 parameters, 1 required.',
					137,
				],
				[
					'Instantiated class UndefinedClass1 not found.',
					169,
				],
				[
					'Instantiated class UndefinedClass2 not found.',
					172,
				],
				[
					'Instantiated class UndefinedClass3 not found.',
					179,
				],
				[
					'Class TestInstantiation\FinalClass does not have a constructor and must be instantiated without any parameters.',
					190,
				],
				[
					'Class TestInstantiation\ClassWithFinalConstructor constructor invoked with 0 parameters, 1 required.',
					206,
				],
				[
					'Class TestInstantiation\ConstructorComingFromAnInterface constructor invoked with 0 parameters, 1 required.',
					229,
				],
				[
					'Class TestInstantiation\AbstractClassWithFinalConstructor constructor invoked with 1 parameter, 0 required.',
					245,
				],
				[
					'Class TestInstantiation\AbstractConstructor constructor invoked with 0 parameters, 1 required.',
					257,
				],
				[
					'Class TestInstantiation\ClassExtendingAbstractConstructor constructor invoked with 0 parameters, 1 required.',
					273,
				],
			]
		);
	}

	public function testSoap(): void
	{
		$this->analyse(
			[__DIR__ . '/data/instantiation-soap.php'],
			[
				[
					'Parameter #2 $faultstring of class SoapFault constructor expects string, int given.',
					6,
				],
			]
		);
	}

}
