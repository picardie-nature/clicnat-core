<?php
namespace Picnat\Clicnat;

class clicnat_travaux_lien extends clicnat_travaux {
	public function lien($url=false) {
		if ($url)
			$this->update_field('data', $url);
		return $this->data;
	}

	public static function nouveau($db, $titre, $type='lien') {
		return parent::nouveau($db, $titre, $type);
	}
}
