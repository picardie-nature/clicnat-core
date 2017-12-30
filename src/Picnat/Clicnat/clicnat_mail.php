<?php
namespace Picnat\Clicnat;

class clicnat_mail {
	private $sujet;
	private $headers = '';
	private $from = CLICNAT_MAIL_EXPEDITEUR;

	public function __construct() {
		$this->header_ajoute("Content-Type: text/plain; charset=utf-8");
	}

	public function header_ajoute($head) {
		$this->headers .= "$head\r\n";
	}

	public function from($nouveau_expediteur = null) {
		return $this->__gs('from', $nouveau_expediteur);
	}

	public function sujet($nouveau_sujet = null) {
		return $this->__gs('sujet', $nouveau_sujet);
	}

	public function message($message = null) {
		return $this->__gs('message', $message);
	}


	private function __gs($k,$nv) {
		if (!is_null($nv))
			$this->$k = $nv;
		return $this->$k;
	}

	public function envoi($destinataire) {
		if (empty($this->sujet))
			throw new \Exception('Sujet vide');

		if (empty($this->message))
			throw new \Exception('Message vide');

		$hfrom = "From: {$this->from}\r\n";

		if (!mail($destinataire, $this->sujet, $this->message, $this->headers.$hfrom, "-f{$this->from}")) {
			throw new \Exception('Message pas envoyé');
		}

		return true;
	}
}
