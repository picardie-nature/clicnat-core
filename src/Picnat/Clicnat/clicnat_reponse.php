<?php
namespace Picnat\Clicnat;

class clicnat_reponse extends bobs_element {
	protected $id_reponse;
	protected $id_question;
	protected $id_utilisateur;
	protected $date_creation;
	protected $texte;

	const __table__ = 'questions_reponses';
	const __prim__ = 'id_reponse';
	const __seq__ = 'questions_reponses_id_reponse_seq';

	public function __construct ($db, $id) {
		parent::__construct($db, self::__table__, self::__prim__, $id);
	}

	public static function enregistrer_reponse($db, $id_question, clicnat_utilisateur $utilisateur, $texte) {
		$texte = trim($texte);
		if (empty($texte))
			throw new \Exception("pas de texte");
		$data = [
			self::__prim__ => self::nextval($db, self::__seq__),
			'id_question' => $id_question,
			'id_utilisateur' => $utilisateur->id_utilisateur,
			'texte' => $texte
		];
		parent::insert($db, self::__table__, $data);
		return $data[self::__prim__];
	}

	public function auteur() {
		return get_utilisateur($this->db, $this->id_utilisateur);
	}

	public function date() {
		return strftime("%d-%m-%Y %H:%M", strtotime($this->date_creation));
	}

	public function __get($k) {
		switch ($k) {
			case 'texte':
				return $this->texte;
		}
	}
}
