<?php
namespace Picnat\Clicnat\ExtractionsConditions;

class bobs_ext_c_esp_comite_homolog extends bobs_extractions_conditions {
	private $id_comite;
	private $interieur;

	public function __construct($id_comite) {
		parent::__construct();
		bobs_element::cli($id_comite);
		$this->id_comite = $id_comite;
		$this->arguments[] = 'id_comite';
		$this->interieur = $id_comite == 3;
	}

	public function  __toString() {
		return 'Espèces à homologation comite #{$this->id_comite}';
	}

	public static function get_titre() {
		return 'Espèces à homologation';
	}

	const l_communes = '81495,81348,81180,80993,81287,81347,80896,80965,81273,81242,81563,80950,81258,81116,81633,81449,81598,81353';

	public function get_sql() {
		if ($this->interieur) {
			return sprintf('especes.id_chr = %d and littoral_id_espace is null and commune_id_espace not in (%s)', $this->id_comite, self::l_communes);
		}
		return sprintf('especes.id_chr = %d', $this->id_comite);
	}

	public function get_tables() {
		return ['especes', 'espace_point'];
	}

	public static function new_by_array($t) {
		return new self($t['id']);
	}
}
