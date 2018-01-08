<?php
namespace Picnat\Clicnat;

use Michelf\Markdown;

/**
 * @brief classe de base pour les "controleurs" des sites de saisies
 */
abstract class clicnat_smarty extends \Smarty {
	protected $db;

	use clicnat_http_headers;
	use clicnat_autocompletes;

	const k_s_alertes = '__alertes';

	public function __construct($db, $template_dir=null, $compile_dir=null, $config_dir=null, $cache_dir='/tmp/clicnat_cache_dfl') {
		parent::__construct();
		if (!defined('LOCALE')) {
			define('LOCALE','fr_FR');
		}
		setlocale(LC_ALL, LOCALE);
		if (defined('CLICNAT2')) {
			// c'est déjà fait
		} else {
			$this->template_dir = $template_dir; //SMARTY_TEMPLATE_QG;
			$this->compile_dir = $compile_dir; //SMARTY_COMPILE_QG;
			$this->config_dir = $config_dir; //SMARTY_CONFIG_QG;
			$this->cache_dir = $cache_dir;
		}

		$this->db = $db;

		if (!defined('CLICNAT2')) {
			if (!file_exists($this->compile_dir)) {
				if (!mkdir($this->compile_dir)) {
					throw new \Exception("ne peux pas créer le dossier {$this->compile_dir}");
				}
			}
			if (!file_exists($this->cache_dir)) {
				if (!mkdir($this->cache_dir)) {
					throw new \Exception("ne peux pas créer le dossier {$this->cache_dir}");
				}
			}
		}
		if (method_exists($this, 'register_function')) { // Smarty 2.x
			$this->register_function('get_espece', array(&$this,'bobs_espece'));
			$this->register_function('texte', array(&$this,'smarty_function_texte'));
			$this->register_function('doc', array(&$this,'bobs_doc'));
			$this->register_function('commtr_txt', array(&$this,'smarty_commtr_txt'));
			$this->register_modifier('markdown', 'smarty_modifier_markdown');
			$this->register_modifier('markdown_txt', 'smarty_modifier_markdown_txt');
		} else { // Smarty 3.x
			$this->registerPlugin('function', 'get_espece', [$this,'bobs_espece']);
			$this->registerPlugin('function', 'texte', [$this,'smarty_function_texte']);
			$this->registerPlugin('function', 'doc', [$this,'bobs_doc']);
			$this->registerPlugin('function', 'commtr_txt', [$this, 'smarty_commtr_txt']);
			//$this->registerPlugin('modifier', 'markdown', 'smarty_modifier_markdown');
			//$this->registerPlugin('modifier', 'markdown_txt', 'smarty_modifier_markdown_txt');
		}
		$this->alertes = array();
	}

	public function __call($method, $arguments) {
		// assign_by_ref existe pas dans smarty3 et est pas reprit dans BC
		if ($method == 'assign_by_ref') {
			return $this->assign($arguments[0], $arguments[1]);
		}
	}

	/**
	 * @brief ajoute une alerte a afficher
	 * @param $classe la classe du message : error, success ou info
	 * @param $message le message a afficher
	 */
	public function ajoute_alerte($classe, $message) {
		$this->alertes[] = array("classe"=>$classe, "message"=>$message);
		$this->assign_by_ref('alertes', $this->alertes);
	}

	static function cli(&$ent, $opt=bobs_tests::null_si_vide) {
		return bobs_tests::cli($ent, $opt);
	}

	static function cls(&$ent) {
		return bobs_tests::cls($ent);
	}

	protected function classes_especes() {
		$t = array();
		foreach (bobs_classe::get_classes() as $kc) {
			$t[$kc] = bobs_classe::get_classe_lib_par_lettre($kc);
		}
		return $t;
	}

	public function smarty_commtr_txt($data) {
		$params = $data['commentaire'];
		if ($params['type_commentaire'] == 'attr') {
			if (preg_match("/^tag ([\+\-])(\d+)$/", $params['commentaire'],  $matches)) {
				$tag = get_tag($this->db, $matches[2]);
				$params['commentaire'] = "étiquette <i>{$tag}</i> ".($matches[1]=='+'?"ajoutée":"retirée");
			} else if (preg_match("/^(\w+) (.+) => (.+)/", $params['commentaire'], $matches)) {
				list($tt,$champ,$v1,$v2) = $matches;
				$params['commentaire'] = "Modification du champ $champ : <i>$v1</i> remplacée par <i>$v2</i>";
				switch ($champ) {
					case 'age':
						$params['commentaire'] = "âge modifié : $v1 remplace $v2";
						break;
					case 'nb':
						$params['commentaire'] = "effectif modifié : $v1 remplace $v2";
						break;
					case 'id_espece':
						$db = get_db();
						$e1 = get_espece($db, $v1);
						$e2 = get_espece($db, $v2);
						$e1 = $e1?$e1:$v1;
						$e2 = $e2?$e2:$v2;
						$params['commentaire'] = "identification modifiée <i>{$e1}</i> remplace <i>{$e2}</i>";
						break;
					case 'indice_qualite':
						try {
							$i1 = new bobs_indice_qualite($v1);
						} catch (\Exception $e) {
							$i1 = "inconnu";
						}
						try {
							$i2 = new bobs_indice_qualite($v2);
						} catch (\Exception $e) {
							$i2 = "inconnu";
						}
						$params['commentaire'] = "niveau de certitude identification passe de <i>{$i1}</i> a <i>{$i2}</i>";
						break;
				}

			}
		}
		return $params['commentaire'];
	}

	public function smarty_function_texte($params) {
		static $htmlpurifier;
		if (!$htmlpurifier) {
			$config = \HTMLPurifier_Config::createDefault();
			$config->set('Autoformat.AutoParagraph', true);
			$config->set('AutoFormat.RemoveEmpty', true);
			$config->set('AutoFormat.RemoveEmpty.RemoveNbsp', true);
			$htmlpurifier = new \HTMLPurifier($config);
		}

		$nom = $params['nom'];
		if (empty($nom)) {
			throw new \Exception('usage : {texte nom="ref_du_texte"}');
		}
		try {
			$texte = clicnat_textes::par_nom($this->db, $nom);
			return $htmlpurifier->purify(Markdown::defaultTransform(trim($texte->texte))."<span title=\"{$nom}\">&bull;</span>");
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * @brief proxy pour obtenir un objet bobs_espece dans les templates
	 *
	 * La fonction enregistrée dans smarty est get_espece.
	 *
	 * pour obtenir une instance dans smarty
	 *
	 *   {get_espece id=$var_id_espece}
	 *
	 * affichera le nom de l'espèce, pour mettre l'instance dans une variable
	 * du template
	 *
	 *   {get_espece id=$var_id_espece var=espece}
	 *   {$espece->nom_s}
	 *
	 * ne fonctionne pas dans smarty 3
	 */
	public function bobs_espece($params) {
		$esp = get_espece($this->db, $params['id']);
		if (!empty($params['var'])) {
			$this->assign($params['var'], $esp);
			return '';
		} else {
			return $esp;
		}
	}

	public function bobs_doc($params, &$smarty) {
		$this->assign($params['var'], bobs_document::get_instance($params['id']));
	}

	/**
	 * @brief Redirection et arret du script
	 *
	 * @param <string> $dest url
	 */
	public function redirect($dest) {
		if (is_array($this->alertes) && count($this->alertes) > 0) {
			$_SESSION[self::k_s_alertes] = json_encode($this->alertes);
		} else {
			$_SESSION[self::k_s_alertes] = false;
		}
		header(sprintf('Location: %s',$dest));
		echo sprintf('<a href="%s">%s</a>', $dest, $dest);
		echo sprintf('<script>document.location = "%s";</script>', $dest);
		exit(0);
	}

	protected function before_autocomplete_espece() {
		return $this->before_espece_autocomplete2();
	}

	protected function before_espece_autocomplete2() {
		$r = $this->array_autocomplete_espece();
		echo json_encode($r);
		exit();
	}

	protected function before_autocomplete_commune() {
		bobs_element::cls($_GET['term']);
		$tt = array();
		if (!empty($_GET['term'])) {
			$s = str_replace(
				["é","è","ê","ï","î","ô"],
				["e","e","e","i","i","o"],
				$_GET['term']);
			$t = bobs_espace_commune::rechercher($this->db, array("nom" => $s));
			if (is_array($t)) {
				foreach ($t as $l) {
		    	$tt[] = [
						'label'=>sprintf("%s (%02d) ",$l->nom,$l->dept),
						'value'=>$l->id_espace
					];
				}
			}
		}
		// change pas tous les jours
		$this->header_cacheable(86400);
		echo json_encode($tt);
		exit();
	}

	protected function before_autocomplete_departement() {
		bobs_element::cls($_GET['term']);
		$t = bobs_espace_departement::rechercher($this->db, array("nom" => $_GET['term']));
		$tt = array();
		foreach ($t as $l) {
			$tt[] = array('label' => sprintf("%s (%02d)",$l->nom, $l->reference), 'value'=>$l->id_espace);
		}
		echo json_encode($tt);
		// change pas tous les jours
		$this->header_cacheable(86400);
		exit();
	}

	protected function __before_autocomplete_observateur() {
		bobs_element::cls($_GET['term']);
		$t = bobs_utilisateur::rechercher2($this->db, $_GET['term']);
		$tt = array();
		if (is_array($t))
			foreach ($t as $l)
				$tt[] = array('label'=>$l->nom.' '.$l->prenom, 'value'=>$l->id_utilisateur);
		echo json_encode($tt);
		$this->header_cacheable(3600);
		exit();
	}

	protected function before_autocomplete_tag_citation() {
		bobs_element::cls($_GET['term']);
		$tags = bobs_tags::recherche_tag_citation($this->db, $_GET['term']);
		$tt = array();
		foreach ($tags as $tag) {
			$tt[] = array('label' => $tag->lib, 'value' => $tag->id_tag);
		}
		echo json_encode($tt);
		$this->header_cacheable(3600);
		exit();
	}

	protected function before_commune_gml() {
		$this->header_xml();
		$this->header_cacheable(86400);
		$commune = get_espace_commune($this->db, $_GET['id']);
		echo '<clicnat xmlns:gml="http://www.opengis.net/gml">';
		echo $commune->get_geom_gml();
		echo "</clicnat>";
		exit();
	}

	protected function before_commune_geojson() {
		$this->header_json();
		$commune = get_espace_commune($this->db, $_GET['id']);
		echo $commune->get_geom_json();
		exit();
	}

	public function display($tpl=null, $cache_id=null, $compile_id=null, $parent = null) {
		if (isset($_SESSION[self::k_s_alertes]) && $_SESSION[self::k_s_alertes] != false) {
			$this->alertes = json_decode($_SESSION[self::k_s_alertes],true);
			$_SESSION[self::k_s_alertes] = false;
		}
		$this->assign('alertes', $this->alertes);
		return parent::display($tpl, $cache_id, $compile_id);
	}
}
