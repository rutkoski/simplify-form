<?php

use Simplify as s;

$config = Simplify::config();

define('FORM_DIR', __dir__);

$config['forms:dir'] = __dir__;
$config['templates:path:'] = '{forms:dir}/templates';
