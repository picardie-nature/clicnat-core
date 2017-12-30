<?php
namespace Picnat\Clicnat;

class clicnat_sortie extends bobs_element_commentaire {
	protected $id_sortie;
	protected $id_utilisateur_propose;
	protected $date_proposition;
	protected $date_maj;
	protected $orga_nom;
	protected $orga_prenom;
	protected $adresse;
	protected $tel;
	protected $portable;
	protected $mail;
	protected $description;
	protected $id_espace_point;
	protected $description_lieu;
	protected $duree_heure;
	protected $gestion_picnat;
	protected $id_sortie_type;
	protected $id_sortie_public;
	protected $accessible_mobilite_reduite;
	protected $accessible_deficient_auditif;
	protected $accessible_deficient_visuel;
	protected $id_sortie_cadre;
	protected $partenariat; // FIXME doit pas exister en fait
	protected $id_sortie_pole;
	protected $structure;
	protected $validation_externe;
	protected $notes_admin;
	protected $nom;
	protected $id_sortie_reseau;
	protected $materiel_autre;
	protected $db;

	function __construct($db, $id) {
		parent::__construct($db, 'sortie', 'id_sortie', $id);
		$this->champ_date_maj = 'date_maj';
		if (!isset($this->id_sortie))
			throw new Exception('pas trouvé');
	}

	const sql_associer_document = 'insert into sortie_document (doc_id,id_sortie) values ($1,$2)';
	const sql_doc_liste = 'select * from sortie_document where id_sortie=$1';

	public function associer_document($doc_id) {
		return bobs_qm()->query($this->db, 'sortie_add_doc', self::sql_associer_document, [$doc_id, $this->id_sortie]);
	}

	public function documents_liste() {
		$q = bobs_qm()->query($this->db, 'sortie_doc_liste', self::sql_doc_liste, [$this->id_sortie]);
		$tr = [];
		while ($r = self::fetch($q)) {
			$doc = bobs_document::getInstance($r['doc_id']);
			if ($doc)
				$tr[] = $doc;
		}
		return $tr;
	}

	/**
	 * @brief Efface le pointage pour pouvoir le refaire
	 */
	public function annuler_localisation() {
		$this->update_field("id_espace_point", null);
	}

	const sql_delete = 'delete from sortie where id_sortie=$1';
	public function supprimer() {
		$this->materiel_vide();
		return bobs_qm()->query($this->db, 'sortie_suppr', self::sql_delete, array($this->id_sortie));
	}

	/**
	 * @brief Point associé à la sortie
	 * @see bobs_espace_point
	 * @return bob_espace_point
	 */
	public function point() {
		return new clicnat_sortie_point($this->db, $this->id_espace_point);
	}

	const sql_sorties_toutes = 'select * from sortie';

	public static function toutes($db) {
		$q = bobs_qm()->query($db, 'sortie_l_toutes', self::sql_sorties_toutes, array());
		$t = array();
		while ($r = self::fetch($q)) {
			$t[] = new clicnat_sortie($db, $r);
		}
		return $t;
	}

	const sql_dates = 'select * from sortie_date where id_sortie=$1 order by date_sortie desc';
	const sql_premiere_date = 'SELECT * FROM sortie_date WHERE id_sortie=$1 ORDER BY date_sortie ASC LIMIT 1';
	const sql_derniere_date = 'SELECT * FROM sortie_date WHERE id_sortie=$1 ORDER BY date_sortie DESC LIMIT 1';

	public function dates($inverse=false) {
		$q = bobs_qm()->query($this->db, 'sortie_l_dates', self::sql_dates, array($this->id_sortie));
		$t = array();
		while ($r = self::fetch($q)) {
			$t[] = new clicnat_sortie_date($this->db, $r);
		}
		if ($inverse)
			return array_reverse($t);
		return $t;
	}

	public function premiere_date() {
		$q = bobs_qm()->query($this->db, 'sortie_l_premiere_date', self::sql_premiere_date, array($this->id_sortie));
		$r = self::fetch($q);
		if (is_array ($r)) {
			return new clicnat_sortie_date($this->db, $r);
		} else {
			return new clicnat_sortie_date($this->db, array('date_sortie', '0000-00-00'));
		}
	}

	public function derniere_date() {
		$q = bobs_qm()->query($this->db, 'sortie_l_derniere_date', self::sql_derniere_date, array($this->id_sortie));
		$r = self::fetch($q);
		if (is_array ($r)) {
			return new clicnat_sortie_date($this->db, $r);
		} else {
			return new clicnat_sortie_date($this->db, array('date_sortie', '0000-00-00'));
		}
	}

	const sql_date_sortie_ts = 'select * from sortie_date where id_sortie=$1 and extract(epoch from date_sortie::timestamptz)=$2';

	public function date_par_timestamp($ts) {
		$q = bobs_qm()->query($this->db, 'sortie_date_timest', self::sql_date_sortie_ts, array($this->id_sortie, (int)$ts));
		$r = self::fetch($q);
		return new clicnat_sortie_date($this->db, $r);
	}

	public static function nouvelle($db, $id_utilisateur_propose) {
		$new_id = self::nextval($db, 'sortie_id_sortie_seq');
		$data = array(
			'date_proposition' => strftime("%Y-%m-%d", mktime()),
			'id_utilisateur_propose' => (int)$id_utilisateur_propose,
			'id_sortie' => $new_id

		);
		self::insert($db, 'sortie', $data);
		return new clicnat_sortie($db, $new_id);
	}

	public function utilisateur_propose() {
		return get_utilisateur($this->db, $this->id_utilisateur_propose);
	}

	public function __get($prop) {
		switch ($prop) {
			case 'accessible_mobilite_reduite':
				return $this->accessible_mobilite_reduite == 't';
			case 'accessible_deficient_auditif':
				return $this->accessible_deficient_auditif == 't';
			case 'accessible_deficient_visuel':
				return $this->accessible_deficient_visuel == 't';
			case 'gestion_picnat':
				return $this->gestion_picnat == 't';
			case 'validation_externe':
				return $this->validation_externe == 't';
			case 'nom_lib':
				return empty($this->nom)?'sortie sans nom':$this->nom;
			case 'duree_lib':
				return sprintf("%dh%02d", (int)$this->duree_heure, ($this->duree_heure - ((int)$this->duree_heure))*60);
			default:
				return $this->$prop;
		}
	}

	const sql_types_sorties = 'select * from sortie_type order by lib';
	const sql_type_sortie = 'select * from sortie_type where id_sortie_type=$1';

	public function type_sortie() {
		$q = bobs_qm()->query($this->db, 'l_type_sortie', self::sql_type_sortie, array($this->id_sortie_type));
		$r = self::fetch($q);
		return $r['lib'];
	}

	public static function types_sortie() {
		$q = bobs_qm()->query(get_db(), 'l_types_sorties', self::sql_types_sorties, array());
		return self::fetch_all($q);
	}

	const sql_publics_sorties = 'select * from sortie_public order by lib';
	const sql_public_sortie = 'select * from sortie_public where id_sortie_public=$1';

	public function public_sortie() {
		$q = bobs_qm()->query($this->db, 'l_pubic_sortie', self::sql_public_sortie, array($this->id_sortie_public));
		$r = self::fetch($q);
		return $r['lib'];
	}

	public static function publics_sortie() {
		$q = bobs_qm()->query(get_db(), 'l_publics_sorties', self::sql_publics_sorties, array());
		return self::fetch_all($q);
	}

	const sql_poles_sorties = 'select * from sortie_pole order by lib';
	const sql_pole_sortie = 'select * from sortie_pole where id_sortie_pole=$1';

	public function pole_sortie() {
		$q = bobs_qm()->query($this->db, 'l_pole_sortie', self::sql_pole_sortie, array($this->id_sortie_pole));
		$r = self::fetch($q);
		return $r['lib'];
	}

	public static function poles_sortie() {
		$q = bobs_qm()->query(get_db(), 'l_poles_sorties', self::sql_poles_sorties, array());
		return self::fetch_all($q);
	}

	const sql_cadres_sorties = 'select * from sortie_cadre order by id_sortie_cadre';
	const sql_cadre_sortie = 'select * from sortie_cadre where id_sortie_cadre=$1';

	public function cadre_sortie() {
		$q = bobs_qm()->query($this->db, 'l_cadre_sortie', self::sql_cadre_sortie, array($this->id_sortie_cadre));
		$r = self::fetch($q);
		return $r['lib'];
	}

	public static function cadres_sortie() {
		$q = bobs_qm()->query(get_db(), 'l_cadres_sorties', self::sql_cadres_sorties, array());
		return self::fetch_all($q);
	}

	const sql_materiels_sorties = 'select * from sortie_materiel order by id_sortie_materiel';
	const sql_liste_materiels = 'select sortie_materiel.*,id_sortie from sortie_materiel left join sortie_sortie_materiel on sortie_materiel.id_sortie_materiel=sortie_sortie_materiel.id_sortie_materiel and sortie_sortie_materiel.id_sortie=$1';

	public static function materiels_sortie() {
		$q = bobs_qm()->query(get_db(), 'l_materiels_sorties', self::sql_materiels_sorties, array());
		return self::fetch_all($q);
	}

	/**
	 * @brief liste du matériel disponnible
	 * @return un tableau associatif avec les clés : lib,id_sortie_materiel,a_prevoir (0/1)
	 */
	public function materiels() {
		$q = bobs_qm()->query($this->db, 'l_materiel', self::sql_liste_materiels, array($this->id_sortie));
		$r = self::fetch_all($q);
		$ret = array();
		foreach ($r as $mat) {
			$ret[] = array('lib' => $mat['lib'], 'id_sortie_materiel' => $mat['id_sortie_materiel'], 'a_prevoir' => empty($mat['id_sortie'])?0:1);
		}
		return $ret;
	}

	const sql_sortie_materiel_select = 'select sortie_materiel.* from sortie_materiel,sortie_sortie_materiel where id_sortie=$1 and sortie_materiel.id_sortie_materiel=sortie_sortie_materiel.id_sortie_materiel';
	const sql_sortie_materiel_insert = 'insert into sortie_sortie_materiel (id_sortie,id_sortie_materiel) values ($1,$2)';
	const sql_sortie_materiel_vide = 'delete from sortie_sortie_materiel where id_sortie=$1';

	public function materiel_vide() {
		return bobs_qm()->query($this->db, 'l_del_materiel', self::sql_sortie_materiel_vide, array($this->id_sortie));
	}

	public function materiel_ajoute($id_sortie_materiel) {
		return bobs_qm()->query($this->db, 'l_ins_materiel', self::sql_sortie_materiel_insert, array($this->id_sortie, $id_sortie_materiel));
	}

	/**
	 * @brief liste du matériel associé
	 */
	public function sortie_materiels() {
		$q = bobs_qm()->query($this->db, 'l_materiel', self::sql_liste_materiels, array($this->id_sortie));
		return self::fetch_all($q);
	}

	const sql_ajoute_date = 'insert into sortie_date (id_sortie,date_sortie,etat) values ($1,$2,1)';
	const sql_get_date = 'select * from sortie_date where id_sortie=$1 and date_sortie=$2';

	public function ajoute_date($date) {
		$q = bobs_qm()->query($this->db, 'ajoute_date_sortie', self::sql_ajoute_date, array($this->id_sortie, $date));
		$q = bobs_qm()->query($this->db, 'select_date_sortie', self::sql_get_date, array($this->id_sortie, $date));
		return new clicnat_sortie_date($this->db, self::fetch($q));
	}

	public function __toString() {
		return empty($this->nom)?'sans nom':$this->nom;
	}

	const sql_liste_reseau = "select * from sortie_reseau order by lib";
	const sql_lib_reseau = "select lib from sortie_reseau where id_sortie_reseau=$1";

	/**
	 * @brief liste des réseaux disponnible
	 * @return un tableau associatif avec les clés : lib,id_sortie_reseau
	 */
	public static function reseaux_sortie() {
		$q = bobs_qm()->query(get_db(), 'l_reseau', self::sql_liste_reseau, array());
		return self::fetch_all($q);
	}

	public function reseau_sortie() {
		if (!empty($this->id_sortie_reseau)) {
			$q = bobs_qm()->query($this->db, 'lib_reseau_sortie', self::sql_lib_reseau, array($this->id_sortie_reseau));
			$r = self::fetch($q);
			return $r['lib'];
		}
		return "";
	}

	/**
	 * @brief liste des colonnes pour les exports CSV
	 *
	 * et comme on peut pas avoir un array comme constante...
	 */
	public function sorties_cols() {
		return  [
			'id_sortie',
			'id_utilisateur_propose',
			'date_proposition',
			'nom_sortie',
			'orga_nom',
			'orga_prenom',
			'orga_adresse',
			'orga_tel',
			'orga_portable',
			'orga_mail',
			'desc',
			'commune',
			'departement',
			'xy',
			'description_lieu',
			'duree_heure',
			'gestion_picnat',
			'sortie_type',
			'sortie_public',
			'accessible_mobilite_reduite',
			'accessible_deficient_auditif',
			'accessible_deficient_visuel',
			'sortie_cadre',
			'structure',
			'materiels',
			'materiel_autre',
			'pole',
			'reseau_sortie',
			'id_sortie_type',
			'id_sortie_public',
			'id_sortie_cadre',
			'date_sortie',
			'date_sortie_en',
			'etat',
			'inscription_prealable',
			'inscription_date_limite',
			'inscription_participants_max',
			'id_sortie_reseau',
			'pays'
		];
	}

	public function sortie_xml_fields() {
		# definit l'ordre des champs a placer dans le fichier xml
		return array (
			'date',
			'heure_depart',
			'departement_nom',
			'grille_x',
			'grille_y',
			'materiel_autre',
			'nom',
			'description',
			'heure_depart_bis',
			'description_lieu',
			'commune',
			'lonlat',
			'sortie_type',
			'sortie_public',
			'inscription_prealable',
			'inscription_participants_max',
			'inscription_date_limite',
			'orga_prenom',
			'orga_nom',
			'structure',
			'mail_reservation',
			'contact_reservation',
			'etat_lib',
			'id_sortie',
			'materiels_lib',
			'id_utilisateur_propose',
			'date_proposition',
			'adresse',
			'tel',
			'portable',
			'mail',
			'id_espace_point',
			'duree_heure',
			'gestion_picnat',
			'sortie_type_n',
			'sortie_type_picto',
			'accessible_mobilite_reduite',
			'accessible_deficient_auditif',
			'accessible_deficient_visuel',
			'sortie_cadre',
			'materiels',
			'validation_externe',
			'reseau_n',
			'reseau',
			'pole_n',
			'pole',
			'departement',
			'duree',
			'duree_lib',
			'pied',
			'image_personne',
			'illustration',
			'longitude',
			'latitude',
			'pole_couleur',
			'departement_n'
		);
	}


	public static function attrs2string ($attrs) {
		$ret = '';
		foreach ($attrs as $ak=>$av) {
			$ret .= sprintf (' %s="%s"', $ak, htmlspecialchars($av));
		}
		return $ret;
	}

	public static function nom_vers_fichier($nom) {
		$nom = strtolower($nom);
		$a = array("é","è","ë","ï","î"," ",".","'",",","ç");
		$b = array("e","e","e","i","i","_","-","_","_","c");
		$nom = str_replace($a, $b, $nom);
		return $nom;
	}

	# $fields : dans quel ordre sortir les balises
	# $s : un objet sortie
	# $d : la date de la sortie
	public static function ligne_xml ($fields, $s, $d, $position) {
		$url_image = "file:///Users/florence/Desktop/cal";
		$ret = '';
		$sstr = '';
		$date = $d->date_sortie;
		$sdate = strtotime($date);
		$sattrs = array (
			'id' => $s->id_sortie,
			'type'	=> $s->id_sortie_type,
			'date'	=> $date,
			'date_fr' => strftime('%d/%m/%Y', $sdate),
			'mois'	=> strftime('%m', $sdate),
			'jour'	=> strftime('%d', $sdate),
			'annee'	=> strftime('%Y', $sdate),
			'position' => $position,
			'etat'	=> $d->etat
		);
		$ret .= sprintf("  <sortie%s>\n", self::attrs2string($sattrs));
		$point = $s->point();
		$grille = $point->grille_x_y();
		$departement = $point->get_departement();
		foreach ($fields as $f) {
			$attrs = array();
			$val = '';
			switch ($f) {
				case 'description':
					$val = htmlspecialchars(str_replace("\n"," ",clicnat_markdown_txt($s->description)));
					break;
				case 'illustration':
					$val = '';
					$attrs = array (
						'href' => "$url_image/res/illustrations/sortie-{$s->id_sortie}.eps"
					);

					break;
				case 'contact_reservation':
					$val = empty($s->portable)?$s->tel:$s->portable;
					if ($s->gestion_picnat == 't')
						$val = "Picardie Nature 03.62.72.22.54";
					break;
				case 'mail_reservation':
					$val = $s->mail;
					if ($s->gestion_picnat == 't')
						$val = "decouverte@picardie-nature.org";
					break;
				case 'image_personne':
					$nom = self::nom_vers_fichier($s->orga_prenom).'_'.self::nom_vers_fichier($s->orga_nom);
					$attrs = array (
						'href' => "$url_image/res/personnes/$nom.eps"
					);
					break;
				case 'date':
					$n = intval(trim(strftime("%e", $sdate)));
					$val = strftime("%A $n %B", $sdate);
					break;
				case 'lonlat':
					$x =  $point->get_x();
					$y =  $point->get_y();
					$val = '';
					if (!empty($x) && !empty($y)) {
						$val = sprintf("Long. %0.4F W - Lat. %0.4F N", $x, $y);
					}
					break;
				case 'longitude':
					$val = sprintf("%0.4F", $point->get_x());
					break;
				case 'latitude':
					$val = sprintf("%0.4F", $point->get_y());
					break;
				case 'commune':
					$val = '';
					$commune = $point->get_commune();
					if ($commune) $val = $commune->nom2;
					if (empty($val)) $val = '.';
					break;
				case 'departement_n':
					$val = $departement?$departement->reference:'';
					break;
				case 'departement_nom':
					$val = $departement?$departement->nom:'PICARDIE';
					break;
				case 'departement':
					$val = "";
					$ref = $departement?$departement->reference:'';
					switch ($ref) {
						case '02':
						case '60':
						case '80':
							break;
						default:
							$ref = intval($ref);
							$ref = !empty($ref)?'hors_dept':'region';
					}
					$attrs = array('href' => "$url_image/res/departements/{$ref}.eps");
					break;
				case 'grille_x':
					$val = $grille['x'];
					break;
				case 'grille_y':
					$val = $grille['y'];
					break;
				case 'pied': // trace
					$img = "escarpin.eps";

					foreach ($materiels as $m) {
						if ($m['a_prevoir']) {
							switch ($m['id_sortie_materiel']) {
								case 2:
									$img = "rando.eps";
									break;
								case 3:
									// rando c'est plus fort que botte
									if ($img != "rando.eps")
										$img = "botte.eps";
									break;
							}
						}
					}
					$attrs = array (
						'href' => "$url_image/res/pieds/$img"
					);
					break;
				case 'materiels':
					// jumelles 1
					/// botte obli 2 recommande 3
					// picnic 4
					// torche 5
					$materiels = $s->materiels();
					$c = 0;

					$jumelle = 0;
					$lampe = 0;
					$picnic = 0;
					foreach ($materiels as $m) {
						if ($m['a_prevoir']) {
							switch ($m['id_sortie_materiel']) {
								case 1: // jumelle
									$jumelle = 1;
									break;
								case 4: // picnic
									$picnic = 1;
									break;
								case 5: // torche
									$lampe = 1;
									break;
							}
						}
					}
					$attrs = array (
						'href' => "$url_image/res/materiels/p{$picnic}j{$jumelle}m$lampe.eps"
					);
					break;
				case 'materiels_lib':
					$lib = '';
					foreach ($s->materiels() as $m) {
						if ($m['a_prevoir']) {
							$lib .= " {$m['lib']},";
						}
					}
					$val = ucfirst(trim($lib, ' ,'));
					break;
				case 'accessible_mobilite_reduite':
					$attrs = array (
						'href' => "$url_image/res/acces/mobi_reduite_".(($s->$f=='t')?'1':'0').".eps"
					);
					break;
				case 'accessible_deficient_auditif':
					$attrs = array (
						'href' => "$url_image/res/acces/deficient_auditif_".(($s->$f=='t')?'1':'0').".eps"
					);
					break;
				case 'accessible_deficient_visuel':
					$attrs = array (
						'href' => "$url_image/res/acces/deficient_visuel_".(($s->$f=='t')?'1':'0').".eps"
					);
					break;
				case 'gestion_picnat':
				case 'validation_externe':
					$val = ( $s->$f ? 1 : 0 );
					break;
				case 'sortie_public':
					$public_id = $s->id_sortie_public;
					$val = htmlspecialchars(strtolower($s->public_sortie()));
					break;
				case 'sortie_type':
					$val = htmlspecialchars(ucfirst($s->type_sortie()));
					break;
				case 'sortie_type_picto':
					$attrs = array (
						'href' => "$url_image/res/sorties/types_pico_{$s->id_sortie_type}.eps"
					);
					break;
				case 'sortie_type_n':
					$val = htmlspecialchars($s->id_sortie_type);
					break;
				case 'pole_couleur':
					switch ($s->id_sortie_pole) {
						case 1: $val = '#660066'; break;
						case 2: $val = '#336666'; break;
						case 3: $val = '#3366ff'; break;
						case 4: $val = '#cc9900'; break;
						default: $val = '#660066'; break;
					}
					break;
				case 'pole_n':
					$val = $s->id_sortie_pole;
					break;
				case 'pole':
					$attrs = array (
						'href' => "$url_image/res/poles/{$s->id_sortie_pole}.eps"
						);
					break;
				case 'reseau_lib':
					$val = htmlspecialchars($s->reseau_sortie());
					break;
				case 'reseau':
					$val = '';
					$attrs = array (
						'href' => "$url_image/res/reseaux/reseau_{$s->id_sortie_reseau}.eps"
					);
					break;
				case 'reseau_n':
					$val = htmlspecialchars($s->id_sortie_reseau);
					break;
				case 'sortie_cadre':
					$attrs = array (
						'titre' => htmlspecialchars($s->cadre_sortie()),
						'href' => "$url_image/res/cadres/cadre_{$s->id_sortie_cadre}.eps"
					);
					$val = '';
					break;
				case 'inscription_date_limite':
					$val = '';
					$dl = $d->inscription_date_limite;
					if ($dl) {
						$idate = strtotime($dl);
						$attrs = array (
							'date_fr' => strftime('%d/%m/%Y', $idate),
							'mois'    => strftime('%m', $idate),
							'jour'    => strftime('%d', $idate),
							'annee'   => strftime('%Y', $idate),
							);
						$val = htmlspecialchars(strftime('avant le %A %d %B %Y', $idate));
					}
					break;
				case 'inscription_prealable':
					$val = "";
					if ($d->$f) $val = "Sur inscription";
					break;
				case 'inscription_participants_max':
					$val = "";
					if ($d->$f > 1) {
						$val = htmlspecialchars($d->$f);
						$val .= " places";
					}
					break;
				case 'heure_depart':
				case 'heure_depart_bis':
					$val = strftime("%Hh%M", $sdate);
					if ($val == "00h00") {
						$val = "?";
					}
					break;
				case 'duree_lib':
					$val = $s->duree_lib;
					break;
				case 'duree':
					$val = '';
					$d = sprintf("%02dh%02d", (int)$s->duree_heure, ($s->duree_heure - ((int)$s->duree_heure))*60);
					$attrs = array (
						"href" => "$url_image/res/durees/$d.eps"
					);
					break;
				case 'etat':
					$val = $d->etat;
					break;
				case 'etat_lib':
					switch ($d->etat) {
						case 1:
							$val = 'proposition';
							break;
						case 2:
							$val = 'refus';
							break;
						case 3:
							$val = 'valide';
							break;
						case 4:
							$val = 'annulation';
							break;
					}
					break;
				default:
					if (method_exists($s, $f)) {
						$val = htmlspecialchars($s->$f());
					} else {
						$val = htmlspecialchars($s->$f);
					}
					break;
			}
			$astr = '';
			$ret .= sprintf ("    <%s%s>%s</%s>\n",
					$f, self::attrs2string($attrs), $val, $f);
		}
		$ret .= "  </sortie>\n";
		return $ret;
	}

	public static function sorties_extraction($db, $format, $date_d, $date_f, $etats, $types, $pays) {
		$sorties_dates = clicnat_sortie_date::periode($db, $date_d, $date_f);

		$f = tempnam('/tmp/','cal');
		$fout = fopen($f,'w');

		switch ($format) {
			case 'xml':
				fprintf ($fout, '<?xml version="1.0" encoding="UTF-8"?'.">\n");
				fprintf ($fout, '<calendrier>'. "\n");
				fprintf ($fout, "<!-- du $date_d au $date_f -->\n");
				break;
			case 'csv':
				fputcsv ($fout, self::sorties_cols());
				break;
			case 'geojson':
				$geojson = [
					"type" => "FeatureCollection",
					"features" => []
				];
				break;
		}

		$position = 0;
		foreach ($sorties_dates as $sd) {
			if (array_search($sd->etat, $etats) === false) {
				continue;
			}
			if (count($types) > 0) {
				if (array_search($sd->sortie->id_sortie_type, $types) === false) {
					continue;
				}
			}
			$commune = false;
			$pays_commune = false;
			$id_pays_commune = '';
			if (is_object($sd->sortie->point())) {
				$commune = $sd->sortie->point()->get_commune();
				if ($commune) {
					$pays_commune = $commune->pays_statistique();
				}
			}

			if (!empty($pays) && $pays_commune && $pays_commune->id_pays != $pays) {
				continue;
			}

			$position++;

			switch($format) {
				case 'xml':
					fprintf ($fout, self::ligne_xml(self::sortie_xml_fields(), $sd->sortie, $sd, $position));
					break;
				case 'csv':
					$s = $sd->sortie;
					$commune_nom = '';
					if (is_object($s->point())) {
						$commune_o = $s->point()->get_commune();
						if ($commune_o) {
							$commune_nom = $commune_o->nom;
						}
					}
					$materiels = '';
					foreach ($s->materiels() as $m) {
						if ($m['a_prevoir'] == 1) {
							$materiels .= " {$m['lib']}  -";
						}
					}
					$materiels = trim(trim($materiels,"-"));
					$la = [
						$s->id_sortie,
						$s->id_utilisateur_propose,
						$s->date_proposition,
						$s->nom,
						$s->orga_nom,
						$s->orga_prenom,
						$s->adresse,
						$s->tel,
						$s->portable,
						$s->mail,
						clicnat_markdown_txt($s->description),
						$commune_nom,
						is_object($s->point())?$s->point()->get_departement():'#',
						is_object($s->point())?"{$s->point()->get_x()} {$s->point()->get_y()}":"#",
						$s->description_lieu,
						$s->duree_heure,
						$s->gestion_picnat,
						$s->type_sortie(),
						$s->public_sortie(),
						$s->accessible_mobilite_reduite,
						$s->accessible_deficient_auditif,
						$s->accessible_deficient_visuel,
						$s->cadre_sortie(),
						$s->structure,
						$materiels,
						$s->materiel_autre,
						$s->pole_sortie(),
						$s->reseau_sortie(),
						$s->id_type_sortie,
						$s->id_public_sortie,
						$s->id_cadre_sortie
					];
					$d = $sd;
					$lb = [
						strftime("%d-%m-%Y %H:%M", strtotime($d->date_sortie)),
						$d->date_sortie,
						$d->etat,
						$d->inscription_prealable,
						$d->inscription_date_limite,
						$d->inscription_participants_max,
						$s->id_sortie_reseau,
						$pays_commune
					];
					$l = array_merge($la,$lb);
					fputcsv($fout,$l);
					break;
				case 'geojson':
					$s = $sd->sortie;
					if (is_object($s->point())) {
						$geojson['features'][] = [
							"type" => "Feature",
							"geometry" => [
								"type" => "Point",
								"coordinates" => [(float)$s->point()->get_x(), (float)$s->point()->get_y()]
							],
							"properties" => [
								"date" => $sd->date_sortie,
								"nom" => $s->nom,
								"description" => $s->description,
								"sortie_type" => $s->type_sortie(),
								"sortie_type_n" => (int)$s->id_sortie_type,
								"pole_n" => (int)$s->id_sortie_pole
							]
						];
					}
					break;
			}
		}

		switch ($format) {
			case 'xml':
				fprintf($fout, "</calendrier>\n");
				break;
			case 'geojson':
				fwrite($fout, json_encode($geojson));
				break;
		}

		fclose($fout);
		$ret = file_get_contents($f);
		unlink($f);
		return $ret;
	}

	const sql_sortie_sans_dates = 'select id_sortie from sortie where id_sortie not in (select id_sortie from sortie_date) and id_utilisateur_propose=$1 order by id_sortie desc';

	/**
	 * @brief liste les sorties sans dates (brouillons)
	 */
	public static function sans_dates($db, $id_utilisateur) {
		$q = bobs_qm()->query($db, 'sorties_sans_date', self::sql_sortie_sans_dates, array($id_utilisateur));
		$ids = array();
		while ($r = self::fetch($q)) {
			$ids[] = $r['id_sortie'];
		}
		return new clicnat_iterateur_sortie($db, $ids);
	}

	public function tests() {
		$resultats = array();
		if (empty($this->orga_nom))
			$resultats[] = array('msg' => "Pas de nom pour l'organisateur", "bloquant" => true);

		if (empty($this->orga_prenom))
			$resultats[] = array('msg' => "Pas de prénom pour l'organisateur", "bloquant" => true);

		if (empty($this->nom))
			$resultats[] = array('msg' => "Pas de nom pour votre activité", "bloquant" => true);

		if (empty($this->description))
			$resultats[] = array('msg' => "Pas de description pour votre activité", "bloquant" => true);

		if (empty($this->duree_heure))
			$resultats[] = array('msg' => "Pas de durée indiquée", "bloquant" => true);

		if (mb_strlen($this->description,'utf-8') > 300)
			$resultats[] = array('msg' => 'Description trop longue, il faut un texte de moins de 300 caractères', "bloquant"=>true);

		return $resultats;
	}
}
