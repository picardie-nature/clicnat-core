-- mise a jour des tables espace_intersect et espace_index_atlas
select count(*) from (select bob_index_espace('espace_point', id_espace, the_geom) from espace_point) as s;
select count(*) from (select bob_index_espace('espace_line', id_espace, the_geom) from espace_line) as s;
select count(*) from (select bob_index_espace('espace_polygon', id_espace, the_geom) from espace_polygon) as s;
select count(*) from (select bob_index_espace('espace_chiro', id_espace, the_geom) from espace_chiro) as s;
