<?php
namespace Picnat\Clicnat;

class clicnat_iterateur_citations extends clicnat_iterateur {
	protected $tri = null;
	protected $sens = null;
	protected $terme = null;

	function current() {
		return get_citation($this->db, $this->ids[$this->position]);
	}

	function __construct($db, $ids, $hash=null, $info_it=null){
		$nhash = is_null($hash) ? null : 'clicnat_iterateur_citations_'.$hash;
		parent::__construct($db,$ids,$nhash);
		if (!is_null($info_it)){
			$this->tri = isset($info_it['tri']) ? $info_it['tri'] : null ;
			$this->sens = isset($info_it['sens']) ? $info_it['sens'] : null;
			$this->terme = isset($info_it['terme']) ? $info_it['terme'] : null;
			$this->position = isset($info_it['position']) ? $info_it['position'] : 0;
		}
	}

	public function tri_par_date() {
		$in = '';
		foreach ($this->ids as $id)
			$in .= $id.',';
		$in = trim($in,',');
		$q = bobs_element::query($this->db, 'select id_citation
			from citations,observations
			where id_citation in ('.$in.')
			and observations.id_observation = citations.id_observation
			order by date_observation');
		$this->ids = array_column(self::fetch_all($q), 'id_citation');
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
		$q = bobs_element::query($this->db, 'select citations.id_citation,especes.nom_s,especes.nom_f
			from citations,especes
			where citations.id_citation in ('.$in.') and citations.id_espece = especes.id_espece
			');
		$tt = [];
		while($r = bobs_element::fetch($q)){
				if(!is_null($r['nom_f']))
					$levenshtein = !is_null($r['nom_f']) ? min(levenshtein($terme, $r['nom_s'],1,20,30)*2,levenshtein($terme, $r['nom_f'],1,20,30)) : levenshtein($terme, $r['nom_s'],1,20,30);
				$tt[] = [
					'id_citation' => $r['id_citation'],
					'levenshtein' => $levenshtein
				];
		}
		function mysort($a,$b) {
			if ($a['levenshtein'] == $b['levenshtein'])
				return 0;
			return $a['levenshtein']>$b['levenshtein']?1:-1;
		}
		usort($tt, 'mysort');
		$this->ids = array_column($tt,'id_citation');
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
			case 'nom_s':
				$tri = 'nom_s';
				$sort = true;
				break;
			case 'nom_espece' :
				$tri = 'nom_f';
				$sort = true;
				break;
			case 'date_obs' :
				$tri = 'date_observation';
				$sort = true;
				break;
			case 'lieu':
				$tri = 'id_espace';
				$sort = true;
				break;
		}
		if (!in_array($tri, ['date_observation', 'nom_s', 'id_citation','nom_f','id_espace']))
			throw new \InvalidArgumentException("colonne tri invalide $tri");
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
		if ($modifie_tri) {
			$in = '';
			foreach ($this->ids as $id)
				$in .= $id.',';
			$in = trim($in,',');
			$sql = sprintf("select id_citation from citations,observations,especes
					where id_citation in ($in)
					and observations.id_observation = citations.id_observation
					and citations.id_espece = especes.id_espece
					order by %s %s ", $this->tri, $this->sens);
			$citations = bobs_element::query_fetch_all($this->db, $sql);
			$this->ids = array_column($citations, 'id_citation');
		} else if ($modifie_sens) {
			$this->inverse();
		}
		$this->position = 0;
		$this->to_session();
	}

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
				$citation = $this->current();
				$data = [];
				$data["DT_RowId"] =  $citation->id_citation;
				$data["DT_RowClass"] = 'data_row';

				for($i = 0 ; $i<count($args["columns"]);$i++){
					switch ($args["columns"][$i]["name"]){
						case  'nom_espece':
							$data["nom_espece"] = $citation->get_espece()->__toString();
							break;
						case 'date_obs':
							$date_obs =  $citation->get_observation()->date_observation;
							$data['date_obs'] = date_format(new Datetime($date_obs),"d-m-Y");
							break;
						case 'indice_qualite' :
							$data['indice_qualite'] = $citation->indice_qualite;
							break;
						case 'nb_txt' :
							$data['nb_txt'] = $citation->nb_txt;
							break;
						case 'id_citation' :
							$data['id_citation'] = $citation->id_citation;
							break;
						case 'nom_f':
							$data['nom_f'] = $citation->get_espece()->nom_f;
							break;
						case 'nom_s':
							$data['nom_s'] = $citation->get_espece()->nom_s;
							break;
						case 'observateurs':
							$data['observateurs'] = $citation->get_observation()->get_observateurs_str();
							break;
						case 'lieu':
							$espace =  $citation->get_observation()->get_espace();
							switch ($espace->get_table()) {
								case 'espace_point':
								case 'espace_chiro':
									$txt = '';
									if (!empty($espace->nom))
										$txt = "<span title=\"Nom du point\">{$espace->nom}</span>";
									foreach ($espace->get_toponymes() as $topo) {
										$txt .= (empty($txt)?'':' / ')."<span title=Toponyme>{$topo}</span>";
									}
									foreach ($espace->get_communes() as $com) {
										$txt .= (empty($txt)?'':' / ')."<span title=Commune>{$com}</span>";
									}
									break;
								default:
									$txt = $espace->__toString();
									break;
							}
							$data['lieu'] = $txt;
							break;
						case 'etat':
							if ($citation->en_attente_de_validation())
								$data['etat'] = '<i class="fa fa-question" aria-hidden="true"></i>';
							elseif ($citation->invalide())
								$data['etat'] = '<i class="fa fa-ban" aria-hidden="true"></i>';
							else
								$data['etat'] = '<i class="fa fa-check" aria-hidden="true"></i>';
							break;
					}
				}
				$rep["data"][] = $data;
			}
		}
		$this->to_session();
		return $rep;
	}

	public static function in_session($hash){
		return parent::in_session('clicnat_iterateur_citations_'.$hash);
	}

	public function to_session(){
		parent::to_session();
		$_SESSION['iterateurs'][$this->hash]['tri'] = $this->tri;
		$_SESSION['iterateurs'][$this->hash]['sens'] = $this->sens;
		$_SESSION['iterateurs'][$this->hash]['terme'] = $this->terme;
	}

	/**
	 * @brief extrait un iterateur enregistré dans la session
	 * @param $db ressource postgres
	 * @param $hash identifiant de l'iterateur
	 */
	public static function from_session($db,$hash) {
		if (self::in_session($hash)){
			$session_it = $_SESSION['iterateurs']['clicnat_iterateur_citations_'.$hash];
			$ids = $_SESSION['iterateurs']['clicnat_iterateur_citations_'.$hash]['ids'];
			unset($session_it['ids']);
			return new clicnat_iterateur_citations($db,$ids,$hash,$session_it);
		}
		return false;
	}

	public function inverse() {
		$this->ids = array_reverse($this->ids);
	}

	/**
	 * @param id à rechercher
	 * @return objet citation
	 */
	 public function by_id($id){
		if( $this->in_array($id))
			return get_citation($this->db, $id);
	}
}
