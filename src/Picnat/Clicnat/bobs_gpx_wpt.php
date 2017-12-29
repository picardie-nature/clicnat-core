<?php
namespace Picnat\Clicnat;

class bobs_gpx_wpt {
	public $latitude;
	public $longitude;
	public $nom;
	public $commtr;

	public function __construct($dom_element) {
		$this->latitude = $dom_element->getAttribute('lat');
		if (empty($this->latitude))
			throw new \Exception('latitude vide');
		$this->longitude = $dom_element->getAttribute('lon');
		if (empty($this->longitude))
			throw new \Exception('longitude vide');
		foreach ($dom_element->getElementsByTagName('cmt') as $cmt) {
			$this->commtr = $cmt->nodeValue;
		}
		foreach ($dom_element->getElementsByTagName('name') as $nom) {
			$this->nom = $nom->nodeValue;
		}
	}
}
