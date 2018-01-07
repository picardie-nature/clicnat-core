create type t_phoque_pelage as enum ('clair', 'foncé', 'waddenzee (pv)', 'scotish (pv)');
create type t_phoque_taches as enum ('0 à 30%','30 à 60%','60 à 100%');
create type t_phoque_photo_orient as enum('profil droit','face + corps droit','gauche + face ventrale','profil gauche','face + corps gauche','droit + face ventrale');
create type t_phoque_sexe as enum ('M','F');

create table phoques (
	id_phoque integer primary key,
	nom varchar(30) not null unique,
	numéro varchar(100) not null unique,
	id_espece integer references especes (id_espece),
	sexe t_phoque_sexe,
	bague_numero varchar(50),
	bague_couleur varchar(50),
	pelage t_phoque_pelage,
	taches t_phoque_taches
);

alter table phoques rename column numéro to numero;

alter table phoques add date_creation timestamp not null default now();
alter table phoques add date_modification timestamp;

alter table phoques add unique(nom);
alter table phoques add unique(numero);

create sequence phoques_id_phoque;
alter table phoques alter id_phoque set default nextval('phoques_id_phoque');
alter sequence phoques_id_phoque restart with 200;

create table phoques_photos (
	id_phoque integer references phoques (id_phoque),
	document_ref char(13),
	orientation t_phoque_photo_orient,
	primary key (document_ref)
);

alter table phoques_photos add date_creation timestamp not null default now();
alter table phoques_photos add date_modification timestamp;
