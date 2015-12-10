<?php
/**
 * Created by PhpStorm.
 * User: johnarben
 * Date: 12/7/15
 * Time: 3:38 PM
 */

namespace abd\app\core;

use Doctrine\ORM\Cache\Persister\EntityManager;

abstract class AbstractBaseService{

    protected $entityManager;

    public function __construct(\Doctrine\ORM\EntityManager $entityManager){
        $this->entityManager = $entityManager;

    }

}