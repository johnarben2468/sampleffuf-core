<?php
/**
 * Created by PhpStorm.
 * User: johnarben
 * Date: 12/7/15
 * Time: 3:32 PM
 */

namespace abd\app\service;


use abd\app\model\Sampletable;

interface UserServiceInterface{
    /**
     * @return mixed
     */
    public function getById($id);

    public function getAll();

    public function create(Sampletable $sampletable);


}