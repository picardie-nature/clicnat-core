<?php
namespace Picnat\Clicnat;

function clicnat_wfs_op($db, $args) {
	if (is_object($args)) {
		if (get_class($args) == 'DOMDocument') {
			switch ($args->firstChild->nodeName) {
				case 'wfs:GetFeature':
					return new clicnat_wfs_get_feature($db, $args);
			}
		} else {
			throw new Exception('pas de la bonne classe');
		}
	} else {
		$request = null;
		foreach ($args as $k => $v) {
			if (strtoupper($k) == "REQUEST") {
				$request = $v;
				break;
			}
		}
		if (is_null($request))
			throw new Exception("Pas d'argument request");
		switch ($request) {
			case 'GetCapabilities':
				return new clicnat_wfs_get_capabilites($db, $args);
			case 'GetFeature':
				return new clicnat_wfs_get_feature($db, $args);
			case 'DescribeFeatureType':
				return new clicnat_wfs_get_desc_feature_type($db, $args);
		}
		throw new Exception("REQUEST=$v");
	}
}
