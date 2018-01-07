create table espace (
	id_espace serial,
	id_utilisateur integer,
	reference character varying(255),
	nom character varying(255)
);

comment on column espace.id_espace is e'numéro de l\'espace, commun au table espace_*';
comment on column espace.id_utilisateur is e'identifiant de l\'utilsateur qui a créé l\'objet';
comment on column espace.reference is e'référence original de l\'objet s\'il existe';
comment on column espace.nom is e'nom de l\'espace';

create table espace_line (
	the_geom public.geometry,
	constraint "$1" check ((public.st_srid(the_geom) = (4326))),
	constraint "$2" check (((public.st_geometrytype(the_geom) in ('LINESTRING','ST_LineString') OR (the_geom is null))))
) inherits (espace);

-- Littoral
create table espace_littoral (
	the_geom public.geometry,
	constraint "$1" check ((public.st_srid(the_geom) = (4326))),
	constraint "$2" check (((public.geometrytype(the_geom) = 'MULTIPOLYGON'::text) OR (the_geom is null))),
	primary key (id_espace)
) inherits (espace);

create table espace_toponyme (
	the_geom public.geometry,
	commune_id_espace integer,
	departement_id_espace integer,
	l93_10x10_id_espace integer,
	constraint "$1" check ((public.st_srid(the_geom) = (4326))),
	constraint "$2" check (((public.geometrytype(the_geom) = 'POINT'::text) OR (the_geom is null))),
	primary key (id_espace)
) inherits (espace);

alter table espace_toponyme add the_geom_rgf93 geometry;
alter table espace_toponyme add constraint esp_topo_srid_rgf93 check (st_srid(the_geom_rgf93)=2154);
alter table espace_toponyme add constraint esp_topo_type_rgf93 check (geometrytype(the_geom_rgf93)='POINT' or the_geom_rgf93 is null);
-- update espace_toponyme set the_geom_rgf93=st_setsrid(transform(the_geom,2154),2154);

create table espace_point (
	the_geom public.geometry,
	commune_id_espace integer,
	departement_id_espace integer,
	l93_10x10_id_espace integer,
	littoral_id_espace integer references espace_littoral (id_espace),
	toponyme_id_espace integer references espace_toponyme (id_espace),
	constraint "$1" check ((public.st_srid(the_geom) = (4326))),
	constraint "$2" check (((public.geometrytype(the_geom) = 'POINT'::text) OR (the_geom is null)))
) inherits (espace);

insert into geometry_columns (f_table_catalog,f_table_schema,f_table_name,f_geometry_column,coord_dimension,srid,"type") values ('','public','espace_toponyme', 'the_geom', 2, 4326, 'POINT');
create index espace_point_ref on espace_point (reference);

create table espace_polygon (
	the_geom public.geometry,
	superficie bigint,
	constraint "$1" check ((public.st_srid(the_geom) = (4326))),
	constraint "$2" check (((public.geometrytype(the_geom) = 'MULTIPOLYGON'::text) OR (geometrytype(the_geom) = 'POLYGON'::text) OR (the_geom is null)))
) inherits (espace);

create index espace_poly_ref on espace_polygon (reference);

create table espace_commune (
	the_geom public.geometry,
	code_insee_txt varchar(5),
	nombre_espece integer default 0,
	constraint commune_geom_type check (((public.geometrytype(the_geom) = 'MULTIPOLYGON'::text) OR (the_geom is null))),
	constraint commune_srid1 check ((public.st_srid(the_geom) = (4326))),
	primary key (id_espace)
) inherits (espace);

create table espace_departement (
	the_geom public.geometry,
	constraint dept_geom_type check (((public.geometrytype(the_geom) = 'MULTIPOLYGON'::text) OR (the_geom is null))),
	constraint dept_srid1 check ((public.st_srid(the_geom) = (4326))),
	primary key (id_espace)
) inherits (espace);

create table espace_grid (
	the_geom public.geometry,
	constraint grid_geom_type check (((public.geometrytype(the_geom) = 'MULTIPOLYGON'::text) OR (the_geom is null))),
	constraint "$1" check ((public.st_srid(the_geom) = (4326)))
) inherits (espace);

create table espace_epci (
	the_geom public.geometry,
	constraint epci_geom_type check (((public.geometrytype(the_geom) = 'MULTIPOLYGON'::text) OR (the_geom is null))),
	constraint "$1" check ((public.st_srid(the_geom) = (4326)))
) inherits (espace);

create table espace_route (
	the_geom public.geometry,
	constraint route_geom_type check (((public.geometrytype(the_geom) = 'LINESTRING'::text) OR (the_geom is null))),
	constraint "$1" check ((public.st_srid(the_geom) = (4326)))
) inherits (espace);

create table espace_chiro (
	the_geom public.geometry,
	commune_id_espace integer,
	departement_id_espace integer,
	constraint "$1" check ((public.st_srid(the_geom) = (4326))),
	constraint "$2" check (((public.geometrytype(the_geom) = 'POINT'::text) OR (the_geom is null)))
) inherits (espace);

alter table espace_chiro add constraint espace_chiro_commune_id_espace_fk  foreign key (commune_id_espace) references espace_commune(id_espace);
alter table espace_chiro add constraint espace_chiro_departement_id_espace_fk foreign key (departement_id_espace) references espace_departement(id_espace);

create unique index espace_chiro_ref_u on espace_chiro (reference);

alter table espace_chiro add date_modif timestamp;
create sequence espace_chiro_seq;

create table espace_corine (
	the_geom public.geometry,
	constraint grid_geom_type check (((public.geometrytype(the_geom) = 'MULTIPOLYGON'::text) OR (the_geom is null))),
	constraint "$1" check ((public.st_srid(the_geom) = (4326)))
) inherits (espace);

create table espace_structure (
	the_geom public.geometry,
	structure varchar(20),
	constraint grid_geom_type check (((public.geometrytype(the_geom) = 'POLYGON'::text) OR (public.geometrytype(the_geom) = 'MULTIPOLYGON'::text) OR (the_geom is null))),
	constraint "$1" check ((public.st_srid(the_geom) = (4326)))
) inherits (espace);

-- Carrés Atlas
create table espace_l93_10x10 (
	the_geom public.geometry,
	l93_10x10_id_espace integer,
	constraint "$1" check ((public.st_srid(the_geom) = (4326))),
	constraint "$2" check (((public.geometrytype(the_geom) = 'MULTIPOLYGON'::text) OR (the_geom is null))),
	primary key (id_espace)
) inherits (espace);

create table espace_l93_5x5 (
	the_geom public.geometry,
	constraint "$1" check ((public.st_srid(the_geom) = (4326))),
	constraint "$2" check (((public.geometrytype(the_geom) = 'MULTIPOLYGON'::text) OR (the_geom is null))),
	primary key (id_espace)
) inherits (espace);

alter table espace_chiro add l93_10x10_id_espace integer references espace_l93_10x10(id_espace);

drop table if exists utilisateur_espace_l93_10x10;

create table utilisateur_espace_l93_10x10 (
	id_utilisateur integer,
	id_espace integer
);

alter table utilisateur_espace_l93_10x10 add constraint utilisateur_espace_l93_pk primary key (id_utilisateur, id_espace);
alter table utilisateur_espace_l93_10x10 add constraint utilisateur_espace_l93_fk1 foreign key (id_utilisateur) references utilisateur (id_utilisateur);
alter table utilisateur_espace_l93_10x10 add constraint utilisateur_espace_l93_fk2 foreign key (id_espace) references espace_l93_10x10 (id_espace);
alter table utilisateur_espace_l93_10x10 add decideur_aonfm boolean default false;

-- clés primaires
alter table espace add constraint espace_pk primary key (id_espace);
alter table espace_line add constraint espace_line_pk primary key (id_espace);
alter table espace_point add constraint espace_point_pk primary key (id_espace);
alter table espace_polygon add constraint espace_polygon_pk primary key (id_espace);
alter table espace_grid add constraint espace_grid_pk primary key (id_espace);
alter table espace_route add constraint espace_route_pk primary key (id_espace);
alter table espace_chiro add constraint espace_chiro_pk primary key (id_espace);
alter table espace_epci add constraint espace_epci_pk primary key (id_espace);
alter table espace_structure add constraint espace_struct_pk primary key (id_espace);

create view v_commune_l93_10x10 as
    select ec.id_espace,ec.nom as commune,el.nom as carre
    from espace_commune ec,espace_l93_10x10 el
    where st_intersects(ec.the_geom, el.the_geom);

select * into s_commune_l93_10x10 from v_commune_l93_10x10;

alter table espace_commune add code_insee integer;
alter table espace_commune add nom2 varchar(200);
alter table espace_commune add dept integer;

create table espace_commentaires (
	id_commentaire serial,
	id_espace integer not null,
	id_utilisateur integer references utilisateur (id_utilisateur),
	type_commentaire char(4) check (type_commentaire in ('info','attr')),
	date_commentaire timestamp,
	commentaire text,
	primary key (id_commentaire)
);

create index espace_commentaires_idx on espace_commentaires (id_espace);

create table listes_espaces (
	id_liste_espace serial,
	id_utilisateur integer references utilisateur (id_utilisateur),
	nom varchar(100),
	ref boolean,
	date_creation date not null default now(),
	date_maj timestamp,
	mention text,
	primary key (id_liste_espace)
);

alter table listes_espaces add column attributs_defs text default null;

create table listes_espaces_data (
	id_liste_espace integer references listes_espaces (id_liste_espace),
	id_espace integer not null,
	espace_table varchar(100),
	primary key (id_liste_espace,id_espace)
);

alter table listes_espaces_data add column attributs text default null;

create table espace_intersect (
	table_espace_ref varchar(255),
	id_espace_ref integer,
	table_espace_obs varchar(255),
	id_espace_obs integer,
	primary key (table_espace_ref,id_espace_ref,table_espace_obs,id_espace_obs)
);

create table espace_index_atlas (
	table_espace varchar(255),
	id_espace integer,
	srid integer,
	pas integer,
	x0 integer,
	y0 integer,
	primary key (table_espace, id_espace, srid, pas, x0, y0)
);

create table pays_statistique (
	id_pays serial,
	nom varchar(255) not null,
	primary key (id_pays)
);

alter table espace_commune add id_pays integer references pays_statistique(id_pays);
