<?php
namespace Picnat\Clicnat;

class bobs_aonfm_choix_resp_tps_abond {
	const id_liste_especes_a_denombrer_a = 52;
	const id_liste_especes_a_denombrer_b = 53;
	const fourchette_large = 'f_large';
	const fourchette_precise = 'f_precise';
	const chiffre_precis = 'c_precis';
	const raretes = 'TR,R,AR,E';
	const menaces = 'VU,EN,CR,NT';

	const sql_choix_existe = 'select count(*) as n from aonfm_choix_responsable_tps_abond where id_espace=$1 and id_espece=$2';
	const sql_choix_init = 'insert into aonfm_choix_responsable_tps_abond (id_espace,id_espece) values ($1,$2)';
	const sql_choix_update = 'update aonfm_choix_responsable_tps_abond set abondance_liste=$3, abondance_n=$4 where id_espace=$1 and id_espece=$2';
	const sql_select = 'select * from aonfm_choix_responsable_tps_abond where id_espace=$1 and id_espece=$2';
	const sql_select_r = 'select * from aonfm_choix_responsable_tps_abond where id_espace=$1';

	public static function get_resultats($db, $id_espace) {
		$t = [];
		$q = bobs_qm()->query($db, 'gresult', self::sql_select_r, [$id_espace]);
		while ($r = bobs_element::fetch($q)) {
			$s = '';
			if ($r['abondance_liste'] > 0) {
				$s = "classe ".((int)$r['abondance_liste']/100);
			}
			if ($r['abondance_n'] > 0) {
				$s = $r['abondance_n'];
			}
			$t[$r['id_espece']] = empty($s)?'?':$s;
		}
		return $t;
	}

	public static function get($db, $id_espace, $id_espece) {
		$t = [
			'abondance_liste' => 0,
			'abondance_n' => 0
		];
		$q = bobs_qm()->query($db, 'qsel', self::sql_select, [$id_espace, $id_espece]);
		$r = bobs_element::fetch($q);
		if (pg_num_rows($q)== 1) {
			$t['abondance_liste'] = $r['abondance_liste'];
			$t['abondance_n'] = $r['abondance_n'];
		}
		return $t;
	}

	public static function enregistrer($db,$id_espace,$id_espece,$abondance_liste,$abondance_n) {
		$q = bobs_qm()->query($db, 'qexiste', self::sql_choix_existe, [$id_espace,$id_espece]);
		$r = bobs_element::fetch($q);
		if ($r['n'] != 1) {
			bobs_qm()->query($db, 'qinsert', self::sql_choix_init, [$id_espace, $id_espece]);
		}
		if (!empty($abondance_n)) $abondance_liste=0;
		$abondance_n = (int)$abondance_n;
		return bobs_qm()->query($db, 'qupdate', self::sql_choix_update, array($id_espace, $id_espece, $abondance_liste, $abondance_n));
	}

	public static function liste_classes_large () {
		static $classes;
		if (!isset($classes)) {
			$classes = [
				100 => ['id'=>100, 'min'=>1, 'max'=>9],
				200 => ['id'=>200, 'min'=>10, 'max'=>99],
				300 => ['id'=>300, 'min'=>100, 'max'=>999],
				400 => ['id'=>400, 'min'=>1000, 'max'=> 999999]
			];
		}
		return $classes;
	}

	public static function liste_classes_precise () {
		static $classes;
		if (!isset($classes)) {
			$classes = [
				100 => ['id'=>100, 'min'=>1, 'max'=>5],
				105 => ['id'=>105, 'min'=>6, 'max'=>9],
				200 => ['id'=>200, 'min'=>10, 'max'=>24],
				210 => ['id'=>210, 'min'=>25, 'max'=>49],
				215 => ['id'=>215, 'min'=>50, 'max'=>74],
				220 => ['id'=>220, 'min'=>75, 'max'=>99],
				305 => ['id'=>305, 'min'=>100, 'max'=>249],
				310 => ['id'=>310, 'min'=>250, 'max'=>499],
				315 => ['id'=>315, 'min'=>500, 'max'=>749],
				320 => ['id'=>320, 'min'=>749, 'max'=>999],
				400 => ['id'=>400, 'min'=>1000, 'max'=> 999999],
			];
		}
		return $classes;
	}

	public static function classe_large($nb_couples) {
		foreach (self::liste_classes_large() as $id_classe => $classe) {
			if (($nb_couples >= $classe['min']) && ($nb_couples <= $classe['max'])) {
				return $id_classe;
			}
		}
		return null;
	}

	public static function requis($db, $espece) {
		static $raretes;
		static $menaces;
		static $liste_a;
		static $liste_b;

		if (is_object($espece)) {
			if (get_class($espece) != 'bobs_espece') {
				$espece = get_espece($db, $espece->id_espece);
			}
		} else {
			$espece = get_espece($db, $espece->id_espece);
		}

		$rr = $espece->get_referentiel_regional();

		$r = [];

		$r[self::fourchette_large] = 1;
		$r[self::fourchette_precise] = 0;
		$r[self::chiffre_precis] = 0;
		if (!isset($raretes)) $raretes = explode(',', self::raretes);
		if (!isset($menaces)) $menaces = explode(',', self::menaces);

		if ((array_search($espece->get_indice_rar(), $raretes) !== false)
			||(array_search($espece->get_degre_menace(), $menaces) !== false)) {

			$r[self::fourchette_large] = 0;
			$r[self::fourchette_precise] = 1;
			$r[self::chiffre_precis] = 1;
			return $r;
		}

		if (!isset($liste_a)) $liste_a = new clicnat_listes_especes($db, self::id_liste_especes_a_denombrer_a);
		if (!isset($liste_b)) $liste_b = new clicnat_listes_especes($db, self::id_liste_especes_a_denombrer_b);

		if ($liste_a->a_espece($espece) || $liste_b->a_espece($espece)) {
			$r[self::fourchette_large] = 0;
			$r[self::fourchette_precise] = 1;
			$r[self::chiffre_precis] = 1;
		}

		return $r;
	}
}
