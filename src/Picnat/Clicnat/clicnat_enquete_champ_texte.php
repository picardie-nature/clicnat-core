<?php
namespace Picnat\Clicnat;

class clicnat_enquete_champ_texte extends clicnat_enquete_champ {
	public function formulaire($valeur='') {
		return "<input type=\"text\" name=\"{$this->nom}\" value=\"{$valeur}\"/>";
	}

	public function afficher($valeur) {
	}
}
