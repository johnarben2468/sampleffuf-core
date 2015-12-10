<?php
/**
 * Created by PhpStorm.
 * User: johnarben
 * Date: 12/7/15
 * Time: 3:32 PM
 */

namespace abd\app\service;

use abd\app\model\Book;
use abd\app\core\AbstractBaseService;
use abd\app\service\BookServiceInterface;
use xvsys\validator\Validator;

use Doctrine\ORM\EntityManager;
class BookService extends AbstractBaseService implements BookServiceInterface {

    private $validator;

    private $merger;

    public function __construct(EntityManager $entityManager, Validator $validator){
        parent::__construct($entityManager);
        $this->validator =$validator;


    }

    public function getById($id){
        return $this->entityManager->createQuery('SELECT a FROM  abd\app\model\Book a WHERE a.id=?0 ')->setParameter("0",$id)->getSingleResult();

    }

    public function getAll(){
        return $this->entityManager->getRepository(Book::class)->findAll();
    }

    public function create(Book $book){

        $this->validator->validate($book);
        $this->entityManager->beginTransaction();
        $this->entityManager->persist($book);
        $this->entityManager->flush($book);

        $this->entityManager->commit();
    }
    public function delete($id){
        $this->entityManager->beginTransaction();
        $book = $this->getById($id);
        $this->entityManager->remove($book);
        $this->entityManager->flush();
        $this->entityManager->commit();
    }

    /*public function update(Book $book){

        print_r($book);
        $this->validator->validate($book);

        $this->entityManager->beginTransaction();
        // Load managed models.

        $managed = $this->getById($book->getId());
        // Apply the changes that the user has submitted
        $this->merger->merge($book, $managed);
        // Apply the managed entities to the book model.

        $this->entityManager->merge($managed);
        $this->entityManager->flush();
        $this->entityManager->commit();
    }
     */
    public function update(Book $book) {
        $this->entityManager->merge($book);
        $this->entityManager->flush();
    }

}