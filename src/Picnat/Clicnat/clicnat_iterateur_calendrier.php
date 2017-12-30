<?php
namespace Picnat\Clicnat;

class clicnat_iterateur_calendrier implements \Iterator {
	private $db;
	private $position;
	private $ids = [];

	public function __construct($db, $ids) {
		$this->db = $db;
		$this->position = 0;
		$this->ids = $ids;
	}

	public function rewind() {
		$this->position = 0;
	}

	public function current() {
		return new bobs_calendrier($this->db, $this->ids[$this->position]);
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

	const dtable_c_date = 0;
	const dtable_c_participants = 1;
	const dtable_c_espace_nom = 2;
	const dtable_c_id_date = 3;
	const dtable_c_espace_table = 4;
	const dtable_c_id_espace = 5;
	const dtable_c_x = 6;
	const dtable_c_y = 7;

	public function get_datatable($args) {
		$in = '';
		foreach ($this->ids as $id) {
			$in .= ','.((int)$id);
		}
		$in = trim($in,',');

		$rep = [
			'sEcho'                => $args['sEcho'],
			'iTotalRecords'        => count($this->ids),
			'iTotalDisplayRecords' => count($this->ids),
			'aaData'               => []
		];

		for ($position = (int)$args['iDisplayStart']; $position < ((int)$args['iDisplayLength']+(int)$args['iDisplayStart']); $position++) {
			if (!isset($this->ids[$position])) break;
			//$rep['iTotalDisplayRecords']++;
			$date = new bobs_calendrier($this->db, $this->ids[$position]);
			$participants = '';
			foreach ($date->get_participants() as $p) {
				$participants .= trim("{$p['prenom']} {$p['nom']}");
				$participants .= ', ';
			}
			$participants = trim($participants, ', ');
			$xy = $date->get_espace()->get_centroid();
			$rep['aaData'][] = [
				strftime("%d-%m-%Y", strtotime($date->date_sortie)),
				$participants,
				$date->get_espace()->__toString(),
				$date->id_date,
				$date->espace_table,
				$date->id_espace,
				$xy['x'],
				$xy['y']
			];
		}

		return $rep;
	}
}
