<?php
namespace Picnat\Clicnat;

/**
 * @brief Photos, image, enregistrement sonore
 */
class bobs_document {
	private $xml_doc;
	private $f_content_xml;
	protected $f_blob;
	protected $c_path;
	private $doc_dir;

	protected $db;

	public function __construct($doc_dir, $db=null) {
		if (empty($doc_dir))
			throw new \Exception('doc_id vide');
		// chemin complet vers le fichier
		if (!preg_match('/^[a-f0-9]{13}$/', $doc_dir))
			throw new \Exception('doc_id invalide (1) '.$doc_dir);

		$this->c_path = sprintf('%s/%s', DOCS_BASE_DIR, $doc_dir);

		if (!file_exists($this->c_path))
			throw new \Exception('doc_id invalide (2)');

		$this->f_content_xml = $this->c_path.'/contents.xml';
		$this->f_blob = $this->c_path.'/blob.bin';
		$this->charge_xml();
		$this->doc_dir = $doc_dir;

		$this->db = $db;
	}

	public static function getInstance($doc_id) {
		static $instances;
		if (!isset($instances)) {
			$instances = [];
		}
		if (!isset($instances[$doc_id])) {
			try {
				$instances[$doc_id] = new bobs_document($doc_id);
			} catch (\Exception $e) {
				return false;
			}
		}
		return $instances[$doc_id];
	}

	public function get_doc_id() {
		return $this->doc_dir;
	}

	private function charge_xml() {
		$this->xml_doc = new DOMDocument();
		$this->xml_doc->load($this->f_content_xml);
	}

	public function sauve_xml() {
		$this->xml_doc->save($this->f_content_xml);
		$this->charge_xml();
	}

	public function ajoute_auteur($nom_prenom, $id_utilisateur=null) {
		$auteur = $this->xml_doc->createElement('auteur');
		if (!empty($id_utilisateur))
			$auteur->setAttribute('id_utilisateur', $id_utilisateur);
		$auteur->nodeValue = $nom_prenom;
		$this->xml_doc->firstChild->appendChild($auteur);
		$this->sauve_xml();
	}

	public function get_auteur() {
		$liste = $this->xml_doc->getElementsByTagName('auteur');
		foreach ($liste as $ele) {
			return $ele->nodeValue;
		}
	}

	public function get_type() {
		return $this->xml_doc->firstChild->getAttribute('type');
	}

	public function get_format() {
		return $this->xml_doc->firstChild->getAttribute('format');
	}

	/**
	 * @brief création d'un nouvel espace pour stocker un document
	 * @return array : list($doc_id, $dossier, $chemin_vers_blob)
	 */
	private static function nouvel_emplacement() {
		for ($i=0; $i<3; $i++) {
			$d = uniqid();
			$path = sprintf("%s/%s/blob.bin", DOCS_BASE_DIR, $d);
			if (file_exists($path)) {
				$erreur = true;
			} else {
				$erreur = false;
				break;
			}
		}
		if ($erreur) {
			throw new \Exception('upload : ne peut pas créer de répertoire');
		}
		$create_dir = sprintf("%s/%s", DOCS_BASE_DIR, $d);
		if (!mkdir($create_dir)) {
			throw new \Exception('upload erreur création dossier (1) : '.$create_dir);
		}
		return [$d, $create_dir, $path];
	}

	public static function sauve_fichier($path_src) {
		list($doc_id, $create_dir, $path_dest) = self::nouvel_emplacement();
		copy($path_src, $path_dest);
		self::creation_donnees_xml($path_dest, $create_dir);
		return $doc_id;
	}

	/**
	 * @brief Create document from base64 encoded Image
	 */
	public static function sauve_base64($data) {
		list($doc_id, $create_dir, $path_dest) = self::nouvel_emplacement();
		// remove data type & base64 tag
		list($type, $data) = explode(';', $data);
    list(, $data)      = explode(',', $data);
    $data = base64_decode($data);

    file_put_contents($path_dest, $data);
		self::creation_donnees_xml($path_dest, $create_dir);

		return $doc_id;
	}

	private function creation_donnees_xml($path, $create_dir) {
		$mime = self::get_mime($path);
		switch ($mime) {
			case 'image/gif':
				$xml = '<file type="image" format="gif"></file>';
				break;
			case 'image/jpeg':
				$xml = '<file type="image" format="jpeg"></file>';
				break;
			case 'image/png':
				$xml = '<file type="image" format="png"></file>';
				break;
			case 'audio/mpeg':
				$xml = '<file type="audio" format="mpeg"></file>';
				break;
			case 'application/pdf':
				$xml = '<file type="pdf" format="pdf"></file>';
				break;
			default:
				throw new \Exception('upload : mime inconnu '.$mime);
		}
		file_put_contents($create_dir.'/contents.xml', $xml);
	}

	public static function sauve($_files_ligne) {
		// ['name'] nom original du fichier
		// ['type'] type mime (envoyé par le navigateur pas fiable)
		// ['size'] taille en octets
		// ['tmp_name'] nom du fichier temporaire
		// ['error'] code d'erreur

		list($d,$create_dir,$path) = self::nouvel_emplacement();


		$f_src = $_files_ligne['tmp_name'];

		if (!file_exists($f_src)) {
			throw new \Exception("upload : le fichier à déplacer n'existe pas $f_src");
		}

		if (!move_uploaded_file($f_src, $path)) {
			unlink($f_src);
			throw new \Exception('upload : ne peux pas copier le fichier envoyé');
		}

		self::creation_donnees_xml($path, $create_dir);

		return $d;
	}

	public static function get_instance($doc_id, $db=null) {
		$doc = new bobs_document($doc_id,$db);
		switch ($doc->get_type()) {
			case 'image':
				return new bobs_document_image($doc_id, $db);
				break;
			case 'audio':
				return new bobs_document_audio($doc_id, $db);
				break;
			case 'pdf':
				return new bobs_document_pdf($doc_id, $db);
				break;

			default:
				$type = $doc->get_type();
		}
		throw new \Exception("$type inconnu");
	}

	public static function get_mime($path) {
		// FILEINFO_MIME_TYPE = 16
		$finf = finfo_open(16);
		$mime = finfo_file($finf, $path);
		finfo_close($finf);
		return $mime;
	}

	const sql_attente = 'update especes_documents set en_attente=$2 where document_ref=$1';
	const sql_attente_liste = 'select document_ref from especes_documents where en_attente=true';
	const sql_doc_espece = 'select * from especes_documents where document_ref=$1';

	public function mettre_en_attente() {
		return bobs_qm()->query(get_db(), 'doc_attente', self::sql_attente, [$this->get_doc_id(),'t']);
	}

	public function enlever_en_attente() {
		return bobs_qm()->query(get_db(), 'doc_attente', self::sql_attente, [$this->get_doc_id(),'f']);
	}

	public function est_en_attente() {
		$q = bobs_qm()->query(get_db(), 'doc_ex_attente', self::sql_doc_espece, [$this->get_doc_id()]);
		$r = bobs_element::fetch($q);
		if ($r == false) {
			throw new \Exception("pas associé à un taxon pour illustration");
		}
		return $r['en_attente'] == 't';
	}

	static function liste_en_attente($db=null) {
		$db = get_db();
		$r = [];
		$q = bobs_qm()->query($db, 'doc_ls_attente', self::sql_attente_liste, []);
		while ($l = bobs_element::fetch($q)) {
			$doc_dir = $l['document_ref'];
			$d = new bobs_document($doc_dir, $db);
			if ($d->get_type() == 'image') {
				$d = new bobs_document_image($doc_dir, $db);
			} else if ($d->get_type() == 'audio') {
				$d = new bobs_document_audio($doc_dir, $db);
			}
			$r[] = $d;
		}
		return $r;
	}

	const sql_select_id_espece = 'select id_espece from especes_documents where document_ref=$1';

	public function get_doc_espece() {
		$q = bobs_qm()->query($this->db, 'b_g_id_esp', self::sql_select_id_espece, [$this->get_doc_id()]);
		$r = bobs_element::fetch($q);
		return get_espece($this->db, $r['id_espece']);
	}

	public function get_doc_id_citation() {
		/**
		 * @todo a faire
		 */
	}

	public function backup() {
		$f_back = $this->f_blob.'.back';

		if (file_exists($f_back)) {
			unlink($f_back);
		}

		if (!link($this->f_blob, $f_back)) {
			throw new \Exception("Ne peut pas faire le lien {$this->f_blob} -&gt; $f_back");
		}

		return true;
	}

	public function restore() {
		$f_back = $this->f_blob.'.back';
		if (file_exists($f_back)) {
			unlink($this->f_blob);
			if (!link($f_back, $this->f_blob)) {
				throw new \Exception('ne peut restaurer ancien document');
			}
			unlink($f_back);
			return true;
		} else {
			return false;
		}
	}

	public function a_un_backup() {
		$f_back = $this->f_blob.'.back';
		return file_exists($f_back);
	}
}
