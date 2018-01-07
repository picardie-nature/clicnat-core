create table travaux (
	id_travail serial primary key,
	id_travail_categorie integer,
	titre varchar(200),
	date_creation timestamp,
	date_modif timestamp,
	description text,
	"type" varchar(100),
	data text
);
