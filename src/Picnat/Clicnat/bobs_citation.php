<?php
namespace Picnat\Clicnat;

/**
 * @brief Citation
 * @see bobs_observation
 */
class bobs_citation extends bobs_element_commentaire {
	protected $id_citation;
	protected $id_observation;
	protected $id_espece;
	protected $sexe;
	protected $age;
	protected $nb;
	protected $nb_min;
	protected $nb_max;
	protected $commentaire;
	protected $indice_qualite;
	protected $ref_import;
	protected $resultat_enquete;
	protected $date_modif;
	protected $guid;

	const table_commentaire = 'citations_commentaires';

	/**
	 * @brief permet un accès en lecture seule aux propriétés
	 */
	public function __get($prop) {
		switch($prop) {
			case 'id_citation':
				return $this->id_citation;
			case 'id_observation':
				return $this->id_observation;
			case 'id_espece':
				return $this->id_espece;
			case 'sexe':
				return $this->sexe;
			case 'age':
				return $this->age;
			case 'nb':
				return $this->nb;
			case 'nb_min':
				return $this->nb_min;
			case 'nb_max':
				return $this->nb_max;
			case 'nb_txt':
				if (!empty($this->nb_max)) {
					return "entre {$this->nb_min} et {$this->nb_max}";
				} else {
					if ($this->nb > 0)
						return $this->nb;
					else if ($this->nb == -1) {
						return "prospection négative";
					}
				}
				return "inconnu";
			case 'commentaire':
				return $this->commentaire;
			case 'indice_qualite':
				return $this->indice_qualite;
			case 'ref_import':
				return $this->ref_import;
			case 'resultat_enquete':
				return $this->resultat_enquete;
			case 'guid':
				return $this->guid;
		}
	}

	function __construct($db, $id) {
		parent::__construct($db, 'citations', 'id_citation', $id);
		$this->tags = $this->get_tags();
		self::cls($this->sexe);
		self::cls($this->age);
		$this->champ_date_maj = 'date_modif';
	}

	/**
	 * @brief listes les étiquettes associées
	 */
	public function get_tags() {
		return $this->__get_tags(BOBS_TBL_TAG_CITATION, $this->id_citation, 'and id_citation=$1');
	}

	/**
	 * @brief ajouter une étiquette sur la citation
	 *
	 * @param $id_tag l'identifiant de l'étiquette
	 * @param $intval valeur numérique associée
	 * @param $textval texte associé
	 * @param $id_utilisateur numéro de l'utilisateur à l'origine de la modification
	 *
	 * Un commentaire est ajouté sur la citation pour l'historique si un numéro d'utilisateur
	 * est fournit
	 */
	public function ajoute_tag($id_tag, $intval=null, $textval=null, $id_utilisateur=false) {
		$r = $this->__ajoute_tag(BOBS_TBL_TAG_CITATION, 'id_citation', $id_tag, $this->id_citation, $intval, $textval);
		if ($id_utilisateur) {
			$this->ajoute_commentaire('attr', $id_utilisateur, "tag +$id_tag");
		}
		return $r;
	}

	/**
	 * @brief supprime une étiquette de la citation
	 * @param $id_tag l'identifiant de l'étiquette
	 * @param $id_utilisateur le numéro de l'utilisateur à l'origine de la modification
	 *
	 * Un commentaire est ajouté sur la citation pour l'historique si un numéro d'utilisateur
	 * est fournit
	 */
	public function supprime_tag($id_tag, $id_utilisateur=false) {
	    $this->__supprime_tag(BOBS_TBL_TAG_CITATION, 'id_citation', $id_tag, $this->id_citation);
		if ($id_utilisateur) {
			$this->ajoute_commentaire('attr', $id_utilisateur, "tag -$id_tag");
		}
	    $this->get_tags();
	}

	public function get_commentaires() {
		return $this->__get_commentaires(self::table_commentaire, 'id_citation', $this->id_citation);
	}

	public function ajoute_commentaire($type_c, $id_utilisateur, $commtr, $sans_mail=false) {
		return $this->__ajoute_commentaire(self::table_commentaire,'id_citation',$this->id_citation,$type_c,$commtr,$id_utilisateur,$sans_mail);
	}

	public function supprime_commentaire($id_commentaire) {
		return $this->__supprime_commentaire(self::table_commentaire, $id_commentaire);
	}

	public function set_ref_import($ref_import) {
		$this->update_field('ref_import', $ref_import);
	}

	/**
	 * @brief tags biblio
	 * @return array
	 *
	 * [k=>v,k=>v,...] des tags ARCA,ARCD,ARCP et RBIB
	 */
	public function biblio(){
		$biblio = [];
		$tags = $this->get_tags();
		foreach($tags as $tag){
			switch ($tag['ref']) {
				case 'ARCA':
				case 'ARCD':
				case 'ARCP':
					$biblio[$tag['ref']] = $tag['v_int'];
					break;
				case 'RBIB':
					$biblio[$tag['ref']] = $tag['v_text'];
					break;
			}
		}
		return $biblio;
	}

	const sql_set_effectif = 'update citations set nb_min=null, nb_max=null, nb=$2 where id_citation=$1';

	public function set_effectif($n) {
		self::cli($n,false);
		if (empty($n)) {
			$n = 0;
			$this->nb = 0;
		} else {
			$this->nb = $n;
		}
		$this->update_date_maj_field();
		return bobs_qm()->query($this->db, 'citations_set_eff', self::sql_set_effectif, [$this->id_citation, $n]);
	}

	const sql_set_min_max = 'update citations set nb=null, nb_min=$2, nb_max=$3 where id_citation=$1';
	const sql_set_min_max_null = 'update citations set nb_min=null, nb_max=null where id_citation=$1';

	public function set_effectif_min_max($eff_min, $eff_max) {
		if ($eff_min == '0' && $eff_max == '0') {
			return bobs_qm()->query($this->db, 'citations_set_eff_min_max_null', self::sql_set_min_max_null, [$this->id_citation]);
		}
		self::cli($eff_min, self::except_si_inf_1);
		self::cli($eff_max, self::except_si_inf_1);

		if ($eff_min == $eff_max)
			throw new \Exception('effectif min = max');

		$this->update_date_maj_field();
		return bobs_qm()->query($this->db, 'citations_set_eff_min_max', self::sql_set_min_max, [
			$this->id_citation,
			min($eff_min,$eff_max),
			max($eff_min,$eff_max)
		]);
	}

	/**
	 * @brief Modifier l'espèce de la citation
	 * @param un numéro d'espèce
	 */
	public function set_id_espece($id_espece) {
		self::cli($id_espece, self::except_si_inf_1);
		if (empty($id_espece)) {
			throw new \Exception('$id_espece ne peut être vide');
		}
		$this->update_date_maj_field();
		return $this->update_field('id_espece', $id_espece);
	}

	/**
	 * @brief Faire une modification sur une citation en enregistrant un commentaire pour historique
	 * @param $id_utilisateur le numéro de l'utilisateur qui fait la modif
	 * @param $champ le nom du champ modifié
	 * @param $valeur la nouvelle valeur
	 */
	public function modification($id_utilisateur, $champ, $valeur) {
		switch ($champ) {
			case 'nb':
				$old_v = $this->nb;
				if ($old_v != $valeur) {
					if ($this->set_effectif($valeur)) {
						$this->ajoute_commentaire('attr',$id_utilisateur,sprintf('nb %d => %d', $old_v, $valeur));
					}
				}
				break;
			case 'nb_min':
				$old_v = $this->nb_min;
				if ($old_v != $valeur) {
					if ($this->set_effectif_min_max($valeur, $this->nb_max)) {
						$this->ajoute_commentaire('attr',$id_utilisateur,sprintf('nb_min %d => %d', $old_v, $valeur));
					}
				}
				break;
			case 'nb_max':
				$old_v = $this->nb_max;
				if ($old_v != $valeur) {
					if ($this->set_effectif_min_max($this->nb_min, $valeur)) {
						$this->ajoute_commentaire('attr',$id_utilisateur,sprintf('nb_max %d => %d', $old_v, $valeur));
					}
				}
				break;
			case 'id_espece':
				$old_v = $this->id_espece;
				if ($old_v != $valeur) {
					if ($this->set_id_espece($valeur)) {
						$this->ajoute_commentaire('attr',$id_utilisateur,sprintf('id_espece %d => %d', $old_v, $valeur));
					}
				}
				break;
			case 'indice_qualite':
				$old_v = $this->indice_qualite;
				if ($old_v != $valeur) {
					if ($this->set_indice_qualite($valeur)) {
						$this->ajoute_commentaire('attr',$id_utilisateur,sprintf('indice_qualite %d => %d', $old_v, $valeur));
					}
				}
				break;
			case 'age':
				$old_v = $this->age;
				if ($old_v != $valeur) {
					if ($this->set_age($valeur)) {
						$this->ajoute_commentaire('attr',$id_utilisateur,sprintf('age %s => %s', $old_v, $valeur));
					}
				}
				break;
			case 'sexe':
				$old_v = $this->sexe;
				if ($old_v != $valeur) {
					if ($this->set_sex($valeur)) {
						$this->ajoute_commentaire('attr',$id_utilisateur,sprintf('sexe %s => %s', $old_v, $valeur));
					}
				}
				break;
			case 'tag_supprimer':
				$this->supprime_tag($valeur, $id_utilisateur);
				break;
			case 'tag_ajouter':
				$this->ajoute_tag($valeur, null, null, $id_utilisateur);
				break;
		}
	}

	/**
	 * @brief modifie l'indice de qualité de l'observationa
	 * @param $indice le numéro de l'indice
	 */
	public function set_indice_qualite($indice) {
		$indice = new bobs_indice_qualite($indice);
		return $this->update_field('indice_qualite', $indice->indice);
	}

	/**
	 * @brief objet indice qualité ou false
	 */
	public function get_indice_qualite() {
		if ($this->indice_qualite > 0) {
			return new bobs_indice_qualite($this->indice_qualite);
		}
		return false;
	}

	/**
	 * @brief mise à jour du genre de l'espèce
	 * @param $sex nom court de l'age AD JUV...
	 */
	public function set_sex($sex) {
		self::cls($sex);
		$list = $this->get_gender_list();

		if (!array_key_exists($sex, $list))
			throw new \InvalidArgumentException('$sex unknown : ('.$sex.')');

		return $this->update_field('sexe', $sex);
	}

	public function set_enquete_resultat($doc) {
		return $this->update_field('enquete_resultat', $doc->saveXML());
	}

	public function set_age($age) {
		self::cls($age);
		self::cli($this->id_citation);
		$list = $this->get_age_list();

		if (!array_key_exists($age, $list)) {
			throw new \InvalidArgumentException('$age unknown : ('.$age.')');
		}

		return $this->update_field('age', $age);
	}

	public function set_commentaire($cmtr) {
		self::cls($cmtr);
		self::cli($this->id_citation);

		return $this->update_field('commentaire', $cmtr);
	}

	const sql_del_cit_1 = 'delete from citations_tags where id_citation=$1';
	const sql_del_cit_2 = 'delete from citations_commentaires where id_citation=$1';
	const sql_del_cit_3 = 'delete from citations_documents where id_citation=$1';
	const sql_del_cit_4 = 'delete from selection_data where id_citation=$1';
	const sql_del_cit_5 = 'delete from utilisateur_citations_ok where id_citation=$1';
	const sql_del_cit_6 = 'delete from structures_mad where id_citation=$1';
	const sql_del_cit_7 = 'delete from citations where id_citation=$1';
	//20171213 Francois
	const sql_del_cit_8 = 'delete from sinp_dee where id_citation=$1';
	const sql_del_cit_9 = 'delete from sinp_dee_archive where id_citation=$1';

	public function delete() {
		try {
			$qm = bobs_qm();
			$qm->query($this->db, 'cit_del_1', self::sql_del_cit_1, [$this->id_citation]);
			$qm->query($this->db, 'cit_del_2', self::sql_del_cit_2, [$this->id_citation]);
			$qm->query($this->db, 'cit_del_3', self::sql_del_cit_3, [$this->id_citation]);
			$qm->query($this->db, 'cit_del_4', self::sql_del_cit_4, [$this->id_citation]);
			$qm->query($this->db, 'cit_del_5', self::sql_del_cit_5, [$this->id_citation]);
			$qm->query($this->db, 'cit_del_6', self::sql_del_cit_6, [$this->id_citation]);
			$qm->query($this->db, 'cit_del_8', self::sql_del_cit_8, [$this->id_citation]);
			$qm->query($this->db, 'cit_del_9', self::sql_del_cit_9, [$this->id_citation]);
			$qm->query($this->db, 'cit_del_7', self::sql_del_cit_7, [$this->id_citation]);
		} catch (\Exception $e) {
			bobs_log(sprintf("ERROR : can't delete citation %d", $this->id_citation));
			bobs_log($e->getMessage());
			throw $e;
		}
		bobs_log(sprintf("citation %d dropped", $this->id_citation));
		return true;
	}

	public function get_espece() {
	    return get_espece($this->db, $this->id_espece);
	}

	public static function get_gender_list() {
		return [
			' '  => ['val' => ' ',  'lib' => 'inconnu', 'prop' => false],
			'?'  => ['val' => '?',  'lib' => 'inconnu', 'prop' => true],
			'C'  => ['val' => 'C',  'lib' => 'couple',  'prop' => true],
			'F'  => ['val' => 'F',  'lib' => 'femelle', 'prop' => true],
			'F?' => ['val' => 'F?', 'lib' => 'femelle', 'prop' => false],
			'FI' =>	['val' => 'FI', 'lib' => 'femelle ou immature', 'prop' => true],
			'I'  =>	['val' => 'I',  'lib' => 'immature','prop' => true],
			'M'  => ['val' => 'M',  'lib' => 'mâle',    'prop' => true],
			'M?' => ['val' => 'M?', 'lib' => 'mâle *',  'prop' => false],
			'MF' => ['val' => 'MF', 'lib' => 'mâle et femelle', 'prop' => true],
			'N'  => ['val' => 'N',  'lib' => 'N ?',     'prop' => false]
		];
	}

	public static function get_age_list() {
		return array(
			'?'   => ['val' => '?',   'lib' => 'inconnu', 'prop' => true, 'classes' => 'ABROMIPL'],
			'1A'  => ['val' => '1A',  'lib' => 'un an', 'prop' => true, 'classes' => 'OM'],
			'+1A' => ['val' => '+1A', 'lib' => 'plus de un an', 'prop' => true, 'classes' => 'OM'],
			'2A'  => ['val' => '2A',  'lib' => 'deux ans', 'prop' => true, 'classes' => 'OM'],
			'+2A' => ['val' => '+2A', 'lib' => 'plus de deux ans', 'prop' => true, 'classes' => 'OM'],
			'3A'  => ['val' => '3A',  'lib' => 'trois ans', 'prop' => true, 'classes' => 'OM'],
			'4A'  => ['val' => '4A',  'lib' => 'quatre ans', 'prop' => true, 'classes' => 'OM'],
			'5A'  => ['val' => '5A',  'lib' => 'cinq ans', 'prop' => true, 'classes' => 'OM'],
			'AD'  => ['val' => 'AD',  'lib' => 'adulte', 'prop' => true, 'classes' => 'BROMI'],
			'AD&' => ['val' => 'AD&', 'lib' => 'adulte et immature', 'prop' => true, 'classes' => 'I'],
			'ADP' => ['val' => 'ADP', 'lib' => 'adulte et pulli', 'prop' => true, 'classes' => 'O'],
			'EX'  => ['val' => 'EX',  'lib' => 'exuvie', 'prop' => true, 'classes' => 'I'],
			'IMM' => ['val' => 'IMM', 'lib' => 'immature', 'prop' => true, 'classes' => 'OI'],
			'EME' => ['val' => 'EM',  'lib' => 'émergence', 'prop' => true, 'classes' => 'I'],
			'JUV' => ['val' => 'JUV', 'lib' => 'juvénile',	'prop' => true, 'classes' => 'BORI'],
			'LA'  => ['val' => 'LA',  'lib' => 'larve', 'prop' => true, 'classes' => 'IBR'],
			'P'   => ['val' => 'P',   'lib' => 'ponte', 'prop' => true, 'classes' => 'BR'],
			'PUL' => ['val' => 'PUL', 'lib' => 'poussin', 'prop' => true, 'classes' => 'O'],
			'VOL' => ['val' => 'VOL', 'lib' => 'volant', 'prop' => true, 'classes' => 'OI'],
			'CHE' => ['val' => 'CHE', 'lib' => 'chenille', 'prop' => true, 'classes' => 'I'],
			'CRY' => ['val' => 'CRY', 'lib' => 'chrysalide', 'prop' => true, 'classes' => 'I']
		);
	}

	/**
	 *
	 * @return bobs_observation objet observation associé
	 * @deprecated
	 */
	public function get_observation() {
		return $this->observation();
	}

	/**
	 *
	 * @return bobs_observation objet observation associé
	 */
	public function observation() {
	    return get_observation($this->db, $this->id_observation);
	}

	/**
	 * Nidification
	 */
	public function nicheur() {
	    $tags = explode(',', BOBS_TAGS_NIDIF);
	    foreach ($tags as $tag)
		if ($this->a_tag($tags))
			return true;
	    return false;
	}

	public function get_ligne_csv($opts=false) {
		$obs = $this->get_observation();
		$espace = $obs->get_espace();
		$espece = $this->get_espece();
		$commune = '';
		$departement = '';
		foreach ($espace->get_communes() as $c) {
			$commune .= $c->nom.',';
			$departement = sprintf("%02d", $c->dept);
		}
		$commune = trim($commune, ',');

		$observateurs = '';

		foreach ($obs->get_observateurs() as $observ)
			$observateurs .= trim(trim($observ['nom']).' '.trim($observ['prenom'])).', ';

		$observateurs = trim($observateurs, ', ');

		$s = sprintf('"%s";"%s";"%d";"%d";"%s";"%d";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%d";"%d";"%d";"%s";"%s";"%s"',
			$obs->date_observation,
			$obs->heure_observation,
			$obs->precision_date,
			$this->id_espece,
			csv_clean_string($espece->espece,'"'),
			$this->indice_qualite,
			csv_clean_string($espece->nom_s,'"'),
			csv_clean_string($espece->nom_f,'"'),
			csv_clean_string($departement,'"'),
			csv_clean_string($commune,'"'),
			csv_clean_string($this->get_str_tags(),'"'),
			$this->nb,
			$this->nb_min,
			$this->nb_max,
			$this->sexe,
			csv_clean_string($observateurs,'"'),
			$this->id_citation,
			$this->id_observation,
			$espece->taxref_inpn_especes,
			$this->age,
			csv_clean_string($this->commentaire,'"'),
			csv_clean_string($espace->__toString(),'"')
		);
		if (is_array($opts)) {
			if (array_key_exists(bobs_selection::csv_opt_toponymes, $opts)) {
				switch (get_class($espace)) {
					case 'bobs_espace_point':
					case 'bobs_espace_chiro':
						if ($espace->dans_zone_urbaine_dense()) {
							$s = sprintf('%s;"%s;"', $s, 'zone urbaine');
						} else {
							$topos = bobs_espace_toponyme::a_proximite_espace($espace);

							if (count($topos) > 0) {
								$s = sprintf(';%s;"%s"',$s,$topos[0]->nom);
							} else {
								$s = sprintf(';%s;"%s"',$s,'N.D.');
							}
						}
						break;
					default:
						$classe = get_class($espace);
						$s = $s.';"'.$classe.'"'; break;
				}
			}
			if (array_key_exists(bobs_selection::csv_opt_xy, $opts)) {
				if (is_subclass_of($espace, 'bobs_espace_point') or (get_class($espace) == 'bobs_espace_point')) {
					$s .= sprintf(';"%F";"%F"', $espace->get_x(), $espace->get_y());
				} else {
					$s .= ';"nd";"nd"';
				}
			}
			if (array_key_exists(bobs_selection::csv_opt_enquete, $opts)) {
				if (!empty($opts[bobs_selection::csv_opt_enquete])) {
					list($enquete,$version) = explode(".",$opts[bobs_selection::csv_opt_enquete]);
					$enq_ver = clicnat_enquete_version::getInstance(get_db(), $enquete, $version);
					$champs_enquete =  $enq_ver->liste_champs_depuis_def();
					$resultats_enquete = $enq_ver->citation_reponses($this);
					if ($resultats_enquete) {
						foreach ($champs_enquete as $_c) {
							$s .= sprintf(";\"%s\"", csv_clean_string($resultats_enquete[$_c],'"'));
						}
					} else {
						foreach ($champs_enquete as $_c) {
							$s .= ";";
						}
					}
				}

			}
		}
		return "$s\n";
	}

	public static function get_ligne_csv_titre($opts=false) {
		$t = [
			'date_observation',
			'heure_observation',
			'precision_date',
			'id_espece',
			'code_espece',
			'ind_q_identif',
			'nom_s',
			'nom_f',
			'departement',
			'communes',
			'tags',
			'nb',
			'nb_min',
			'nb_max',
			'sexe',
			'observateurs',
			'id_citation',
			'id_observation',
			'cd_nom_mnhn',
			'age',
			'commentaire',
			'lieu'
		];
		if (is_array($opts)) {
			if (array_key_exists(bobs_selection::csv_opt_toponymes, $opts)) {
				$t[] = 'toponyme';
			}
			if (array_key_exists(bobs_selection::csv_opt_xy, $opts)) {
				$t[] = 'x';
				$t[] = 'y';
			}
			if (array_key_exists(bobs_selection::csv_opt_enquete, $opts)) {
				if (!empty($opts[bobs_selection::csv_opt_enquete])) {
					list($enquete,$version) = explode(".",$opts[bobs_selection::csv_opt_enquete]);
					$enq_ver = clicnat_enquete_version::getInstance(get_db(), $enquete, $version);
					$champs_enquete =  $enq_ver->liste_champs_depuis_def();
					bobs_log("csv ajouter champs_enquete ".count($champs_enquete)." champs");
					$t = array_merge($t,$champs_enquete);
				}
			}
		}
		return $t;
	}

	public function get_ligne_array() {
		$obs = $this->get_observation();
		$espace = $obs->get_espace();
		$espece = $this->get_espece();
		$commune ='';
		$departement = '';
		$insee = '';

		foreach ($espace->get_communes() as $c) {
	    		if ($c) {
				$commune .= $c->nom.',';
				$insee .= sprintf("%d%03d", $c->get_dept(), $c->code_insee);
				$departement = $c->get_dept();
	    		}
		}

		$commune = trim($commune, ',');
		$insee = trim($insee, ',');

		$observateurs = '';
		foreach ($obs->get_observateurs() as $observ)
			$observateurs .= trim(trim($observ['nom']).' '.trim($observ['prenom'])).', ';

		$observateurs = trim($observateurs, ', ');
		list($y, $m, $d) = explode('-', $obs->date_observation);

		return [
			$d,
			$m,
			$y,
			$this->id_espece,
			$espece->nom_s,
			$espece->nom_f,
			$departement,
			$commune,
			$insee,
			$this->get_str_tags(),
			$this->nb,
			$this->sexe,
			$observateurs,
			$this->id_citation,
			$this->id_observation,
			$espece->taxref_inpn_especes,
			$this->age,
			$this->commentaire,
			$espace->__toString()
		];
	}

	public static function get_ligne_array_titre() {
		return [
			'jour',
			'mois',
			'annee',
			'id_espece',
			'nom_s',
			'nom_f',
			'departement',
			'communes',
			'codes_insee',
			'tags',
			'nb',
			'sexe',
			'observateurs',
			'id_citation',
			'id_observation',
			'cd_nom_mnhn',
			'age',
			'commentaire',
			'lieu'
		];
	}

	public function get_str_tags() {
	    $r = '';
	    $this->get_tags();
	    foreach ($this->tags as $tag) {
			$r .= $tag['lib'].'/';
	    }

	    return trim($r, '/');
	}

	const sql_n_citations = 'select count(*) as n from citations';

	/**
	 * @brief retourne le nombre total de citations dans la base de données
	 * @param ressource $db
	 * @return integer
	 */
	public static function nombre_citations($db) {
		$sql = "select count(*) as n from citations";
		$q = bobs_qm()->query($db, "nb_citations_t", self::sql_n_citations, []);
		$r = self::fetch($q);
		return $r['n'];
	}

	public function ajouter_dans_structure($structure,$id_utilisateur) {
		self::cls($structure, self::except_si_vide);
		$tag = bobs_tags::by_ref($this->db, TAG_STRUCTURE);
		$this->ajoute_tag($tag->id_tag, null, $structure, $id_utilisateur);
	}

	/**
	 * @brief associe la citation à une étude
	 * @param $etude nom de l'étude
	 * @param $id_utilisateur utilisateur qui fait la modification
	 */
	public function ajouter_dans_protocole($etude, $id_utilisateur) {
		self::cls($etude, self::except_si_vide);
		$tag = bobs_tags::by_ref($this->db, TAG_PROTOCOLE);
		$this->ajoute_tag($tag->id_tag, null, $etude, $id_utilisateur);
	}

	/**
	 * @brief enlève le tag en attente de validation
	 * @param $id_utilisateur utilisateur qui fait la modification
	 */
	public function validation($id_utilisateur) {
		$tag = bobs_tags::by_ref($this->db, TAG_ATTENTE_VALIDATION);
		if ($this->a_tag($tag->id_tag))
			$this->supprime_tag($tag->id_tag, $id_utilisateur);
	}

	/**
	 * @brief remettre en attente de validation
	 * @param $id_utilisateur utilisateur qui fait la modification
	 */
	public function remettre_en_attente($id_utilisateur) {
		if ($this->en_attente_de_validation())
			return false;

		if ($this->invalide())
			$this->revalider($id_utilisateur);

		$tag = bobs_tags::by_ref($this->db, TAG_ATTENTE_VALIDATION);
		$this->ajoute_tag($tag->id_tag, null, null, $id_utilisateur);

		return true;
	}

	/**
	 * @brief invalide l'observation
	 * @param $id_utilisateur utilisateur qui fait la modification
	 */
	public function invalider($id_utilisateur) {
		// Enlever en attente de validation
		$tag = bobs_tags::by_ref($this->db, TAG_ATTENTE_VALIDATION);
		if ($this->a_tag($tag->id_tag))
			$this->supprime_tag($tag->id_tag, $id_utilisateur);

		$tag = bobs_tags::by_ref($this->db, TAG_INVALIDE);

		if ($this->a_tag($tag->id_tag))
			return false;

		$this->ajoute_tag($tag->id_tag, null, null, $id_utilisateur);
	}

	/**
	 * @brief Revalider une citation
	 * @param $id_utilisateur identifiant de la personne qui fait la modification
	 * @return bool
	 *
	 * Supprime l'étiquette TAG_INVALIDE si elle existe.
	 */
	public function revalider($id_utilisateur) {
		$tag = bobs_tags::by_ref($this->db, TAG_INVALIDE);

		if ($this->a_tag($tag->id_tag))
			return $this->supprime_tag($tag->id_tag, $id_utilisateur);

		return false;
	}

	public function proposer_homologation($id_utilisateur) {
		$tag = bobs_tags::by_ref($this->db, TAG_ATTENTE_VALIDATION);
		if ($this->a_tag($tag->id_tag)) {
			$this->supprime_tag($tag->id_tag, $id_utilisateur);
		} else {
			return false;
		}
		$tag = bobs_tags::by_ref($this->db, TAG_HOMOLOGATION_NECESSAIRE);

		$this->ajoute_tag($tag->id_tag, null, null, $id_utilisateur);
	}

	const sql_preval_score = 'select coalesce(array_length(validation_avis_positif,1),0)-coalesce(array_length(validation_avis_negatif,1),0) as score from citations where id_citation=$1';

	/**
	 * @brief Score de la prévalidation
	 * @return int
	 *
	 * Retourne le nombre d'éléments dans la colonne avis_positif retranché du nombre d'éléments dans la colonne avis négatif
	 */
	public function prevalidation_score() {
		$q = bobs_qm()->query($this->db, 'preval_score', self::sql_preval_score, [$this->id_citation]);
		$r = self::fetch($q);
		return (int)$r['score'];
	}

	const sql_preval_lu_pos = "select utilisateur.* from citations,utilisateur where id_citation=$1 and id_utilisateur = any (validation_avis_positif)";
	const sql_preval_lu_neg = "select utilisateur.* from citations,utilisateur where id_citation=$1 and id_utilisateur = any (validation_avis_negatif)";
	const sql_preval_lu_nul = "select utilisateur.* from citations,utilisateur where id_citation=$1 and id_utilisateur = any (validation_sans_avis)";

	public function prevalidation_validateurs_positifs() {
		$q = bobs_qm()->query($this->db, 'preval_lu_pos', self::sql_preval_lu_pos, [$this->id_citation]);
		$ids = array_column(self::fetch_all($q), 'id_utilisateur');
		return new clicnat_iterateur_utilisateurs($this->db, $ids);
	}

	public function prevalidation_validateurs_negatifs() {
		$q = bobs_qm()->query($this->db, 'preval_lu_neg', self::sql_preval_lu_neg, [$this->id_citation]);
		$ids = array_column(self::fetch_all($q), 'id_utilisateur');
		return new clicnat_iterateur_utilisateurs($this->db, $ids);
	}

	public function prevalidation_validateurs_sans_avis() {
		$q = bobs_qm()->query($this->db, 'preval_lu_nul', self::sql_preval_lu_nul, [$this->id_citation]);
		$ids = array_column(self::fetch_all($q), 'id_utilisateur');
		return new clicnat_iterateur_utilisateurs($this->db, $ids);
	}


	const sql_preval_ajout_pos = 'update citations set validation_avis_positif=array_append(validation_avis_positif, $1) where id_citation=$2';
	const sql_preval_ajout_neg = 'update citations set validation_avis_negatif=array_append(validation_avis_negatif, $1) where id_citation=$2';
	const sql_preval_ajout_nul = 'update citations set validation_sans_avis=array_append(validation_sans_avis, $1) where id_citation=$2';
	const sql_preval_deja_fait = 'select
		coalesce($1 = any (validation_avis_negatif),false)
		or coalesce($2 = any(validation_avis_positif),false)
		or coalesce($3 = any(validation_sans_avis),false) as deja_fait
		from citations where id_citation=$4';

	public function deja_evalue($utilisateur){
		$q = bobs_qm()->query($this->db, 'preval_test_fait', self::sql_preval_deja_fait, [
			$utilisateur->id_utilisateur,
			$utilisateur->id_utilisateur,
			$utilisateur->id_utilisateur,
			$this->id_citation
		]);
		$r = self::fetch($q);
		return $r['deja_fait'];
	}

	/**
	 * @brief enregistrer une évaluation
	 * @param $utilisateur instance de clicnat_utilisateur ou id_utilisateur
	 * @param $points 1 pour valider, -1 pour invalider, 0 pour ne se prononce pas
	 */
	public function prevalidation_ajoute_evaluation($utilisateur, $points) {
		if ($this->deja_evalue($utilisateur) == 't') {
			throw new \Exception('Citation déjà évalué par cet utilisateur');
		}
		if (abs($points) > 1) {
			throw new \Exception('$points doit être -1, 1 ou 0');
		}
		$observateurs = $this->get_observation()->observateurs();
		if ($observateurs->in_array($utilisateur->id_utilisateur)){
			throw new \Exception('Citation réalisée par cet utilisateur');
		}
		$score = $this->prevalidation_score();
		if (abs($score) >= 2) {
			throw new \Exception('Citation déja évaluée (contacter un administrateur)');
		}
		$reseau = clicnat2_reseau::get_reseau_espece($this->db, $this->id_espece);
		if (!$reseau)
			throw new \Exception('Pas trouvé le réseau de l\'espèce');
		$validateur =  $reseau->est_validateur($utilisateur->id_utilisateur);
		if (!$validateur){
			throw new \Exception('Utilisateur non validateur pour cette espèce.');
		}
		switch ($points) {
			case 1:
				return bobs_qm()->query($this->db, 'preval_ajout_pos', self::sql_preval_ajout_pos, [$utilisateur->id_utilisateur, $this->id_citation]);
			case 0:
				return bobs_qm()->query($this->db, 'preval_ajout_nul', self::sql_preval_ajout_nul, [$utilisateur->id_utilisateur, $this->id_citation]);
			case -1:
				return bobs_qm()->query($this->db, 'preval_ajout_neg', self::sql_preval_ajout_neg, [$utilisateur->id_utilisateur, $this->id_citation]);
		}
	}

	/**
	 * @brief applique le résultat de la prévalidation
	 *
	 * si le score >= 2 valider la citation si le score est <= 2 invalider
	 */
	public function prevalidation_applique() {
		$score = $this->prevalidation_score();
		if ($score >= 2 && $this->en_attente_de_validation()) {
			$this->validation(0);
		} else if ($score <= -2 && $this->en_attente_de_validation()) {
			$this->invalider(0);
		}
	}

	/**
	 * Est en attente de validation ?
	 * @return boolean
	 */
	public function en_attente_de_validation() {
		$tag = bobs_tags::by_ref($this->db, TAG_ATTENTE_VALIDATION);
		return $this->a_tag($tag->id_tag);
	}

	/**
	 * Est invalide ?
	 * @return boolean
	 */
	public function invalide() {
		$tag = bobs_tags::by_ref($this->db, TAG_INVALIDE);
		return $this->a_tag($tag->id_tag);
	}

	const sql_doc_associer = 'insert into citations_documents (id_citation,document_ref) values ($1,$2)';
	const sql_doc_suppr = 'delete from citations_documents where id_citation=$1 and document_ref=$2';
	const sql_doc_liste = 'select * from citations_documents where id_citation=$1';

	public function document_associer($doc_id) {
		return bobs_qm()->query($this->db, 'cits_assoc_doc', self::sql_doc_associer, [$this->id_citation, $doc_id]);
	}

	public function document_detacher($doc_id) {
		return bobs_qm()->query($this->db, 'cits_assoc_detach', self::sql_doc_suppr, [$this->id_citation, $doc_id]);
	}

	public function documents_liste() {
		$q = bobs_qm()->query($this->db, 'cits_assoc_doc_liste', self::sql_doc_liste, [$this->id_citation]);
		$tr = [];
		while ($r = self::fetch($q)) {
			$tr[] = new bobs_document($r['document_ref']);
		}
		return $tr;
	}

	const chemin_citation_public = '/var/cache/bobs/citations_public/%d';

	public function rendre_public() {
		$f = fopen(sprintf(self::chemin_citation_public, $this->id_citation), 'w');
		fwrite($f, '1');
		fclose($f);
	}

	public function acces_public() {
		return file_exists(sprintf(self::chemin_citation_public, $this->id_citation));
	}

	public function enlever_acces_public() {
		if ($this->acces_public())
			unlink(sprintf(self::chemin_citation_public, $this->id_citation));
	}

	public function autorise_validation($id_utilisateur) {
		$u = get_utilisateur($this->db, $id_utilisateur);

		// quelqu'un qui a accès au qg peut faire des modifs
		if ($u->acces_qg_ok())
			return true;

		// si tete de reseau
		$reseau = $this->get_espece()->get_reseau();
		if ($reseau)
			return $reseau->est_coordinateur($u->id_utilisateur);

		return false;
	}

	public function autorise_modification($id_utilisateur) {
		$u = get_utilisateur($this->db, $id_utilisateur);

		// quelqu'un qui a accès au qg peut faire des modifs
		if ($u->acces_qg_ok())
			return true;

		// si tete de reseau
		$reseau = $this->get_espece()->get_reseau();
		if ($reseau)
			if ($reseau->est_coordinateur($u->id_utilisateur))
				return true;

		$obs = $this->get_observation();

		// pour ce qui suit l'observation doit être attente de validation
		if (!$this->en_attente_de_validation()) {
			return false;
		}

		// celui qui a saisit peut aussi modifier
		if ($u->id_utilisateur == $obs->id_utilisateur)
			return true;

		// si on est dans la liste des observateurs ont peut aussi
		$observateurs = $obs->get_observateurs();
		if (count($observateurs) > 0) {
			foreach ($observateurs as $observ) {
				if ($observ['id_utilisateur'] == $u->id_utilisateur) {
					return true;
				}
			}
		}

		// donc c'est non
		return false;
	}

	const sql_ref_import = 'select id_citation from citations where ref_import=trim($1)';

	/**
	 * @brief trouve les citations correspondant à une référence d'import
	 * @param $db ressource pour accéder à la base de données
	 * @param $ref référence de l'import
	 * @return clicnat_iterateur_citations
	 */
	public static function by_ref_import($db, $ref) {
		$q = bobs_qm()->query($db, 'cit_by_ref_import', self::sql_ref_import, [$ref]);
		$ids = array();
		while ($r = self::fetch($q)) {
			$ids[] = $r['id_citation'];
		}
		return new clicnat_iterateur_citations($db, $ids);
	}

	const sql_cit_by_guid = 'select * from citations where guid=$1';
	/**
	 * @brief retourne une citation à partir d'un guid
	 * @param $db ressource pour accéder à la base de données
	 * @param $guid le guid de la citation
	 * @returnn bobs_citation
	 */
	public static function by_guid($db, $guid) {
		$q = bobs_qm()->query($db, 'cit_by_guid', self::sql_cit_by_guid, [$guid]);
		$r = self::fetch($q);
		if (!$r) return false;
		switch (get_called_class()) {
			case 'clicnat_citation_export_sinp':
				return new clicnat_citation_export_sinp($db, $r);
			default:
				return get_citation($db, $r);
		}
	}

	const sql_s_date_creation = "select * from creations_citations_par_date
					where date_creation
						between now()::date - interval '%d day' and now()::date";

	/**
	 * @brief nombre de citations par jour
	 * @param $db ressource db
	 * @param $n nombre de jours
	 * @return int
	 */
	public static function stats_creation_par_jour($db, $n) {
		self::cli($n, self::except_si_vide);
		$q = bobs_qm()->query($db, "s_cit_par_date_crea$n", sprintf(self::sql_s_date_creation, $n),[]);
		return self::fetch_all($q);
	}

	const sql_s_date_citation = "select * from nombre_citations_par_date
				where date_observation between now()::date-interval '%d day' and now()::date";

	/**
	 * @brief nombre de citations crées par jour
	 * @param $db ressource db
	 * @param $n nombre de jours depuis aujourd'hui
	 * @return int
	 */
	public static function stats_citations_par_jour($db, $n) {
		self::cli($n, self::except_si_vide);
		$q = bobs_qm()->query($db, "s_cit_par_date_obs$n", sprintf(self::sql_s_date_citation, $n), []);
		return self::fetch_all($q);
	}

	public function validation_tests() {
		// creation de la liste des tests
		$tests = array();
		foreach (get_declared_classes() as $classe) {
			if (is_subclass_of($classe, 'clicnat_validation_test')) {
				$tests[] = new $classe($this);
			}
		}

		$resultats = array();
		foreach ($tests as $test) {
			$resultats[get_class($test)] = $test->evaluer();
		}

		return $resultats;
	}

	public function validation_test() {
		// creation de la liste des tests
		$tests = array();
		foreach (get_declared_classes() as $classe) {
			if (is_subclass_of($classe, 'clicnat_validation_test')) {
				$tests[] = new $classe($this);
			}
		}

		$resultats = array();
		foreach ($tests as $test) {
			$r = $test->evaluer();
			if (!$r['passe']) {
				return false;
			}
		}

		return true;
	}
}
