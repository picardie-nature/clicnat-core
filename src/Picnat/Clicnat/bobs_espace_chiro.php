<?php
namespace Picnat\Clicnat;

class bobs_espace_chiro extends bobs_espace_point {
	/**
	 * Nom du tag indiquant que le point est dans le programme de prospection
	 */
	const tag_prospection = 'CAPR';
	const tag_acces_prospect = 'ACCP';
	const tag_type_site_1 = 'CTS1';

	function __construct($db, $id, $table='espace_chiro') {
		parent::__construct($db, $id, $table);
	}

	public static function get_espaces_in_box_geojson($db, $boite, $table='espace_chiro') {
		$t = parent::get_espaces_in_box_geojson($db, $boite, $table);
		$data = json_decode($t, true);
		if (count($data['features']) < 100) {
			foreach ($data['features'] as $k => $feature) {
				$id = $feature['properties']['id_espace'];
				$gite = get_espace_chiro($db, $id);
				$classe = 'inconnu';
				if (!$gite->accessible_prospection()) {
					$classe = 'comble';
				} else if ($gite->visite_6mois()) {
					$classe = 'fait';
				} else if ($gite->prospection_prevue()) {
					$classe = 'prevue';
				} else if ($gite->a_prospecter()) {
					$classe = 'a_prospecter';
				} else if ($gite->visites_n_moins_2()) {
					$classe = 'moins2';
				}
				$data['features'][$k]['properties']['classe'] = $classe;

			}
		}
		return json_encode($data);
	}
	public static function insert_wkt($db, $data, $table='espace_chiro') {
		return parent::insert_wkt($db, $data, $table);
	}

	function get_tags_racines() {
	    return [555,551,526,559,513,518,545,540,572,533,495,501,413,420];
	}

	public static function get_by_ref($db, $ref) {
	    return self::__get_by_ref($db, 'espace_chiro', 'bobs_espace_chiro', $ref);
	}

	public function ajoute_tag($id_tag, $intval=null, $textval=null) {
	    return $this->__ajoute_tag(BOBS_TBL_TAG_ESPACE, 'id_espace', $id_tag, $this->id_espace, $intval, $textval);
	}

	public function supprime_tag($id_tag) {
	    return $this->__supprime_tag(BOBS_TBL_TAG_ESPACE, 'id_espace', $id_tag, $this->id_espace);
	}

	public static function get_list($db, $table='espace_chiro') {
	    return parent::get_list($db, $table);
	}

	/**
	 * @brief gîte a prospecter ?
	 * @return bool
	 */
	public function a_prospecter() {
	    $tag = bobs_tags::by_ref($this->db, self::tag_prospection);
	    return $this->a_tag($tag->id_tag);
	}

	const sql_liste_a_prospecter = 'select espace_chiro.* from espace_chiro,espace_tags where espace_table=$1 and espace_tags.id_tag=$2 and espace_chiro.id_espace=espace_tags.id_espace';

	public static function get_list_a_prospecter($db, $avec_objs=false) {
		$tag = bobs_tags::by_ref($db, self::tag_prospection);
		$sql =
		$q = bobs_qm()->query($db, 'espace_chiro_prosp', self::sql_liste_a_prospecter, array('espace_chiro', $tag->id_tag));
		$t = self::fetch_all($q);
		if ($avec_objs)
		foreach ($t as $k => $v)
			$t[$k]['obj'] = get_espace_chiro($db, $v);
		return $t;
	}

	/**
	 * @brief visitée ces deux dernières années
	 * @return array
	 */
	public function visites_n_moins_2() {
	    $sql = 'select count(*) as n from observations
		    where id_espace=$1 and espace_table=$2
		    and date_observation between $3::date and $4::date';

	    $args = array(
		$this->id_espace,
		$this->table,
		strftime('%Y-11-01', mktime()-86400*365*3),
		strftime('%Y-03-31', mktime())
	    );

	    $q = bobs_qm()->query($this->db, 'esp_chi_n_2', $sql, $args);
	    $r = self::fetch($q);

	    return self::cli($r['n']);
	}

	/**
	 * @brief visitée ces six derniers mois !! 4 en fait
	 * @return array
	 */
	public function visite_6mois() {
	    $sql = 'select count(*) as n from observations
		    where id_espace=$1 and espace_table=$2
		    and date_observation between $3::date and $4::date';

	    $args = array(
		$this->id_espace,
		$this->table,
		strftime('%Y-%m-%d', mktime()-86400*31*4),
		strftime('%Y-%m-%d', mktime())
	    );

	    $q = bobs_qm()->query($this->db, 'esp_chi_m_6', $sql, $args);
	    $r = self::fetch($q);

	    return self::cli($r['n']);
	}

	/**
	 * @brief Ajout d'une nouvelle cavité
	 *
	 * les valeurs 'reference' et 'nom' du tableau data seront remise à zéro
	 * c'est le trigger de la base qui donnera le nom
	 *
	 * retourne l'id du nouveau point
	 *
	 * @param ressource $db
	 * @param array $data
	 * @param string $table
	 * @return int
	 */
	public static function insert($db, $data, $table='espace_chiro') {
	    $data['reference'] = '';
	    $data['nom'] = '';
	    return parent::insert($db, $data, $table);
	}

	public function date_modif_maj() {
	    $sql = 'update '.$this->table.' set date_modif=now() where id_espace=$1';
	    return bobs_qm()->query($this->db, 'ep_chir_dmod', $sql, [$this->id_espace]);
	}

	const sql_l_cav = "select espace_chiro.id_espace,reference,st_y(the_geom) as pt_lat, st_x(the_geom) as pt_lon,count(distinct o.id_observation) as no, count(c.id_citation) as nc,max(extract(year from date_observation)) as ymax from espace_chiro left join observations o on o.id_espace=espace_chiro.id_espace left join citations c on (c.id_observation=o.id_observation and c.nb > -1) group by espace_chiro.id_espace,pt_lat,pt_lon order by id_espace";

	/**
	 * Export de la liste des sites chiros
	 */
	public static function tous_avec_tags($db, $fh) {
		$q = bobs_qm()->query($db, 'esp_chiro_all_s', self::sql_l_cav, []);
		fwrite($fh, '"#";"ref";"cts1";"cts2";"cswa";"bpot";"iswa";"chiv";"bfnc";"cest";"bdur";"bdim";"cdec";"bdan";"bamn";"accp";"accc";"mod";"cges";"lon";"lat";"nobs";"ncits";"anneemax"'."\n");
		while ($r = bobs_element::fetch($q)) {
			$esp = get_espace_chiro($db, $r);
			$tags = $esp->get_tags();
			$t_tag = array();
			foreach ($tags as $tag) {
				$t_tag[$tag['ref']] = $tag['v_text'];
			}
			//             #   ref  cts1 cts2 cswa bpot iswa chiv bfnc cest bdur bdim cdec bdan bamn accp accc mod pt_lon pt_lat n_obs n_cit
			fprintf($fh, '"%d";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%d";"%d";"%d";'."\n",
				$esp->id_espace,
				$esp->reference,
				(isset($t_tag['CTS1'])?$t_tag['CTS1']:''),
				(isset($t_tag['CTS2'])?$t_tag['CTS2']:''),
				(isset($t_tag['CSWA'])?$t_tag['CSWA']:''), // ?
				(isset($t_tag['BPOT'])?$t_tag['BPOT']:''),
				(isset($t_tag['ISWA'])?$t_tag['ISWA']:''),
				(isset($t_tag['CHIV'])?$t_tag['CHIV']:''), // ?
				(isset($t_tag['BFNC'])?$t_tag['BFNC']:''),
				(isset($t_tag['CEST'])?$t_tag['CEST']:''), // ?
				(isset($t_tag['BDUR'])?$t_tag['BDUR']:''),
				(isset($t_tag['BDIM'])?$t_tag['BDIM']:''),
				(isset($t_tag['CDEC'])?$t_tag['BDIM']:''), // ?
				(isset($t_tag['BDAN'])?$t_tag['BDAN']:''),
				(isset($t_tag['BAMN'])?$t_tag['BAMN']:''),
				(isset($t_tag['ACCP'])?$t_tag['ACCP']:''),
				(isset($t_tag['ACCC'])?$t_tag['ACCC']:''),
				$esp->date_modif,
				(isset($t_tag['CGES'])?$t_tag['CGES']:''),
				$esp->pt_lon,
				$esp->pt_lat,
				$r['no'],
				$r['nc'],
				$r['ymax']
			);
		}
	}

	public function accessible_prospection() {
		$tags = $this->get_tags();
		foreach ($tags as $tag) {
			if (($tag['ref'] == self::tag_acces_prospect) || ($tag['ref'] == self::tag_type_site_1)) {
				if ($tag['v_text'] == "Impossible (Comblé...)")
					return false;
			}
		}
		return true;
	}
}
