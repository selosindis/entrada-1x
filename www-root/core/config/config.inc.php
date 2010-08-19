<?php

return array (
'entrada_url' => (((isset($_SERVER["HTTPS"])) ? "https" : "http")."://developer.qmed.ca/~finglanj/entrada/www-root"),
'entrada_relative' => "/~finglanj/entrada/www-root",
'entrada_absolute' => "/Users/finglanj/Sites/entrada/www-root",
'entrada_storage' => "/Users/finglanj/Sites/entrada/www-root/core/storage",
'database' =>
array (
'host' => 'localhost',
'username' => 'entrada',
'password' => 'MMe7yyaa',
'entrada_database' => 'medtech_central',
'auth_database' => 'medtech_auth',
'clerkship_database' => 'medtech_clerkship',
),
'admin' =>
array (
'firstname' => 'Jonathan',
'lastname' => 'Fingland',
'email' => 'jonathan.fingland@queensu.ca',
)
);
?>