<?php
namespace Picnat\Clicnat;

class bobs_selection_filtrer_observateurs extends bobs_selection_action {
	protected $id_selection;
	protected $t_id_utilisateur;
	protected $in;

	public function __construct($db) {
		parent::__construct($db);
		$this->allowed_varnames[] = 'id_selection';
		$this->allowed_varnames[] = 't_id_utilisateur';
	}

	public function prepare() {
		if (empty($this->id_selection)) {
			$this->ready = false;
			return false;
		}

		if (count($this->t_id_utilisateur) <= 0) {
			$this->ready = false;
			return false;
		}
		$this->in = '{';
		foreach ($this->t_id_utilisateur as $u) {
			self::cli($u);
			$this->in .= $u.',';
		}
		$this->in = trim($this->in, ',');
		$this->in .= '}';
	}

	public function execute() {
		if (empty($this->in))
			return false;
		$sql = "delete from selection_data where id_selection=%d and bob_diffusion_restreinte(id_citation, '%s')";
		bobs_log("selection filtre diffusion restreinte {$this->id_selection} {$this->in}");
		bobs_element::query($this->db, sprintf($sql, $this->id_selection, $this->in));
		$this->messages[] = count($this->t_id_utilisateur).' observateurs retirés';
	}
}
