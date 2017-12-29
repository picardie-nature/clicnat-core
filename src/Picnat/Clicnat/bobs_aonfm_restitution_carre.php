<?php
namespace \Picnat\Clicnat;

class bobs_aonfm_restitution_carre {
	protected $id_espace;
	protected $db;
	protected $annee;

	public function __construct($db, $id_espace_carre, $annee) {
		$this->id_espace = $id_espace_carre;
		$this->db = $db;
		$this->annee = $annee;
	}

	public static function trois_annees($db, $id_espace_carre) {
		// 4 dans les faits
		$t = array();
		$especes = array();
		for ($y=2009;$y<=2012;$y++) {
			$arc = new bobs_aonfm_restitution_carre($db, $id_espace_carre, $y);
			$t[$y] = $arc->get_especes_etats();
			foreach ($t[$y] as $id_espece=>$e) {
				if (!array_key_exists($id_espece, $especes)) {
					$especes[$id_espece] = array('espece' => get_espece($db, $id_espece));
				}
			}
		}
		uasort($especes, 'aonfm_tri_sys2');
		$requis_abondance = array();
		foreach ($especes as $e) {
			$esp = $e['espece'];
			if (empty($esp)) continue;
			$requis_abondance[$esp->id_espece] = bobs_aonfm_choix_resp_tps_abond::requis($db, $esp);
		}
		$t = array(
			'especes' => $especes,
			'resultats' => $t,
			'requis_abond' => $requis_abondance,
			'fourchette_large' => bobs_aonfm_choix_resp_tps_abond::liste_classes_large(),
			'fourchette_precise' => bobs_aonfm_choix_resp_tps_abond::liste_classes_precise (),
			'resultats_abond' => bobs_aonfm_choix_resp_tps_abond::get_resultats($db, $id_espace_carre)
		);
		//echo "<pre style='text-align:left;'>"; print_r($t); echo "</pre>";
		return $t;
	}

	public function get_espace() {
		return get_espace_l93_10x10($this->db, $this->id_espace);
	}

	const sql_aonfm_structure = 'select * from aonfm_structure where structure=$1 and annee=$2 and carre=$3';

	public function get_resultats_structure($structure) {
		$q = bobs_qm()->query($this->db, 'aonfm_structure_1', self::sql_aonfm_structure, array($structure,$this->annee, $this->get_espace()->nom));
		$r = array();
		while ($l = bobs_element::fetch($q)) {
			$statut = null;
			switch($l['statut']) {
				case 'certain':
					$statut = NICHEUR_CERTAIN;
					break;
				case 'probable':
					$statut = NICHEUR_PROBABLE;
					break;
				case 'possible':
					$statut = NICHEUR_POSSIBLE;
					break;
			}
			$r[] = array(
				'id_espece' => $l['id_espece'],
				'statut_n' => $statut
			);
		}
		return $r;
	}

	public function get_resultats_lpo() {
		$sql = "select * from referentiel_especes_tiers where tiers='visionature'";

		$q = bobs_qm()->query($this->db, 'aonfm_visio_gesv', $sql, array());
		$referentiel = array();
		while ($e = bobs_element::fetch($q)) {
			$referentiel[$e['id_tiers']] = $e['id_espece'];
		}
		$statut_nicheurs = array(
			'nicheur certain' => NICHEUR_CERTAIN,
			'nicheur probable' => NICHEUR_PROBABLE,
			'nicheur possible' => NICHEUR_POSSIBLE
		);
		$t = array();
		$sql = 'select * from aonfm_visionature where carre_atlas=$1 and annee=$2';
		$c = $this->get_espace();
		$param = array($c->nom, $this->annee);
		$q = bobs_qm()->query($this->db, 'aonfm_visio', $sql, $param);
		while ($r = bobs_element::fetch($q)) {
			$r['id_espece'] = $referentiel[$r['id_espece_vnat']];
			$r['statut_n'] = $statut_nicheurs[$r['statut_nicheur']];
			$t[] = $r;
		}
		return $t;
	}

	public function set_resultats_responsables($id_espece, $statut, $classe, $utilisateur, $uniquement_insertion=false) {
		// déjà un résultat ou pas ?
		$sql = 'select * from aonfm_choix_responsables where id_espace=$1 and annee=$2 and id_espece=$3';
		$q = bobs_qm()->query($this->db, 'aonfm_g_r1', $sql, array($this->id_espace, $this->annee, $id_espece));
		$r = bobs_element::fetch($q);
		if ($statut < 1) $statut = 0;
		if ($statut > 3) throw new \Exception('pas possible');
		$params = array(
			$this->id_espace,
			$this->annee,
			$id_espece,
			$statut,
			$classe,
			strftime("%Y-%m-%d %H:%M:%S"),
			$utilisateur->id_utilisateur
		);
		if ($r) {
			if (!$uniquement_insertion) {
				$sql = 'update aonfm_choix_responsables set n_statut=$4, classe=$5, dmaj=$6, id_utilisateur_maj=$7
					where id_espace=$1 and annee=$2 and id_espece=$3';
				$q = bobs_qm()->query($this->db, 'aofnm_s_r1', $sql, $params);
			} else {
				return false;
			}
		} else {
			$sql = 'insert into aonfm_choix_responsables (id_espace,annee,id_espece,n_statut,classe,dmaj,id_utilisateur_maj)
				values ($1,$2,$3,$4,$5,$6,$7)';
			$q = bobs_qm()->query($this->db, 'aofnm_s_r2', $sql, $params);
		}
		return $q;
	}

	public function get_resultats_responsables() {
		$sql = "select * from aonfm_choix_responsables where id_espace=$1 and annee=$2";
		$q = bobs_qm()->query($this->db, 'aonfm_rresp_c', $sql, array($this->id_espace, $this->annee));
		return bobs_element::fetch_all($q);
	}

	public function get_especes_etats() {
		$resultats_auto = bobs_aonfm::especes_carre($this->db, $this->id_espace, $this->annee);
	//	$resultats_lpo = $this->get_resultats_lpo();
		$resultats_lpo = $this->get_resultats_structure('GOP/Atlas-Ornitho.fr');
	//	echo "<pre> {$this->annee} "; print_r($resultats_lpo); echo "</pre>";
		$resultats_resp = $this->get_resultats_responsables();

		// resultats[id_espece] (id_espece, statut_auto, statut_resp, statut_lpo, classe)
		$resultats = array();
		$conv_statuts_n = array('possible'=>1,'probable'=>2,'certain'=>3);
		if (array_key_exists($this->annee, $resultats_auto))
		foreach ($resultats_auto[$this->annee] as $r) {
			$e = $r['objet'];

			$resultats[$e->id_espece] = array(
				'id_espece' => $e->id_espece,
				'statut_auto' => $conv_statuts_n[$r['statut']],
				'statut_resp' => null,
				'statut_lpo' => null,
				'classe' => null
			);
		}

		foreach ($resultats_lpo as $r) {
			if (!array_key_exists($r['id_espece'], $resultats)) {
				$resultats[$r['id_espece']] = array(
					'id_espece' => $r['id_espece'],
					'statut_auto' => null,
					'statut_resp' => null,
					'statut_lpo' => $r['statut_n'],
					'classe' => null
				);
			} else {
				$resultats[$r['id_espece']]['statut_lpo'] = $r['statut_n'];
			}
		}

		foreach ($resultats_resp as $r) {
			if (!array_key_exists('id_espece', $r)) {
				$resultats[$r['id_espece']] = array(
					'id_espece' => $r['id_espece'],
					'statut_auto' => null,
					'statut_resp' => $r['n_statut'],
					'statut_lpo' => null ,
					'classe' => null
				);
			} else {
				$resultats[$r['id_espece']]['statut_resp'] = $r['n_statut'];
				$resultats[$r['id_espece']]['classe'] = $r['classe'];
			}
		}

		// on ajoute un objet espece sur chaque ligne
		foreach ($resultats as $k=>$v) {
			$resultats[$k]['espece'] = get_espece($this->db, $k);
		}

		uasort($resultats, 'aonfm_tri_sys2');
	//	echo" <pre style='text-align:left;'>"; print_r($resultats); echo "</pre>";
		return $resultats;
	}
}
