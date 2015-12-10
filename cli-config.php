<?php

use Doctrine\ORM\Tools\Console\ConsoleRunner;

require 'vendor/autoload.php';
require_once 'config/db-local.php';

return ConsoleRunner::createHelperSet($entityManager);
