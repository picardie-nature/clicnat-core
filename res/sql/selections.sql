create table selection (
	id_selection serial,
	id_utilisateur integer not null,
	nom_selection varchar(250) not null,
	date_creation date
);

alter table selection add partage_qg boolean default false;
alter table selection add constraint selection_pk primary key (id_selection);
alter table selection add constraint selection_fk foreign key (id_utilisateur) references utilisateur (id_utilisateur);
alter table selection add extraction_xml text;
alter table selection add date_modif timestamp;

create table selection_data (
	id_selection integer not null,
	id_citation integer not null
);

alter table selection_data add constraint selection_data_pk  primary key (id_selection,id_citation);
alter table selection_data add constraint selection_data_fk1 foreign key (id_selection) references selection (id_selection);
alter table selection_data add constraint selection_data_fk2 foreign key (id_citation) references citations (id_citation);

create table selection_mix_annees (
	id_sma serial,
	id_selection integer references selection (id_selection),
	x0 integer not null,
	y0 integer not null,
	pas integer not null,
	srid integer not null,
	annee integer not null,
	id_espece integer references especes (id_espece),
	the_geom geometry,
	primary key (id_selection,id_espece,x0,y0,pas,srid,annee),
	constraint smix_geom_type check (((public.geometrytype(the_geom) = 'POLYGON'::text) OR (the_geom is null)))
);

create unique index idx_sma_unique on selection_mix_annees (id_sma);
--create view selection_mix_annees_2154 as select * from selection_mix_annees where srid=2154;

-- insert into geometry_columns (f_table_catalog,f_table_schema,f_table_name,f_geometry_column,coord_dimension,srid,"type")
	 values ('','public','selection_mix_annees_2154', 'the_geom', 2, 2154, 'MULTIPOLYGON');
