<?php
/**************************************************************/
/* config.php
/* Fichier de configuration.
/**************************************************************/

define('SQL_DB', 'localhost');
define('SQL_NAME', 'FeuilleTemps');
define('SQL_LOGIN', 'root');
define('SQL_PASS', 'adheayz');
define('SQL_PORT', '3307');

setlocale (LC_TIME, 'fr_FR','fra');
date_default_timezone_set("Europe/Paris");
mb_internal_encoding("UTF-8");
?>