<?php
namespace Picnat\Clicnat;

class clicnat_ex_404 extends Exception {
	function __construct($message,$code=0,$previous=null) {
		if (empty($message))
			$message = "page ou objet non trouvée";
		if (empty($code))
			$code = 404;
		parent::__construct($message,$code,$previous);
	}
}
