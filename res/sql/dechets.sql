create table depotoirs (
	id_depotoir serial,
	auteur varchar(200),
	id_utilisateur integer references utilisateur(id_utilisateur),
	the_geom geometry not null,
	date_creation timestamp default now(),
	date_modif timestamp,
	sur_voie_public boolean,
	statut varchar(100),
	primary key (id_depotoir)
);

create table depotoirs_observations (
	id_observation serial,
	id_depotoir integer not null references depotoirs(id_depotoir),
	date_observation date,
	id_utilisateur integer references utilisateur(id_utilisateur),
	document_ref char(13)[],
	categories_dechets varchar(100)[],
	primary key (id_observation)
);

create table depotoirs_commentaires (
	id_commentaire serial,
	id_depotoir integer references depotoirs(id_depotoir),
	id_utilisateur integer references utilisateur(id_utilisateur),
	type_commentaire char(4),
	date_commentaire timestamp default now(),
	commentaire text,
	primary key (id_commentaire)
);

insert into geometry_columns (f_table_catalog,f_table_schema,f_table_name,f_geometry_column,coord_dimension,srid,"type") values ('','public','depotoirs', 'the_geom', 2, 4326, 'POINT');

alter table depotoirs_observations add commentaire text;
alter table depotoirs_observations add date_creation timestamp default now();
alter table depotoirs_observations add date_modif timestamp;
alter table depotoirs_observations drop column id_utilisateur;
alter table depotoirs drop column id_utilisateur;
alter table depotoirs_observations add auteur text;
