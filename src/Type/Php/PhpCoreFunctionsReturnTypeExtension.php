<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypehintHelper;

class PhpCoreFunctionsReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	/** @var string[] */
	private $coreFunctions = [
		'zend_version' => 'string',
		'func_num_args' => 'int',
		'func_get_arg' => 'mixed|false',
		'func_get_args' => 'array',
		'strlen' => 'int',
		'strcmp' => 'int',
		'strncmp' => 'int',
		'strcasecmp' => 'int',
		'strncasecmp' => 'int',
		'each' => 'array|false',
		'error_reporting' => 'int',
		'define' => 'bool',
		'defined' => 'bool',
		'get_class' => 'string|false',
		'get_called_class' => 'string|false',
		'get_parent_class' => 'string|false',
		'method_exists' => 'bool',
		'property_exists' => 'bool',
		'trait_exists' => 'bool',
		'class_exists' => 'bool',
		'interface_exists' => 'bool',
		'function_exists' => 'bool',
		'class_alias' => 'bool',
		'get_included_files' => 'string[]',
		'get_required_files' => 'string[]',
		'is_subclass_of' => 'bool',
		'is_a' => 'bool',
		'get_class_vars' => 'array',
		'get_object_vars' => 'array',
		'get_class_methods' => 'string[]',
		'trigger_error' => 'bool',
		'user_error' => 'bool',
		'set_error_handler' => 'mixed',
		'set_exception_handler' => 'callback',
		'restore_exception_handler' => 'true',
		'get_declared_classes' => 'string[]',
		'get_declared_interfaces' => 'string[]',
		'get_declared_traits' => 'string[]',
		'get_defined_functions' => 'string[][]',
		'get_defined_vars' => 'array',
		'create_function' => 'string|false',
		'get_resource_type' => 'string|false',
		'get_loaded_extensions' => 'string[]',
		'extension_loaded' => 'bool',
		'get_extension_funcs' => 'string[]',
		'get_defined_constants' => 'array',
		'debug_backtrace' => 'array',
		'error_clear_last' => 'void',
		'debug_print_backtrace' => 'void',
		'gc_collect_cycles' => 'int',
		'gc_enabled' => 'bool',
		'gc_enable' => 'void',
		'gc_disable' => 'void',
		'sapi_windows_cp_get' => 'int',
		'sapi_windows_cp_set' => 'bool',
		'sapi_windows_cp_conv' => 'string',
		'sapi_windows_cp_is_utf8' => 'bool',
		'is_iterable' => 'bool',
	];

	/** @var int[] */
	private $arrayFunctionsThatCreateArrayBasedOnClosureReturnType = [
		'array_map' => 0,
	];

	/** @var int[] */
	private $arrayFunctionsThatDependOnClosureReturnType = [
		'array_reduce' => 1,
	];

	/** @var int[] */
	private $arrayFunctionsThatDependOnArgumentType = [
		'array_filter' => 0,
		'array_unique' => 0,
		'array_reverse' => 0,
	];

	/** @var int[] */
	private $arrayFunctionsThatCreateArrayBasedOnArgumentType = [
		'array_fill' => 2,
		'array_fill_keys' => 1,
	];

	/** @var string[] */
	private $functionsThatCombineAllArgumentTypes = [
		'min' => '',
		'max' => '',
	];

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		$functionName = strtolower($functionReflection->getName());

		return isset($this->coreFunctions[$functionName])
			|| isset($this->arrayFunctionsThatCreateArrayBasedOnClosureReturnType[$functionName])
			|| isset($this->arrayFunctionsThatDependOnClosureReturnType[$functionName])
			|| isset($this->arrayFunctionsThatDependOnArgumentType[$functionName])
			|| isset($this->arrayFunctionsThatCreateArrayBasedOnArgumentType[$functionName])
			|| isset($this->functionsThatCombineAllArgumentTypes[$functionName]);
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		$functionName = strtolower($functionReflection->getName());

		if (isset($this->coreFunctions[$functionName])) {
			/** @var \PHPStan\Type\Type[] $types */
			$types = [];

			foreach (explode('|', $this->coreFunctions[$functionName]) as $typePart) {
				$typePart = trim($typePart);
				if ($typePart === '') {
					continue;
				}
				if (substr($typePart, 0, 1) === '?') {
					$typePart = substr($typePart, 1);
					$types[] = new NullType();
				}
				$types[] = TypehintHelper::getTypeObjectFromTypehint($typePart);
			}

			return TypeCombinator::combine(...$types);
		}

		if (isset($this->arrayFunctionsThatCreateArrayBasedOnClosureReturnType[$functionName])) {
			if (!isset($functionCall->args[$this->arrayFunctionsThatCreateArrayBasedOnClosureReturnType[$functionName]])) {
				return $functionReflection->getReturnType();
			}

			$argumentValue = $functionCall->args[$this->arrayFunctionsThatCreateArrayBasedOnClosureReturnType[$functionName]]->value;
			if (!$argumentValue instanceof Closure) {
				return $functionReflection->getReturnType();
			}

			$anonymousFunctionType = $scope->getFunctionType($argumentValue->returnType, $argumentValue->returnType === null, false);

			return new ArrayType($anonymousFunctionType, true);
		}

		if (isset($this->arrayFunctionsThatDependOnClosureReturnType[$functionName])) {
			if (!isset($functionCall->args[$this->arrayFunctionsThatDependOnClosureReturnType[$functionName]])) {
				return $functionReflection->getReturnType();
			}

			$argumentValue = $functionCall->args[$this->arrayFunctionsThatDependOnClosureReturnType[$functionName]]->value;
			if (!$argumentValue instanceof Closure) {
				return $functionReflection->getReturnType();
			}

			return $scope->getFunctionType($argumentValue->returnType, $argumentValue->returnType === null, false);
		}

		if (isset($this->arrayFunctionsThatDependOnArgumentType[$functionName])) {
			if (!isset($functionCall->args[$this->arrayFunctionsThatDependOnArgumentType[$functionName]])) {
				return $functionReflection->getReturnType();
			}

			$argumentValue = $functionCall->args[$this->arrayFunctionsThatDependOnArgumentType[$functionName]]->value;
			return $scope->getType($argumentValue);
		}

		if (isset($this->arrayFunctionsThatCreateArrayBasedOnArgumentType[$functionName])) {
			if (!isset($functionCall->args[$this->arrayFunctionsThatCreateArrayBasedOnArgumentType[$functionName]])) {
				return $functionReflection->getReturnType();
			}

			$argumentValue = $functionCall->args[$this->arrayFunctionsThatCreateArrayBasedOnArgumentType[$functionName]]->value;
			return new ArrayType($scope->getType($argumentValue), true, true);
		}

		if (isset($this->functionsThatCombineAllArgumentTypes[$functionName])) {
			if (!isset($functionCall->args[0])) {
				return $functionReflection->getReturnType();
			}

			if ($functionCall->args[0]->unpack) {
				$argumentType = $scope->getType($functionCall->args[0]->value);
				if ($argumentType instanceof ArrayType) {
					return $argumentType->getItemType();
				}
			}

			if (count($functionCall->args) === 1) {
				$argumentType = $scope->getType($functionCall->args[0]->value);
				if ($argumentType instanceof ArrayType) {
					return $argumentType->getItemType();
				}
			}

			$argumentType = null;
			foreach ($functionCall->args as $arg) {
				$argType = $scope->getType($arg->value);
				if ($argumentType === null) {
					$argumentType = $argType;
				} else {
					$argumentType = $argumentType->combineWith($argType);
				}
			}

			/** @var \PHPStan\Type\Type $argumentType */
			$argumentType = $argumentType;

			return $argumentType;
		}

		throw new \PHPStan\ShouldNotHappenException();
	}

}
