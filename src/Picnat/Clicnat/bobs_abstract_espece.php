<?php
namespace Picnat\Clicnat;

/**
 * @brief Classe intermédiaire de gestion des espèces
 *
 * Pseudo classe abstraite qui doit pas être utilisée
 * tel que. Elle fournie deux fonctions qui permettent
 * l'indexation des noms scientifique des espèces.
 *
 */
class bobs_abstract_espece extends bobs_element_commentaire {
	/**
	 * Transforme une chaîne de caractères représentant
	 * un nom scientifique pour en extraire des mots clés.
	 *
	 * @param $nom la chaine a chercher
	 * @return Retourne un tableau de mots clés.
	 */
	public static function index_nom($nom) {
		$nom = trim($nom);

		if (empty($nom)) {
			throw new \InvalidArgumentException('pas de nom');
		}

		// que des minuscules
		$nom = strtolower($nom);

		// on coupe au nom de l'autorité
		$n = strpos($nom, '(');
		if ($n !== false) {
			$nom = trim(substr($nom, 0, $n));
		}

		static $subst_chars;

		if (!isset($subst_chars)) {
			$defs = [
				'  ' => ' ',
				'y' => 'i',
				'yy' => 'i',
				'll' => 'l',
				'mm' => 'm',
				'ph' => 'p',
				'gg' => 'g',
				'tt' => 't',
				'é' => 'e',
				'è' => 'e',
				'ë' => 'e',
				'ê' => 'e',
				'à' => 'a',
				'É' => 'e',
				'È' => 'e',
				'î' => 'i',
				'ï' => 'i'
			];

			$subst_chars = [
				array_keys($defs),
				array_values($defs)
			];

			unset($defs);
		}
		$nom = str_replace($subst_chars[0], $subst_chars[1], $nom);
		return explode(' ', $nom);
	}

		/**
		 * @brief Effectue la recherche du nom scientifique dans un index.
		 *
		 * @param $db un descripteur vers la base de données
		 * @param $nom le nom scientifique de l'espèce
		 * @param $table la table où ce trouve l'index
		 * @param $pk la clé primaire du résultat
		 * @param $classe la classe de l'objet résultat dont on va créer une instance.
		 */
		protected static function __index_recherche($db, $nom, $table, $pk, $classe) {
			$mots = self::index_nom($nom);
			$especes_id = array();
			$premier = true;
			$last_pos_ok = 0;
			$pos = 0;
			foreach ($mots as $mot) {
				if ($premier) {
					if (strlen($mot) > 2) {
						$sql = sprintf("select * from %s
								where mot like lower('%s')||'%%' and ordre=0",
								$table, self::escape($mot));
						$q = self::query($db, $sql);

						while ($r = self::fetch($q))
							$especes_id[] = $r[$pk];
						$premier = false;
					}
					if (count($especes_id) > 0)
						$last_pos_ok = 1;
				} else {
					$pos++;
					if (count($especes_id) > 0) {
						$where_in = 'in (';
						foreach ($especes_id as $eid)
							$where_in .= $eid.',';
						$where_in = trim($where_in,',').')';
						$sql = sprintf("select * from %s
								where mot = lower('%s') and %s %s and ordre=%d",
								$table, self::escape($mot), $pk, $where_in,
								$pos);
						$q = self::query($db, $sql);
						$prev_especes_id = $especes_id;
						$especes_id = array();
						while ($r = self::fetch($q))
							$especes_id[] = $r[$pk];

						if (count($especes_id) > 0) {
							$last_pos_ok = $pos;
						} else {
							$especes_id = $prev_especes_id;
							break;
						}
					}
				}
			}

			$especes = [];

			foreach ($especes_id as $id) {
				try {
					$especes[] = new $classe($db, $id);
				} catch (\Exception $e) {
					bobs_log("recherche $classe pas de résultat pour id: $id");
					continue;
				}
			}
			return [
				'mots'       => $mots,
				'last_ok'    => $last_pos_ok,
				'n_mots'     => count($mots),
				'especes'    => $especes,
				'n_resultat' => count($especes_id),
				'sstring'    => $nom
			];
		}
	}
