<?php
namespace \Picnat\Clicnat;

class bobs_citation_avec_validation extends bobs_citation {
	protected $time_start;
	protected $time_end;
	protected $test;
	protected $x;
	protected $y;

	function filtre_reseau() {
		$bobs_observation=$this->get_observation();
		$espece = $this->get_espece();
		$classe = $espece->classe;
		$observateur=$bobs_observation->get_observateurs();
		$bobs_espece= new bobs_espece($this->db,$this->id_espece);
		$reseau=$bobs_espece->get_reseau();
		$nom_reseau=$reseau->get_id();


		$id_observateurs=array();
		foreach($observateur as $case){  //recupére tout les id_utilisateurs liés a l'observation
			foreach($case as $cle=>$value){
				if($cle=="id_utilisateur")
					$id_observateurs[]=$value;
			}
		}
		foreach($id_observateurs as $id_observateur){
			$sql="SELECT id_utilisateur
				FROM stats_validation.utilisateurs
				WHERE id_utilisateur=$1";
			$selection=bobs_qm()->query($this->db, 'test_filtre_reseau', $sql, array($id_observateur));
			$resultat=self::fetch($selection);
			if(empty($resultat)){
				$bobs_utilisateur = new bobs_utilisateur($this->db,$id_observateur);
				$reseaux=$bobs_utilisateur->get_reseaux();
				foreach($reseaux as $value){
					$nom=$value->__get('id');
					$sql="INSERT INTO stats_validation.utilisateurs (id_utilisateur,reseau) VALUES ($1,$2)";
					bobs_qm()->query($this->db, 'mettre_reseau_dans_db', $sql, array($id_observateur,$nom));
				}
				$sql="SELECT id_utilisateur,reseau
					FROM stats_validation.utilisateurs
					WHERE id_utilisateur=$1
					AND reseau=$2";
				$selection=bobs_qm()->query($this->db, 'test_filtre_reseau_deux', $sql, array($id_observateur, $nom_reseau));
				$resultat=self::fetch($selection);
				if(empty($resultat))
					return false;
				else
					return true;
			}
			else{
				$sql="SELECT id_utilisateur,reseau
					FROM stats_validation.utilisateurs
					WHERE id_utilisateur=$1
					AND reseau=$2";
				$selection=bobs_qm()->query($this->db, 'test_filtre_reseau_deux', $sql, array($id_observateur, $nom_reseau));
				$resultat=self::fetch($selection);
				if(empty($resultat))
					return false;
				else
					return true;
			}
		}
	}


	function filtre_documents(){
		try {
			$this->time_start=microtime(true);
			$document=$this->documents_liste();
			$this->time_end=microtime(true);
			$this->test=$this->time_end-$this->time_start;

		} catch (\Exception $e) {
			return false;
		}
		return(count($document)>0);
	}

	function extraction_effectifs() {
		throw new \Exception('ne pas utiliser');
		try {
			$extraction  = new bobs_extractions($this->db);
			$extraction->ajouter_condition(new bobs_ext_c_espece($this->id_espece));
			/*if(file_exists("/home/thomas/baseobs/branches/picardie-nature/validation/scripts/valides/effectif/".$this->id_espece)){
				//----------------------------------------------fichier lecture------------------------------------------------------
				$filename="/home/thomas/baseobs/branches/picardie-nature/validation/scripts/valides/effectif/".$this->id_espece;
				$handle=fopen($filename,"r");
				$lignes=file($filename);
				$n=count($lignes);
				$ligne=$lignes[0];
				$t=unserialize($ligne);
				fclose($handle);
				//----------------------------------------------fichier lecture------------------------------------------------------
			}
			else{*/
				$t = array();
				foreach($extraction->get_citations() as $citation) {
					if($citation->invalide())
						continue;
					if(empty($citation->nb)||($citation->nb<=-1))
						continue;
					$t[]=$citation->nb;
				}
				/*//----------------------------------------------fichier ecriture-----------------------------------------------------
				$handle=fopen("/home/thomas/baseobs/branches/picardie-nature/validation/scripts/valides/effectif/".$this->id_espece,"w");
				$serialize=serialize($t);
				fwrite($handle,$serialize);
				fclose($handle);
				//----------------------------------------------fichier ecriture-----------------------------------------------------
				*/
			//}
			return $t;
		} catch (\Exception $e) {
			return false;
		}
	}


	const sql_filtre_effectifs = "select moyenne from stats_validation.effectifs where id_espece=$1";

	function filtre_effectifs() {
			$this->time_start=microtime(true);
			$selection=bobs_qm()->query($this->db, 'test_fltre_effectifs', self::sql_filte_effectifs, array($this->id_espece));
			$resultat=self::fetch($selection);
			$this->time_end=microtime(true);
			$this->test=$this->time_end-$this->time_start;

			if(empty($resultat))
				return false;

			$moyenne=$resultat['moyenne'];

			if($this->nb <= $moyenne)
				return true;

			if($this->nb > $moyenne)
				return (($this->nb/$moyenne)*100) > 25;
	}


	function filtre_indice(){
		try {
			$this->time_start=microtime(true);
			$indice=$this->indice_qualite;
			if(!isset($indice))
				$indice=4;
			$this->time_end=microtime(true);
			$this->test=$this->time_end-$this->time_start;

			return($indice==4);

		} catch (\Exception $e) {
			return false;
		}
	}


	function filtre_chr(){
		try {
			$this->time_start=microtime(true);
			$bobs_espece=$this->get_espece();
			$chr=$bobs_espece->get_chr();
			$this->time_end=microtime(true);
			$this->test=$this->time_end-$this->time_start;

			return !($chr == false);
		} catch (\Exception $e) {
			return false;
		}
	}


	function filtre_lieu(){
		try {
			$this->time_start=microtime(true);
			$bobs_observation = $this->get_observation();
			$espace = $bobs_observation->get_espace();
			$index_atlas_repartition=$espace->get_index_atlas_repartition(2154,10000);//coordonnés du carré de la citation
			$this->x=$index_atlas_repartition[0]['x0'];
			$this->y=$index_atlas_repartition[0]['y0'];
			$espece=$this->get_espece();
			$index_atlas_repartition_derniere_annee=$espece->get_index_atlas_repartition_derniere_annee(2154,10000);//les carré ou l'espece a ete cité
			$annee_ya_onze_ans= strftime("%Y")-11;
			$nb_zones=count($index_atlas_repartition_derniere_annee);
			$this->time_end=microtime(true);
			$this->test=$this->time_end-$this->time_start;


			foreach($index_atlas_repartition_derniere_annee as $case){
				if(($case['x0']==$this->x)&&($case['y0']==$this->y)&&($case['annee_max']>=$annee_ya_onze_ans))
					return "Espece citée recement dans les dix kilometres";
			}
			foreach($index_atlas_repartition_derniere_annee as $case){
				if(($case['x0']==$this->x)&&($case['y0']==$this->y)&&($case['annee_max']<$annee_ya_onze_ans))
					return "Espece citée il y a plus de 10 dans les dix kilometres";
			}
			$cpt=0;
			foreach($index_atlas_repartition_derniere_annee as $case){
				if(($case['x0']!=$this->x)||($case['y0']!=$this->y))
					$cpt++;
			}
			if($cpt==$nb_zones)
				return "Espece jamais citée dans les dix kilometres";
		} catch (\Exception $e) {
			return false;
		}
	}


	static function decade($date) {
		return floor((int)strftime("%j", strtotime($date))/10);
	}

	/**
	 * @brief produit un tableau du nombre d'observations / décade
	 * @return array : t[annee][decade] = n
	 *
	 * Déplacer sur bobs_espece
	 */
	function extraction_espece() {
		throw new \Exception('déplacé');
		try {
			$extraction=new bobs_extractions($this->db);
			$extraction->ajouter_condition(new bobs_ext_c_espece($this->id_espece));
			$annee_max = strftime("%Y");
			for ($i=1;$i<=11;$i++) {
				$extraction->ajouter_condition(new bobs_ext_c_annee($annee_max-$i));
			}
			$t = array();
			foreach($extraction->get_citations() as $citation) {
				if($citation->invalide())
					continue;

				$observation = $citation->get_observation();
				// passe si la précision de la date d'observation est supérieur à +- 5 jours
				if ($observation->precision_date >= 5)
					continue;

				$annee = strftime("%Y",strtotime($observation->date_observation));

				$decade = self::decade($observation->date_observation);

				if (!isset($t[$annee][$decade]))
					$t[$annee][$decade] = 0;

				$t[$annee][$decade]++;
			}
			return $t;
		} catch (\Exception $e) {
			return false;
		}
	}

	function affiche_espece($t) {
		foreach ($t as $k=>$v) {
			$i = 0;
			$txt_ligne = $k.",";
			while ($i < 37) {
				$txt_ligne .= isset($v[$i])?$v[$i]:0;
				$txt_ligne .= ',';
				$i++;
			}
			echo "$txt_ligne\n";
		}
	}


	function get_periode($t) {
		try {
			$cpt_zero=0;
			$cpt_nb_cases=370;
			$i=0;
			//affiche_espece($t);
			//parcourt les cases du tableau et compte nb cases indefinies.
			foreach ($t as $k=>$v) {
				for($i=0;$i<=36;$i++){
					if(!isset($v[$i]))
						$cpt_zero++;
				}
			}
			//echo "nb de zero" . $cpt_zero . "\n";
			//somme des obs par decades
			$som=0;
			$somme=array();
			foreach($t as $decades){
				foreach($decades as $num_decade=>$nb){
					if(!isset($somme[$num_decade]))
						$somme[$num_decade]=0;
					$somme[$num_decade]+=$nb;
				}
			}
			//moyenne des obs par decades
			$moy=0;
			$moyenne=array();
			for($i=0;$i<37;$i++){
				if(!isset($somme[$i]))
					$somme[$i]=0;
				$moyenne[$i]=($somme[$i]/10);
			}
			//print_r($moyenne);
			//echo "\n";
			//moyenne des sommes des obs par decades
			$somme_somme=0;
			foreach($somme as $v)
				$somme_somme+=$v;
			$moyenne_somme=($somme_somme/37);
			//moyenne des moyennes des obs par decades
			$somme_moyenne=0;
			foreach($moyenne as $v)
				$somme_moyenne+=$v;
			$moyenne_moyenne=($somme_moyenne/37);
			//echo $moyenne_moyenne."\n";
			if($cpt_zero<=((1/3)*$cpt_nb_cases)){             		//si 1/3 des cases ou moins sont a 0
				if($cpt_zero<=((0.1)*$cpt_nb_cases)){     		//10% ou moin de 360 cases a 0.
					//prendre decades jusqu'a 30%.
					//faire pourcentages par rapport a la somme des obs par decades et la moyennes des somme
					$pourcent=array();
					for($i=0;$i<=36;$i++){
					if (!isset($moyenne[$i]))
							$pourcent[$i]=0;
						else
							$pourcent[$i]=($moyenne[$i]/$moyenne_moyenne)*100;
					}
					//prendre decades >=30%
					$cpt=0;						//le cpt est important il sert a eviter de rentrer une valeur et eviter un test
					$l=0;
					$periode=array(array());
					for($i=0;$i<=36;$i++){              		//rempli un tableau $periode selon certaines conditions
						if(($pourcent[$i]>=30)&&($cpt==0)){ 	//si le pourcentage >= 25 et le compteur a 0
							$periode[$l][0]=$i;  		//met $i dans la case
							$cpt++; 			//ajoute 1 au cpt
						}
						if(($pourcent[$i]<30)&&($cpt>0)){  	//si le pourcentage est < 25 et cpt > 0
							$periode[$l][1]=$i-1; 		//met la valeur $i inferieure
							$cpt=0; 			//remet compteur a 0 pour pouvoir retourner dans la condition superieur
							$l++; 				//change de ligne
						}
					}
					if($cpt>0)
						$periode[$l][1]=36;
				}
				else{
					//prendre decades jusqu'a 50%.
					//faire pourcentages par rapport a la somme des obs par decades et la moyennes des somme
					$pourcent=array();
					for($i=0;$i<=36;$i++){
					if (!isset($moyenne[$i]))
							$pourcent[$i]=0;
						else
							$pourcent[$i]=($moyenne[$i]/$moyenne_moyenne)*100;
					}
					//prendre decades >=50%
					$cpt=0;						//le cpt est important il sert a eviter de rentrer une valeur et eviter un test
					$l=0;
					$periode=array(array());
					for($i=0;$i<=36;$i++){              		//rempli un tableau $periode selon certaines conditions
						if(($pourcent[$i]>=50)&&($cpt==0)){ 	//si le pourcentage >= 25 et le compteur a 0
							$periode[$l][0]=$i;  		//met $i dans la case
							$cpt++; 			//ajoute 1 au cpt
						}
						if(($pourcent[$i]<50)&&($cpt>0)){  	//si le pourcentage est < 25 et cpt > 0
							$periode[$l][1]=$i-1; 		//met la valeur $i inferieure
							$cpt=0; 			//remet compteur a 0 pour pouvoir retourner dans la condition superieur
							$l++; 				//change de ligne
						}
					}
					if($cpt>0)
						$periode[$l][1]=36;
				}
			}
			else{                                            		//plus d'un tier des cases a 0.
				if($cpt_zero>=((0.7)*$cpt_nb_cases)){     		//si au moin 70% des cases sont a 0.
					//faire pourcentages par rapport a la somme des obs par decades et la moyennes des somme
					$pourcent=array();
					for($i=0;$i<=36;$i++){
					if (!isset($somme[$i]))
							$pourcent[$i]=0;
						else
							$pourcent[$i]=($somme[$i]/$moyenne_somme)*100;
					}
					//prendre decades >=25%
					$cpt=0;						//le cpt est important il sert a eviter de rentrer une valeur et eviter un test
					$l=0;
					$periode=array(array());
					for($i=0;$i<=36;$i++){              		//rempli un tableau $periode selon certaines conditions
						if(($pourcent[$i]>=25)&&($cpt==0)){ 	//si le pourcentage >= 25 et le compteur a 0
							$periode[$l][0]=$i;  		//met $i dans la case
							$cpt++; 			//ajoute 1 au cpt
						}
						if(($pourcent[$i]<25)&&($cpt>0)){  	//si le pourcentage est < 25 et cpt > 0
							$periode[$l][1]=$i-1; 		//met la valeur $i inferieure
							$cpt=0; 			//remet compteur a 0 pour pouvoir retourner dans la condition superieur
							$l++; 				//change de ligne
						}
					}
					if($cpt>0)
						$periode[$l][1]=36;
				}
				else{
					//prendre decades >=25% pourcentages par rapport a la moyenne des obs par decades et moyenne generale des moyennes des obs par decades
					//faire pourcentages par rapport a la somme des obs par decades et la moyennes des somme
					$pourcent=array();
					for($i=0;$i<=36;$i++){
						if (!isset($moyenne[$i]))
							$pourcent[$i]=0;
						else
							$pourcent[$i]=($moyenne[$i]/$moyenne_moyenne)*100;
					}
					//prendre decades >=25%
					$cpt=0;						//le cpt est important il sert a eviter de rentrer une valeur et eviter un test
					$l=0;
					$periode=array(array());
					for($i=0;$i<=36;$i++){              		//rempli un tableau $periode selon certaines conditions
						if(($pourcent[$i]>=25)&&($cpt==0)){ 	//si le pourcentage >= 25 et le compteur a 0
							$periode[$l][0]=$i;  		//met $i dans la case
							$cpt++; 			//ajoute 1 au cpt
						}
						if(($pourcent[$i]<25)&&($cpt>0)){  	//si le pourcentage est < 25 et cpt > 0
							$periode[$l][1]=$i-1; 		//met la valeur $i inferieure
							$cpt=0; 			//remet compteur a 0 pour pouvoir retourner dans la condition superieur
							$l++; 				//change de ligne
						}
					}
					if($cpt>0)
						$periode[$l][1]=36;
				}
			}
			return($periode);
		} catch (\Exception $e) {
			return false;
		}
	}

	const sql_filtre_periode = "SELECT id_espece,decade FROM stats_validation.periodes_especes WHERE id_espece=$1 AND decade=$2";

	function filtre_periode(){
		$this->time_start=microtime(true);
		$observation=$this->get_observation(); //recupere l'observation qui correnspond
		$decade = self::decade($observation->date_observation); //recupere le numero de la decade
		$selection=bobs_qm()->query($this->db, 'test_filtre_periode', self::sql_filtre_periode, array($this->id_espece, $decade));
		$resultat=self::fetch($selection);
		$this->time_end=microtime(true);
		$this->test=$this->time_end-$this->time_start;

		if(!empty($resultat)) //si la ligne avec l'id_espece et la decade existe
			return true;
		else
			return false;
	}


	function filtre_rarete_espece(){
		try{
			$this->time_start=microtime(true);
			$bobs_espece=$this->get_espece();
			$ref_national=$bobs_espece->get_referentiel_regional();
			if(empty($ref_national))
				return false;
			foreach($ref_national as $cle=>$case){
				if($cle=='categorie')
					$categorie=$ref_national[$cle];
				if($cle=='indice_rar')
					$indice_rar=$ref_national[$cle];
			}

			$this->time_end=microtime(true);
			$this->test=$this->time_end-$this->time_start;

			if( ( ($categorie=="VU") || ($categorie=="EN") || ($categorie=="CR") || ($categorie=="RE") )
				&&( ($indice_rar=="EX") || ($indice_rar=="TR") || ($indice_rar=="R") || ($indice_rar=="AR") || ($indice_rar=="PC") ) ){
				return $t=array($categorie,$indice_rar);
			}
			else{
				if( ($categorie=="VU") || ($categorie=="EN") || ($categorie=="CR") || ($categorie=="RE") )
					return $categorie;
				if( ($indice_rar=="EX") || ($indice_rar=="TR") || ($indice_rar=="R") || ($indice_rar=="AR") || ($indice_rar=="PC") )
					return $indice_rar;
			}
			return false;
		} catch (\Exception $e) {
			return false;
		}
	}


	function filtre_comportement(){
		try{
			$this->time_start=microtime(true);
			$espece=$this->get_espece(); //recupere le nom de l'espece
			$tag=$this->get_tags(); //recupere le tableau avec tout les tags (code comportement, indice du code, etc)
			if(empty($tag))
				return true;
			$id_tags=array();
			foreach($tag as $t){			//remplis un tableau avec que les codes comportements en nb
				foreach($t as $cle=>$valeur){
					if($cle=="id_tag"){
						if($valeur!=579)
							$id_tags[]=$valeur;
					}
				}
			}
			$nb_tags=count($id_tags); //compte le nombre de tags
			$cpt=0;
			for($i=0;$i<$nb_tags;$i++){
				$test=new bobs_tags($this->db,$id_tags[$i]);
				if ($test->test_association_espece($espece))
					$cpt++;
			}
			$this->time_end=microtime(true);
			$this->test=$this->time_end-$this->time_start;

			return ($cpt==$nb_tags);
		} catch (\Exception $e) {
			return false;
		}
	}


	function filtre_junior(){
		try{
			$this->time_start=microtime(true);
			$bobs_observation=$this->get_observation();
			$observateurs = $bobs_observation->get_observateurs();
			$j =0;
			foreach ($observateurs as $observateur) {
				$id_utilisateur=$observateur['id_utilisateur'];
				$bobs_utilisateur=new bobs_utilisateur($this->db, $id_utilisateur);
				if ($bobs_utilisateur->junior())
					$j++;
			}
			$nb=count($observateurs);
			$this->time_end=microtime(true);
			$this->test=$this->time_end-$this->time_start;

			if($j==$nb)
				return false;
			if(($j>1)&&($j<$nb))
				return ("il y a ".$j." utilisateurs juniors");
			if($j==0)
				return true;
		} catch (\Exception $e) {
			return false;
		}
	}


	function filtrer_citations_dans_selection(){
		$test3=$this->afficher_test_validation_complet();
		return $test3;
	}


	function afficher_test_validation(){
		$cpt=0;


		$rarete=$this->filtre_rarete_espece();
		if($rarete)
			$cpt++;
		if($cpt>0)
			return false;


		$comportement=$this->filtre_comportement();
		if(!$comportement)
			$cpt++;
		if($cpt>0)
			return false;


		$junior_or_not=$this->filtre_junior();
		if(!$junior_or_not)
			$cpt++;
		if($cpt>0)
			return false;


		$periode=$this->filtre_periode();
		if(!$periode)
			$cpt++;
		if($cpt>0)
			return false;


		$effectifs=$this->filtre_effectifs();
		if(!$effectifs)
			$cpt++;
		if($cpt>0)
			return false;


		$lieu=$this->filtre_lieu();
		if($lieu!="Espece citée recement dans les dix kilometres")
			$cpt++;
		if($cpt>0)
			return false;


		$documents=$this->filtre_documents();
		if($documents)
			$cpt++;
		if($cpt>0)
			return false;


		$indice=$this->filtre_indice();
		if(!$indice)
			$cpt++;
		if($cpt>0)
			return false;


		$chr=$this->filtre_chr();
		if($chr)
			$cpt++;
		if($cpt>0)
			return false;


		$autorisation=$this->filtre_reseau();
		if(!$autorisation)
			$cpt++;
		if($cpt>0)
			return false;

		if($cpt==0)
			return true;
	}


	function afficher_test_validation_complet(){
		$cpt=0;
		$tab=array();


		$lieu=$this->filtre_lieu();
		if($lieu!="Espece citée recement dans les dix kilometres")
			$tab[]="1)".$lieu;

		$rarete=$this->filtre_rarete_espece();
		if($rarete){
			if(is_array($rarete)){
				$tab[]="2)".$rarete[0];
				$tab[]="2)".$rarete[1];
			}
			else{
				$tab[]="2)".$rarete;
			}
		}

		$junior_or_not=$this->filtre_junior();
		if(substr($junior_or_not,0,6)=="il y a")
			$tab[]="3)".$junior_or_not;
		$junior_or_not?"3)Utilisateur connu":$tab[]="3)Utilisateur junior";


		$autorisation=$this->filtre_reseau();
		$autorisation?"4)Espece appartenant au domaine de competences de l'observateur":$tab[]="4)Espece n'appartenant pas au domaine de competences de l'observateur";


		$documents=$this->filtre_documents();
		$documents?$tab[]="5)Possede une piece jointe":"5)Pas de piece jointe";


		$periode=$this->filtre_periode();
		$periode?"6)Espece dans sa periode":$tab[]="6)Espece hors periode";


		$effectifs=$this->filtre_effectifs();
		$effectifs?"7)Effectif normal":$tab[]="7)Effectif anormal";


		$indice=$this->filtre_indice();
		$indice?"8)Indice egal a 4":$tab[]="8)Indice inferieur a 4";


		$chr=$this->filtre_chr();
		$chr?$tab[]="9)Espece avec CHR":"9)Pas de CHR";


		$comportement=$this->filtre_comportement();
		$comportement?"10)Code comportement correct":$tab[]="10)Code comportement erroné";

		return $tab;
	}


	/**
	 * @brief mise à jour des périodes d'observations
	 * @todo déplacer dans bobs_espece
	 */
	function ecriture_table_periodes($id_espece){
		$sql="SELECT id_espece
			FROM stats_validation.periodes_especes
			WHERE id_espece=$1";
		$selection=bobs_qm()->query($this->db, 'MAJ_tables_periodes', $sql, array($id_espece));
		$resultat=self::fetch($selection);
		if(empty($resultat)){	 //si l'espece n'est pas repertoriée
			if(file_exists("/home/thomas/baseobs/branches/picardie-nature/validation/scripts/valides/periodes/".$id_espece)){
				//----------------------------------------------fichier lecture------------------------------------------------------
				$filename="/home/thomas/baseobs/branches/picardie-nature/validation/scripts/valides/periodes/".$id_espece;
				$handle=fopen($filename,"r");
				$lignes=file($filename);
				$n=count($lignes);
				$ligne=$lignes[0];
				$periode=unserialize($ligne);
				fclose($handle);
				//----------------------------------------------fichier lecture------------------------------------------------------
				$i=0;
				$sql="INSERT INTO stats_validation.periodes_especes (id_espece,decade) VALUES ($1,$2)";
				if(!empty($periode)){
					foreach($periode as $periodes){
						if(!empty($periodes)){
							for($i=$periodes[0];$i<=$periodes[1];$i++){
								bobs_qm()->query($this->db, 'mettre_periodes_dans_db', $sql, array($id_espece, $i));
							}
						}
					}
				}
			}
			else{
				$t = $this->extraction_espece(); //remplis un tableau $t qui contient le nombre d'observations par decade par année
				$periode=$this->get_periode($t); //remplis un tableau 2D $periode qui possede les intervales où l'espece est presente

				$i=0;
				$sql="INSERT INTO stats_validation.periodes_especes (id_espece,decade) VALUES ($1,$2)";
				foreach($periode as $periodes){
					for($i=$periodes[0];$i<=$periodes[1];$i++){
						bobs_qm()->query($this->db, 'mettre_periodes_dans_db', $sql, array($id_espece, $i));								 		 	 }
				}
			}
		}
	}

	/**
	 * @todo déplacer dans bobs_utilisateur
	 */
	function ecriture_table_reseau($id_utilisateur) {
		$sql="SELECT id_utilisateur
			FROM stats_validation.utilisateurs
			WHERE id_utilisateur=$1";
		$selection=bobs_qm()->query($this->db, 'MAJ_tables_reseaux', $sql, array($id_utilisateur));
		$resultat=self::fetch($selection);
		if(empty($resultat)){
			$bobs_utilisateur = new bobs_utilisateur($this->db,$id_utilisateur);
			$reseaux=$bobs_utilisateur->get_reseaux();
			foreach($reseaux as $value){
				$nom=$value->__get('id');

				self::query($this->db, 'begin');

				$sql="INSERT INTO stats_validation.utilisateurs (id_utilisateur,reseau) VALUES ($1,$2)";
				bobs_qm()->query($this->db, 'mettre_reseau_dans_db', $sql, array($id_utilisateur,$nom));

				self::query($this->db, 'commit');
			}
		}

	}

	function mise_a_jour_des_tables_periodes_effectifs(){
		$classes=array('I','O','A','L','M','C','P','R','B','H','N');
		foreach($classes as $classe){
			$liste_par_classe=bobs_espece::get_liste_par_classe($this->db,$classe);
			foreach($liste_par_classe as $espece){
				self::query($this->db, 'begin');

				$id_espece=$espece->id_espece;

				$sql="DELETE id_espece
					FROM stats_validation.periodes
					WHERE id_espece=$1";
				$selection=bobs_qm()->query($this->db, 'delete_element_table_periodes', $sql, array($id_espece));

				$sql="DELETE id_espece
					FROM stats_validation.effectifs
					WHERE id_espece=$1";
				$selection=bobs_qm()->query($this->db, 'delete_element_table_effectifs', $sql, array($id_espece));

				$this->ecriture_table_periodes($id_espece);
				$this->ecriture_table_effectifs($id_espece);

				self::query($this->db, 'commit');
			}
		}
	}

	function mise_a_jour_table_utilisateur_reseau(){
		$utilisateurs=bobs_utilisateur::tous($this->db);  // renvoi un tableau de bobs_utilisateurs objets
		$id_utilisateurs=array();
		foreach($utilisateurs as $objet){
			$id=$objet->__get('id_utilisateur');
			$id_utilisateurs[]=$id;
		}
		foreach($id_utilisateurs as $ids){
			self::query($this->db, 'begin');

			$sql="DELETE id_utilisateur
				FROM stats_validation.utilisateurs
				WHERE id_utilisateur=$1";
			$selection=bobs_qm()->query($this->db, 'delete_element_table_utilisateurs', $sql, array($ids));

			$this->ecriture_table_reseau($ids);

			self::query($this->db, 'begin');
		}
	}
}
