<?php
namespace Picnat\Clicnat;

use PHPUnit\Framework\TestCase;

class clicnat_couleurTests extends TestCase {
	public function testRGB2TSV() {
		$tsv = clicnat_couleur::rvb2tsv(1,0,0);
		list($t,$s,$v) = $tsv;
		$this->assertEquals(0, $t, "tsv=".join(',',$tsv));
		$this->assertEquals(1, $s, "tsv=".join(',',$tsv));
		$this->assertEquals(1, $v, "tsv=".join(',',$tsv));

		$tsv = clicnat_couleur::rvb2tsv(0,1,0);
		list($t,$s,$v) = $tsv;
		$this->assertEquals(120, $t, "tsv=".join(',',$tsv));
		$this->assertEquals(1, $s, "tsv=".join(',',$tsv));
		$this->assertEquals(1, $v, "tsv=".join(',',$tsv));

		$tsv = clicnat_couleur::rvb2tsv(0,0,1);
		list($t,$s,$v) = $tsv;
		$this->assertEquals(240, $t, "tsv=".join(',',$tsv));
		$this->assertEquals(1, $s, "tsv=".join(',',$tsv));
		$this->assertEquals(1, $v, "tsv=".join(',',$tsv));

		$tsv = clicnat_couleur::rvb2tsv(0.5,0.5,0.5);
		list($t,$s,$v) = $tsv;
		$this->assertEquals(0,   $t, "tsv=$t,$s,$v");
		$this->assertEquals(0,   $s, "tsv=$t,$s,$v");
		$this->assertEquals(0.5, $v, "tsv=$t,$s,$v");
	}

	public function testTSV2RGB() {
		list($r,$g,$b) = clicnat_couleur::tsv2rvb(0,1,1);
		$this->assertEquals(1,$r);
		$this->assertEquals(0,$g);
		$this->assertEquals(0,$b);

		list($r,$g,$b) = clicnat_couleur::tsv2rvb(0,0,0.5);
		$this->assertEquals(0.5,$r);
		$this->assertEquals(0.5,$g);
		$this->assertEquals(0.5,$b);
	}
}
