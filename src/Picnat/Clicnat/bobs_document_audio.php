<?php
namespace Picnat\Clicnat;

class bobs_document_audio extends bobs_document {
	public function __construct($doc_dir, $db=null) {
		parent::__construct($doc_dir, $db);
		if (!file_exists($this->f_blob)) {
			throw new Exception('Fichier inexistant : '.$this->f_blob);
		}
	}

	public function get_audio() {
		$s = filesize($this->f_blob);
		header("Content-Length: $s");
		header('Content-Type: audio/mpeg');
		header('Cache-Control: public, max-age=864000');
		header('Expires:');
		header('Pragma:');
		echo file_get_contents($this->f_blob);
		exit();
	}
}

class bobs_document_pdf extends bobs_document {
	public function get_pdf() {
		$s = filesize($this->f_blob);
		header("Content-Length: $s");
		header('Content-Type: application/pdf');
		header('Cache-Control: public, max-age=864000');
		header('Expires:');
		header('Pragma:');
		echo file_get_contents($this->f_blob);
		exit();
	}

	public function get_vignette() {
		if (!file_exists("{$this->c_path}/vignette.jpg"))
			exec("convert -density 150 -quality 100 -resize 300x {$this->f_blob}[0] {$this->c_path}/vignette.jpg");
		if (file_exists("{$this->c_path}/vignette.jpg")) {
			header('Content-Type: image/jpeg');
			echo file_get_contents("{$this->c_path}/vignette.jpg");
			exit();
		}
		throw new Exception('echec creation vignette');
	}
}
