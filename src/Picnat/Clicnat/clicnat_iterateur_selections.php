<?php
namespace Picnat\Clicnat;

class clicnat_iterateur_selections extends clicnat_iterateur {
	protected $tri;
	protected $sens;
	protected $terme;

	function __construct($db,$ids,$hash,$info_it = null){
		$nhash = is_null($hash) ? null : 'clicnat_iterateur_selections_'.$hash;
		parent::__construct($db,$ids,$nhash);
		if(!is_null($info_it)){
			$this->tri = isset($info_it['tri']) ? $info_it['tri'] : null ;
			$this->sens = isset($info_it['sens']) ? $info_it['sens'] : null;
			$this->terme = isset($info_it['terme']) ? $info_it['terme'] : null;
			$this->position = isset($info_it['position']) ? $info_it['position'] : 0;
		}else{
			$this->tri = null;
			$this->sens = null;
			$this->terme =null;
		}
	}
	function current() {
		return get_selection($this->db, $this->ids[$this->position]);
	}

	public function tri_par_date() {
		$in = '';
		foreach ($this->ids as $id)
			$in .= $id.',';
		$in = trim($in,',');
		$q = bobs_element::query($this->db, 'select id_selection
			from citations,observations
			where id_citation in ('.$in.')
			and observations.id_observation = citations.id_observation
			order by date_observation');
		$this->ids = [];
		while ($r = bobs_element::fetch($q))
			$this->ids[] = $r['id_citation'];
		$this->position = 0;
	}
	public function tri_par_terme($terme){
		if ( strcmp($this->terme,$terme) !==0){
		$this->terme = $terme;
		$this->tri = 'search';
		$in = '';
		foreach ($this->ids as $id)
			$in .= $id.',';
		$in = trim($in,',');
		$q = bobs_element::query($this->db, 'select id_selection,nom_selection
			from selection
			where id_selection in ('.$in.')
			order by nom_selection');
		$tt = [];
		while($r = bobs_element::fetch($q))
				$tt[] = [
					'id_selection' => $r['id_selection'],
					'levenshtein' => levenshtein($terme, $r['nom_selection'],1,20,30)
				];
		function mysort($a,$b) {
			if ($a['levenshtein'] == $b['levenshtein'])
				return 0;
			return $a['levenshtein']>$b['levenshtein']?1:-1;
		}
		usort($tt, 'mysort');
		$this->ids = array_column($tt,'id_selection');
		$this->rewind();
		$this->to_session();
		}
	}
	public function tri($args){
		$tri = '';
		$sens ='';
		$sort = false;
		$modifie_tri = false;
		$modifie_sens = false;
		switch ($args['columns'][$args['order'][0]['column']]['name']){
			case 'nom':
				$tri .=  'nom_selection';
				$sort = true;
				break;
			case 'date_creation':
				$tri .=  'date_creation';
				$sort = true;
				break;
			case 'id_selection':
				$tri .= 'id_selection';
				$sort = true;
				break;
		}
		if (!in_array($tri, array('date_creation', 'nom_selection', 'id_selection')))
			throw new InvalidArgumentException();
		if ($sort) {
			switch ($args['order'][0]['dir']) {
				case 'asc':
					$sens .= 'asc';
					break;
				case 'desc':
					$sens .= 'desc';
					break;
			}
		}

		if (strcmp($this->tri,$tri) !== 0){
			$this->tri = $tri;
			$modifie_tri = true;
		}
		if (strcmp($this->sens,$sens) !== 0){
			$this->sens = $sens;
			$modifie_sens = true;
		}
		if ($modifie_tri){
			if($this->tri == 'id'){
				if ($this->sens == 'asc')
					sort($this->ids);
				else rsort($this->ids);
			}
			else{
				$in = '';
				foreach ($this->ids as $id)
					$in .= $id.',';
				$in = trim($in,',');
				$sql = sprintf("select id_selection from selection where id_selection in ($in) order by %s %s", $this->tri, $this->sens);
				$selections = bobs_element::query_fetch_all($this->db, $sql);
				$n_selection = $this->count();
				$this->ids = array_column($selections, 'id_selection');
			}
		}else if ($modifie_sens)
			$this->inverse();
		$this->position = 0;
		$this->to_session();

	}
	const sql_n = 'select count(id_citation) as n from selection_data where id_selection=$1';

	public function to_dataTable($args){
		$tri = '';
		$sens = '';
		$sort= false;
		$rep = parent::to_dataTable($args);
		$n_display =  $rep['recordsTotal'];
		$n_total =  $rep['recordsFiltered'];
		$dt_start = $args["start"];
		$dt_length = $args["length"];
		$terme = $args['search']['value'];
		if(!is_null($terme) && $terme !=""){
			if(strcmp($this->terme,$terme) !== 0){
				bobs_element::cls($terme);
				$this->tri_par_terme($terme);
				$rep["terme"]=$terme;
			}
			else {
				$this->tri($args);
			}
		}else {
			$this->tri($args);
		}
		if ($dt_start < $n_total){
			$this->position = $dt_start;
			$dt_stop = min($dt_start + $dt_length , $n_total);
			for ($this->position; $this->position < $dt_stop ; $this->next()){
				$selection = $this->current();
				$q = bobs_qm()->query($this->db, 'sel-count-cit', self::sql_n, array($selection->id_selection));
				$r = bobs_element::fetch($q);
				$data = [];
				$data["DT_RowId"] =  $selection->id_selection;
				$data["DT_RowClass"] = 'data_row';
				for($i = 0 ; $i<count($args["columns"]);$i++){
					switch ($args["columns"][$i]["name"]){
					case "nom" :
						$data["nom"] =  $selection->nom_selection;
						break;
					case 'date_creation' :
						$data["date_creation"] = date_format(new Datetime($selection->date_creation),"d-m-Y");
						break;
					case 'n_citations' :
						$data["n_citations"] = $r["n"];
						break;
					case 'id_selection' :
						$data["id_selection"] = $selection->id_selection;
						break;
					}
				}
					$rep['data'][] =  $data;
			}
		}
		$this->to_session();
		return $rep;
	}
	public static function in_session($hash){
		return clicnat_iterateur::in_session('clicnat_iterateur_selections_'.$hash);
	}
	public function to_session(){
		parent::to_session();
		$_SESSION['iterateurs'][$this->hash]['tri'] = $this->tri;
		$_SESSION['iterateurs'][$this->hash]['sens'] = $this->sens;
		$_SESSION['iterateurs'][$this->hash]['terme'] = $this->terme;
	}
	public static function from_session($db,$hash){
		if (self::in_session($hash)){
			$session_it = $_SESSION['iterateurs']['clicnat_iterateur_selections_'.$hash];
			$ids = $_SESSION['iterateurs']['clicnat_iterateur_selections_'.$hash]['ids'];
			unset ($session_it['ids']);
			return new clicnat_iterateur_selections($db,$ids,$hash,$session_it);
		}
		else return false;
	}
	public function inverse() {
		$this->ids = array_reverse($this->ids);
	}
	/**
	 * @param id à rechercher
	 * @return objet selection
	 */
	 public function by_id($id){
		if( $this->in_array($id))
			return get_selection($this->db, $id);
	}
}
