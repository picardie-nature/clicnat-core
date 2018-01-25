<?php
namespace Picnat\Clicnat;

/**
 * @brief Vide la sélection de sont contenu
 *
 * <pre>
 * $action = new bobs_selection_vider();
 * $action->set('id_selection', $id);
 * if ($action->prepare())
 *   $action->execute();
 * </pre>
 */
class bobs_selection_vider extends bobs_selection_action {
    protected $selection;
    protected $ready;

    public function __construct($db) {
		parent::__construct($db);
		$this->allowed_varnames[] = 'id_selection';
    }

    public function prepare() {
		$this->selection = new bobs_selection($this->db, $this->id_selection);
		$this->ready = parent::prepare();
		return $this->ready;
    }

    public function execute() {
		parent::execute();
		$this->messages[] = 'sélection vidée';
		return $this->selection->vider();
    }
}
