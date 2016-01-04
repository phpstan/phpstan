<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Node;
use PHPStan\Broker\Broker;
use PHPStan\Rules\FunctionCallParametersCheck;

class CallOwnMethodsRule implements \PHPStan\Rules\Rule
{

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	/**
	 * @var \PHPStan\Rules\FunctionCallParametersCheck
	 */
	private $check;

	/**
	 * @param \PHPStan\Broker\Broker $broker
	 * @param \PHPStan\Rules\FunctionCallParametersCheck $check
	 */
	public function __construct(Broker $broker, FunctionCallParametersCheck $check)
	{
		$this->broker = $broker;
		$this->check = $check;
	}

	public function getNodeType(): string
	{
		return MethodCall::class;
	}

	/**
	 * @param \PHPStan\Analyser\Node $node
	 * @return string[]
	 */
	public function processNode(Node $node): array
	{
		$methodCall = $node->getParserNode();
		if ($node->getScope()->isInClosureBind()) {
			return [];
		}
		if ($node->getScope()->getClass() === null) {
			return [];
		}
		if (!($methodCall->var instanceof Variable)) {
			return [];
		}
		if ((string) $methodCall->var->name !== 'this') {
			return [];
		}
		if (!is_string($methodCall->name)) {
			return [];
		}
		$name = (string) $methodCall->name;
		$class = $node->getScope()->getClass();
		if ($class === null) {
			return []; // using $this as a normal variable
		}
		$classReflection = $this->broker->getClass($class);

		if (!$classReflection->hasMethod($name)) {
			return [
				sprintf(
					'Call to an undefined method %s::%s().',
					$class,
					$name
				),
			];
		}

		$method = $classReflection->getMethod($name);
		if ($method->getDeclaringClass()->getName() !== $class) {
			if ($method->isPrivate()) {
				return [
					sprintf(
						'Call to private method %s() of parent class %s.',
						$name,
						$method->getDeclaringClass()->getName()
					),
				];
			}
		}

		$methodName = $method->getDeclaringClass()->getName() . '::' . $name . '()';

		return $this->check->check(
			$method,
			$methodCall,
			[
				'Method ' . $methodName . ' invoked with %d parameter, %d required.',
				'Method ' . $methodName . ' invoked with %d parameters, %d required.',
				'Method ' . $methodName . ' invoked with %d parameter, at least %d required.',
				'Method ' . $methodName . ' invoked with %d parameters, at least %d required.',
				'Method ' . $methodName . ' invoked with %d parameter, %d-%d required.',
				'Method ' . $methodName . ' invoked with %d parameters, %d-%d required.',
			]
		);
	}

}
