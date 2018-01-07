create type t_espece_classe as enum ('A','B','I','M','O','P','R','L','N','C','H');
alter type t_espece_classe add value 'S';
alter type t_espece_classe add value 'G';
alter type t_espece_classe add value 'G';
alter type t_espece_classe add value 'E';
alter type t_espece_classe add value '_';

create table especes (
	id_espece serial,
	classe char(1),
	espece varchar(20),
	type_fiche integer,
	systematique integer,
	acces_sec boolean,
	verif_doublon boolean,
	pas_exportable boolean,
	exportable boolean,
	commentaire text,
	ordre varchar(100),
	famille varchar(100),
	nom_f varchar(200),
	nom_s varchar(100),
	nom_a varchar(100),
	superficie_max integer default 0,
	primary key (id_espece)
);

alter table especes alter column classe type t_espece_classe using classe::t_espece_classe;
alter table especes alter column classe set not null;
alter table especes add exclure_restitution boolean default false;
alter table especes add id_espece_parent integer references especes(id_espece);
alter table especes add nom_pic varchar(100);
alter table especes add absent_region boolean default false;
alter table especes add sinp_sensibilite_national integer not null default 0;
alter table especes add sinp_sensibilite_local integer not null default 0;
alter table especes add remarquable boolean default false;
alter table especes add expert boolean default false;

alter table especes add categorie_arbo boolean default false;

comment on column especes.absent_region is e'taxon absent de la région, mais conservé parce que présent sur des textes ou données anciennes';
comment on column especes.sinp_sensibilite_national is e'sensibilité national SINP entre 0 et 4 - 4 étant le plus confidentiel';
comment on column especes.sinp_sensibilite_national is e'sensibilité SINP en région entre 0 et 4 - 4 étant le plus confidentiel';
comment on column especes.remarquable is e'espèce remarquable dont on demandera une photos ou des précisions sur l\'observation';
comment on column especes.categorie_arbo is e'taxon utilisé comme point de départ pour lister les taxons classe/famille';

create table especes_similaires (
	id_espece_a integer references especes(id_espece),
	id_espece_b integer references especes(id_espece),
	primary key (id_espece_a, id_espece_b)
);

create table especes_index (
	id_espece integer,
	ordre integer,
	mot varchar(100)
);

create table especes_tags (
	id_espece integer references especes (id_espece),
	id_tag integer references tags (id_tag),
	v_text integer,
	v_int integer,
	primary key (id_espece, id_tag)
);

alter table especes add taxref_inpn_especes integer;
alter table especes add constraint taxref_unique unique (taxref_inpn_especes);
alter table especes add jour_debut_nidif integer;
alter table especes add jour_fin_nidif integer;
alter table especes add mois_debut_nidif integer;
alter table especes add mois_fin_nidif integer;
alter table especes add taxref_dreal integer;
alter table especes add constraint taxref_dreal_unique unique (taxref_dreal);
alter table especes add determinant_znieff boolean default false;
alter table especes add habitat text;
alter table especes add menace text;
alter table especes add invasif boolean default false;
alter table especes add id_chr integer references comite_homologation (id_chr);
alter table especes add niveaux_restitutions integer default 1|2|4;
alter table especes add action_conservation text;
alter table especes add commentaire_statut_menace text;
alter table especes add textes_valides boolean default false;
alter table especes add n_citations integer default 0;
alter table especes add id_espece_parent integer references especes (id_espece);
alter table especes add borne_a integer default null;
alter table especes add borne_b integer default null;

create index especes_ordre_idx on especes(ordre);
create index especes_ncitations_idx on especes(n_citations);
create index especes_classe_idx on especes(classe);

create table referentiel_regional (
	id_espece integer,
	id_referentiel integer,
	statut_origine varchar(34) default null,
	statut_bio varchar(21) default null,
	indice_rar varchar(2) default null,
	niveau_con varchar(24) default null,
	categorie char(2) default null,
	fiabilite varchar(20) default null,
	etat_conv varchar(11) default null,
	prio_conv_cat varchar(32) default null,
	prio_conv_fia varchar(11) default null
);

alter table referentiel_regional add constraint referentiel_regional_pk primary key (id_espece);
alter table referentiel_regional ADD constraint fk_id_espece foreign key (id_espece) references especes (id_espece);

/*
alter table referentiel_regional drop constraint chk_refp_statut_org;
alter table referentiel_regional drop constraint chk_refp_statut_bio;
alter table referentiel_regional drop constraint chk_refp_indice_rar;
alter table referentiel_regional drop constraint chk_refp_niv_c;
alter table referentiel_regional drop constraint chk_refp_cat;
alter table referentiel_regional drop constraint chk_refp_fia;
alter table referentiel_regional drop constraint chk_refp_ec;
alter table referentiel_regional drop constraint chk_refp_pcc;
alter table referentiel_regional drop constraint chk_refp_pcf;
*/

alter table referentiel_regional add constraint chk_refp_statut_org 
	check (statut_origine 
	in (
		'archéonaturalisé',
		'naturalisé',
		'naturalisé dangereux',
		'naturalisé dangereux non soutenu',
		'naturalisé dangereux soutenu',
		'naturalisé sans danger non soutenu',
		'naturalisé sans danger soutenu',
		'naturalisé soutenu',
		'sauvage',
		'sauvage soutenu',
		'sauvage réintroduit',
		null
	));

alter table referentiel_regional add constraint chk_refp_statut_bio
	check (statut_bio
	in (
		'erratique',
		'reproducteur',
		'visiteur',
		'données insuffisante',
		'inconnu',
		null
	));

alter table referentiel_regional add constraint chk_refp_indice_rar
	check (indice_rar
	in (
		'AC', 'AR', 'C', 'E', 'D',
		'PC', 'R', 'TC', 'TR', 'NA',
		null
	));

alter table referentiel_regional add constraint chk_refp_niv_c
	check (niveau_con 
	in (
		'indéterminable',
		'moyennement satisfaisant',
		'peu satisfaisant',
		'satisfaisant',
		null
	));


alter table referentiel_regional add constraint chk_refp_cat
	check (categorie 
	in (
		'CR', 'DD', 'EN', 'LC', 'NA',
		'NE', 'NT', 'RE', 'VU', null
	));

alter table referentiel_regional add constraint chk_refp_fia
	check (fiabilite in (
		'bonne', 'incertitude', 'moyenne', null
	));

alter table referentiel_regional add constraint chk_refp_ec
	check (etat_conv in (
		'défavorable',
		'favorable',
		'mauvais',
		null
	));

alter table referentiel_regional add constraint chk_refp_pcc
	check (prio_conv_cat in (
		'fortement prioritaire',
		'fortement prioritaire conservé',
		'non prioritaire',
		'prioritaire',
		'très fortement prioritaire',
		'moyennement prioritaire',
		'moyennement prioritaire conservé',
		null
	));

alter table referentiel_regional add constraint chk_refp_pcf
	check (prio_conv_fia 
	in (
		'bonne',
		'incertitude',
		'moyenne',
		null
	));

create table taxref_csnp_chiro (
    id_espece integer references especes(id_espece),
    id_csnp integer
);

alter table taxref_csnp_chiro
    add constraint id_csnp_pk primary key (id_csnp);

create table taxref_inpn_especes (
	regne varchar(100),
	phylum varchar(100),
	classe	varchar(100),
	ordre varchar(100),
	famille varchar(100),
	cd_nom integer primary key,
	lb_nom varchar(100),
	lb_auteur varchar(100),
	cd_ref integer,
	rang_es varchar(10),
	nom_vern varchar(200),
	nom_vern_eng varchar(200),
	fr char(1),
	mar char(1),
	gua char(1),
	smsb char(1),
	gf char(1),
	spm char(1),
	rev char(1),
	may char(1),
	taaf char(1)
);

create table taxref_inpn_especes_index (
	id_taxref_inpn_especes integer,
	ordre integer,
	mot varchar(100)
);

create table taxref_inpn_protections (
	cd_nom integer,
	code_protection varchar(10)
);

create table especes_documents (
	id_espece integer references especes(id_espece),
	document_ref char(13) not null,
	primary key (id_espece, document_ref)
);

alter table especes_documents add column date_creation timestamp default now();
alter table especes_documents add column en_attente boolean default true;
comment on column especes_documents.en_attente is e'document ne devant pas être affiché tant qu\'il n\'est pas validé';
comment on column especes_documents.date_creation is e'date d\'enregistrement de la photo';

create table listes_especes (
	id_liste_espece serial,
	id_utilisateur integer not null references utilisateur (id_utilisateur),
	nom varchar(100) not null check (length(nom)>0),
	ref boolean not null default false,
	date_creation date not null default now(),
	primary key (id_liste_espece)
);

create table listes_especes_data (
	id_liste_espece integer not null references listes_especes (id_liste_espece),
	id_espece integer not null references especes(id_espece),
	primary key (id_liste_espece, id_espece)
);

create table especes_commentaires (
	id_commentaire serial,
	id_espece integer references especes (id_espece),
	id_utilisateur integer references utilisateur (id_utilisateur),
	type_commentaire char(4) check (type_commentaire in ('info','attr')),
	date_commentaire timestamp,
	commentaire text,
	primary key (id_commentaire)
);

create table especes_stats_mois (
	id_espece integer references especes (id_espece),
	mois integer,
	n integer,
	primary key (id_espece,mois)
);

create table especes_stats_mois_tags (
	id_espece integer references especes (id_espece),
	id_tag integer references tags (id_tag),
	mois integer,
	n integer,
	primary key (id_espece,id_tag,mois)
);

