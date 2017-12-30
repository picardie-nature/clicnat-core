<?php
namespace Picnat\Clicnat;

abstract class clicnat_enquete_champ {
	protected $doc;
	protected $nom;
	protected $lib;

	public function __get($c) {
		switch($c) {
			case 'doc': return $this->doc;
			case 'nom': return $this->nom;
			case 'lib': return $this->lib;
			throw new Exception('pas trouvé');
		}
	}
	public function __construct($domdocument) {
		$this->doc = $domdocument;
		$this->nom = $this->doc->documentElement->getAttribute('nom');
		$this->lib = $this->doc->documentElement->getAttribute('lib');
	}

	public function formulaire($valeur='') {
		echo "pas implémenté";
	}

	public function afficher($valeur) {
	}

	public function lib() {
		return $this->lib;
	}

	public function doc_champ_sauve($element, $data) {
		$element->setAttribute('nom', $this->nom);
		$element->setAttribute('type', 'simple');
		$element->setAttribute('valeur', $data[$this->nom]);
		return true;
	}
}
