<?php
/**
 * Created by PhpStorm.
 * User: johnarben
 * Date: 12/7/15
 * Time: 3:32 PM
 */

namespace abd\app\service;


use abd\app\model\Sampletable;
use abd\app\core\AbstractBaseService;
use Doctrine\ORM\EntityManager;
use abd\app\service\UserServiceInterface;
use xvsys\validator\Validator;

class UserService extends AbstractBaseService implements UserServiceInterface {

    private $validator;

    public function __construct(EntityManager $entityManager, Validator $validator){
        parent::__construct($entityManager);
        $this->validator =$validator;
    }

    public function getById($id){
        return $this->entityManager->createQuery('SELECT a FROM  abd\app\model\Sampletable a WHERE a.id=?0 ')->setParameter("0",$id)->getSingleResult();

    }

    public function getAll(){
        return $this->entityManager->getRepository(Sampletable::class)->findAll();
    }

    public function create(Sampletable $sampletable){

        $this->validator->validate($sampletable);
        $this->entityManager->beginTransaction();
        $this->entityManager->persist($sampletable);
        $this->entityManager->flush($sampletable);

        $this->entityManager->commit();
    }
    public function delete($id){
        $this->entityManager->beginTransaction();
        $user = $this->getById($id);
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        $this->entityManager->commit();
    }

}