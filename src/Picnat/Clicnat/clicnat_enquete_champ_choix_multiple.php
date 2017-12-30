<?php
namespace Picnat\Clicnat;

class clicnat_enquete_champ_choix_multiple extends clicnat_enquete_champ {
	public function formulaire($valeurs_init=false) {
		if (!$valeurs_init) $valeurs_init = array();
		$choix = array();
		$liste = $this->doc->getElementsByTagName('option');
		$len = $liste->length;
		for ($p = 0;$p<$len; $p++) {
			$choix[$liste->item($p)->getAttribute('value')] = $liste->item($p)->nodeValue;
		}
		$r = '';
		foreach ($choix as $valeur=>$lib) {
			if (array_search($valeur, $valeurs_init) !== false) $chk = 'checked=1'; else $chk = '';
			$r .= "<input type=\"checkbox\" $chk name=\"{$this->nom}_{$valeur}\" id=\"choixm_{$this->nom}_{$valeur}\"/>";
			$r .= "<label for=\"choixm_{$this->nom}_{$valeur}\">{$lib}</label><br/>";
		}
		return $r;
	}

	public function doc_champ_sauve($element, $data) {
		$element->setAttribute('nom', $this->nom);
		$element->setAttribute('type', 'multiple');
		$cles = array_keys($data);
		foreach ($cles as $cle) {
			if (preg_match("/^{$this->nom}_(.*)$/", $cle, $m)) {
				if ($data[$cle] == 1) {
					$val = $element->ownerDocument->createElement('valeur', $m[1]);
					$element->appendChild($val);
				}
			}
		}
		return true;
	}
}
