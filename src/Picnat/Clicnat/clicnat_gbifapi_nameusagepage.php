<?php
namespace Picnat\Clicnat;

class clicnat_gbifapi_nameusagepage {
	private $data;

	public function __construct($json) {
		$this->data = json_decode($json,true);
	}

	public function __get($c) {
		if (isset($this->data[$c])){
			return $this->data[$c];
		}
		throw \InvalidArgumentException();
	}

	public function preview() {
		echo "offset: {$this->data['offset']}\tlimit: {$this->data['limit']}\tcount: {$this->data['count']}\n";
		foreach ($this->data['results'] as $r) {
			echo "{$r['nubKey']}\t{$r['rank']}\t{$r['scientificName']}\n";
		}
	}

	public function nubKeys() {
		$ret = [];
		foreach ($this->data['results'] as $r) {
			if (isset($r['nubKey'])) {
				$ret[$r['nubKey']] = 1;
			}
		}
		return array_keys($ret);
	}
}
