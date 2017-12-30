<?php
namespace Picnat\Clicnat;

class clicnat_histogramme_mois {
	private $data;
	private $img;
	function __construct($titre, $xlabel, $ylabel, $serie) {
		$this->data = array(
			'title' => $titre,
			'xlabel' => $xlabel,
			'ylabel' => $ylabel,
			'y' => $serie
		);
	}

	public function get() {
		$env['MPLCONFIGDIR'] = '/tmp';
		$descripteurs = array(
			0 => array('pipe', 'r'),
			1 => array('pipe', 'w'),
			2 => array('file', '/tmp/debug_hist_mois.log', 'w')
		);
		$proc = proc_open(BIN_HISTOGRAMME_MOIS, $descripteurs, $tubes, getcwd(), $env);

		if (!is_resource($proc)) {
			throw new Exception('echec execution de '.BIN_HISTOGRAMME_MOIS);
		}

		fwrite($tubes[0], json_encode($this->data));

		fclose($tubes[0]);

		$this->img = stream_get_contents($tubes[1]);
		fclose($tubes[1]);

		proc_close($proc);
		return true;
	}

	public function puts() {
		header("Content-Type: image/png");
		echo $this->img;
		exit(0);
	}
}
?>
