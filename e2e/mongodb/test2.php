<?php declare(strict_types = 1);

use MongoDB\Driver\Manager;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\WriteResult;

class MongoDBE2ETest
{
	public function test(): WriteResult
	{
		// connect
		$manager = new Manager("mongodb://localhost:27017");
		return $manager->executeBulkWrite("collection", new BulkWrite([
			'bypassDocumentValidation' => true,
		]));
	}
}
