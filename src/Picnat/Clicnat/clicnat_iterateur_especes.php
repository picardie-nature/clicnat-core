<?php
namespace Picnat\Clicnat;

class clicnat_iterateur_especes extends clicnat_iterateur {
	function current() {
		return get_espece($this->db, $this->ids[$this->position]);
	}

	function csv($fh) {
		$this->position = 0;
		$columns = array("id_espece","classe","ordre","famille","nom_s","nom_f","taxref_cd_nom","znieff","rarete","menace");
		fputcsv($fh, $columns);
		foreach ($this as $espece) {
			$ref_r = $espece->get_referentiel_regional();
			$ligne = array(
				"{$espece->id_espece}",
				"{$espece->classe}",
				"{$espece->ordre}",
				"{$espece->famille}",
				"{$espece->nom_s}",
				"{$espece->nom_f}",
				"{$espece->taxref_inpn_especes}",
				"{$espece->determinant_znieff}",
				isset($ref_r['indice_rar'])?$ref_r['indice_rar']:"",
				isset($ref_r['categorie'])?$ref_r['categorie']:""
			);
			fputcsv($fh, $ligne);
		}
	}

	private function in_string() {
		$in_string = '';
		foreach ($this->ids as $id) {
			$in_string .= sprintf("%d,", $id);
		}
		return trim($in_string,",");
	}

	private function order_by($sql_order) {
		$in_string = $this->in_string();
		$sql = "select id_espece from especes where id_espece in ($in_string)
			order by $sql_order";
		$q = bobs_qm()->query($this->db, md5($sql), $sql, array());
		$r = bobs_element::fetch_all($q);
		$this->ids = array_column($r, 'id_espece');
		return true;
	}

	public function trier_par_classe_ordre_famille_nom() {
		return $this->order_by("classe,ordre,famille,nom_f,nom_s");
	}

	public function trier_arbre_taxo() {
		return $this->order_by("borne_a");
	}
}
