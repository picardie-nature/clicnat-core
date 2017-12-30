<?php
namespace Picnat\Clicnat;

class clicnat_tr_shell_exec extends clicnat_travail implements i_clicnat_travail {
	public function __construct($db, $args) {
		parent::__construct($db, $args);
	}

	public function executer() {
		print_r($this->args);
		$cmd = escapeshellcmd($this->args['cmd']);
		if (empty($cmd))
			throw new Exception('cmd vide');
		exec($cmd, $t_output);
		$output = '';
		foreach ($t_output as $l)
			$output .= "$l\n";
		return [clicnat_tache::ok, $output];
	}
}
