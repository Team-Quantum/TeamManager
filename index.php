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
$core->execute();

// TODO: password_hash & password_verify für login
// TODO: registration mit key, den nur der admin einsehen kann + recaptcha


