<?php
namespace Picnat\Clicnat;

trait clicnat_mini_template {
	function mail_attachment($filename, $path, $mailto, $from_mail, $from_name, $replyto, $subject, $message) {
		$file = $path.$filename;
		$file_size = filesize($file);
		$handle = fopen($file, "r");
		$content = fread($handle, $file_size);
		fclose($handle);
		$content = chunk_split(base64_encode($content));
		$uid = md5(uniqid(time()));
		$header = "From: ".$from_name." <".$from_mail.">\r\n";
		$header .= "Reply-To: ".$replyto."\r\n";
		$header .= "MIME-Version: 1.0\r\n";
		$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
		$header .= "This is a multi-part message in MIME format.\r\n";
		$header .= "--".$uid."\r\n";
		$header .= "Content-type:text/plain; charset=iso-8859-1\r\n";
		$header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
		$header .= $message."\r\n\r\n";
		$header .= "--".$uid."\r\n";
		$header .= "Content-Type: application/octet-stream; name=\"".$filename."\"\r\n"; // use different content types here
		$header .= "Content-Transfer-Encoding: base64\r\n";
		$header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
		$header .= $content."\r\n\r\n";
		$header .= "--".$uid."--";
		if (mail($mailto, $subject, "", $header)) {
			echo "mail send ... OK"; // or use booleans here
		} else {
			echo "mail send ... ERROR!";
 		}
	}

	/**
	 * Usage :
	 * $from = "my@mail.com";
	 * $dest = "mycustomer@extern.com"
	 * $filename = "somefile.jpg";
	 * $path = "/your_path/to_the_attachment/";
	 * ...
	 * mail_template_base64($from, $dest, $sujet_tpl, $texte_tpl, $vars, "image.png", $base64)
	 * inspired with thanks from : https://www.tutdepot.com/php-e-mail-attachment-script/
	**/
	protected static function mail_template_base64($from, $dest, $sujet_tpl, $texte_tpl, $vars, $type, $filename, $base64) {
		// first process message content cause must be included in MIME header
		$filename = "";
		// $type = "image/jpeg";
		$vars['from'] = $from;
		$message = self::mini_template($texte_tpl, $vars);
		// base64 attachment & header processing
		$content = chunk_split($base64);
		$uid = md5(uniqid(time()));
		$header = "From: {from}\r\n";
		$header .= "MIME-Version: 1.0\r\n";
		$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
		// $header .= "This is a multi-part message in MIME format.\r\n";

		$output = "--".$uid."\r\n";
		$output .= "Content-type:text/plain; charset=\"utf-8\"\r\n";
		$output .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
		$output .= $message."\r\n\r\n";
		$output .= "--".$uid."\r\n";
		$output .= "Content-Type: $type; name=\"".$filename."\"\r\n"; // use different content types here
		$output .= "Content-Transfer-Encoding: base64\r\n";
	  $output .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
		$output .= $content."\r\n\r\n";
		$output .= "--".$uid."--";
		// apply header replacements
		$headers = self::mini_template($header, $vars);
		$subject = self::mini_template($sujet_tpl, $vars);
		if (!mail($dest, $subject, $output, $headers, "-f{$vars['from']}")) {
			throw new Exception('Erreur survenue lors de l\'envoi du mail');
		// } else {
		// 	bobs_log("clicnat-elec mail sent : " . $headers . $output);
		}
		return true;
	}

	/**
	 * Usage :
	 * $from = "my@mail.com";
	 * $dest = "mycustomer@extern.com"
	 * $filename = "somefile.jpg";
	 * $path = "/your_path/to_the_attachment/";
	 * ...
	 * mail_template_attachment($from, $dest, $sujet_tpl, $texte_tpl, $vars, $filename, $path)
	 * inspired with thanks from : https://www.tutdepot.com/php-e-mail-attachment-script/
	**/
	protected static function mail_template_attachment($from, $dest, $sujet_tpl, $texte_tpl, $vars, $filename, $path) {
		// first process message content cause must be included in MIME header
		$vars['from'] = $from;
		$message = self::mini_template($texte_tpl, $vars);
		// attachment & header processing
		$file = $path.$filename;
		$file_size = filesize($file);
		$handle = fopen($file, "r");
		$content = fread($handle, $file_size);
		fclose($handle);
		$content = chunk_split(base64_encode($content));
		$uid = md5(uniqid(time()));
		$header = "From: {from}\r\n";
		$header .= "MIME-Version: 1.0\r\n";
		$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
		$header .= "This is a multi-part message in MIME format.\r\n";
		$header .= "--".$uid."\r\n";
		$header .= "Content-type:text/plain; charset=utf-8\r\n";
		$header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
		$header .= $message."\r\n\r\n";
		$header .= "--".$uid."\r\n";
		$header .= "Content-Type: application/octet-stream; name=\"".$filename."\"\r\n"; // use different content types here
		$header .= "Content-Transfer-Encoding: base64\r\n";
		$header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
		$header .= $content."\r\n\r\n";
		$header .= "--".$uid."--";
		// apply header replacements
		$headers = self::mini_template($header, $vars);
		$subject = self::mini_template($sujet_tpl, $vars);
		if (!mail($dest, $subject, "", $headers, "-f{$vars['from']}")) {
			throw new Exception('Erreur survenue lors de l\'envoi du mail');
		}
		return true;
	}

	protected static function mail_template($from, $dest, $sujet_tpl, $texte_tpl, $vars) {
		$vars['from'] = $from;
		$texte_headers =
			"From: {from}\r\n".
			"Content-Type: text/plain; charset=utf-8\r\n".
			"\r\n";
		$headers = self::mini_template($texte_headers, $vars);
		$msg = self::mini_template($texte_tpl, $vars);
		$sujet = self::mini_template($sujet_tpl, $vars);
		if (!mail($dest, $sujet, $msg, $headers, "-f{$vars['from']}")) {
			throw new Exception('Message pas envoyé');
		}
		return true;
	}

	protected static function mini_template($texte, $vars) {
		$sortie = '';
		$etat = T_ETAT_PARCOURS;
		$i = 0;
		while (isset($texte[$i])) {
			switch ($texte[$i]) {
				case '{':
					if ($etat == T_ETAT_PARCOURS) {
						$nomvar = '';
						$etat = T_ETAT_VAR;
					} else {
						throw new Exception("erreur invalide { @ car $i");
					}
					break;
				case '}':
					if ($etat == T_ETAT_VAR) {
						if (isset($vars[$nomvar])) {
							$sortie .= $vars[$nomvar];
						} else {
							throw new Exception("$nomvar est utilisée dans le template mais n'est pas disponible");
						}
						$etat = T_ETAT_PARCOURS;
					} else {
						throw new Exception("erreur invalide } @ car $i");
					}
					break;
				default:
					if ($etat == T_ETAT_PARCOURS)
						$sortie .= $texte[$i];
					else
						$nomvar .= $texte[$i];
					break;

			}
			$i++;
		}

		if ($etat == T_ETAT_VAR)
			throw new Exception();

		return $sortie;
	}
}
