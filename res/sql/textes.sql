-- ce sont des morceaux de clicnat dont le texte est modifiable

create table textes (
	id_texte serial primary key,
	nom varchar(200) not null unique,
	texte text,
	date_modif timestamp
);
