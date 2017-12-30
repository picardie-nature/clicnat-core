<?php
namespace Picnat\Clicnat;

class bobs_document_image extends bobs_document {
	public function __construct($doc_dir, $db=null) {
		parent::__construct($doc_dir, $db);
		if (!file_exists($this->f_blob)) {
			throw new Exception('Fichier inexistant : '.$this->f_blob);
		}
	}

	public function get_exif_data() {
		if ($this->get_format() != 'jpeg')
			return false;
		return exif_read_data($this->f_blob);
	}

	private function gd_im() {
		bobs_log('imagecreate '.$this->f_blob);
		switch ($this->get_format()) {
			case 'gif':
				$gd_im = imagecreatefromgif($this->f_blob);
				break;
			case 'png':
				$gd_im = imagecreatefrompng($this->f_blob);
				break;
			case 'jpeg':
				$gd_im = imagecreatefromjpeg($this->f_blob);
				break;
			default:
				throw new Exception('Format non géré');
		}
		return $gd_im;
	}

	private function redim_nom_fichier($nouvelle_largeur, $nouvelle_hauteur) {
		return sprintf('%s/redim-%d-%d.jpg', $this->c_path, $nouvelle_largeur, $nouvelle_hauteur);
	}

	private function drop_redim_cache() {
		$fichiers = glob(sprintf('%s/redim-*-*.jpg', $this->c_path));
		if (count($fichiers) > 0) {
			foreach ($fichiers as $f) {
				unlink($f);
			}
		}
	}

	public function redims_dim($nouvelle_largeur, $nouvelle_hauteur, $dims=null) {
		if (is_null($dims))
			$dims = $this->get_original_dims();

		if (empty($nouvelle_largeur))
			$nouvelle_largeur = $nouvelle_hauteur*($dims['largeur']/$dims['hauteur']);

		if (empty($nouvelle_hauteur))
			$nouvelle_hauteur = $nouvelle_largeur*($dims['hauteur']/$dims['largeur']);

		return array((int)$nouvelle_largeur, (int)$nouvelle_hauteur);
	}

	private function redim($nouvelle_largeur, $nouvelle_hauteur) {
		$dims = $this->get_original_dims();
		$nom_fichier = $this->redim_nom_fichier($nouvelle_largeur, $nouvelle_hauteur, $dims);

		list($nouvelle_largeur, $nouvelle_hauteur) = $this->redims_dim($nouvelle_largeur, $nouvelle_hauteur);

		$gd_im = $this->gd_im();
		$n = imagecreatetruecolor($nouvelle_largeur, $nouvelle_hauteur);
		imagecopyresampled($n, $gd_im, 0, 0, 0, 0, $nouvelle_largeur, $nouvelle_hauteur, $dims['largeur'], $dims['hauteur']);
		imagejpeg($n, $nom_fichier);
		imagedestroy($n);
		imagedestroy($gd_im);
	}

	public function get_image_redim($nouvelle_largeur, $nouvelle_hauteur) {
		$nom_fichier = $this->redim_nom_fichier($nouvelle_largeur, $nouvelle_hauteur);
		if (!file_exists($nom_fichier)) {
			bobs_log("creation vignette $nom_fichier");
			$this->redim($nouvelle_largeur, $nouvelle_hauteur);
		}
		header('Content-type: image/jpeg');
		ob_clean();
		flush();
		readfile($nom_fichier);
	}

	public function get_image() {
		$d = $this->get_original_dims();
		$this->get_image_redim($d['largeur'], $d['hauteur']);
	}

	public function get_original_dims() {
		$s = getimagesize($this->f_blob);
		return array('largeur'=>$s[0], 'hauteur'=>$s[1]);
	}

	/**
	 * @brief Test si les dimensions de l'image ont un rapport de 4/3
	 * @return boolean
	 */
	public function quatre_tiers() {
		$d = $this->get_original_dims();
		return floor(100*$d['largeur']/$d['hauteur']) == 133;
	}

	public function decoupe_original($tmp_w, $x, $y, $x2, $y2) {
		bobs_tests::cli($tmp_w, bobs_tests::except_si_inf_1);
		bobs_tests::cli($x, bobs_tests::except_si_vide);
		bobs_tests::cli($y, bobs_tests::except_si_vide);
		bobs_tests::cli($x2, bobs_tests::except_si_inf_1);
		bobs_tests::cli($y2, bobs_tests::except_si_inf_1);

		$orig_dims = $this->get_original_dims();
		$tmp_h = $tmp_w*$orig_dims['hauteur']/$orig_dims['largeur'];

		$sx1 = floor($orig_dims['largeur']/$tmp_w*$x);
		$sy1 = floor($orig_dims['hauteur']/$tmp_h*$y);

		$sx2 = floor($orig_dims['largeur']/$tmp_w*$x2);
		$sy2 = floor($orig_dims['hauteur']/$tmp_h*$y2);

		$gd_im = $this->gd_im();
		$n = imagecreatetruecolor($sx2-$sx1, $sy2-$sy1);
		imagecopyresampled($n, $gd_im, 0, 0, $sx1, $sy1, $sx2-$sx1, $sy2-$sy1, $sx2-$sx1, $sy2-$sy1);
		imagedestroy($gd_im);

		if ($this->backup())
			unlink($this->f_blob);

		switch ($this->get_format()) {
			case 'jpeg':
				imagejpeg($n, $this->f_blob);
				break;
			case 'png':
				imagepng($n, $this->f_blob);
				break;
			default:
				throw new Exception('format inconnu');
		}
		imagedestroy($n);
		$this->drop_redim_cache();

		return true;
	}

	public function restore() {
		parent::restore();
		$this->drop_redim_cache();
	}
}
