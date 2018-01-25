<?php
namespace Picnat\Clicnat;

class bobs_selection_extraction_nicheurs extends bobs_selection_action {
	protected $id_selection;
	protected $especes;
	protected $id_utilisateur;
	protected $selection;
	protected $selection_possible;
	protected $selection_probable;
	protected $selection_certain;

	public function __construct($db) {
		parent::__construct($db);
		$this->allowed_varnames[] = 'id_selection';
	}

	public function prepare() {
		if (empty($this->id_selection))
			return false;

		$this->selection = new bobs_selection($this->db, $this->id_selection);
		$this->especes = $this->selection->get_especes();
		$this->selection_possible = bobs_selection::nouvelle($this->db, $this->selection->id_utilisateur, $this->selection->nom_selection.' (nicheur possible)');
		$this->selection_probable = bobs_selection::nouvelle($this->db, $this->selection->id_utilisateur, $this->selection->nom_selection.' (nicheur probable)');
		$this->selection_certain = bobs_selection::nouvelle($this->db, $this->selection->id_utilisateur, $this->selection->nom_selection.' (nicheur certain)');
		parent::prepare();
	}

	public function execute() {
		ob_start();
		$s_possible = new bobs_selection($this->db, $this->selection_possible);
		$s_probable = new bobs_selection($this->db, $this->selection_probable);
		$s_certain = new bobs_selection($this->db, $this->selection_certain);

		foreach ($this->especes as $espece) {
			if ($espece['classe'] != 'O') {
				continue;
			}

			$aonfm = new bobs_aonfm_selection($this->db, $espece['id_espece'], null);
			$aonfm->run($this->selection->id_selection);

			if (count($aonfm->nicheurs) > 0)
			foreach ($aonfm->nicheurs as $nicheur) {
				$nicheur->def_statut();
				$s_out = false;
				switch ($nicheur->statut) {
					case 'possible':
						$s_out = $s_possible;
						break;
					case 'probable':
						$s_out = $s_probable;
						break;
					case 'certain':
						$s_out = $s_certain;
						break;
				}

				if( !$s_out) continue;

				$added = array();

				foreach ($nicheur->citations as $citation) {
					if ($citation->id_espece != $espece['id_espece']) {
						throw new Exception('pas la bonne espèce');
					}
					if (array_key_exists($citation->id_citation, $added)) {
						print_r($nicheur);
						throw new Exception($e);
					}
					try {
						$s_out->ajouter($citation->id_citation);
					} catch (Exception $e) {
						print_r($aonfm->nicheurs);
						print_r($this->especes);
						throw $e;
					}
					$added[$citation->id_citation] = true;
				}
			}
			unset($aonfm);
		}
		ob_end_clean();
		$this->messages[] = "Extractions des nicheurs <a href=\"?t=selection&sel={$s_possible->id_selection}\">Nicheurs possible</a>, <a href=\"?t=selection&sel={$s_probable->id_selection}\">probable</a>, <a href=\"?t=selection&sel={$s_certain->id_selection}\">certains</a>";
	}
}
