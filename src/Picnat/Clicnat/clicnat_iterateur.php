<?php

namespace \Picnat\Clicnat;

abstract class clicnat_iterateur implements \Iterator {
	protected $db;
	protected $position;
	protected $ids;
	protected $hash;
	protected $session_ok = false;

	/**
	 * @brief constructeur
	 * @param $db ressource postgres
	 * @param $ids tableau d'identifiants
	 * @param $hash identifiant utilisé pour l'enregistrement en session
	 */
	public function __construct($db, $ids, $hash = null) {
		if (!is_array($ids)) {
			throw new \InvalidArgumentException('$ids doit être un tableau');
		}
		if (!is_resource($db)) {
			throw new \InvalidArgumentException('$db doit être une ressource');
		}
		$this->db = $db;
		$this->position = 0;
		$this->ids = $ids;
		if(!is_null($hash)){
			$this->hash = $hash;
			$session_ok = true;
			$this->to_session();
		}
		else $this->hash = get_called_class()."_".substr(spl_object_hash($this),0,30);
	}

	/**
	 * @brief modifie le hash par defaut pour être plus facilement accesible depuis l'exterieur
	 * @param id ou nom
	 */
	public function set_hash($hash){
		$this->hash = get_called_class()."_".$hash;
	}

	public function rewind() {
		$this->position = 0;
	}

	public function get_hash(){
		return $this->hash;
	}

	public function key() {
		return $this->position;
	}

	public function next() {
		$this->position++;
	}

	public function valid() {
		return isset($this->ids[$this->position]);
	}

	public function count() {
		return count($this->ids);
	}

	public function ids() {
		return $this->ids;
	}
	/**
	 * @param id à rechercher
	 * @return si trouver position sinon false
	 */
	public function get_position($id){
		$ids = $this->ids();
		for ($i=0;$i<$this->n();$i++){
			if($ids[$i]==intval($id))
				return $i;
		}
		return false;
	}

	/**
	 * @brief l'identifiant est-il dans l'itérateur ?
	 * @param id à rechercher
	 * @return si trouver true sinon false
	 */
	public function in_array($id) {
		return in_array($id, $this->ids);
	}

	public function n(){
		return count($this->ids);
	}
	/**
	 * @brief Données réponses Datatable
	 * @param argument de requete datatable 1.10
	 * @return array datatable avec les info general rempli
	 */
	public function to_dataTable($args){
		$n_total = $this->count();
		$n_display = min($args['length'],$n_total);
		$data = [];
		$rep = array(
			'draw' => $args['draw'],
			'recordsTotal' =>$n_display ,
			'recordsFiltered' =>$n_total ,
			'data' => $data
		);
		return $rep;
	}

	/**
	 * @brief retirer un id de l'itérateur
	 * @param id à supprimer
	 * @return si supprimer longeur du tableau ids sinon false
	 */
	public function remove_id($id){
		if ($this->in_array($id)) {
			unset($this->ids[$id]);
			if(!$this->valid())
				$this->position--;
			if (self::in_session($this->hash)) {
				$_SESSION["iterateurs"][$this->hash]['ids'] = $this->ids;
				$_SESSION["iterateurs"][$this->hash]['position'] = $this->position;
			}
			return $this->n();
		}
		return false;
	}

	/**
	 * @brief ajouter un id a l'itérateur
	 * @param id à ajouter
	 * @return si pas dans ids position d'ajout sinon false
	 */
	public function ajout_id($id){
		if (!self::in_array($id)) {
			$this->ids[] = $id;
			if (self::in_session($this->hash))
				$_SESSION["iterateurs"][get_called_class()."_".$this->hash]["ids"][] = $id;
			return $this->n()-1;
		}
		return false;
	}

	/**
	 * @brief Place l'iterateur en session
	 * le remplace si deja en session
	 * @return true
	 */
	public function to_session(){
		$tab = [];
		$tab['ids'] = $this->ids;
		$tab['position'] = $this->position;

		$_SESSION["iterateurs"][$this->hash] = $tab;

		// todo deprecated var session_ok
		$this->session_ok = true;
		$_SESSION["iterateurs"][$this->hash]['session_ok'] = true;

		return true;
	}
	/**
	 * @brief Supprime l'iterateur en session
	 * @return si supprimé ture false sinon
	 */

	public function remove_session(){
		if (isset($_SESSION["iterateurs"][$this->hash])){
			unset($_SESSION["iterateurs"][$this->hash]);
			$this->session_ok = false;
			return true;
		}
		$this->session_ok = false;
		return false;
	}

	/**
	 * @brief test si l'iterateur est en session
	 * @param nom de l'iterateur
	 * @return si en session true false sinon
	 */

	public static function in_session($hash){
		return isset($_SESSION["iterateurs"][$hash] ) && !is_null($_SESSION["iterateurs"][$hash]);
	}

	/**
	 * @brief renvoie l'iterateur si stocké en session
	 * @param nom de l'iterateur
	 * @return si en session objet iterateur false sinon
	 */
	public static function from_session($hash) {
		if (isset($_SESSION["iterateurs"][$hash]) && !is_null($_SESSION["iterateurs"][$hash])) {
			$iterateur = $_SESSION["iterateurs"][$hash];
			return $iterateur;
		}
		return false;
	}

	public function tri($sens) {
		if ($sens == 'asc') {
			sort($this->ids);
			return;
		}
		rsort($this->ids);
	}
}
