<?php
namespace Picnat\Clicnat;


class clicnat_selection_export_full extends bobs_selection_action {
	protected $chemin;
	protected $id_selection;

	private $f_observation;
	private $f_citation;
	private $f_obsobs;
	private $f_observateur;
	private $f_tags;
	private $f_citations_tags;

	protected $selection;

	const fmt_datetime = 'Y-m-d H:i:s';

	const entete_observations = '"Id";"DateDebut";"DateFin";"CatLoc";"IDLoc";"Ref"';
	const format_observations = '"%d";"%s";"%s";"%s";"%d";""';

	const entete_citations = '"Id";"IdObservation";"IdEspece";"Nb";"Ref"';
	const format_citations = '"%d";"%d";"%d";"%d";""';

	const entete_obsobs = '"IdObservation";"IdObservateur"';
	const format_obsobs = '"%d";"%d"';

	const entete_observateurs = '"Id";"Nom";"Prenom"';
	const format_observateurs = '"%d";"%s";"%s"';

	const entete_tags = '"Id";"Libelle";"A_Int";"A_Text"';
	const format_tags = '"%d";"%s";"%d";"%d"';

	const entete_citations_tags = '"IdCitation";"IdTag";"V_Int";"V_Text"';
	const format_citations_tags = '"%d";"%d";"%d";"%s"';

	const entete_observations_tags = '"IdObservation";"IdTag";"V_Int";"V_Text"';
	const format_observations_tags = '"%d";"%d";"%d";"%s"';

	public function __construct($db) {
		parent::__construct($db);
		$this->allowed_varnames = array_merge($this->allowed_varnames,
			array('chemin','id_selection'));
	}

	public function prepare() {
		if (!file_exists($this->chemin))
			mkdir($this->chemin);

		$this->f_observation = fopen($this->chemin.'/observations.csv', 'w');
		$this->f_citation = fopen($this->chemin.'/citations.csv', 'w');
		$this->f_obsobs = fopen($this->chemin.'/observations_observateurs.csv', 'w');
		$this->f_observateur = fopen($this->chemin.'/observateurs.csv', 'w');
		$this->f_tags = fopen($this->chemin.'/tags.csv', 'w');
		$this->f_citation_tags = fopen($this->chemin.'/citations_tags.csv', 'w');
		$this->f_observation_tags = fopen($this->chemin.'/observations_tags.csv', 'w');

		fwrite($this->f_observation, self::entete_observations."\n");
		fwrite($this->f_citation, self::entete_citations."\n");
		fwrite($this->f_observateur, self::entete_observateurs."\n");
		fwrite($this->f_obsobs, self::entete_obsobs."\n");
		fwrite($this->f_tags, self::entete_tags."\n");
		fwrite($this->f_citation_tags, self::entete_citations_tags."\n");
		fwrite($this->f_observation_tags, self::entete_observations_tags."\n");

		$this->selection = new bobs_selection($this->db, $this->id_selection);
	}

	public function termine() {
		fclose($this->f_observation);
		fclose($this->f_citation);
		fclose($this->f_obsobs);
		fclose($this->f_observateur);
		fclose($this->f_tags);
		fclose($this->f_citation_tags);
		fclose($this->f_observation_tags);
		return $this->zip();
	}

	const nom_fichier_zip = 'selection.zip';

	private function zip() {
		$zip = $this->chemin.'/'.self::nom_fichier_zip;
		if (file_exists($zip)) unlink($zip);
		$zf = new ZipArchive();
		$zf->open($zip, ZipArchive::CREATE);
		$zf->setArchiveComment($this->selection->nom_selection);
		foreach (glob($this->chemin.'/*') as $filename)
			$zf->addFile($filename, "selection_{$this->id_selection}_full/".basename($filename));
		$zf->close();
		return $zip;
	}

	public function execute() {
		foreach ($this->selection->get_observations() as $obs) {
			fprintf($this->f_observation, self::format_observations."\n",
				$obs->id_observation,
				$obs->get_observation_deb_datetime()->format(self::fmt_datetime),
				$obs->get_observation_fin_datetime()->format(self::fmt_datetime),
				$obs->espace_table,
				$obs->id_espace
				// ref
			);

			foreach ($obs->get_citations() as $cit) {
				fprintf($this->f_citation, self::format_citations."\n",
					$cit->id_citation,
					$cit->id_observation,
					$cit->id_espece,
					$cit->nb
					// ref
				);
				foreach ($cit->get_tags() as $tag) {
					fprintf($this->f_citation_tags, self::format_citations_tags."\n",
						$cit->id_citation,
						$tag['id_tag'],
						$tag['v_int'],
						$tag['v_text']
					);
				}
			}

			foreach ($obs->get_observateurs() as $observ) {
				fprintf($this->f_obsobs, self::format_obsobs."\n",
					$obs->id_observation,
					$observ['id_utilisateur']
				);
			}

			foreach ($obs->get_tags() as $tag) {
				fprintf($this->f_observation_tags, self::format_observations_tags."\n",
					$obs->id_observation,
					$tag['id_tag'],
					$tag['v_int'],
					$tag['v_text']
				);
			}
		}

		foreach ($this->selection->get_observateurs() as $obs) {
			fprintf($this->f_observateur, self::format_observateurs."\n",
				$obs['id_utilisateur'],
				$obs['nom'],
				$obs['prenom']
			);
		}

		foreach ($this->selection->get_tags() as $tag) {
			fprintf($this->f_tags, self::format_tags."\n",
				$tag['id_tag'],
				$tag['lib'],
				$tag['a_entier']=='t'?1:0,
				$tag['a_chaine']=='t'?1:0
			);
		}
		exec(sprintf('%s %d %s %d',
			'/usr/local/bin/extract_shps_selection',
			$this->id_selection,
			$this->chemin,
			2154),$o,$rv);

		//bobs_log("exec ($rv): $l");

		return $this->termine();
	}
}
