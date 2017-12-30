<?php
namespace Picnat\Clicnat;

/**
 * @brief Comité d'homologation
 */
class bobs_chr extends bobs_element {
	const chr_table = 'comite_homologation';
	const chr_pkey = 'id_chr';
	const chr_memb_table = 'comite_homologation_membre';

	public $id_chr;
	public $nom;

	function __construct($db, $id) {
		parent::__construct($db, self::chr_table, self::chr_pkey, $id);
	}

	public function __toString() {
		return $this->nom;
	}

	/**
	 * @brief liste les membres du CHR
	 *
	 * retourne un tableau d'objets bobs_utilisateur
	 *
	 * @return array
	 */
	public function get_members() {
		$sql = 'select utilisateur.* from '.self::chr_memb_table.' as chr_t ,utilisateur where id_chr=$1
				and utilisateur.id_utilisateur=chr_t.id_utilisateur order by nom,prenom';
		$q = bobs_qm()->query($this->db, 'bobs_chr_gm', $sql, array($this->id_chr));
		$results = self::fetch_all($q);
		$t = array();
		foreach ($results as $u) {
			$t[] = get_utilisateur($this->db, $u);
		}
		return $t;
	}

	/**
	 * @brief liste les espèces associées au CHR
	 * @return array un tableau de bobs_espece
	 */
	public function get_especes() {
		$sql = 'select * from especes where id_chr=$1 order by systematique';
		$q = bobs_qm()->query($this->db, 'bobs_chr_l_esp', $sql, array($this->id_chr));
		$result = self::fetch_all($q);
		$t = array();
		foreach ($result as $e) {
			$t[] = get_espece($this->db, $e);
		}
		return $t;
	}

	/**
	 * @brief ajoute un membre au CHR
	 * @param integer $id_utilisateur
	 */
	public function add_member($id_utilisateur) {
		$sql = 'insert into '.self::chr_memb_table.' (id_chr,id_utilisateur) values ($1,$2)';
		return bobs_qm()->query($this->db, 'bobs_chr_add', $sql, array($this->id_chr, $id_utilisateur));
	}

	/**
	 * @brief enlève un membre au CHR
	 * @param integer $id_utilisateur
	 */
	public function del_member($id_utilisateur) {
		$sql = 'delete from '.self::chr_memb_table.' where id_chr=$1 and id_utilisateur=$2';
		return bobs_qm()->query($this->db, 'bobs_chr_del', $sql, array($this->id_chr, $id_utilisateur));
	}

	/**
	 * @brief ajoute une espèce
	 * @param $id_espece numéro de l'espèce
	 */
	public function add_espece($id_espece) {
		$espece = get_espece($this->db, $id_espece);
		if (empty($espece->id_chr))
			$espece->add_chr($this->id_chr);
		else
			throw new \Exception('déjà dans un CHR');
	}

	/**
	 * @brief liste l'ensemble des comités
	 * @return array un tableau des lignes de la base
	 */
	public static function get_list($db) {
		$sql = 'select * from '.self::chr_table.' order by nom';
		return self::fetch_all(bobs_qm()->query($db, 'chr_get_list', $sql, array()));
	}
}
