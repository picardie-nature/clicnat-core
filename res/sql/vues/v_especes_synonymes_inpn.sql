drop view if exists v_especes_synonymes_inpn;
create view v_especes_synonymes_inpn as select distinct id_espece,trim(regexp_split_to_table(tax2.nom_vern,E'\\,+')) as nom_vern
from taxref_inpn_especes tax,especes,taxref_inpn_especes tax2
where tax.cd_nom=especes.taxref_inpn_especes
and tax2.cd_ref=tax.cd_ref
and length(tax2.nom_vern)>0;


drop view if exists v_especes_synonymes_sc_inpn;
create view v_especes_synonymes_sc_inpn as
select distinct id_espece,tax2.lb_nom as nom_sc
from taxref_inpn_especes tax,especes,taxref_inpn_especes tax2
where tax.cd_nom=especes.taxref_inpn_especes
and tax2.cd_ref=tax.cd_ref;
