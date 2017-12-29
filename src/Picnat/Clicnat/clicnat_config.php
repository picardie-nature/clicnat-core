<?php
namespace \Picnat\Clicnat;

/**
 * @brief Intéroge le fichier de configuration XML
 */
class clicnat_config extends DOMDocument {
	private $doc;

	function __construct($fichier) {
		parent::__construct('1.0', 'UTF-8');
		if (!$this->load($fichier)) {
			throw new \Exception('échec chargement configuration');
		}
	}

	public function query($xpath_q) {
		$xp = new DOMXpath($this);
		if (!$xp)
			throw new \Exception('expression : '.$xpath_q.' invalide');
		return $xp->query($xpath_q);
	}

	private function query_tab_attr_id_nom($xpath_expression) {
		$r = $this->query($xpath_expression);
		$t = [];
		foreach ($r as $e) {
			$t[$e->getAttribute('id')] = $e->getAttribute('nom');
		}
		asort($t);
		return $t;

	}

	/**
	 * @brief Liste les structures qui sont ok pour saisie direct
	 * @return array clé:valeur id=>nom
	 */
	public function structures_ok_pour_saisie() {
		return $this->query_tab_attr_id_nom('/clicnat/structures/structure[@saisie=1]');
	}

	/**
	 * @brief Liste toutes les structures
	 * @return array clé:valeurs id => nom
	 */
	public function structures() {
		return $this->query_tab_attr_id_nom('/clicnat/structures/structure');
	}

	const sql_protos = 'select id_protocole,lib from protocoles order by lib';
	const sql_protos_ouvert = 'select id_protocole,lib from protocoles where ouvert=true order by lib';

	/**
	 * @brief Liste tous les protocoles
	 * @return array clé:valeurs id => nom
	 */
	public function protocoles() {
		$db = get_db();
		$q = bobs_qm()->query($db, 'protos_l', self::sql_protos, []);
		$r = bobs_element::fetch_all($q);
		$tr = [];
		foreach ($r as $l) {
			$tr[$l['id_protocole']] = $l['lib'];
		}
		return $tr;
	}

	/**
	 * @brief Liste les protocoles en cours
	 * @return array clé:valeurs id => nom
	 */
	public function protocoles_en_cours() {
		$db = get_db();
		$q = bobs_qm()->query($db, 'protos_l_o', self::sql_protos_ouvert, []);
		$r = bobs_element::fetch_all($q);
		$tr = [];
		foreach ($r as $l) {
			$tr[$l['id_protocole']] = $l['lib'];
		}
		return $tr;
	}


	/**
	 * @brief Emprise initiale des cartes
	 * @param $srid_dest : code epsg
	 * @return un table minx miny maxx maxy
	 *
	 * Exemple de configuration :
	 * <cartographie>
	 *    <extent srid="2154" minx="570000" miny="6840000" maxx="790000" maxy="7450000"/>
	 * </cartographie>
	 */
	public function extent($srid_dest) {
		require_once(OBS_DIR.'cartographie.php');
		$exts = $this->query('/clicnat/cartographie/extent');
		foreach ($exts as $ext) continue;
		$srid = $ext->getAttribute('srid');
		$srid = (int)$srid;
		$srid_dest = (int)$srid_dest;
		$extent = [
			'minx' => $ext->getAttribute('minx'),
			'miny' => $ext->getAttribute('miny'),
			'maxx' => $ext->getAttribute('maxx'),
			'maxy' => $ext->getAttribute('maxy')
		];
		if ($srid_dest != $srid) {
			$pt = bobmap_point_reproject($extent['minx'], $extent['miny'], $srid, $srid_dest);
			$extent['minx'] = $pt[0];
			$extent['miny'] = $pt[1];
			$pt = bobmap_point_reproject($extent['maxx'], $extent['maxy'], $srid, $srid_dest);
			$extent['maxx'] = $pt[0];
			$extent['maxy'] = $pt[1];
		}
		return $extent;
	}

	/**
	 * @brief Valeur du noeud de la première réponse
	 * @param $xpath_expression Expression XPATH
	 * @return string
	 */
	public function query_nv($xpath_expression) {
		$q = $this->query($xpath_expression);
		if (!$q)
			return false;
		foreach ($q as $e) {
			return $e->nodeValue;
		}
		return false;
	}
}
