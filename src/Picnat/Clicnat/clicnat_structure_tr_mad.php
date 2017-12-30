<?php
namespace Picnat\Clicnat;

class clicnat_structure_tr_mad extends clicnat_travail implements i_clicnat_travail {
	protected $opts;

	public function executer() {
		foreach (clicnat_structure::structures(get_db()) as $structure) {
			try {
				echo "Structure : $structure\n";
				$structure->mad_structure(false,true);
				foreach ($structure->membres() as $m) {
					$structure->mad($m);
				}
			} catch (\Exception $e) {
				echo "ERREUR !!!! {$e->getMessage()}\n";
			}
		}
		return clicnat_tache::ok;
	}
}
