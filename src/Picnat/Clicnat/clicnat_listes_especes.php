<?php
namespace Picnat\Clicnat;

/**
 * @brief liste d'especes
 */
class clicnat_listes_especes extends bobs_element {
	protected $id_liste_espece;
	protected $id_utilisateur;
	protected $nom;
	protected $ref;

	const __table__ = 'listes_especes';
	const __prim__ = 'id_liste_espece';
	const __seq__ = 'listes_especes_id_liste_espece_seq';
	const __datatable__ = 'listes_especes_data';

	const sql_insert = 'insert into listes_especes (id_utilisateur,nom) values ($1,$2)';
	const sql_suppr = 'delete from listes_especes where id_liste_espece = $1';
	const sql_vider = 'delete from listes_especes_data where id_liste_espece = $1';

	const sql_ajouter = 'insert into listes_especes_data (id_liste_espece,id_espece) values ($1,$2)';
	const sql_liste = 'select e.* from especes e,listes_especes_data l where e.id_espece=l.id_espece and l.id_liste_espece=$1 order by classe,ordre,nom_f,nom_s';
	const sql_liste_lu = 'select * from listes_especes where id_utilisateur=$1 order by date_creation desc';
	const sql_liste_lp = 'select * from listes_especes where ref=true order by nom';
	const sql_a_espece = 'select count(*) as n from listes_especes_data where id_liste_espece=$1 and id_espece=$2';
	const sql_retirer = 'delete from listes_especes_data where id_liste_espece = $1 and id_espece = $2';

	public function __construct($db, $id) {
		parent::__construct($db, self::__table__, self::__prim__, $id);
	}

	public function __toString() {
		return $this->nom;
	}

	public function __get($prop) {
		switch ($prop) {
			case 'id_liste_espece':
				return $this->id_liste_espece;
			case 'id_utilisateur':
				return $this->id_utilisateur;
			case 'nom':
				return $this->nom;
			case 'ref':
				return $this->ref == 't';
		}
		throw new Exception($prop.' pas accessible');
	}

	/**
	 * @brief création d'une nouvelle liste d'espèce
	 * @param $db ressource
	 * @param $id_utilisateur (id du propriétaire)
	 * @param $nom nom de la liste
	 * @return le numéro de la nouvelle liste
	 */
	public static function creer($db, $id_utilisateur, $nom) {
		$data = array();
		$data[self::__prim__] = self::nextval($db, self::__seq__);
		$data['id_utilisateur'] = self::cli($id_utilisateur, self::except_si_inf_1);
		$data['nom'] = self::cls($nom, self::except_si_vide);
		parent::insert($db, self::__table__, $data);
		return $data[self::__prim__];
	}

	/**
	 * @brief dresse la liste des listes appartenant à un utilisateur
	 * @param $db ressource
	 * @param $id_utilisateur numéro de l'utilisateur propriétaire
	 * @return un tableau de lignes de la table listes_especes
	 */
	public static function liste($db, $id_utilisateur) {
		$q = bobs_qm()->query($db, '_l_u_esp', self::sql_liste_lu, array($id_utilisateur));
		return self::fetch_all($q);
	}

	/**
	 * @brief dresse la liste des listes appartenant à un utilisateur
	 * @param $id_utilisateur numéro de l'utilisateur propriétaire
	 * @return un tableau de lignes de la table listes_especes
	 */
	public static function liste_public($db) {
		$q = bobs_qm()->query($db, '_l_p_esp', self::sql_liste_lp, array());
		return self::fetch_all($q);
	}

	/**
	 * @brief vide la liste
	 */
	public function vider() {
		return bobs_qm()->query($this->db, '_lesp_vide', self::sql_vider, array($this->id_liste_espece));
	}

	/**
	 * @brief retirer un taxon de la liste
	 * @param $espece une instance de bobs_espece ou un id_espece
	 */
	public function retirer($espece) {
		if (is_object($espece)) {
			if (is_subclass_of($espece, 'bobs_espece')) {
				$id_espece = $espece->id_espece;
			} else {
				throw new InvalidArgumentException('pas une instance de bobs_espece');
			}
		} else {
			$id_espece = self::cli($espece);
		}
		return bobs_qm()->query($this->db, '_lesp_retir', self::sql_retirer, array($this->id_liste_espece, $id_espece));
	}

	/**
	 * @brief supprime la liste
	 */
	public function supprimer() {
		$this->vider();
		return bobs_qm()->query($this->db, '_lesp_suppr', self::sql_suppr, array($this->id_liste_espece));
	}


	/**
	 * @brief ajoute une espèce à la liste
	 */
	public function ajouter($id_espece) {
		self::cli($id_espece, self::except_si_inf_1);
		return bobs_qm()->query($this->db, '_lesp_inst', self::sql_ajouter, array($this->id_liste_espece, $id_espece));
	}

	/**
	 * @brief liste les espèces dans la liste
	 * @return un tableau de lignes de la table especes
	 */
	public function liste_especes() {
		$q = bobs_qm()->query($this->db, '__lesp_list', self::sql_liste, array($this->id_liste_espece));
		return self::fetch_all($q);
	}

	protected function parse($liste) {
		$a_min = ord(0);
		$a_max = ord(9);
		$tableau = array();
		$buffer = '';
		for ($p=0; $p<strlen($liste); $p++) {
			$c = $liste[$p];
			if ((ord($c) >= $a_min) && (ord($c) <= $a_max)) {
				$buffer .= $c;
			} else {
				if (!empty($buffer)) {
					$tableau[] = intval($buffer);
					$buffer = '';
				}
			}
		}
		if (!empty($buffer))
			$tableau[] = intval($buffer);

		return $tableau;
	}

	public function ajouter_liste_id_mnhn($liste) {
		$t = $this->parse($liste);
		$t_erreurs = array();
		foreach ($t as $l) {
			try {
				$einpn = new bobs_espece_inpn($this->db, $l);
			} catch (Exception $e) {
				$t_erreurs[] = array('id' => $l, 'msg' => 'pas trouvé dans réf. INPN');
				continue;
			}
			$especes = $einpn->get_especes();
			if (count($especes) > 0) {
				$this->ajouter($especes[0]->id_espece);
			} else {
				$t_erreurs[] = array('id' => $l, 'msg' => $einpn->lb_nom.' pas trouvé dans le référentiel');
			}
		}
		return $t_erreurs;
	}

	public function ajouter_liste_id_espece($liste) {
		$t = $this->parse($liste);
		$t_erreurs = array();
		foreach ($t as $l) {
			try {
				$espece = get_espece($this->db, $l);
			} catch (Exception $e) {
				$t_erreurs[] = array('id' => $l, 'msg' => 'pas trouvé dans le référentiel');
				continue;
			}
			$this->ajouter($espece->id_espece);
		}
		return $t_erreurs;
	}

	/**
	 * @brief test si la chaine ne contient que du texte
	 * @param $chaine la chaine a tester
	 * @return boolean
	 */
	private static function a_que_du_texte($chaine) {
		static $subst_chars;

		if (!isset($subst_chars)) {
			$subst_chars = array(
				str_split("éèëêâêïôâa-'"),
				str_split("eeeeaeioaa  ")
			);
		}

		$t = true;
		foreach (explode(' ', $chaine) as $mot) {
			$mot = str_replace($subst_chars[0],$subs_chars[1],$mot);
			if (!ctype_alpha($mot)) {
				$t = false;
				echo "<font color=red>$mot</font>";
				break;
			}
		}

		return $t;
	}

	public function ajouter_liste_nom_espece($liste) {
		$t = explode("\n",$liste);
		foreach ($t as $txt_nom) {
			$txt_nom = trim($txt_nom);
			if (strlen($txt_nom) == 0) continue;
			if ($this->a_que_du_texte($txt_nom)) {
				$rn = bobs_espece::recherche_par_nom($this->db, $txt_nom);
				if (count($rn) == 1) {
					$ss = strtolower(substr($rn[0]['nom_f'], 0, strlen($txt_nom)));
					if (levenshtein(strtolower($txt_nom), $ss) < 5) {
						try {
							$this->ajouter($rn[0]['id_espece']);
						} catch (Exception $e) {
							$t_erreurs[] = array('txt' => $txt_nom, 'msg' => "insertion impossible (déjà dans la liste ?)");
						}
					} else {
						$t_erreurs[] = array('txt' => $txt_nom, 'msg' => "trop loin de {$rn[0]['nom_f']}");
					}
				} else {
					$en_erreur = true;
					foreach ($rn as $r) {
						$ss = strtolower(substr($r['nom_f'], 0, strlen($txt_nom)));
						if (levenshtein(strtolower($txt_nom), $ss) < 5) {
							try {
								$this->ajouter($r['id_espece']);
							} catch (Exception $e) {
								$t_erreurs[] = array('txt' => $txt_nom, 'msg' => "insertion impossible (déjà dans la liste ?)");
							}
							$en_erreur = false;
							break;
						}
					}
					if ($en_erreur) {
						$t_erreurs[] = array('txt' => $txt_nom, 'msg' => count($rn)." résultats");
					}
				}
			} else {
				echo "pas que du texte";
				$t_erreurs[] = array('txt' => $txt_nom, 'msg' => "ne contient pas que du texte");
			}
		}
		return $t_erreurs;
	}

	/**
	 * @brief test si l'espèce est dans la liste
	 * @param $espece id_espece ou objet bobs_espece bobs_citation
	 * @return bool
	 */
	public function a_espece($espece) {
		$id_espece = is_object($espece)?$espece->id_espece:$espece;
		self::cli($id_espece, self::except_si_inf_1);
		$q = bobs_qm()->query($this->db, 'liste_a_espece', self::sql_a_espece, array($this->id_liste_espece, $id_espece));
		$r = self::fetch($q);
		return $r['n'] == 1;
	}
}
