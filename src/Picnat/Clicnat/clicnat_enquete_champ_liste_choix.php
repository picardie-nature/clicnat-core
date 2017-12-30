<?php
namespace Picnat\Clicnat;

class clicnat_enquete_champ_liste_choix extends clicnat_enquete_champ {
	public function formulaire($valeur='') {
		$c = "<select name=\"{$this->nom}\"><option value=''></option>";
		$liste = $this->doc->getElementsByTagName('option');
		$len = $liste->length;
		for ($p = 0;$p<$len; $p++) {
			$i = $liste->item($p);
			$v = $i->getAttribute('value');
			$s = $v==$valeur?'selected=1':'';
			$c .= "<option value=\"$v\" $s>{$i->nodeValue}</option>";
		}
		$c .= "</select>";
		return $c;
	}
}
