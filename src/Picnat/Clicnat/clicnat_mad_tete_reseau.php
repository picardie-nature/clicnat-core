<?php
namespace Picnat\Clicnat;

class clicnat_mad_tete_reseau extends clicnat_travail implements i_clicnat_travail {
	protected $opts;

	public function __construct($db, $args) {
		parent::__construct($db, $args);
	}

	public function executer() {
		$message_retour = "Résultat exécution mad tête de réseau\n\n";
		switch(INSTALL) {
			case 'gonm':
				$liste_reseau = clicnat2_reseau::liste_reseaux($this->db);
				break;
			case 'picnat':
				$liste_reseau = clicnat_reseau::liste_reseaux($this->db);
				break;
			default:
				throw new Exception('type install non définit');
		}
		foreach ($liste_reseau as $reseau) {
			$message_retour .= "Réseau : {$reseau}";
			$extraction = new clicnat_extraction_mad_tete_reseau($this->db);
			$extraction->ajouter_condition(new bobs_ext_c_reseau($reseau));
			$message_retour .= " {$extraction->compte()} citations\n";
			foreach ($reseau->coordinateurs as $coord) {
				$message_retour .= "\t$coord\n";
				$r = $extraction->autorise($coord);
				$message_retour .= "\t\tnouveau: {$r['nouveau']} déjà autorisé: {$r['deja_present']}\n";
			}
		}
		return [0, $message_retour];
	}
}
