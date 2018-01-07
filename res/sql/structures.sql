create table structures (
	id_structure serial primary key,
	nom varchar(200),
	type_mad varchar(100),
	data text,
	date_creation timestamp default now(),
	date_modif timestamp,
	date_execution timestamp
);

create table structures_membres (
	id_structure integer references structures(id_structure),
	id_utilisateur integer references utilisateur(id_utilisateur),
	primary key (id_structure,id_utilisateur)
);

create table structures_diff_restreintes (
	id_structure integer references structures(id_structure),
	id_utilisateur integer references utilisateur(id_utilisateur),
	primary key (id_structure,id_utilisateur)
);

create table structures_log_mad (
	id_structure integer references structures(id_structure),
	date_excecution timestamp default now(),
	temps_execution integer,
	n_citations_dispo integer,
	primary key (id_structure, date_execution)
);

create table structures_mad (
	id_structure integer references structures(id_structure),
	id_citation integer, -- pas de contrainte, ça ira + vite
	primary key (id_structure,id_citation)
);

alter table structures add txt_id varchar(30);
alter table structures add constraint structures_txt_id_uk unique (txt_id);
alter table structures add constraint structures_nom_uk unique (nom);
