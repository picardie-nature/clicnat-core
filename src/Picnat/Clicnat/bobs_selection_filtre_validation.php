<?php
namespace Picnat\Clicnat;

class bobs_selection_filtre_validation extends bobs_selection_action {
	protected $selection_valide;
	protected $selection_invalide;
	protected $id_selection;

	function __construct($db) {
		parent::__construct($db);
		$this->allowed_varnames[] = 'id_selection';
	}

	function prepare(){//creer les selections pos neg par rapport a une selection
		$this->selection = new bobs_selection($this->db, $this->id_selection);
		parent::prepare();

		$nom_selection = $this->selection->__get('nom');

		$this->selection_valide=new bobs_selection($this->db, bobs_selection::nouvelle($this->db,$this->selection->id_utilisateur, $nom_selection." tests ok"));
		$this->selection_invalide=new bobs_selection($this->db, bobs_selection::nouvelle($this->db,$this->selection->id_utilisateur, $nom_selection." a étudier"));
	}

	function execute(){//a partir d'une citation il recupére le test du filtre sur 10 critéres
		$n_total = 0;
		$n_ok = 0;
		foreach($this->selection->get_citations() as $citation){
			$n_total++;
			if($citation->validation_test()){
				$this->selection_valide->ajouter($citation->id_citation);
				$n_ok++;
			}
			else{
				$this->selection_invalide->ajouter($citation->id_citation);
			}
		}
		$this->messages[] = "Assistance validation : $n_ok sur $n_total passent les tests";
		$this->messages[] = "<a href=\"?t=selections&sel={$this->selection_valide->id_selection}\">Voir sélection tests ok</a>";
		$this->messages[] = "<a href=\"?t=selections&sel={$this->selection_invalide->id_selection}\">Voir sélection a étudier</a>";
	}
}
