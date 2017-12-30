<?php
namespace Picnat\Clicnat;

/**
 * @brief Une sortie du document XML
 */
class clicnat_doc_ele_sortie {
	private $elem;

	public function __construct($elem) {
		$this->elem = $elem;
	}

	public function __get($attr) {
		foreach ($this->elem->getElementsByTagName($attr) as $e) {
			return "{$e->nodeValue}";
		}
		throw new Exception('attribut inconnu');
	}

	public function __toString() {
		return $this->__get($nom);
	}

	public function date() {
		return $this->elem->getAttribute('date');
	}

	public function attr($balise, $attr) {
		$elem = null;
		foreach ($this->elem->getElementsByTagName($balise) as $e) {
			$elem = $e;
			break;
		}
		if (empty($elem))
			throw new Exception('attribut inconnu');
		return $e->getAttribute($attr);
	}
}
