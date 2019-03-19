<?php

namespace GeneralizeScopeRecursiveType;

class Foo
{

	public function doFoo(array $array, array $values)
	{
		$data = [];
		foreach ($array as $val) {
			foreach ($values as $val2) {
				$data['foo'] = array_merge($data, $this->doBar());
			}
		}

		die;
	}

	/**
	 * @return string[][]|int[][]
	 */
	private function doBar(): array
	{
		return [];
	}

}
