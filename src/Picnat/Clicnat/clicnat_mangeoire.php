<?php
namespace Picnat\Clicnat;

/**
 * @brief Gestion d'une mangeoire
 */
class clicnat_mangeoire extends bobs_espace_point {
	const tag_mangeoire = 'MANG';
	const especes_de_base = '879,506,344,963,991,822,1140,592,401,553,266,1165,508,510,572,343,615,993,349,389';
	const especes_suppl = '';

	public static function tag_mangeoire($db) {
		return bobs_tags::by_ref($db, self::tag_mangeoire);
	}

	/**
	 * @brief Création d'une mangeoire
	 * @param $db ressource
	 * @param $data un tableau associatif
	 * @param $table la table espace associée
	 * @return int le nouvel id_espace
	 *
	 * Un tag est ajouté au point pour indiquer que c'est une mangeoire
	 * data[id_utilisateur] est l'observateur associé à la mangeoire
	 */
	public static function insert($db, $data, $table='espace_point') {
		self::cli($data['id_utilisateur'], self::except_si_inf_1);
		$id_espace = parent::insert($db, $data, $table);
		$mangeoire = new clicnat_mangeoire($db, $id_espace);
		$mangeoire->ajoute_observateur($data['id_utilisateur']);
		return $id_espace;
	}

	/**
	 * @brief Test si un observateur est associé
	 * @return boolean
	 */
	public function a_observateur($id_utilisateur) {
		self::cli($id_utilisateur, self::except_si_inf_1);
		$tags = $this->get_tags();
		foreach ($tags as $tag) {
			if ($tag['ref'] == self::tag_mangeoire) {
				if ($tag['v_int'] == $id_utilisateur)
					return true;
			}
		}
		return false;
	}

	/**
	 * @brief Liste les observateurs de la mangeoire
	 * @return un tableau d'objets clicnat_mangeoire_observateur
	 */
	public function liste_observateurs() {
		$utilisateurs = array();
		$tags = $this->get_tags();
		foreach ($tags as $tag) {
			if ($tag['ref'] == self::tag_mangeoire) {
				$utilisateurs[] = new clicnat_mangeoire_observateur($this->db, $tag['v_int']);
			}
		}
		return $utilisateurs;
	}

	const sql_l_annee = 'select distinct extract(\'year\' from date_observation)
		from observation where id_espace=$1 and espace_table=$2
		order by nom_f';

	const sql_l_espece = 'select especes.* from especes,observations o,citations c
		where o.id_observation=c.id_observation and especes.id_espece=c.id_espece
		and o.brouillard=false and extract(\'year\' from date_observation) = $1
		and id_espace=$2';

	/**
	 * @brief Liste les espèces vues
	 * @param $annee
	 * @return un tableau
	 */
	public function liste_especes($annee='') {
		if ($annee == '')
			$annee = strftime("%Y");
		self::cli($annee, self::except_si_inf_1);
		$q = bobs_qm()->query($this->db, 'mangeoire_l_esp', self::sql_l_espece, array($annee,$this->id_espace));
		return self::fetch_all($q);
	}

	/**
	 * @brief Ajoute un observateur
	 * @param $id_utilisateur le numéro du nouvel observateur
	 */
	public function ajoute_observateur($id_utilisateur) {
		if ($this->a_observateur($id_utilisateur))
			return false;

		$tag = self::tag_mangeoire($this->db);
		return $this->ajoute_tag($tag->id_tag, $id_utilisateur);
	}

	const sql_suppr = 'delete from espace_tags where id_espace=$1 and v_int=$2 and espace_table=\'espace_point\' and id_tag=$3';

	/**
	 * @brief supprime un utilisateur
	 * @param $id_utilisateur identifiant observateur
	 */
	public function supprime_observateur($id_utilisateur) {
		if ($id_utilisateur == $this->id_utilisateur) {
			throw new Exception('Pas possible pour le propriétaire');
		}

		$tag = self::tag_mangeoire($this->db);
		return bobs_qm()->query($this->db, 'mangeoire_s_observ', self::sql_suppr, array($this->id_espace, $id_utilisateur, $tag->id_tag));
	}

	/**
	 * @brief création d'une observation
	 * @param $id_utilisateur identifiant observateur
	 * @param $date_obs date de l'observation
	 * @return le numéro de la nouvelle observation
	 */
	public function ajoute_observation($id_utilisateur, $date_obs) {
		$id = bobs_observation::insert($this->db, array(
			'id_utilisateur' => $id_utilisateur,
			'date_observation' => $date_obs,
			'id_espace' => $this->id_espace,
			'table_espace' => $this->table
		));
		return $id;
	}

	public function liste_especes_base() {
		$t = explode(',', self::especes_de_base);
		$rt = array();
		foreach ($t as $id_espece)
			$rt[] = get_espece($this->db, $id_espece);
		return $rt;
	}
}
