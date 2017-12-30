<?php
namespace Picnat\Clicnat;

/**
 * @brief Pré-inscription d'un nouvel observateur
 *
 * Première partie :
 * - enregistrer la pré-inscription : pre->set_vars(tab)
 * - envoyer le mail avec le lien de confirmation : pre->sauve_et_envoi_mail
 *
 * Deuxième partie :
 * - retrouve les infos enregistrée pre->reprise()
 * - création du compte : pre->creation_compte()
 *
 */
class bobs_utilisateur_preinscription implements i_clicnat_tests {
	use clicnat_mini_template;
	use clicnat_tests;

	private $nom;
	private $prenom;
	private $email;
	private $date_naissance;
	private $identifiant;

	public $suivi;

	public function __get($prop) {
		switch ($prop) {
			case 'nom': return $this->nom;
			case 'prenom': return $this->prenom;
			case 'email': return $this->email;
			case 'date_naissance' : return $this->date_naissance;
			default: throw new Exception($prop.' pas accessible');
		}
	}

	/**
	 * @brief Liste les pré-inscriptions
	 */
	public static function liste() {
		$t = array();
		$liste = glob(BOBS_PREINSCRIPTION_PATH.'/ins*');
		foreach ($liste as $f) {
			$o = unserialize(file_get_contents($f));
			if ($o) {
				$o->suivi = basename($f);
				$t[] = $o;
			}
		}
		return $t;
	}

	/**
	 * @brief supprime le fichier de pré-inscription
	 */
	public function annuler() {
		if (empty($this->suivi))
			throw new Exception('ne sait pas quel fichier supprimer');
		$f = BOBS_PREINSCRIPTION_PATH.'/'.$this->suivi;
		if (!file_exists($f))
			throw new Exception($f.' existe pas');
		if (!unlink($f))
			throw new Exception('échec suppression de '.$f);
		return true;
	}

	public static function compte() {
		$liste = glob(BOBS_PREINSCRIPTION_PATH.'/ins*');
		return count($liste);
	}

	public function set_vars($post_vals) {
		$this->nom = self::cls($post_vals['nom'], self::except_si_vide);
		$this->prenom =self::cls($post_vals['prenom'], self::except_si_vide);
		$this->email = self::cls($post_vals['email'], self::except_si_vide);
	}

	public static function reprise($suivi) {
		$suivi = escapeshellcmd(self::cls($suivi));

		if (empty($suivi))
			throw new Exception('Pas de numéro de suivi, il faut utiliser l\'adresse en entier avec l\'identifiant');

		$f = BOBS_PREINSCRIPTION_PATH.'/'.$suivi;
		if (!file_exists($f))
			throw new Exception('Numéro de suivi existe pas '.$f);
		$repr = unserialize(file_get_contents($f));
		$repr->suivi = $suivi;
		return $repr;
	}

	public function sauve_et_envoi_mail($base_url, $mail_support, $signature) {
		$db = get_db();
		$sujet_tpl = clicnat_textes::par_nom($db, "base/inscription/mail_confirmation_sujet")->texte;
		$texte_tpl = clicnat_textes::par_nom($db, "base/inscription/mail_confirmation")->texte;
		$vars = [
			"mail_support" => $mail_support,
			"base_url" => $base_url
		];
		return $this->sauve_et_envoi_mail_tpl($sujet_tpl, $texte_tpl, $vars);
	}

	/**
	 * @brief enregistrement pré-inscription et envoi du mail de confirmation
	 *
	 * @param $sujet_tpl sujet du mail
	 * @param $texte_tpl message
	 * @param $vars variables
	 *
	 * Le sujet et le corps du mail sont traité avec le système de mini template
	 * le contenu {xxxx} du texte est remplacé pour l'entrée $vars[xxxx] correspondante
	 *
	 *  Entrées de vars nécéssaires :
	 *  - mail_support : from du mail
	 *  - base_url : url du lien dans le message
	 *
	 *  Entrées de vars ajoutées et utilisable dans le template :
	 *  - ins_id : numéro temporaire pour l'inscription
	 *  - nom, prenom : de la personne qui s'inscrit
	 */
	public function sauve_et_envoi_mail_tpl($sujet_tpl, $texte_tpl, $vars) {
		$vars['ins_id'] = uniqid('ins', true);
		$vars['nom'] = $this->nom;
		$vars['prenom'] = $this->prenom;
		$vars['date_naissance'] = $this->date_naissance;

		$texte_headers =
			"From: {mail_support}\r\n".
			"Content-Type: text/plain; charset=utf-8\r\n".
			"\r\n";
		$headers = self::mini_template($texte_headers, $vars);
		$msg = self::mini_template($texte_tpl, $vars);
		$sujet = self::mini_template($sujet_tpl, $vars);

		if (!mail($this->email, $sujet, $msg, $headers, "-f{$vars['mail_support']}")) {
			throw new Exception('Message pas envoyé');
		}

		$f = fopen(BOBS_PREINSCRIPTION_PATH."/{$vars['ins_id']}", 'w');
		if (!$f) {
			throw new Exception('Ne peut créer le fichier '.$ins_id.' dans BOBS_PREINSCRIPTION_PATH');
		}

		fwrite($f, serialize($this));

		fclose($f);
		return true;
	}

	public function __toString() {
		return "{$this->nom} {$this->prenom}";
	}

	/**
	 * @brief Création du compte et envoi du mot de passe
	 * @param $db ressource postgres
	 * @param $base_url url de base des liens
	 * @param $mail_support adresse de réponse
	 * @param $signature signature en pied du message
	 */
	public function creation_compte($db, $base_url, $mail_support, $signature) {
		// Création du nom d'utilisateur
		$this->identifiant = bobs_utilisateur::genere_nom_utilisateur_libre($db, $this->nom, $this->prenom);

		// Création du compte
		$data = array(
			'nom' => $this->nom,
			'prenom' => $this->prenom,
			'username' => $this->identifiant,
			'mail' => $this->email,
			'tel' => '',
			'fax' => '',
			'port' => ''
		);
		bobs_utilisateur::nouveau($db, $data);

		$u = bobs_utilisateur::by_login($db, $this->identifiant);
		if ($u->id_utilisateur > 0) {
			if (!empty($this->suivi)) {
				$f = BOBS_PREINSCRIPTION_PATH.'/'.$this->suivi;
				if (file_exists($f)) {
					unlink($f);
				}
			}
			// Observateur public => diffusion large
			$u->accept_rules(false);
			// C'est un nouveau
			$u->set_junior(true);
			// Envoi du mot de passe
			$u->send_password($base_url, $mail_support, $signature);
		}
	}
}
