<?php
namespace Picnat\Clicnat;

interface i_clicnat_reseau  {
	public function __toString();
	public function __get($prop);
	public static function liste_reseaux($db);
	public function get_n_especes();
	public function get_liste_especes();
	public function get_id();
	public static function get_reseau_espece($db, $id_espece);

	//TODO
	//public function maj_stats_nb_esp_par_maille($pas=10000, $crs=2154);
	//public function maj_stats_nb_cit_par_maille($pas=10000, $crs=2154);
	public static function planifier_mad_tete_reseau($db);

	public function est_coordinateur($id_utilisateur);
	public function est_validateur($id_utilisateur);
	public function espece_dans_le_reseau($id_espece);
}
