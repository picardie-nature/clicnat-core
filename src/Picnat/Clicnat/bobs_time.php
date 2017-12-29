<?php
namespace \Picnat\Clicnat;

class bobs_time extends bobs_tests {
	const equal = 0;
	const lower = 1;
	const greater = 2;

	/** string return by db */
	protected $the_str;

	/** the_str split */
	protected $the_tab;

	function __construct($str_time) {
		self::cls($str_time);
		$this->the_str = $str_time;

		$this->the_tab = explode(':', $this->the_str);
	}

	/**
	 * @brief retourne l'heure
	 * @return int
	 */
	public function get_h() {
		return intval($this->the_tab[0]);
	}

	/**
	 * @brief retourne les minutes
	 * @return int
	 */
	public function get_m() {
		return intval($this->the_tab[1]);
	}

	/**
	 * @brief retourne les secondes
	 * @return int
	 */
	public function get_s() {
		return intval($this->the_tab[2]);
	}

	public function __toString() {
		return sprintf("%02d:%02d:%02d", $this->get_h(), $this->get_m(), $this->get_s());
	}

	/**
	 * @brief retourne le nombre de secondes
	 * @return int
	 */
	public function get_tstamp() {
		return $this->get_h()*3600+$this->get_m()*60+$this->get_s();
	}

	/**
	 * @brief compare t1 avec t2
	 *
	 *   - si t1 > t2 retourne bobs_time::greater
	 *   - si t2 > t1 retourne bobs_time::lower
	 *   - et si t1 = t2 retourne bobs_time::equal
	 *
	 * @param bobs_time $t1
	 * @param bobs_time $t2
	 * @return int
	 */
	public static function compare($t1, $t2) {
		if ($t1->get_tstamp() > $t2->get_tstamp())
			return self::greater;
		if ($t1->get_tstamp() < $t2->get_tstamp())
			return self::lower;
		return self::equal;
	}

	public function get_str() {
		return $this->the_str;
	}
}
