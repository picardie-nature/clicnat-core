<?php
namespace Picnat\Clicnat;

class bobs_selection_deplacer_contenu_poly extends bobs_selection_action {
    /**
     * @var bobs_selection selection source
     */
    protected $selection_a;

    /**
     * @var bobs_selection selection cible
     */
    protected $selection_b;

    protected $id_selection_a;
    protected $id_selection_b;
    protected $geom_wkt;

    public function __construct($db) {
		parent::__construct($db);
		$this->allowed_varnames = array_merge($this->allowed_varnames,
			array(
			    'id_selection_a',
			    'id_selection_b',
			    'geom_wkt'
			));
    }

    public function prepare() {
		self::cli($this->id_selection_a);
		self::cli($this->id_selection_b);
		self::cls($this->geom_wkt);

		$this->selection_a = new bobs_selection($this->db, $this->id_selection_a);
		$this->selection_b = new bobs_selection($this->db, $this->id_selection_b);

		if (empty($this->geom_wkt))
		    throw new Exception('geom_wkt not defined');

		$this->ready = parent::prepare();
    }

    public function execute() {
		$sql = '
		    select
			sd.id_citation
		    from
			selection_data sd,
			citations c,
			observations o,
			espace_point e
		    where sd.id_selection = $1
		    and sd.id_citation = c.id_citation
		    and o.espace_table = $2
		    and o.id_observation = c.id_observation
		    and o.id_espace = e.id_espace
		    and contains(setsrid(geomfromtext($3),4326), e.the_geom)';

		$args = array($this->id_selection_a, 'espace_point', $this->geom_wkt);

		$q = bobs_qm()->query($this->db, 's_p_deplace', $sql, $args);
		$tids = bobs_element::fetch_all($q);
		$ids = array();

		foreach ($tids as $id)
		    $ids[] = $id['id_citation'];

		if ((is_array($ids)) and (count($ids) > 0)) {
		    $this->selection_a->enlever_ids($ids);
		    $this->selection_b->ajouter_ids($ids);
		}
    }
}
