<?php
namespace Picnat\Clicnat;

class clicnat_travaux_images extends clicnat_travaux {
	public function images() {
		if (empty($this->data)) {
			return [];
		}
		return json_decode($this->data, true);
	}

	public function __toString() {
		return $this->titre;
	}

	public static function nouveau($db, $titre, $type='images') {
		return parent::nouveau($db, $titre, $type);
	}

	public function ajoute_image($url) {
		if (empty($url)) return false;
		$images = $this->images();
		$images[] = $url;
		return $this->update_field('data', json_encode($images));
	}

	public function retire_image($url) {
		$images = $this->images();
		$pos = array_search($url, $images);
		if ($pos !== false) {
			if ($pos == 0) array_shift($images);
			else if ($pos == count($images)-1) array_pop($images);
			else {
				$a = array_slice($images, 0, $pos);
				$b = array_slice($images, $pos+1);
				$images = array_merge($a,$b);
			}
			return $this->update_field('data', json_encode($images));
		}
		return false;
	}
}
