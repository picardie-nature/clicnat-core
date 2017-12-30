<?php
namespace Picnat\Clicnat;

class clicnat_wfs_get_capabilites extends clicnat_wfs_operation {
	public function __construct() {
		$this->titre = 'WFS';
		$this->keywords = 'nature,species';
	}

	public function reponse($version='1.0.0') {
		$doc = new DOMDocument();
		$doc->formatOutput = true;
		$root = $doc->createElementNS('http://www.opengis.net/wfs','wfs:WFS_Capabilities');
		$root->setAttribute('version', self::version);
		$doc->appendChild($root);
		switch ($version) {
			case '1.1.0':
				$service_id = $doc->createElement('ServiceIdentification');
				$service_id->appendChild($doc->createElement('Title', $this->titre));
				$keywords = $doc->createElement('Keywords');
				foreach (explode(',', $this->keywords) as $kw) {
					$keywords->appendChild($doc->createElement('Keyword', $kw));
				}
				$root->appendChild($service_id);
				$root->appendChild($keywords);
				$service_prov = $doc->createElement('ServiceProvider');
				$service_prov->appendChild($doc->createElement('ProviderName', WFS_SERVICE_PROVIDER));
				$sp_contact = $doc->createElement('ServiceContact');
				$sp_contact->appendChild($doc->createElement('PositionName', WFS_CONTACT_NAME));
				$sp_contact_mail = $doc->createElement('Address', WFS_CONTACT_MAIL);
				$sp_contact_info = $doc->createElement('ContactInfo');
				$sp_contact_info->appendChild($sp_contact_mail);
				$sp_contact->appendChild($sp_contact_info);
				$service_prov->appendChild($sp_contact);
				$root->appendChild($service_prov);
				break;
			case '1.0.0':
				$service = $doc->createElementNS('http://www.opengis.net/wfs','wfs:Service');
				$service->appendChild($doc->createElementNS('http://www.opengis.net/wfs','wfs:Name', $this->titre));
				$service->appendChild($doc->createElementNS('http://www.opengis.net/wfs','wfs:Title', $this->titre));
				$service->appendChild($doc->createElementNS('http://www.opengis.net/wfs','wfs:Abstract', $this->titre));
				$service->appendChild($doc->createElementNS('http://www.opengis.net/wfs','wfs:OnlineResource', WFS_URL_BASE));
				$root->appendChild($service);
				break;
		}

		switch ($version) {
			case '1.1.0':
				$ops = $doc->createElement('OperationsMetadata');
				// GetCapabilities
				$op = $doc->createElement('Operation');
				$op->setAttribute("name", "GetCapabilities");
				$dcp = $doc->createElement('DCP');
				$http = $doc->createElement('HTTP');
				$get = $doc->createElement('GET');
				$get->setAttributeNS("http://www.w3.org/1999/xlink","xlink:href",WFS_URL_BASE);
				$http->appendChild($get);
				$dcp->appendChild($http);
				$op->appendChild($dcp);
				$ops->appendChild($op);

				// GetFeature
				$op = $doc->createElement('Operation');
				$op->setAttribute("name", "GetFeature");
				$dcp = $doc->createElement('DCP');
				$http = $doc->createElement('HTTP');
				$get = $doc->createElement('GET');
				$get->setAttributeNS("http://www.w3.org/1999/xlink","xlink:href",WFS_URL_BASE);
				$http->appendChild($get);
				$dcp->appendChild($http);
				$op->appendChild($dcp);
				$pmOF = $doc->createElement("Parameter");
				$pmOF->setAttribute("outputFormat");
				$pmOF->appendChild($doc->createElement("Value","text/xml"));
				$op->appendChild($pmOF);
				$ops->appendChild($op);
				$root->appendChild($ops);
				break;
			case '1.0.0':
				$cap = $doc->createElementNS('http://www.opengis.net/wfs','wfs:Capability');
				$req = $doc->createElementNS('http://www.opengis.net/wfs','wfs:Request');

				$getcap = $doc->createElementNS('http://www.opengis.net/wfs','wfs:GetCapabilities');
				$getcap_dcpt = $doc->createElementNS('http://www.opengis.net/wfs','wfs:DCPType');
				$getcap_dcpt_http = $doc->createElementNS('http://www.opengis.net/wfs','wfs:HTTP');
				$getcap_dcpt_http_get = $doc->createElementNS('http://www.opengis.net/wfs','wfs:Get');
				$getcap_dcpt_http_get->setAttribute("onlineResource", WFS_URL_BASE);
				$getcap_dcpt_http->appendChild($getcap_dcpt_http_get);
				$getcap_dcpt->appendChild($getcap_dcpt_http);
				$getcap->appendChild($getcap_dcpt);
				$req->appendChild($getcap);


				$getfeature = $doc->createElement('GetFeature');
				$getfeature_rf = $doc->createElement('ResultFormat');
				$getfeature_gml2 = $doc->createElement('GML2');
				$getfeature_rf->appendChild($getfeature_gml2);
				$getfeature->appendChild($getfeature_rf);

				$getfeature_dcpt = $doc->createElement('DCPType');
				$getfeature_dcpt_http = $doc->createElement('HTTP');
				$getfeature_dcpt_http_get = $doc->createElement('Get');
				$getfeature_dcpt_http_get->setAttribute("onlineResource", WFS_URL_BASE);
				$getfeature_dcpt_http->appendChild($getfeature_dcpt_http_get);
				$getfeature_dcpt->appendChild($getfeature_dcpt_http);

				$getfeature_dcpt = $doc->createElement('DCPType');
				$getfeature_dcpt_http = $doc->createElement('HTTP');
				$getfeature_dcpt_http_get = $doc->createElement('Post');
				$getfeature_dcpt_http_get->setAttribute("onlineResource", WFS_URL_BASE);
				$getfeature_dcpt_http->appendChild($getfeature_dcpt_http_get);
				$getfeature_dcpt->appendChild($getfeature_dcpt_http);

				$getfeature->appendChild($getfeature_dcpt);
				$req->appendChild($getfeature);

				$cap->appendChild($req);
				$root->appendChild($cap);
				break;
		}
		// FeatureTypeList
		$ftl = $doc->createElementNS('http://www.opengis.net/wfs','wfs:FeatureTypeList');
		$ops = $doc->createElementNS('http://www.opengis.net/wfs','wfs:Operations');
		$ops->appendChild($doc->createElementNS('http://www.opengis.net/wfs','wfs:Operation','Query'));
		$ftl->appendChild($ops);

		$db = get_db();
		foreach (clicnat_listes_espaces::listes_publiques($db) as $liste_espace) {
			$le = new clicnat_listes_espaces($db, $liste_espace['id_liste_espace']);
			foreach ($le->liste_types_espace() as $type) {
				$ft = $doc->createElementNS('http://www.opengis.net/wfs','wfs:FeatureType');
				$ft->appendChild($doc->createElementNS('http://www.opengis.net/wfs','wfs:Name', "liste_espace_{$liste_espace['id_liste_espace']}_$type"));
				$title = $doc->createElementNS('http://www.opengis.net/wfs','wfs:Title');
				$title->appendChild($doc->createCDATASection($liste_espace['nom']));
				$ft->appendChild($title);
				$ft->appendChild($doc->createElement("SRS", "EPSG:4326"));
				$ftl->appendChild($ft);
			}
		}
		$root->appendChild($ftl);
		return $doc;
	}
}
