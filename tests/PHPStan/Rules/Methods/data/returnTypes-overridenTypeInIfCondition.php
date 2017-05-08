<?php

namespace ReturnTypes;

class OverridenTypeInIfCondition
{

	public function getAnotherAnotherStock(): Stock
	{
		$stock = new Stock();
		if ($stock->findStock() === null) {

		}

		return $stock->findStock();
	}

}
