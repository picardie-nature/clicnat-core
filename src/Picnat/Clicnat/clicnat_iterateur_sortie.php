<?php
namespace Picnat\Clicnat;

class clicnat_iterateur_sortie implements Iterator {
	private $db;
	private $position;
	private $ids = array();

	public function __construct($db, $ids) {
		$this->db = $db;
		$this->position = 0;
		$this->ids = $ids;
	}

	public function rewind() {
		$this->position = 0;
	}

	public function current() {
		return new clicnat_sortie($this->db, $this->ids[$this->position]);
	}

	public function key() {
		return $this->position;
	}

	public function next() {
		$this->position++;
	}

	public function valid() {
		return isset($this->ids[$this->position]);
	}
}
