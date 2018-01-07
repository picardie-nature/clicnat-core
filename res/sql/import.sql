create table imports_lignes (
	id_import integer not null,
	num_ligne integer not null,
	colonne_a text,
	colonne_b text,
	colonne_c text,
	colonne_d text,
	colonne_e text,
	colonne_f text,
	colonne_g text,
	colonne_h text,
	colonne_i text,
	colonne_j text,
	colonne_k text,
	colonne_l text,
	colonne_m text,
	colonne_n text,
	colonne_o text,
	colonne_p text,
	colonne_q text,
	colonne_r text,
	colonne_s text,
	colonne_t text,
	colonne_u text,
	colonne_v text,
	colonne_w text,
	colonne_x text,
	colonne_y text,
	colonne_z text,
	colonne_aa text,
	colonne_ab text,
	colonne_ac text,
	colonne_ad text,
	colonne_ae text,
	colonne_af text,
	colonne_ag text,
	colonne_ah text,
	colonne_ai text,
	colonne_aj text,
	colonne_ak text
);
alter table imports_lignes add constraint imports_lignes_pk primary key (id_import, num_ligne);

create table imports_observations (
	id_import integer not null,
	num_ligne integer not null,
	like observations
);

alter table imports_observations add constraint imports_observations_pk primary key (id_import,num_ligne);
alter table imports_observations alter id_observation set default nextval('observations_id_observation_seq'::regclass);
alter table imports_observations add n_ligne integer;

create table imports_citations (
	id_import integer not null,
	num_ligne integer not null,
	like citations
);

create table imports (
	id_import serial,
	id_utilisateur integer,
	id_auteur integer,
	libelle varchar(100),
	date_import date
);

alter table imports add constraint imports_pk primary key (id_import);

create table imports_observations_observateurs (
	id_import integer not null,
	like observations_observateurs
);

create table imports_citations_tags (
	id_import integer not null,
	like citations_tags
);

alter table imports_observations add constraint imports_observations_imports_fk foreign key (id_import) references imports (id_import);
alter table imports_citations add constraint imports_citations_id_import_fk foreign key (id_import) references imports (id_import);
create unique index idx_imports_observations_id_observation on imports_observations (id_observation);
alter table imports_citations add constraint imports_citations_id_observation_fk foreign key (id_observation) references imports_observations (id_observation);
alter table imports_citations_tags add constraint imports_citations_tags_id_import foreign key (id_import) references imports (id_import);
alter table imports_lignes add constraint imports_lignes_id_import foreign key (id_import) references imports (id_import);
create unique index idx_imports_citations_id_citation on imports_citations (id_citation);
alter table imports_citations_tags add constraint imports_citations_id_citation_fk foreign key (id_citation) references imports_citations (id_citation);
alter table imports_observations_observateurs add constraint imports_citations_observateurs_id_import_fk foreign key (id_import) references imports (id_import);
alter table imports_observations_observateurs add constraint imports_citations_observateurs_id_obs_fk foreign key (id_observation) references imports_observations (id_observation);
alter table imports_observations_observateurs add constraint imports_citations_observateurs_id_utl_fk foreign key (id_utilisateur) references utilisateur (id_utilisateur);
alter table imports_citations_tags add constraint imports_citations_tags_id_tag_fk foreign key (id_tag) references tags (id_tag);
alter table imports add constraint imports_id_auteur_fk foreign key (id_auteur) references utilisateur (id_utilisateur);
alter table imports add constraint imports_id_utilisateur_fk foreign key (id_utilisateur) references utilisateur (id_utilisateur);
