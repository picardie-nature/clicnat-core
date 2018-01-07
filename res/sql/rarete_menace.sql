create table listes_rarete_menace (
	id_liste_rarete_menace serial,
	nom varchar(200),
	annee_publi integer,
	primary key (id_liste_rarete_menace)
);

create table listes_rarete_menace_data (
	id_liste_rarete_menace integer references listes_rarete_menace (id_liste_rarete_menace),
	id_espece integer references especes (id_espece),
	rarete varchar(2),
	menace varchar(2),
	primary key (id_liste_rarete_menace, id_espece)
);

/*
-- recupération de l'ancienne liste
insert into listes_rarete_menace (nom,annee_publi) values ('Référentiel 2009', '2009');
insert into listes_rarete_menace_data (id_espece,rarete,menace,id_liste_rarete_menace) select id_espece ,indice_rar,categorie,1 from referentiel_regional;
	

*/