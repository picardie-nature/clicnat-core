drop table if exists utilisateur cascade;

create type t_utilisateur_loc_visible as enum ('restreint', 'reseau', 'tous');

create table utilisateur (
	id_utilisateur serial not null,
	nom character varying(150) not null,
	prenom character varying(150),
	username character varying(64),
	"password" char(64),
	tel character varying(30),
	port character varying(30),
	fax character varying(30),
	mail character varying(100),
	url character varying(100),
	commentaires text,
	associations integer[],
--	virtuel boolean default true,
--	id_csnp integer default null, --id de l'utilisateur dans la base conservatoire
	acces_qg boolean not null default false,
	acces_poste boolean not null default false,
	acces_chiros boolean not null default false,
	reglement_date_sig date default null,
	last_login timestamp default null,
	last_ip varchar(15) default null,
	the_geom geometry,
	localisation_visible t_utilisateur_loc_visible not null default ('restreint')
);
alter table utilisateur drop associations;
alter table utilisateur add pseudo varchar(300);
alter table utilisateur add partage_opts text;
alter table utilisateur add expert boolean default false;
insert into geometry_columns (f_table_catalog,f_table_schema,f_table_name,f_geometry_column,coord_dimension,srid,"type") values ('','public','utilisateur', 'the_geom', 2, 4326, 'POINT');

alter table utilisateur add constraint utilisateur_srid check (st_srid(the_geom)=4326);
alter table utilisateur add constraint utilisateur_type_geom check (geometrytype(the_geom)='POINT' or the_geom is null);

alter table utilisateur add constraint utilisateur_pk primary key (id_utilisateur);
alter table utilisateur add id_gdtc integer;
alter table utilisateur add diffusion_restreinte boolean default false;
alter table utilisateur add constraint utilisateur_uname_uk unique (username);
alter table utilisateur add ticket_mot_de_passe char(40) default null;
alter table utilisateur add date_creation timestamp default now();
alter table utilisateur add date_naissance date;
insert into utilisateur (id_utilisateur,nom,prenom) values (0,'sys','sys');
alter table utilisateur add props hstore;

drop table if exists utilisateur_repertoire;
create table utilisateur_repertoire (
	id_utilisateur integer references utilisateur(id_utilisateur),
	table_espace varchar(60) not null,
	id_espace integer not null,
	date_association timestamp default now(),
	primary key (id_utilisateur,table_espace,id_espace)
);

create table utilisateur_inbox (
	id_utilisateur integer references utilisateur(id_utilisateur),
	doc_id char(13) not null,
	date_creation timestamp default now(),
	primary key (id_utilisateur,doc_id)
);

insert into utilisateur (nom,username,"password",acces_qg) values ('Compte administrateur', 'admin', 'admin', true);

drop table if exists referentiel_utilisateur_tiers;

create table referentiel_utilisateur_tiers (
	tiers varchar(30),
	id_utilisateur integer references utilisateur(id_utilisateur),
	id_tiers integer,
	primary key (tiers,id_utilisateur,id_tiers)
);

create table utilisateur_extractions (
	id_extraction serial,
	id_utilisateur integer references utilisateur(id_utilisateur),
	xml text,
	pour_mad boolean not null default false,
	primary key (id_extraction)
);
alter table utilisateur add column id_extraction_utilisateur_flux integer references utilisateur_extractions(id_extraction);
