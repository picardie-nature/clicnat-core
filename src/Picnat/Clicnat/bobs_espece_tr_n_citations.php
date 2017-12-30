<?php
namespace Picnat\Clicnat;

/**
 * @brief mise a jour du nombre de citations par espèce
 */
class bobs_espece_tr_n_citations extends clicnat_travail implements i_clicnat_travail {
	public function executer() {
		$db = get_db();
		foreach (bobs_classe::get_classes() as $classe) {
			foreach (bobs_espece::get_liste_par_classe($db, $classe) as $espece) {
				$espece->set_nb_citations();
			}
		}
	}

	public static function planifier($db) {
		clicnat_tache::ajouter($db, strftime("%Y-%m-%d %H:%M:%S",mktime()), 0, 'Mise a jour nb citations par espèce', 'bobs_espece_tr_n_citations', []);
	}
}
