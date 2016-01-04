<?php
/**
 * Created by PhpStorm.
 * User: .PolluX
 * Date: 1/3/2016
 * Time: 6:38 PM
 */

/**
 * Copyright © 2016
 * Brought to you by:
 * ___________                      ________                 __
 * \__    ___/___ _____    _____    \_____  \ _____    _____/  |_ __ __  _____
 *    |    |_/ __ \\__  \  /     \    /  / \  \\__  \  /    \   __\  |  \/     \
 *    |    |\  ___/ / __ \|  Y Y  \  /   \_/.  \/ __ \|   |  \  | |  |  /  Y Y  \
 *    |____| \___  >____  /__|_|  /  \_____\ \_(____  /___|  /__| |____/|__|_|  /
 *               \/     \/      \/          \__>    \/     \/                 \/
 *                     https://github.com/Team-Quantum
 *                RealPolluX / https://github.com/RealPolluX
 */

ini_set('display_errors', 0);
ini_set('html_errors', 1);

session_name('q-t_tm-2016');
session_start();

require_once('vendor/autoload.php');

$core = new \TeamManager\Core();
$core->execute();

echo $core->getPathInfo();

// TODO: password_hash & password_verify für login
// TODO: registration mit key, den nur der admin einsehen kann + recaptcha


