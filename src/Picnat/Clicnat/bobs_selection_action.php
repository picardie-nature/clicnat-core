<?php
namespace Picnat\Clicnat;

abstract class bobs_selection_action extends bobs_tests {
	protected $selection;
	protected $db;
	protected $allowed_varnames;
	protected $ready;
	protected $messages;

	/**
	*
	* @param $db db handler
	*/
	public function __construct($db) {
		$this->db = $db;
		$this->ready = false;
		$this->allowed_varnames = array();
		$this->messages = array();
	}

	public function set($var, $value) {
		if (!in_array($var, $this->allowed_varnames))
			throw new InvalidArgumentException($var.' not allowed');
		$this->$var = $value;
	}

	/**
	* @brief prépare l'action a être exécutée
	*/
	public function prepare() {
		return true;
	}

	/**
	* @brief exécution (modif)
	*/
	public function execute() {
		if ($this->ready)
		    return true;
		else
		    throw new Exception('not ready');
	}

	/**
	 * @brief retourne les messages générés pendant l'exécution
	 */
	public function messages() {
		return $this->messages;
	}
}
