<?php declare(strict_types = 1);

namespace PHPStan\Rules;

class Registry
{

	/**
	 * @var \PHPStan\Rules\Rule[][]
	 */
	private $rules;

	/**
	 * @param \PHPStan\Rules\Rule[] $rules
	 */
	public function __construct(array $rules)
	{
		foreach ($rules as $rule) {
			$this->register($rule);
		}
	}

	private function register(Rule $rule)
	{
		if (!isset($this->rules[$rule->getNodeType()])) {
			$this->rules[$rule->getNodeType()] = [];
		}

		$this->rules[$rule->getNodeType()][] = $rule;
	}

	/**
	 * @param string[] $nodeTypes
	 * @return \PHPStan\Rules\Rule[]
	 */
	public function getRules(array $nodeTypes): array
	{
		$rules = [];
		foreach ($nodeTypes as $nodeType) {
			if (!isset($this->rules[$nodeType])) {
				continue;
			}

			$classRules = $this->rules[$nodeType];
			foreach ($classRules as $classRule) {
				$classRuleClass = get_class($classRule);
				if (!array_key_exists($classRuleClass, $rules)) {
					$rules[$classRuleClass] = $classRule;
				}
			}
		}

		return array_values($rules);
	}

}
