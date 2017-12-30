<?php
namespace Picnat\Clicnat;

abstract class clicnat_travail {
	protected $args;
	protected $db;

	public function __construct($db, $args) {
		$this->args = json_decode($args, true);
		$this->db = $db;
	}
}
