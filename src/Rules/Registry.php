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
	 * @param string $nodeType
	 * @return \PHPStan\Rules\Rule[]
	 */
	public function getRules(string $nodeType): array
	{
		if (!isset($this->rules[$nodeType])) {
			return [];
		}

		return $this->rules[$nodeType];
	}

}
