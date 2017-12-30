<?php
namespace Picnat\Clicnat;

class clicnat_validation_test_lieu extends clicnat_validation_test {
	public function evaluer() {
		$srid = 2154;
		$pas = 10000;
		$ymin = strftime("%Y")-11;
		$message = '';


		$cells = $this->citation->get_observation()->get_espace()->get_index_atlas_repartition($srid, $pas);

		$n_cells = count($cells);
		$n_cells_ok = 0;

		foreach ($cells as $cell) {
			if ($this->citation->get_espece()->get_index_atlas_repartition_x_y($srid, $pas, $cell['x0'], $cell['y0'], $ymin)) {
				$n_cells_ok++;
			} else {
				$message .= "absent du carré {$cell['x0']}_{$cell['y0']} ";
			}
		}
		return [
			"passe" => $n_cells_ok > 0,
			"message" => "espèce déjà présente sur $n_cells_ok/$n_cells des mailles $message"
		];
	}
}
