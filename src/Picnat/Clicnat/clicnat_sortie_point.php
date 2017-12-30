<?php
namespace Picnat\Clicnat;

class clicnat_sortie_point extends bobs_espace_point {
	public $departement_id_espace = null;

	public function grille_x_y() {
		if (!defined('C_REPARTITION_X_MIN') or !defined('C_REPARTITION_Y_MAX')) {
			throw new Exception('C_REPARTITION_X_MIN ou C_REPARTITION_Y_MAX non définit');
		}
		$x = $y = '';
		$index_atlas = $this->get_index_atlas_repartition(2154, 10000);
		if (count($index_atlas) > 0) {
			$c = $index_atlas[0];
			$x = chr($c['x0']-C_REPARTITION_X_MIN+65);
			$y = C_REPARTITION_Y_MAX-$c['y0']+1;
		}
		return array('x'=>$x,'y'=>$y);
	}
}


?>
