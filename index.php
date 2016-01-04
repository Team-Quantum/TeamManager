<?php
/**
 * Created by PhpStorm.
 * User: .PolluX
 * Date: 1/3/2016
 * Time: 6:38 PM
 */

ini_set('display_errors', '1');
error_reporting(E_ALL);

session_name('q-t_tm-2015');
session_start();

require_once('vendor/autoload.php');

$core = new \TeamManager\Core();
echo "<pre>";
print_r($core->getSettings());
echo "</pre>";

echo "<br><br><br>";

$a = array('a','hure','hurensohn','deine mama kackt','deine mama kackt einen','deine mama kackt einen hurensohn','deine mama kackt einen hurensohn zum','deine mama kackt einen hurensohn zum mittag');
foreach($a as $b){
    $hash = $core->createHash($b, 'bcrypt');
    echo $hash;
    echo ' | lenght: '.strlen($hash).'<br/>';
}

// TODO: password_hash & password_verify für login
// TODO: registration mit key, den nur der admin einsehen kann + recaptcha

echo "<br><br><br><br><br>";
echo password_verify('a', '$2y$10$/SZHLP8WtxCx6ezoh99ZQOcsZuNcskSPJ0Vu9iaRsXQ5I9n82Ft7u');
echo password_verify('a', '$2y$10$RwdCngh85ZHHAWtJpgEg5eNhofeDjdwxj66MO5.loZsGeupkq3Wi2');
?>

a -> $2y$10$/SZHLP8WtxCx6ezoh99ZQOcsZuNcskSPJ0Vu9iaRsXQ5I9n82Ft7u
a -> $2y$10$RwdCngh85ZHHAWtJpgEg5eNhofeDjdwxj66MO5.loZsGeupkq3Wi2
