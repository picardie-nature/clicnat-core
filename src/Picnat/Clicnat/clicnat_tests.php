<?php
namespace Picnat\Clicnat;

trait clicnat_tests {
	/**
	 * @brief nettoie une chaîne de caractéres
	 */
	 # test une string comme cli
	final public static function cls(&$str, $opt=self::null_si_vide) {
		$str = trim($str);
		# @todo switch case
		if (empty($str)) {
			if ($opt == self::null_si_vide) {
				if ($str == '0')
					return 0;
				return null;
			} elseif ($opt == self::except_si_vide) {
				throw new Exception('chaine vide');
			} elseif ($opt == self::except_si_inf) {
				throw new Exception('$opt invalide');
			}
		}
		return $str;
	}

	/**
	 * @brief modifie la variable pour qu'elle devienne un entier
	 * @param $ent l'entier passé par adresse
	 * @return l'entier modifié
	 */
	 #Execute le test en parametre et renvoie l'entier
	final public static function cli(&$ent, $opt=self::null_si_vide) {
		#http://php.net/manual/fr/function.trim.php
		#supprime caractères invisibles en début et fin de chaine
		$str = trim($ent);
		$entier = (int)$ent;
		switch ($opt) {
			case self::null_si_vide:
				if ($str == '0')
					return 0;
				if (empty($str))
					return null;
				break;
			case self::except_si_vide:
				if (empty($str) && $str != '0')
					throw new Exception('chaine vide cli');
				break;
			case self::except_si_inf_1:
				if ($entier < 1)
					throw new Exception('entier inf. 1');
				break;
		}
		$ent = (int)$ent;
		return $ent;
	}
}
