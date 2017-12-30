<?php
namespace Picnat\Clicnat;

class clicnat_travaux_wfs extends clicnat_travaux {
	protected function prop($nom_prop, $valeur=null) {
		if (!is_null($valeur)) {
			$data = json_decode($this->data, true);
			$data[$nom_prop] = $valeur;
			$this->update_field('data', json_encode($data));
		}
		$data = json_decode($this->data, true);
		return $data[$nom_prop];
	}

	public function liste_espace($nouvel_id_liste=null) {
		if (!is_null($nouvel_id_liste))
			$this->prop('id_liste_espace', $nouvel_id_liste);
		$id = $this->prop('id_liste_espace');
		if (!empty($id))
			return new clicnat_listes_espaces($this->db, $id);
		return false;
	}

	public function sld($sld=null) {
		if (!is_null($sld))
			$this->prop('sld', $sld);
		$_sld = $this->prop('sld');
		if (!empty($_sld))
			return $_sld;
		return false;
	}

	public static function nouveau($db, $titre, $type='wfs') {
		return parent::nouveau($db, $titre, $type);
	}
}
