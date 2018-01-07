create table enquete_def (
	id_enquete serial,
	nom varchar(100),
	primary key (id_enquete)
);

create table enquete_espece (
	id_enquete integer references enquete_def (id_enquete),
	id_espece integer references especes (id_espece),
	primary key (id_enquete,id_espece)
);

create table enquete_def_version(
	id_enquete integer references enquete_def (id_enquete),
	version integer,
	definition xml,
	primary key (id_enquete, version)
);
