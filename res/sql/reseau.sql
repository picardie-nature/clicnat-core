create table reseau (
	id char(2) primary key,
	nom varchar(200),
	id_gdtc integer,
	restitution_nom_s boolean default false,
	restitution_nombre_jours integer default 10,
	restitution_auto boolean default false,
	date_creation timestamp default now(),
	date_modif timestamp null
);

create table reseau_coordinateurs (
	id_reseau char(2) not null references reseau(id),
	id_utilisateur integer not null references utilisateur(id_utilisateur),
	date_creation timestamp default now(),
	primary key (id_reseau, id_utilisateur)
);

create table reseau_membres (
	id_reseau char(2) not null references reseau(id),
	id_utilisateur integer not null references utilisateur(id_utilisateur),
	date_creation timestamp default now(),
	reception_liste_diffusion boolean default true,
	primary key (id_reseau, id_utilisateur)
);

create table reseau_validateurs (
	id_reseau char(2) not null references reseau(id),
	id_utilisateur integer not null references utilisateur(id_utilisateur),
	id_espece integer not null  references especes(id_espece),
	date_creation timestamp default now(),
	primary key (id_reseau, id_utilisateur, id_espece)
);

create table reseau_branches_especes (
	id_reseau char(2) not null references reseau(id),
	id_espece integer not null references especes(id_espece),
	date_creation timestamp default now(),
	primary key(id_reseau, id_espece)
);
