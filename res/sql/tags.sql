drop table if exists tags cascade;
drop table if exists citations_tags cascade;

create table tags (
	id_tag serial,
	parent_id integer,  
	lib varchar(100),
	"ref" char(4),
	a_chaine boolean not null default false,
	a_entier boolean not null default false,
	-- ne peut pas être utilisé si vrai (simple embranchement)
	categorie_simple boolean not null default false,
	affichage_saisie boolean default true,
	borne_a integer,
	borne_b integer,
	classes_esp_ok varchar(10) default 'ABILMOPRN',
	primary key (id_tag)
);

