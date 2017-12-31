<?php
namespace Picnat\Clicnat;

/**
 * @brief Gestion des commentaires
 */
class bobs_commentaire implements i_clicnat_tests {
	use clicnat_tests;
	use clicnat_mini_template;

	public $table;
	public $champ_id;
	public $id_element;
	public $id_commentaire;
	public $id_utilisateur;
	public $type_commentaire;
	public $date_commentaire;
	public $commentaire;

	private static function envoi_mail_commentaire($db, $table, $champ_id, $id_element,$type_c,$commtr,$id_utilisateur,$commentaire) {
		if ($type_c != 'info')
			return false;
		bobs_log("mail commentaire $table $champ_id #$id_element");

		// sélection de tous les utilisateurs ayant participer à la discussion
		$sql = "select distinct id_utilisateur from $table where type_commentaire='info' and $champ_id=$1";
		$q = bobs_qm()->query($db, "tc$champ_id{$table}", $sql, array($id_element));
		$r = bobs_element::fetch_all($q);

		$utilisateurs = array_column($r, 'id_utilisateur');

		bobs_log("destinataires = ".count($utilisateurs));
		// ajout de destinataire en fonction de la table
		switch ($table) {
			case 'citations_commentaires':
				$citation = get_citation($db, $id_element);
				$obs = $citation->get_observation();
				foreach ($obs->get_observateurs() as $observateur) {
					$utilisateurs[] = $observateur['id_utilisateur'];
				}
				$utilisateurs = array_unique($utilisateurs);
				bobs_log("ajout observateurs, destinataires = ".count($utilisateurs));
				break;
		}

		$auteur = get_utilisateur($db, $id_utilisateur);
		foreach ($utilisateurs as $dest_id_utilisateur) {
			if ($dest_id_utilisateur == $id_utilisateur)
				continue;
			$message = new clicnat_mail();
			switch ($table) {
				case 'citations_commentaires':
					$destinataire = get_utilisateur($db, $dest_id_utilisateur);
					if ($destinataire->partage_opts('pas_de_mail_interaction')) {
						bobs_log("le destinataire ne veut pas de mail {$destinataire} #{$destinataire->id_utilisateur}");
						break;
					}
					bobs_log("destinataire : $destinataire");
					$mail_dest = $destinataire->mail;
					if (empty($mail_dest)) {
						bobs_log("pas d'adresse email {$destinataire} #{$destinataire->id_utilisateur} $mail_dest");
						break;
					}
					$citation = get_citation($db, $id_element);

					$message->from('ne-pas-repondre@clicnat.fr');

					$vars = [
						'auteur' => $auteur->__toString(),
						'espece' => $citation->get_espece()->__toString(),
						'date' => strftime("%d/%m/%Y", strtotime($citation->get_observation()->date_observation)),
						'date2' => strftime("%A %e %B %Y", strtotime($citation->get_observation()->date_observation)),
						'txt_commtr' => wordwrap(html_entity_decode($commentaire)),
						'id_element' => $id_element
					];

					$sujet_tpl = clicnat_textes::par_nom(get_db(), 'base/commentaire/mail_notification_sujet')->texte;
					$message_tpl = clicnat_textes::par_nom(get_db(), 'base/commentaire/mail_notification')->texte;

					$message->sujet(self::mini_template($sujet_tpl, $vars));
					$message->message(self::mini_template($message_tpl, $vars));

					bobs_log("envoi à $mail_dest");
					$message->envoi($mail_dest);
					break;
				default:
					bobs_log("pas d'envoi de message");
					break;
			}
		}

	}

	public static function ajout($db, $table, $champ_id, $id_element,$type_c,$commtr,$id_utilisateur,$sans_mail=false) {
		self::cls($table);
		self::cls($type_c);
		self::cls($champ_id);
		self::cli($id_element);
		$sql = "insert into {$table}
				({$champ_id},id_utilisateur,type_commentaire,date_commentaire,commentaire)
				values ($1,$2,$3,now(),$4)";
		if (bobs_qm()->query($db, "cmtr_add_{$table}", $sql, array($id_element,$id_utilisateur,$type_c,$commtr))) {

			if ($type_c == 'info' && !$sans_mail)
				self::envoi_mail_commentaire($db, $table, $champ_id, $id_element,$type_c,$commtr,$id_utilisateur,$commtr);
		}
	}

	public static function get_commentaires($db, $table, $champ_id, $id_element) {
		$t = [];
		self::cls($table);
		self::cls($champ_id);

		$sql = "select * from {$table} where {$champ_id}=$1 order by date_commentaire";
		$q = bobs_qm()->query($db, "cmtr_g_{$table}", $sql, array((int)$id_element));
		while ($r = bobs_element::fetch($q)) {
			$c = new bobs_commentaire();
			$c->table = $table;
			$c->champ_id = $champ_id;
			$c->id_element = $id_element;
			$c->id_commentaire = $r['id_commentaire'];
			$c->date_commentaire = $r['date_commentaire'];
			$c->date_commentaire_f = strftime("%d-%m-%Y à %Hh%M", strtotime($c->date_commentaire));
			$c->type_commentaire = $r['type_commentaire'];
			$c->id_utilisateur = $r['id_utilisateur'];
			$c->utilisateur = get_utilisateur($db, $r['id_utilisateur']);
			$c->commentaire = nl2br($r['commentaire']);
			if ($c->type_commentaire == 'attr') {
				if (preg_match("/^tag ([\+\-])(\d+)$/", $c->commentaire, $matches)) {
					$tag = get_tag($db, $matches[2]);
					$c->commentaire = "étiquette <i>{$tag}</i> ".($matches[1]=='+'?"ajoutée":"retirée");
				} else if (preg_match("/^(\w+) (\d+) => (\d+)/", $c->commentaire, $matches)) {
					list($tt,$champ,$v1,$v2) = $matches;
					$c->commentaire = "Modification du champ $champ : <i>$v1</i> remplacée par <i>$v2</i>";
					switch ($champ) {
						case 'nb':
							$c->commentaire = "Effectif modifié : $v2 remplace $v1";
							break;
						case 'id_espece':
							$e1 = get_espece($db, $v1);
							$e2 = get_espece($db, $v2);
							$e1 = $e1?$e1:$v1;
							$e2 = $e2?$e2:$v2;
							$c->commentaire = "identification modifiée <i>{$e2}</i> remplace <i>{$e1}</i>";
							break;
						case 'indice_qualite':
							try {
								$i1 = new bobs_indice_qualite($v1);
							} catch (\Exception $e) {
								$i1 = "inconnu";
							}
							try {
								$i2 = new bobs_indice_qualite($v2);
							} catch (\Exception $e) {
								$i2 = "inconnu";
							}
							$c->commentaire = "niveau de certitude identification passe de <i>{$i1}</i> a <i>{$i2}</i>";
							break;
					}
				}
			}
			$t[] = $c;
		}
		return $t;
	}

	public static function supprime_commentaire($db, $table, $id_commentaire) {
		self::cls($table, self::except_si_vide);
		self::cli($id_commentaire, self::except_si_inf_1);
		$sql = "delete from {$table} where id_commentaire = $1";
		return bobs_qm()->query($db, "cmtr_del_{$table}", $sql, array($id_commentaire));
	}

	public static function get_tous_les_commentaires($db, $limite=200, $type='info') {
		$sql = "select * from (select
				id_commentaire, c.id_citation as ele_id, type_commentaire, date_commentaire, cc.commentaire, 'citations_commentaires' as tble,u.nom,u.prenom
				from citations_commentaires cc, citations c,utilisateur u
				where cc.id_citation=c.id_citation and u.id_utilisateur=cc.id_utilisateur
			union
				select id_commentaire, oc.id_observation as ele_id, type_commentaire, date_commentaire, commentaire, 'observations_commentaires' as tble,u.nom,u.prenom
				from observations_commentaires oc, utilisateur u
				where u.id_utilisateur=oc.id_utilisateur
			)
			as sq where type_commentaire=$1 order by date_commentaire desc limit $limite";
		$q = bobs_qm()->query($db, 'all_comments_'.$limite, $sql, array($type));
		return bobs_element::fetch_all($q);
	}
}
