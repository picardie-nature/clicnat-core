create table comite_homologation (
	id_chr serial ,
	nom varchar(200) not null,	
	primary key (id_chr)
);

comment on column comite_homologation.id_chr is e'identifiant du CHR';
comment on column comite_homologation.nom is e'nom du comité';

create table comite_homologation_membre (
	id_chr integer references comite_homologation (id_chr),
	id_utilisateur integer references utilisateur (id_utilisateur),
	primary key (id_chr, id_utilisateur)
);

comment on column comite_homologation_membre.id_chr is e'identifiant du CHR';
comment on column comite_homologation_membre.id_utilisateur is e'identifiant utilisateur';

