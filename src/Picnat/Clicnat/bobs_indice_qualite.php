<?php
namespace Picnat\Clicnat;

/**
 * @brief indice de qualité de l'identification
 */
class bobs_indice_qualite {
	private $indice;

	const indice_qualite_max = 4;
	const indice_qualite_min = 1;

	public function __construct($indice) {
		bobs_element::cli($indice);
		if ($indice < self::indice_qualite_min || $indice > self::indice_qualite_max) {
			throw new \InvalidArgumentException('indice invalide');
		}
		$this->indice = $indice;
	}

	public function __toString() {
		switch ($this->indice) {
			case 4:
				return "très fort";
			case 3:
				return "fort";
			case 2:
				return "moyen";
			case 1:
				return "faible";
			case 0:
				return "invalide";
		}
		return "Erreur valeur non prévue : {$this->indice}";
	}

	/**
	 * @brief permet un accès en lecture seule
	 */
	public function __get($prop) {
		if ($prop == 'indice')
			return $this->indice;
	}
}
