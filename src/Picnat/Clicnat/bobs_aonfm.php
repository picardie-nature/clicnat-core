<?php
namespace \Picnat\Clicnat;

/**
 * @brief Recherche des nicheurs pour l'AONFM
 */
class bobs_aonfm {
	public $nicheurs;

	protected $db;
	protected $annee;
	protected $espece;
	protected $citations_a_traiter;
	protected $citations_retenues;
	protected $citations_exclues;
	protected $tags_nidif;

	const distance_min_nidif = 300;
	const nombre_jours_min = 8;
	const nombre_jours_max = 70;

	const root_dir = '/var/lib/bobs/aonfm/';
	const gen_dir = '/var/lib/bobs/aonfm_tmp/';

	const couleur_possible = '#ff6000';
	const couleur_probable = '#ffcc00';
	const couleur_certain = '#00ff00';

	function __construct($db, $id_espece, $annee) {
		$this->db = $db;

		$this->annee = $annee;
		$this->nicheurs = array();
		$this->citations_a_traiter = array();
		$this->citations_retenues = array();
		$this->citations_exclues = array();
		$this->tags_nidif = array();

		$this->espece = get_espece($db, $id_espece);
	}

	function affiche_compteurs() {
		echo "citations_a_traiter\t".count($this->citations_a_traiter)."\n";
		echo "citations_retenues\t".count($this->citations_retenues)."\n";
		echo "citations_exclues\t".count($this->citations_exclues)."\n";
		echo "nicheurs\t".count($this->nicheurs)."\n";
	}

	public static function get_tags_impossible() {
		return array(5306,5304,5132,5301,5138,5137,5114,5308,5133,5135,5112,5111,5117,5124,5136,5134,5127,5118,5115,5155,5121,5125,5116,5156,5128,5126,5152,5123,5105,5307,5302,5305,5106,5113,5200,5103,5110,5300,5100,5104,5120,5107,5151,5131,5150,5130,5101,5140,5108,5102,5122,4700,5303);
	}

	public static function get_tags_possible() {
		return array(1000,1150,2100,2200,3116,3002);
	}

	public static function get_tags_probable() {
		return array(2210,3010,3011,3012,3013,3020,3110,3111,3112,3113,3114,3115,3120,3121,3122,3003);
	}

	public static function get_tags_certains() {
		return array(2202,3014,3030,3031,3032,3033,3140,3150,3160,3170,3200,3210,3211,3310,3320,3330,3004,3005);
	}

	public static function nb_especes_carre($id_carre) {
		$cmd = sprintf('for f in $(find %s/carres/%d -type f); do echo $(basename $f); done|sort -u|wc -l',
						 self::root_dir, $id_carre);
		return shell_exec($cmd);
	}

	const sql_n_esp_car = '
			select e.id_espace,count(distinct id_espece) as n,e.nom,astext(e.the_geom) as wkt
			from aonfm_choix_responsables aoc, espace_l93_10x10 e
			where n_statut > 0
			and e.id_espace=aoc.id_espace
			group by e.id_espace, e.nom, e.the_geom';

	public static function nb_especes_carres_choix_resp($db) {
		$q = bobs_qm()->query($db, 'aonfm_cpt_choix_resp_i', self::sql_n_esp_car, array());
		return bobs_element::fetch_all($q);
	}

	public static function nb_especes_carre_annee($id_carre, $annee) {
		$cmd = sprintf('for f in $(find %s/carres/%d/%d -type f); do echo $(basename $f); done|sort -u|wc -l', self::root_dir, $id_carre, $annee);
		return shell_exec($cmd);
	}

	public static function especes_carre($db, $id_carre,$annee=false) {
		$especes = array();
		$p = sprintf(self::root_dir.'carres/%d/', $id_carre);
		if (file_exists($p)) {
			$d = opendir($p);
			while ($da = readdir($d)) {
				if (($da == '.') || ($da == '..')) continue;
				if ($annee) {
					if ($da != $annee)
						continue;
				}
				if (is_dir($p.$da)) {
					$da_h = opendir($p.$da);
					while ($id_espece = readdir($da_h)) {
						if (($id_espece == '.')||($id_espece == '..')) continue;
						if (is_file($p.$da.'/'.$id_espece)) {
							$especes[$da][$id_espece] = array(
								'statut' => file_get_contents($p.$da.'/'.$id_espece),
								'objet' => get_espece($db, $id_espece)
							);
						}
					}
					closedir($da_h);
				}
			}
			closedir($d);
		}
		foreach (array_keys($especes) as $annee) {
			uasort($especes[$annee], 'aonfm_tri_systematique');
		}
		krsort($especes);
		return $especes;
	}

	public static function carres_espece($db, $id_espece) {
		$carres = array();
		$p = sprintf(self::root_dir.'especes/%d/', $id_espece);
		if (file_exists($p)) {
			$d = opendir($p);
			while ($da = readdir($d)) {
				if (($da == '.') or ($da == '..')) continue;
				if (is_dir($p.$da)) {
					$da_h = opendir($p.$da);
					while ($id_carre = readdir($da_h)) {
						if (($id_carre == '.') or ($id_carre == '..')) continue;
						if (is_file($p.$da.'/'.$id_carre)) {
							if (!isset($carres[$id_carre])) {
								$carres[$id_carre] = array(
									'statut' => file_get_contents($p.$da.'/'.$id_carre),
									'carre' => get_espace_l93_10x10($db, $id_carre)
								);
								$carres[$id_carre]['carre']->get_geom('wkt');
							} else {
								$nouveau_statut = file_get_contents($p.$da.'/'.$id_carre);
								switch ($carres[$id_carre]['statut']) {
									case 'certain':
										break;
									case 'probable':
										if ($nouveau_statut == 'certain') {
											$carres[$id_carre]['statut'] = 'certain';
										}
										break;
									case 'possible':
										if ($nouveau_statut != 'possible')
											$carres[$id_carre]['statut'] = $nouveau_statut;
										break;
								}
							}
						}
					}
					closedir($da_h);
				}
			}
			closedir($d);
		}
		return $carres;
	}

	public function sauve() {
		$dir_esp = self::gen_dir."/especes/{$this->espece->id_espece}";
		$dir_esp_annee = $dir_esp."/{$this->annee}";

		if (!file_exists(self::gen_dir."/especes")) mkdir(self::gen_dir."/especes");
		if (!file_exists($dir_esp)) mkdir($dir_esp);
		if (!file_exists($dir_esp_annee)) mkdir($dir_esp_annee);

		foreach ($this->nicheurs as $nicheur) {
			try {
				if (empty($nicheur->carre_atlas)) $nicheur->def_carre_atlas();
			} catch (\Exception $e) {
				continue;
			}
			$f = "$dir_esp_annee/{$nicheur->carre_atlas}";
			$statut = '';
			if (file_exists($f)) {
				$statut = file_get_contents($f);
			}
			switch ($statut) {
				case 'certain':
					continue;
				case 'probable':
					if ($nicheur->statut == 'certain') {
						$statut = 'certain';
					} else {
						continue;
					}
					break;
				case 'possible':
					if ($nicheur->statut != 'possible') {
						$statut = $nicheur->statut;
					} else {
						continue;
					}
					break;
				default:
					$statut = $nicheur->statut;
			}
			$fo = fopen($f, 'w');
			if (!$fo)
				throw new \Exception('Ne peut pas enregistrer le statut');
			fwrite($fo, $statut);
			fclose($fo);
		}
		foreach ($this->nicheurs as $nicheur) {
			$statut = '';
			$dir_carre = self::gen_dir."/carres/{$nicheur->carre_atlas}";
			$dir_carre_annee = $dir_carre."/{$this->annee}";

			if (!file_exists(self::gen_dir."/carres/")) mkdir(self::gen_dir."/carres/");
			if (!file_exists($dir_carre)) mkdir($dir_carre);
			if (!file_exists($dir_carre_annee)) mkdir($dir_carre_annee);

			$f = $dir_carre_annee."/{$this->espece->id_espece}";
			if (file_exists($f)) {
				$statut = file_get_contents($f);
			}
			switch ($statut) {
				case 'certain':
					continue;
				case 'probable':
					if ($nicheur->statut == 'certain') {
						$statut = 'certain';
					} else {
						continue;
					}
					break;
				case 'possible':
					if ($nicheur->statut != 'possible') {
						$statut = $nicheur->statut;
					} else {
						continue;
					}
					break;
				default:
					$statut = $nicheur->statut;
			}
			$fo = fopen($f, 'w');
			fwrite($fo, $statut);
			fclose($fo);
		}
	}

	private function info($msg) {
		echo "\t\t$msg\n";
		flush();
	}

	private function log_citations($obj_nicheur) {
		$f = fopen(strftime("/tmp/citations_aonfm_%Y_%m_%d.txt", mktime()), 'a');
		foreach ($obj_nicheur->citations as $c) {
			fwrite($f,sprintf("%d;%d\n", $c->id_citation, $obj_nicheur->carre_atlas));
		}
		fclose($f);
	}

	public function run($table_temporaire_deja_prete=false) {
		$tags_possible = $this->get_tags_possible();
		$tags_probable = $this->get_tags_probable();
		$tags_certain =  $this->get_tags_certains();

		$db = $this->db;

		if (!$table_temporaire_deja_prete) {
			$this->info('début extraction');
			bobs_element::query($db, 'begin');
			bobs_element::query($db, 'drop table if exists aonfm');
			$extraction = new bobs_extractions($db);
			$extraction->ajouter_condition(new bobs_ext_c_espece($this->espece->id_espece));
			$extraction->ajouter_condition(new bobs_ext_c_annee($this->annee));
			$extraction->dans_table_temporaire('aonfm');
			$this->info('fin extraction');
		}

		$q = bobs_element::query($db, "
			select citations.* from aonfm,citations,observations
			where aonfm.id_citation=citations.id_citation
			and observations.id_observation=citations.id_observation
			order by date_observation");

		$this->info('créations des instances et suppression invalides');
		while ($citation_data = bobs_element::fetch($q)) {
			$citation = get_citation($db, $citation_data);
			$q_ok = empty($citation->indice_qualite) || ($citation->indice_qualite >= 3);
			if (!$citation->invalide() && $citation->nb >= 0 && $q_ok) {
				$this->citations_a_traiter[$citation_data['id_citation']] = $citation;
			} else {
				$this->info('supprime invalide');
			}
		}
		$this->info('instances ok');
		foreach (array_merge($tags_possible, $tags_probable, $tags_certain) as $tag) {
			$this->tags_nidif[$tag] = true;
		}

		bobs_element::query($db, 'commit');

		$this->info(sprintf("%d citations a traiter", count($this->citations_a_traiter)));
		$n_pass = 0;
		while ($citation = reset($this->citations_a_traiter)) {
			$n_pass++;
			$observation = $citation->get_observation();
			$citations_proches = array();
			// on trouve les proches (si dans date de nidif)
			if ($this->espece->est_dans_date_nidif($observation->date_observation)) {
				$in = '(';

				foreach ($this->citations_a_traiter as $autre_citation) {
					$in .= $autre_citation->id_citation.',';
				}
				$in = trim($in,',').')';
				$esp = $citation->get_observation()->get_espace();
				if ($esp->get_table() != 'espace_point') {
					unset($this->citations_a_traiter[$citation->id_citation]);
					continue;
				}
				$x = $esp->get_x();
				$y = $esp->get_y();

				$sql = "select c.id_citation, st_distance_sphere(st_setsrid(st_point($x,$y),4326), p.the_geom) as distance
					from citations c,observations o,espace_point p
					where c.id_citation in $in
					and c.id_observation = o.id_observation
					and o.id_espace = p.id_espace
					and o.espace_table='espace_point'";
				$q = bobs_element::query($db, $sql, array());
				$d_tmp = bobs_element::fetch_all($q);
				$distances = array();
				foreach ($d_tmp as $d) {
					$distances[$d['id_citation']] = $d['distance'];
				}

				foreach ($this->citations_a_traiter as $autre_citation) {
					if ($citation->id_citation == $autre_citation->id_citation)
						continue;
					$autre_obs = $autre_citation->get_observation();

					// on doit être dans les dates de nidification
					if (!$this->espece->est_dans_date_nidif($autre_obs->date_observation)) {
						// elle y est pas on va tout de suite tester si elle peut donner un statut
						// sinon on la sort maintenant
						$nicheur = new bobs_aonfm_nicheur();
						$nicheur->citations[] = $autre_citation;
						$nicheur->def_statut();
						if ($nicheur->n_statut == PAS_NICHEUR) {
							echo "s";
							unset($this->citations_a_traiter[$autre_citation->id_citation]);
							flush();
						}
						continue;
					}
					$interval = abs($observation->date_obs_tstamp - $autre_obs->date_obs_tstamp)/86400;
					if (($interval < self::nombre_jours_min) or ($interval > self::nombre_jours_max)) {
						continue;
					}
					//if ($observation->get_distance($autre_citation) > self::distance_min_nidif) {
					//	continue;
					//}
					if (!isset($distances[$autre_citation->id_citation])) {
						unset($this->citations_a_traiter[$autre_citation->id_citation]);
						continue;
					}
					if ($distances[$autre_citation->id_citation] > self::distance_min_nidif) {
						continue;
					}
					$citations_proches[] = $autre_citation;
				}
			}
			// on les enregistre
			if (count($citations_proches) > 0) {
				$nicheur = new bobs_aonfm_nicheur();
				$nicheur->citations = $citations_proches;
				$nicheur->citations[] = $citation;
				$nicheur->commentaire = 'origine : proximité';
				foreach ($nicheur->citations as $citation_p) {
					unset($this->citations_a_traiter[$citation_p->id_citation]);
				}
				//$nicheur->def_carre_atlas();
				$ok_pour_ajout = true;
				try {
					$nicheur->def_carre_atlas();
				} catch (\Exception $e) {
					echo "Autre chose que espace_point: on passe";
					$ok_pour_ajout=false;
				}

				$nicheur->def_statut();

				if ($nicheur->n_statut < NICHEUR_POSSIBLE)
					$ok_pour_ajout = false;

				$this->log_citations($nicheur);

				if ($ok_pour_ajout) {
					echo sprintf("[+%d]", count($nicheur->citations));
					$this->nicheurs[] = $nicheur;
				} else {
					echo sprintf("[-%d]", count($nicheur->citations));
					$this->nicheurs[] = $nicheur;
				}
				flush();
			} else {
				$nicheur = new bobs_aonfm_nicheur();
				$nicheur->citations = array($citation);
				$nicheur->commentaire = 'pas proximité';
				unset($this->citations_a_traiter[$citation->id_citation]);
				$ok_pour_ajout = true;
				try {
					$nicheur->def_carre_atlas();
					$this->log_citations($nicheur);
				} catch (\Exception $e) {
					echo "Autre chose que espace_point: on passe";
					$ok_pour_ajout=false;
				}
				if ($ok_pour_ajout) {
					$nicheur->def_statut();
					if ($nicheur->n_statut >= NICHEUR_POSSIBLE) {
						$this->nicheurs[] = $nicheur;
						echo "+";
						flush();
					} else {
						echo "x";
						flush();
					}
				}
			}
		}
		$this->info("terminé $n_pass passes");
	}

	function aonfm_xml($db) {
		$msgs = array();
		// referentiel espece lpo
		$sql = "select * from referentiel_especes_tiers where tiers='visionature'";
		$q = bobs_qm()->query($db, 'aonfm_visio_gesv', $sql, array());
		$referentiel = array();
		while ($e = bobs_element::fetch($q)) {
			$referentiel[$e['id_espece']] = $e['id_tiers'];
		}
		$doc = new DOMDocument('1.0', 'utf-8');
		$doc->formatOutput = true;
		$data = $doc->createElement('data');
		for ($y=2009;$y<=2012;$y++) {
			$year = $doc->createElement('year');
			$year->setAttribute('value', "$y");
			$cells = $doc->createElement('cells');
			foreach (bobs_espace_l93_10x10::tous($db) as $c) {
				$cell = $doc->createElement('cell');
				$cell->setAttribute('name', $c['nom']);
				$pressure = 'A';
				$aonfm = new bobs_aonfm_restitution_carre($db, $c['id_espace'], $y);
				$resultats = $aonfm->get_especes_etats();
				foreach ($resultats as $id_espece=>$r) {
					$pressure = max(trim($r['classe']), $pressure);
					$sn = 0; // statut nicheur
					if (!is_null($r['statut_resp']))
						$sn = $r['statut_resp'];

					if (isset($r['statut_lpo']) && !is_null($r['statut_lpo']))
						$sn = max($sn, $r['statut_lpo']);

					if (is_null($r['statut_resp']) && is_null($r['statut_lpo']))
						$sn = $r['statut_auto'];

					if ($sn > 0) {
						if (!array_key_exists($id_espece, $referentiel)) {
							$msgs[] = 'ignore '.$id_espece;
							continue;
						}
						$specie = $doc->createElement('specie');
						$specie->setAttribute('id', $referentiel[$id_espece]);
						switch($sn) {
							case NICHEUR_POSSIBLE:
								$specie->setAttribute('atlas', 'possible');
								break;
							case NICHEUR_PROBABLE:
								$specie->setAttribute('atlas', 'probable');
								break;
							case NICHEUR_CERTAIN:
								$specie->setAttribute('atlas', 'confirmed');
								break;
						}
						$cell->appendChild($specie);
					}
				}
				$cell->setAttribute('pressure', $pressure);
				$cells->appendChild($cell);
			}
			$year->appendChild($cells);
			$data->appendChild($year);
		}
		$doc->appendChild($data);
		return $doc->saveXML();
	}

	function aonfm_tri_sys2($a,$b) {
		if (is_object(!$a['espece'])) return 0;

		if ($a['espece']->systematique == $b['espece']->systematique) return 0;
		return ((int)$a['espece']->systematique > (int)$b['espece']->systematique);
	}

}
