<?php
namespace Picnat\Clicnat;

class clicnat_citation_export_sinp extends bobs_citation {
	private $__est_une_donnee_biblio = null;

	public function est_une_donnee_biblio() {
		if (is_null($this->__est_une_donnee_biblio)) {
			$tag = get_tag_by_ref($this->db, 'RBIB');
			$this->__est_une_donnee_biblio = $this->a_tag($tag->id_tag);
		}
		return $this->__est_une_donnee_biblio;
	}

	const sql_sauve = 'insert into sinp_dee (id_citation,document,date_creation,date_modification) values ($1,xmlparse(document $2),$3,$4)';
	const sql_update = 'update sinp_dee set document=xmlparse(document $2),date_creation=$3,date_modification=$4 where id_citation=$1';
	const sql_sel = 'select * from sinp_dee where id_citation=$1';

	public function dee() {
		$r = $this->current();

		if (!$r) {
			$this->sauve();
			$r = $this->current();
		}

		if (!$r)
			throw new \Exception('pas de DEE disponible');

		return $r['document'];
	}

	public function current() {
		$q = bobs_qm()->query($this->db, 'dee-current', self::sql_sel, [$this->id_citation]);
		if (!$q) return false;

		$r = self::fetch($q);
		if (!$r) return false;

		return $r;
	}

	public function sauve() {
		$dee = $this->current();

		if (!$dee) {
			// pas de dee archivée c'est une création
			$date_creation = new DateTime("now");
			$date_modification = $date_creation;
		} else {
			// déjà une dee archivée
			$date_creation = new DateTime($dee['date_creation']);
			$date_modification = new DateTime("now");
		}

		$doc = new DOMDocument("1.0", "UTF-8");
		$root = $doc->createElementNS(GML_NS_URL, 'gml:featureMember');

		$flou = true;
		if ($this->est_une_donnee_biblio())
			$flou = false;

		$doc->appendChild($this->occurence($doc,$flou,$date_creation,$date_modification));
	       	// même date pour création et mod, le trigger
		$d = new DateTime("now");
		$xml = $doc->saveXML();
		$data = [
			$this->id_citation,
			$xml,
			$date_creation->format(DateTime::ATOM),
			$date_modification->format(DateTime::ATOM)
		];

		try {
			$q = bobs_qm()->query($this->db, "sauve_sinp_dee", self::sql_sauve, $data);
		} catch (Exception $e) {
			$q = bobs_qm()->query($this->db, "update_sinp_dee", self::sql_update, $data);
			return pg_affected_rows($q) == 1;
		}
	}

	private function occurence($doc,$flou=false,$date_creation,$date_modification) {
		$root = $doc->createElementNS(GML_NS_URL, 'gml:featureMember');
		//$member = $doc->createElement('Member');
		$sujet_obs = $doc->CreateElementNs(SINP_NS_URL,'sinp:SujetObservation');
		$sujet_obs->setAttributeNs(GML_NS_URL, 'gml:id', "citation{$this->id_citation}");

		//$root->appendChild($member);
		$root->appendChild($sujet_obs);
		//$member->appendChild($sujet_obs);

		$observation = $this->get_observation();

		// uuid
		$identifiant = $doc->createElementNS(SINP_NS_URL, 'sinp:identifiantPermanent', SINP_PLATEFORME_URL.$this->guid);
		$sujet_obs->appendChild($identifiant);

		// statut obs
		$statut = $doc->createElementNS(SINP_NS_URL, 'sinp:statutObservation', $this->nb==-1?'No':'Pr');
		$sujet_obs->appendChild($statut);

		// Le taxon
		$espece = $this->get_espece();
		$cdata_nom_cite = $doc->createCDATASection($espece->nom_s);
		$nom_cite = $doc->createElementNS(SINP_NS_URL,'sinp:nomCite');
		$nom_cite->appendChild($cdata_nom_cite);
		$sujet_obs->appendChild($nom_cite);
		if ($espece->taxref_inpn_especes) {
			try {
				$einpn = new bobs_espece_inpn($this->db, $espece->taxref_inpn_especes);
				$sujet_obs->appendChild($doc->createElementNS(SINP_NS_URL,'sinp:cdNom', $espece->taxref_inpn_especes));
				$sujet_obs->appendChild($doc->createElementNS(SINP_NS_URL,'sinp:cdRef', $einpn->cd_ref));
			} catch (Exception $e) {
				throw new Exception("identifiant taxref {$espece->taxref_inpn_especes} invalide pour id_espece={$espece->id_espece}");
			}
		}

		// les observateurs
		$n_obs = 0;
		foreach ($observation->get_observateurs() as $observateur) {
			$n_obs++;
			$observateurs = $doc->createElementNS(SINP_NS_URL, 'sinp:observateur');
			$utl = get_utilisateur($this->db, $observateur['id_utilisateur']);
			if ($utl->partage_opts('transmettre_nom_avec_donnees') || $this->est_une_donnee_biblio()) {
				$personne = $doc->createElementNS(SINP_NS_URL, 'sinp:PersonneType');
				$personne->appendChild($doc->createElementNS(SINP_NS_URL, 'organisme', 'NSP'));
				$personne->appendChild($doc->createElementNS(SINP_NS_URL, 'identite', "{$observateur['nom']} {$observateur['prenom']}"));
			} else {
				$personne = $doc->createElementNS(SINP_NS_URL, 'sinp:PersonneType');
				$personne->appendChild($doc->createElementNS(SINP_NS_URL, 'organisme', 'NSP'));
				$personne->appendChild($doc->createElementNS(SINP_NS_URL, 'identite', "Anonyme"));
			}
			$observateurs->appendChild($personne);
			$sujet_obs->appendChild($observateurs);
		}
		if ($n_obs == 0) {
			$observateurs = $doc->createElementNS(SINP_NS_URL, 'sinp:observateur');
			$personne = $doc->createElementNS(SINP_NS_URL, 'sinp:PersonneType');
			$personne->appendChild($doc->createElementNS(SINP_NS_URL, 'organisme', 'NSP'));
			$personne->appendChild($doc->createElementNS(SINP_NS_URL, 'identite', 'NSP'));
			$observateurs->appendChild($personne);
			$sujet_obs->appendChild($observateurs);
		}

		// date
		$datedeb = $doc->createElementNS(SINP_NS_URL, 'sinp:dateDebut', $observation->date_min->format(DateTime::ATOM)); // ISO8601
		$datefin = $doc->createElementNS(SINP_NS_URL, 'sinp:dateFin', $observation->date_max->format(DateTime::ATOM));
		$sujet_obs->appendChild($datedeb);
		$sujet_obs->appendChild($datefin);

		// determinateur
		$sujet_obs->appendChild($doc->createElementNS(SINP_NS_URL, 'sinp:determinateur'));

		// date détermination
		$date_deter = $doc->createElementNS(SINP_NS_URL, 'sinp:dateDetermination');
		$date_deter->setAttributeNS("http://www.w3.org/2001/XMLSchema-instance","xsi:nil","true");
		$sujet_obs->appendChild($date_deter);

		// denombrement
		$denomb = $doc->createElementNS(SINP_NS_URL, 'sinp:denombrement');
		$denombt = $doc->createElementNS(SINP_NS_URL, 'sinp:DenombrementType');
		if (empty($this->nb)) {
			if (empty($this->nb_min) && empty($this->nb_max)) {
				// effectif inconnu
				$min = $doc->createElementNS(SINP_NS_URL, 'sinp:denombrementMin');
				$min->setAttributeNS("http://www.w3.org/2001/XMLSchema-instance","xsi:nil","true");
				$max = $doc->createElementNS(SINP_NS_URL, 'sinp:denombrementMax');
				$max->setAttributeNS("http://www.w3.org/2001/XMLSchema-instance","xsi:nil","true");
				$denombt->appendChild($min);
				$denombt->appendChild($max);
			} else {
				$denombt->appendChild($doc->createElementNS(SINP_NS_URL, 'sinp:denombrementMin', $this->nb_min));
				$denombt->appendChild($doc->createElementNS(SINP_NS_URL, 'sinp:denombrementMax', $this->nb_max));
			}
		} elseif ($this->nb == -1) {
			// prospection négative
			//$denombt->appendChild($doc->createElementNS(SINP_NS_URL, 'sinp:denombrementMin'));
			//$denombt->appendChild($doc->createElementNS(SINP_NS_URL, 'sinp:denombrementMax'));
			$min = $doc->createElementNS(SINP_NS_URL, 'sinp:denombrementMin');
			$min->setAttributeNS("http://www.w3.org/2001/XMLSchema-instance","xsi:nil","true");
			$max = $doc->createElementNS(SINP_NS_URL, 'sinp:denombrementMax');
			$max->setAttributeNS("http://www.w3.org/2001/XMLSchema-instance","xsi:nil","true");
			$denombt->appendChild($min);
			$denombt->appendChild($max);
		} else {
			$denombt->appendChild($doc->createElementNS(SINP_NS_URL, 'sinp:denombrementMin', $this->nb));
			$denombt->appendChild($doc->createElementNS(SINP_NS_URL, 'sinp:denombrementMax', $this->nb));
		}
		$denombt->appendChild($doc->createElementNS(SINP_NS_URL, 'sinp:objetDenombrement','Individu'));
		$denombt->appendChild($doc->createElementNS(SINP_NS_URL, 'sinp:typeDenombrement','NSP'));
		$denomb->appendChild($denombt);
		$sujet_obs->appendChild($denomb);

		$espace = $observation->get_espace();
		if (!$flou) {
			//geometrie
			$gml = $espace->get_geom_gml(2154);

			$frag = $doc->createDocumentFragment();
			$frag->appendXML($gml);
			foreach ($frag->childNodes as $geom) {
				$geom->setAttributeNs(GML_NS_URL, 'gml:id', "{$observation->espace_table}.{$observation->id_espace}");
				break;
			}

			$objGeo = $doc->createElementNS(SINP_NS_URL, 'sinp:objetGeo');
			$objGeoType = $doc->createElementNS(SINP_NS_URL, 'sinp:ObjetGeographiqueType');
			$natureObjetGeo = $doc->createElementNS(SINP_NS_URL, 'sinp:natureObjetGeo', 'In');
			$geometrie = $doc->createElementNS(SINP_NS_URL, 'sinp:geometrie');
			$geometrie->appendChild($frag);

			$objGeo->appendChild($objGeoType);
			$objGeoType->appendChild($natureObjetGeo);
			$objGeoType->appendChild($geometrie);
			$objGeoType->appendChild($doc->createElementNS(SINP_NS_URL, 'sinp:precisionGeometrie', 0));
		} else {
			$objGeo = $doc->createElementNS(SINP_NS_URL, 'sinp:objetGeo');
			$objGeo->setAttributeNS("http://www.w3.org/2001/XMLSchema-instance","xsi:nil","true");
		}

		$sujet_obs->appendChild($objGeo);

		$habitat = $doc->createElementNS(SINP_NS_URL, 'sinp:habitat');
		$habitat->setAttributeNS("http://www.w3.org/2001/XMLSchema-instance","xsi:nil","true");
		$sujet_obs->appendChild($habitat);

		$altitudeMin = $doc->createElementNS(SINP_NS_URL, 'sinp:altitudeMin');
		$altitudeMin->setAttributeNS("http://www.w3.org/2001/XMLSchema-instance","xsi:nil","true");
		$sujet_obs->appendChild($altitudeMin);

		$altitudeMax = $doc->createElementNS(SINP_NS_URL, 'sinp:altitudeMax');
		$altitudeMax->setAttributeNS("http://www.w3.org/2001/XMLSchema-instance","xsi:nil","true");
		$sujet_obs->appendChild($altitudeMax);

		$profondeurMin = $doc->createElementNS(SINP_NS_URL, 'sinp:profondeurMin');
		$profondeurMin->setAttributeNS("http://www.w3.org/2001/XMLSchema-instance","xsi:nil","true");
		$sujet_obs->appendChild($profondeurMin);

		$profondeurMax = $doc->createElementNS(SINP_NS_URL, 'sinp:profondeurMax');
		$profondeurMax->setAttributeNS("http://www.w3.org/2001/XMLSchema-instance","xsi:nil","true");
		$sujet_obs->appendChild($profondeurMax);

		$organismeStd = $doc->createElementNS(SINP_NS_URL, 'organismeStandard', 'Picardie Nature');
		$sujet_obs->appendChild($organismeStd);

		$validateur = $doc->createElementNS(SINP_NS_URL, 'validateur');
		$validateur->setAttributeNS("http://www.w3.org/2001/XMLSchema-instance","xsi:nil","true");
		$sujet_obs->appendChild($validateur);

		$commentaire = $doc->createElementNs(SINP_NS_URL, 'commentaire');
		$commentaire->setAttributeNS("http://www.w3.org/2001/XMLSchema-instance","xsi:nil","true");
		$sujet_obs->appendChild($commentaire);

		// attribut additionel

		// source
		$src = $doc->createElementNS(SINP_NS_URL, 'sinp:source');
		$source = $doc->createElementNS(SINP_NS_URL, 'sinp:Source');
		$v_statutSource = 'Te'; // sauf pour la biblio tout vient du terrain
		if ($this->est_une_donnee_biblio())
			$v_statutSource = 'Li';
		$source->appendChild($doc->createElementNS(SINP_NS_URL, 'dEEDateDerniereModification', $date_modification->format(DateTime::ATOM)));
		$source->appendChild($doc->createElementNS(SINP_NS_URL, 'dEEDateTransformation', $date_creation->format(DateTime::ATOM)));
		$source->appendChild($doc->createElementNS(SINP_NS_URL, 'statutSource', $v_statutSource));
		$source->appendChild($doc->createElementNS(SINP_NS_URL, 'identifiantOrigine', $this->guid));
		$source->appendChild($doc->createElementNS(SINP_NS_URL, 'dSPublique', 'Pr'));

		$sensible = ! $espece->get_restitution_ok(bobs_espece::restitution_public);

		$source->appendChild($doc->createElementNS(SINP_NS_URL, 'sensiDateAttribution', $date_modification->format(DateTime::ATOM)));
		$source->appendChild($doc->createElementNS(SINP_NS_URL, 'sensible', $sensible?'OUI':'NON'));
		$source->appendChild($doc->createElementNS(SINP_NS_URL, 'sensiNiveau', $sensible?2:0));

		$source->appendChild($doc->createElementNS(SINP_NS_URL, 'jddId', SINP_IDCNP));
		$source->appendChild($doc->createElementNS(SINP_NS_URL, 'jddCode', 'Clicnat'));

		$refBiblio = $doc->createElementNS(SINP_NS_URL, 'referenceBiblio');
		if ($this->est_une_donnee_biblio()) {
			$tag = get_tag_by_ref($this->db, 'RBIB');
			$tag_biblio = $this->get_tag($tag->id_tag);
			$refBiblio->nodeValue = $tag_biblio['v_text'];
		} else {
			$refBiblio->setAttributeNS("http://www.w3.org/2001/XMLSchema-instance","xsi:nil","true");
		}
		$source->appendChild($refBiblio);
		$source->appendChild($doc->createElementNS(SINP_NS_URL, 'organismeGestionnaireDonnee', SINP_GESTIONNAIRE));
		$source->appendChild($doc->createElementNS(SINP_NS_URL, 'codeIDCNPDispositif', SINP_IDCNP));
		$source->appendChild($doc->createElementNS(SINP_NS_URL, 'dEEFloutage', $flou?'OUI':'NON'));

		$src->appendChild($source);
		$sujet_obs->appendChild($src);

		// communes
		foreach ($espace->get_communes() as $commune) {
			$communes = $doc->createElementNS(SINP_NS_URL, 'sinp:communes');
			$com = $doc->createElementNS(SINP_NS_URL, 'sinp:Commune');
			$com->appendChild($doc->createElementNS(SINP_NS_URL, 'sinp:codeCommune', sprintf("%02d%03d",$commune->dept,$commune->code_insee)));
			$com->appendChild($doc->createElementNS(SINP_NS_URL, 'sinp:nomCommune', "{$commune->nom}"));
			$communes->appendChild($com);
			$sujet_obs->appendChild($communes);
		}

		// mailles
		foreach ($espace->get_index_atlas_repartition(2154,10000) as $m) {
			$mailles = $doc->createElementNS(SINP_NS_URL, 'sinp:mailles');
			$maille10x10 = $doc->createElementNS(SINP_NS_URL, "Maille10x10");
			$maille10x10->appendChild($doc->createElementNS(SINP_NS_URL, "codeMaille", "10kmL93W{$m['x0']}N{$m['y0']}"));
			$mailles->appendChild($maille10x10);
			$sujet_obs->appendChild($mailles);
		}
		return $root;
	}
}
