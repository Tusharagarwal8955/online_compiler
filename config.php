<?php
//ALL ALLOWED CORS DOMAINS

//SESSION COOKIE SECURITY
ini_set ( 'max_execution_time', 60);
set_time_limit(60);
define('SECURE', false); //SEND COOKIE OVER HTTPS CONNECTIONS ONLY
define('HTTP_ONLY', true); //PREVENT COOKIE FROM ACCESSING USING JS


//PHP INI VERIALBES
ini_set('upload_max_filesize', '20M');
ini_set('memory_limit', '512M');


//RESERVED ROLE GUARD
define("RESERVED_ROLE_ARRAY_GUARD", ["super_admin", "super admin"]);
define("RESERVED_SENSITIVE_ROLE_ARRAY_GUARD", ["super_admin"]);


//FILE UPLOAD PATH
define("CODE_FILE_UPLOAD_DIRECTORY", __DIR__ . "/uploaded_files/code/");



//MANAGE ENVIRENMENT
$server = $_SERVER["SERVER_NAME"] ?? "";
if ($server == "localhost") {
    define("MASTER_ENVIRONMENT", "LOCAL");
} else if ($server == "api.altiusinvestech.com") {
    define("MASTER_ENVIRONMENT", "PROD");
} else {
    define("MASTER_ENVIRONMENT", "DEV");
}


if (MASTER_ENVIRONMENT == "LOCAL") {
    //DATABASE CONNECTION
    define('DB_NAME', 'compiler');
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASSWORD', 'localhost@1432');



} else if (MASTER_ENVIRONMENT == "DEV") {
    //DATABASE CONNECTION
    define('DB_NAME', 'compiler');
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASSWORD', 'localhost@1432');


} else if (MASTER_ENVIRONMENT == "PROD") {
    //DATABASE CONNECTION
    define('DB_NAME', 'compiler');
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASSWORD', 'localhost@1432');

}
