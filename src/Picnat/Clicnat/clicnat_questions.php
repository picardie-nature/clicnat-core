<?php
namespace Picnat\Clicnat;

class clicnat_questions extends bobs_element {
	protected $id_question;
	protected $date_creation;
	protected $date_modif;
	protected $id_utilisateur;
	protected $titre;
	protected $fermee;

	const __table__ = 'questions';
	const __prim__ = 'id_question';
	const __seq__ = 'questions_id_question_seq';

	public function __construct($db, $id) {
		parent::__construct($db, self::__table__, self::__prim__, $id);
		$this->champ_date_maj = 'date_modif';
	}

	public function __get($k) {
		switch ($k) {
			case 'titre':
				return $this->titre;
				break;
			case 'fermee':
				return $this->fermee == 't';
				break;
		}
	}

	public static function creer($db, clicnat_utilisateur $utilisateur, $titre, array $xy) {
		$titre = trim($titre);
		if (empty($titre))
			throw new \Exception("pas de titre");
		$data = [
			self::__prim__ => self::nextval($db, self::__seq__),
			'geom' => sprintf("SRID=4326;POINT(%F %F)",$xy[0],$xy[1]),
			'id_utilisateur' => $utilisateur->id_utilisateur,
			'titre' => $titre
		];
		parent::insert($db, self::__table__, $data);
		return $data[self::__prim__];
	}

	const sql_reponses = 'select * from questions_reponses where id_question=$1 order by id_reponse';

	public function reponses() {
		$q = bobs_qm()->query($this->db, 'qr_reponses', self::sql_reponses, [$this->id_question]);
		return new clicnat_iterateur_reponses($this->db, array_column(self::fetch_all($q), 'id_reponse'));
	}

	public function auteur() {
		return get_utilisateur($this->db, $this->id_utilisateur);
	}

	public function date() {
		return strftime("%d-%m-%Y %H:%M", strtotime($this->date_creation));
	}

	public function repondre($id_utilisateur, $message) {
	}

	const sql_liste = "select *,st_x(geom),st_y(geom) from questions where fermee=false";

	public static function features($db) {
		$q = bobs_qm()->query($db, 'qr_listes', self::sql_liste, []);
		$t = [];
		while ($r = self::fetch($q)) {
			$t[] = [
				"type" => "Feature",
				"geometry" => [ "type" => "Point", "coordinates" => [(float)$r['st_x'],(float)$r['st_y']] ],
				"properties" => [
					"classe" => "question",
					"titre" => $r['titre'],
					"id_question" => $r['id_question']
				]
			];
		}
		return $t;
	}
}
