<?php
namespace Picnat\Clicnat;

class clicnat_rss_reader_item {
	public $titre;
	public $description;
	public $lien;
	public $date;

	public function __construct($titre, $description, $lien, $date) {
		$this->titre = $titre;
		$this->description= $description;
		$this->lien = $lien;
		$this->date = $date;
	}

	public function __toString() {
		return $this->titre;
	}
}
