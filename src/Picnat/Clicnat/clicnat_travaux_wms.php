<?php
namespace Picnat\Clicnat;

class clicnat_travaux_wms extends clicnat_travaux {
	protected function prop($nom_prop, $valeur=null) {
		if (!is_null($valeur)) {
			$data = json_decode($this->data, true);
			$data[$nom_prop] = $valeur;
			$this->update_field('data', json_encode($data));
		}
		$data = json_decode($this->data, true);
		return $data[$nom_prop];
	}

	public function url_wms($url=null) {
		return $this->prop('url_wms', $url);
	}

	public function layers($layers=null) {
		return $this->prop('layers', $layers);
	}

	public function attribution($attribution=null) {
		return $this->prop('attribution', $attribution);
	}

	public static function nouveau($db, $titre, $type='wms') {
		return parent::nouveau($db, $titre, $type);
	}
}
