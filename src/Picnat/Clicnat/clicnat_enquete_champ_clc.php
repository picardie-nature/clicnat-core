<?php
namespace Picnat\Clicnat;

class clicnat_enquete_champ_clc extends clicnat_enquete_champ {
	private function get_clc_nomenclature() {
		$q = bobs_qm()->query(get_db(), 'g_clc_nom', 'select * from clc_nomenclature', array());
		$t = bobs_element::fetch_all($q);
		$r = array();
		foreach ($t as $l) {
			$r[$l['code']] = $l['lib'];
		}
		return $r;
	}

	public function formulaire($valeur='') {
		$r = "<select name=\"{$this->nom}\"><option value=\"\"></option>";
		$clc_nom = $this->get_clc_nomenclature();
		$liste = $this->doc->getElementsByTagName('clc_code');
		$len = $liste->length;
		for ($p = 0;$p<$len; $p++) {
			$i = $liste->item($p);
			$r .= "<option value=\"{$i->nodeValue}\">{$clc_nom[$i->nodeValue]} ({$i->nodeValue})</option>";
		}
		$r .= "</select>";
		return $r;
	}
}
