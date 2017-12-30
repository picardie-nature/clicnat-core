<?php
namespace Picnat\Clicnat;

trait clicnat_ajoute_alertes {
	public $alertes = [];
	public function ajoute_alerte($classe, $message) {
		$this->alertes[] = ["classe"=>$classe, "message"=>$message];
		$this->assign('alertes', $this->alertes);
	}
}
