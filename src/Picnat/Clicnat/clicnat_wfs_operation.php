<?php
namespace Picnat\Clicnat;

abstract class clicnat_wfs_operation {
	const version = '1.0.0';
	const wfs_service_type = 'WFS';

	protected $db;
	protected $args;


	public function __construct($db, $args) {
		$this->db = $db;
		$this->args = $args;
	}

	protected function newdomdoc() {
		$doc = new DOMDocument();
		$doc->formatOutput = true;
		return $doc;
	}

	protected function get_type_name() {
		if (is_array($this->args)) {
			if (array_key_exists("TYPENAME", $this->args)) {
				$t = explode(":",  $this->args['TYPENAME']);
				return $t[count($t)-1];
				//return $this->args['TYPENAME'];
			}
		} else {
			$r = $this->args->getElementsByTagName("Query");
			foreach ($r as $e) {
				if ($e->hasAttribute('typeName')) {
					$tn = $e->getAttribute('typeName');
					if (preg_match("/^feature:(.*)$/",$tn,$m)) {
						return $m[1];
					}
					return $tn;
				}
			}
		}
		return false;
	}
}
