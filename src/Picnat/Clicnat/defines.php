<?php
namespace \Picnat\Clicnat;

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
