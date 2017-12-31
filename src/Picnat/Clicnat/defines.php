<?php
namespace Picnat\Clicnat;
$context = 'general';
define('DB_RESOURCE_TYPE', 'pgsql link');

define('DATE_M', 'mois');
define('DATE_J', 'jour');
define('DATE_A', 'annee');

define('BOBS_INSERT_QUERY_SUFFIX', '_insert_b');
define('BOBS_NEXTVAL_QUERY_SUFFIX', '_nextval_b');
define('BOBS_SELECT_QUERY_SUFFIX', '_select_b');
define('BOBS_LIBDIR', '/var/lib/bobs/');
define('BOBS_CLI_NO_EX', false);
define('BOBS_LOG_FILE',  '/var/log/bobs.log');
define('BOBS_ERR_NOTFOUND', 404);

if (!defined('CLICNAT_MAIL_EXPEDITEUR')) {
	define('CLICNAT_MAIL_EXPEDITEUR', 'ne.pas.repondre@clicnat.fr');
}

define('NICHEUR_CERTAIN', 3);
define('NICHEUR_PROBABLE', 2);
define('NICHEUR_POSSIBLE', 1);
define('PAS_NICHEUR', 0);

define('BOBS_TAGS_NIDIF', '120,121,122,123');
define('TAG_PROTOCOLE', 'ETUD');
define('TAG_STRUCTURE', 'STRU');

if (!defined('WFS_SERVICE_PROVIDER')) define('WFS_SERVICE_PROVIDER', 'Clicnat - Picardie Nature');
if (!defined('WFS_CONTACT_NAME')) define('WFS_CONTACT_NAME', 'Responsable SIG');
if (!defined('WFS_CONTACT_MAIL')) define('WFS_CONTACT_MAIL', 'md@picardie-nature.org');
if (!defined('WFS_URL_BASE')) define('WFS_URL_BASE', 'http://hyla.picardie-nature.org/~nicolas/public2/?page=wfs');

if (!defined('BOBS_PREINSCRIPTION_PATH')) define('BOBS_PREINSCRIPTION_PATH', '/var/cache/bobs/preinscriptions/');
if (!defined('BOBS_JUNIOR_PATH')) define('BOBS_JUNIOR_PATH', '/var/cache/bobs/juniors/');
if (!defined('BOBS_JUNIOR_TAG')) 	define('BOBS_JUNIOR_TAG', 592);

define('T_ETAT_PARCOURS', 0);
define('T_ETAT_VAR', 1);

define('BOBS_TBL_TAG_CITATION', 'citations_tags');
define('BOBS_TBL_TAG_ESPACE', 'espace_tags');
define('BOBS_TBL_TAG_OBSERVATION','observations_tags');
define('BOBS_TBL_TAG_ESPECE','especes_tags');

define('GML_NS_URL', 'http://www.opengis.net/gml/3.2');
define('SINP_NS_URL', 'http://inpn.mnhn.fr/sinp/');

if (!defined('SINP_PLATEFORME_URL'))
	define('SINP_PLATEFORME_URL', 'http://obs.picardie-nature.org/occtax/');

if (!defined('SINP_IDCNP'))
	define('SINP_IDCNP', '1296');

if (!defined('SINP_GESTIONNAIRE'))
	define('SINP_GESTIONNAIRE', 'Picardie Nature');

define('BOBS_BIN_EXTRACT_SELECTION', '/usr/local/bin/extract_selection');
define('BOBS_EXTRACTSHP_TMP', '/tmp/bobs-sel-%d');
define('BOBS_EXTRACT_SHP_1KM',	'atlas');
define('BOBS_EXTRACT_SHP_NORMAL', 'normal');
define('BOBS_EXTRACT_SHP_MIX', 'mix');
define('BOBS_EXTRACT_SHP_NCHIRO', 'nchiro');


define('EPSG_WGS84', 4326);
define('EPSG_RGF93', 2154);
define('BM_LAYER_PT', 'bm_layer_pt_int');
define('DEFAULT_MAPFILE', '/carto/l93.map');
define('MAPSTORE', '/mapstore');
define('MAPSTORE_BACKGROUNDS', MAPSTORE.'/backgrounds');
define('COORD_REGION_AX', 1.51);
define('COORD_REGION_AY', 50.5679);
define('COORD_REGION_BX', 4,24621);
define('COORD_REGION_BY', 48.7815);
define('FONT_ARIAL', '/usr/share/fonts/truetype/msttcorefonts/arial.ttf');
define('ATLAS_SHAPE_PATH', '/atlas/%d/atlas.shp');

if (!defined('DOCS_BASE_DIR'))
	define('DOCS_BASE_DIR', '/docs');

if (!defined('MONGO_DB_STR')) define('MONGO_DB_STR', 'mongodb://localhost:27017');
if (!defined('MONGO_BASE')) define('MONGO_BASE', 'clicnat');

if (!defined('CLICNAT_HIRONDELLE_TAG'))
	define('CLICNAT_HIRONDELLE_TAG', 629);

if (!defined('CLICNAT_HIRONDELLE_ID_TAG_OCCUPANT'))
	define('CLICNAT_HIRONDELLE_ID_TAG_OCCUPANT', 630);

if (!defined('CLICNAT_HIRONDELLE_ID_TAG_PUBLIQUE'))
	define('CLICNAT_HIRONDELLE_ID_TAG_PUBLIQUE', 631);


if (!defined('CLICNAT_HIRONDELLE_ID_ESPECE_RUSTIQUE'))
	define('CLICNAT_HIRONDELLE_ID_ESPECE_RUSTIQUE', 725);
if (!defined('CLICNAT_HIRONDELLE_ID_ESPECE_FENETRE'))
	define('CLICNAT_HIRONDELLE_ID_ESPECE_FENETRE', 387);
if (!defined('CLICNAT_HIRONDELLE_ID_ESPECE_RIVIERE'))
	define('CLICNAT_HIRONDELLE_ID_ESPECE_RIVIERE', 815);

define('SRID_BY_DEFAULT', 4326);

if (!defined('BIN_HISTOGRAMME_MOIS'))
	define('BIN_HISTOGRAMME_MOIS', '/usr/local/bin/histogramme_mois');

define('IMPORT_MAX_COL', 37);
define('IMPORT_UPDIR', '/tmp');

define('IMPORT_SESSION_N', 'imp_h');

define('IMPORT_COL_OBS_OBSERV', 1);
define('IMPORT_COL_OBS_LIEU', 2);
define('IMPORT_COL_CIT_ORDRE', 3);
define('IMPORT_COL_CIT_ESPECE', 4);
define('IMPORT_COL_CIT_EFFECTIF', 5);
//define('IMPORT_COL_CIT_EFFECTIF2', 6);
define('IMPORT_COL_OBS_DATE', 7);
define('IMPORT_COL_IGNORER', 8);
define('IMPORT_COL_TEMPERATURE', 9);
define('IMPORT_COL_CODE_FNAT', 10);
define('IMPORT_COL_GENRE', 11);
define('IMPORT_COL_AGE', 12);
define('IMPORT_COL_COMMENTAIRE', 13);
define('IMPORT_COL_HEURE', 14);
define('IMPORT_COL_DUREE', 15);
define('IMPORT_COL_LATITUDE_DMS', 16);
define('IMPORT_COL_LONGITUDE_DMS', 17);
define('IMPORT_COL_LATITUDE_D', 18);
define('IMPORT_COL_LONGITUDE_D', 19);
define('IMPORT_COL_CD_NOM', 20);
define('IMPORT_COL_INDICE_FIA', 21);
define('IMPORT_COL_PERIODE_DATE_A', 22);
define('IMPORT_COL_PERIODE_DATE_B', 23);
define('IMPORT_COL_WKT', 24);

if (!defined('BIN_LISTE_ESPACES_SHP_ENGLOBANT'))
	define('BIN_LISTE_ESPACES_SHP_ENGLOBANT','/usr/local/bin/shp_listes_espaces_englobant');

// a garder tant qu'on utilise le double système de date
define('MAINT_ANCIENNE_DATE',1);

if (!defined('TAG_ATTENTE_VALIDATION'))
	define('TAG_ATTENTE_VALIDATION', 'ATTV');

if (!defined('TAG_HOMOLOGATION_NECESSAIRE'))
	define('TAG_HOMOLOGATION_NECESSAIRE', 'LOGN');

if (!defined('TAG_HOMLOGEE'))
	define('TAG_HOMOLOGEE', 'LOGV');

if (!defined('TAG_INVALIDE'))
	define('TAG_INVALIDE', 'INV!');

if (!defined('TAG_NOUVEL_OBSERVATEUR'))
	define('TAG_NOUVEL_OBSERVATEUR', 'NEWO');
