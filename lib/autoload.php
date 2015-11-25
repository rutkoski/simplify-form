<?php

$config = Simplify::config();

define('FORM_DIR', preg_replace('#[\\\/]+#', '/', __dir__ . '/'));

$config['forms:dir'] = FORM_DIR;
$config['templates:path:'] = '{forms:dir}templates';
$config['app:assets:path:'] = 'vendor/rutkoski/simplify-form/lib/assets/';

require_once('functions.php');
