<?php
namespace Picnat\Clicnat;

class entrepot {
	public function db() {
		static $mc;
		if (!isset($mc)) {
			$mc = new \MongoClient(MONGO_DB_STR);
		}
		return $mc->selectDB(MONGO_BASE);
	}
}
