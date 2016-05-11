<?php

use infrajs\router\Router;
use infrajs\path\Path;
use infrajs\controller\Controller;
use infrajs\config\Config;

chdir('../../../');
require_once('vendor/autoload.php');

Router::init();

Router::apply();