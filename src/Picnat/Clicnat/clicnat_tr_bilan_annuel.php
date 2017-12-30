<?php
namespace Picnat\Clicnat;

class clicnat_tr_bilan_annuel extends clicnat_tr_shell_exec {
	public function __construct($db, $args) {
		parent::__construct($db, $args);
		if (!defined('SCRIPT_PATH'))
			throw new Exception('define SCRIPT_PATH');
	}

	public function executer() {
		$this->args['cmd'] = sprintf("php %s/stats-bilan-annuel.php %d", SCRIPT_PATH, $this->args['annee']);
		return parent::executer();
	}
}
