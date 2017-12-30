<?php
namespace Picnat\Clicnat;

class clicnat_ex_403 extends Exception {
	function __construct($message,$code=0,$previous=null) {
		if (empty($message))
			$message = "accès interdit";
		if (empty($code))
			$code = 403;
		parent::__construct($message,$code,$previous);
	}
}

if (!defined("CLICNAT2")) {
		function except_handl($e) {
			?>
		<pre style="border-style:solid; border-color:red; border-width:3px; padding: 4px; background-color: white;">
		<b>Erreur...</b><br/>
		<?php echo $e->getMessage(); ?>

		<b>Trace</b>
		<?php
		printf("\t<b>%-40s</b>\n",
			basename($e->getFile())." +".$e->getLine());
		foreach ($e->getTrace() as $ele) {
			printf("\t%-40s %s%s%s()\n",
				basename($ele['file'])." +{$ele['line']}",  $ele['class'], $ele['type'], $ele['function']);
		}
		?>
		</pre>
		<?php
	}
	set_exception_handler('except_handl');
}
