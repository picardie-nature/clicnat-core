drop table if exists observations cascade;

create table observations (
	id_observation serial,
	id_utilisateur integer references utilisateur (id_utilisateur),
	date_observation date,
	precision_date integer,
	id_espace integer,
	date_modif timestamp,
	espace_table varchar(100),
	heure_observation time,
	duree_observation interval,
	date_creation date,
	brouillard boolean default true,
	primary key (id_observation)
);

alter table observations add constraint chk_obs_date check (date_observation <= now());
alter table observations add date_deb date default null;
alter table observations add date_fin date default null;
alter table observations add heure_debut time default null;
alter table observations add heure_fin time default null;

comment on column observations.date_deb is e'date du jour de l\'observation en cas d\'imprécision représente la date la plus ancienne (voir date_fin)';
comment on column observations.date_fin is e'date du jour de l\'observation en cas d\'imprécision représente la date la plus récente (voir date_deb)';
comment on column observations.date_creation is e'date de création de l\'enregistrement dans la base';
comment on column observations.date_modif is e'date de dernière modification de l\'enregistrement dans la base';
comment on column observations.heure_debut is e'heure dans le système local auxquelles l\'observation a débutée';
comment on column observations.heure_fin is e'heure dans le système local auxquelles l\'observation a pris fin';
comment on column observations.id_observation is e'numéro de l\'observation';
comment on column observations.id_espace is e'numéro de la géométrie';
comment on column observations.espace_table is e'table où est stockée la géométrie';
comment on column observations.brouillard is e'observation en cours de saisie si vrai';
comment on column observations.duree_observation is e'durée de l\'observation';
comment on column observations.id_utilisateur is e'numéro de l\'utilisateur qui a entré la donnée dans clicnat (pas nécessairement l\'auteur)';

comment on column observations.date_observation is e'ANCIEN CHAMP date de l\'observation';
comment on column observations.precision_date is e'ANCIEN CHAMP précision en jour de date_observation';
comment on column observations.heure_observation is e'ANCIEN CHAMP heure de début de l\'observation';


create index idx_obs_id_espace on observations (id_espace);

drop table if exists citations cascade;

create table citations (
	id_citation serial,
	id_observation integer,
	id_espece integer,
	sexe char(3),
	age char(3),
	nb integer,
	nb_min integer,
	nb_max integer,
	precision_nb integer,
	qualite char(1),
	commentaire text,
	origine_statut_repro integer,
	statut_repro integer,
	statut_doublon integer,
	ref_import varchar(100),
	distance_contact varchar(10),
	determinateur varchar(100),
	num_groupe varchar(100),
	espece_confirme_par_obs integer,
	tmp_classe char(1),
	tmp_espece varchar(20),
	date_modif timestamp
);

alter table citations add constraint citation_pk primary key (id_citation);
alter table citations add constraint id_cit_fk_esp foreign key (id_espece) references especes(id_espece);
alter table citations add constraint id_cit_fk_obs foreign key (id_observation) references observations(id_observation);
alter table citations alter column id_espece set not null;
alter table citations alter column id_observation set not null;
alter table citations add enquete_resultat xml default null;

alter table citations add indice_qualite integer default null check (indice_qualite <= 4);
--- postgresql-contrib doit être installé
create extension "uuid-ossp";
alter table citations add guid uuid not null default uuid_generate_v1();

create index idx_cit_id_obs on citations (id_observation);
create index idx_cit_id_esp on citations (id_espece);

alter table citations add validation_avis_positif integer[];
alter table citations add validation_avis_negatif integer[];
alter table citations add validation_sans_avis integer[];
comment on column citations.validation_avis_positif is e'liste des utilisateurs ayant donné un avis positif pour la validation';
comment on column citations.validation_avis_negatif is e'liste des utilisateurs ayant donné un avis négatif pour la validation';
comment on column citations.validation_sans_avis is e'liste des utilisateurs ne souhaitant pas donner un avis sur cette citation';

-- table permettant de limiter l'acces aux donnees
drop table if exists utilisateur_citations_ok cascade;
create table utilisateur_citations_ok (
	id_utilisateur integer,
	id_citation integer
);
alter table utilisateur_citations_ok add constraint utilisateur_citations_pk primary key (id_utilisateur, id_citation);
alter table utilisateur_citations_ok add constraint utilisateur_citations_ok_fk1 foreign key (id_utilisateur) references utilisateur (id_utilisateur) deferrable initially deferred;
alter table utilisateur_citations_ok add constraint utilisateur_citations_ok_fk2 foreign key (id_citation) references citations (id_citation) deferrable initially deferred;;


-- FIXME des valeurs vident empêche la mise en place
-- alter table tags add constraint tags_uk unique ("ref");

create table citations_tags (
	id_citation integer,
	id_tag integer,
	v_text varchar(300),
	v_int integer
);

alter table citations_tags add constraint citations_tags_pk primary key (id_citation, id_tag);
alter table citations_tags add constraint citations_tags_fk1 foreign key (id_citation) references citations (id_citation);
alter table citations_tags add constraint citations_tags_fk2 foreign key (id_tag) references tags (id_tag);

create table citations_commentaires (
	id_commentaire serial,
	id_citation integer references citations (id_citation),
	id_utilisateur integer references utilisateur (id_utilisateur),
	type_commentaire char(4) check (type_commentaire in ('info','attr')),
	date_commentaire timestamp,
	commentaire text,
	primary key (id_commentaire)
);

create table observations_commentaires (
	id_commentaire serial,
	id_observation integer references observations (id_observation),
	id_utilisateur integer references utilisateur (id_utilisateur),
	type_commentaire char(4) check (type_commentaire in ('info','attr')),
	date_commentaire timestamp,
	commentaire text,
	primary key (id_commentaire)
);

create table observations_observateurs (
	id_observation integer,
	id_utilisateur integer
);

alter table observations_observateurs add constraint observations_observateurs_pk primary key (id_observation, id_utilisateur);
alter table observations_observateurs add constraint observations_observateurs_fk1 foreign key (id_observation) references observations (id_observation);
alter table observations_observateurs add constraint observations_observateurs_fk2 foreign key (id_utilisateur) references utilisateur (id_utilisateur);

create table espace_tags (
    id_espace integer not null,
    espace_table varchar(100) not null,
    id_tag integer not null references tags(id_tag),
    v_text varchar(200),
    v_int integer
);

alter table espace_tags alter v_text type text;

create table observations_tags (
    id_observation integer not null,
    id_tag integer not null references tags(id_tag),
    v_text varchar(200),
    v_int integer
);

alter table observations_tags add constraint observations_tags_pk primary key (id_observation,id_tag);

create table citations_documents (
	id_citation integer references citations (id_citation),
	document_ref char(13) not null,
	primary key (id_citation, document_ref)
);

create view creations_citations_par_date as 
	select date_creation::date,count(*) 
	from citations,observations 
	where observations.id_observation=citations.id_observation 
	and date_creation < now()::date - interval '1 day' 
	group by date_creation::date  
	order by date_creation desc;

create view nombre_citations_par_date as
	select date_observation::date,count(*) 
	from citations,observations 
	where observations.id_observation=citations.id_observation 
	and date_observation < now()::date - interval '1 day' 
	group by date_observation::date  
	order by date_observation desc;


