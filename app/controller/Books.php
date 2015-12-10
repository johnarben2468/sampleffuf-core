<?php
/**
 * Created by PhpStorm.
 * User: johnarben
 * Date: 12/7/15
 * Time: 4:02 PM
 */

namespace abd\app\controller;

use abd\app\service\BookServiceInterface;
use phastl\ViewEngineInterface;
use xvsys\validator\ValidationException;
use abd\app\model\Book;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Books{

    /**
     * @var \phastl\ViewEngineInterface
     */
    private $view;

    /**
     * @var \abd\app\service\BookServiceInterface
     */
    private $service;

    /**
     * Users constructor.
     * @param \abd\app\service\BookServiceInterface $service
     * @param \phastl\ViewEngineInterface $view
     * @inject("service", "\abd\app\service\BookService)
     */
    public function __construct(BookServiceInterface $service, ViewEngineInterface $view ){
        $this->view = $view;
        $this->service = $service;
    }


    public function getList(){
        return $this->view->render('books/books',
            [
                'books' => $this->service->getAll()
            ]
        );
    }

    public function create(Book $book){
        try{
            $this->service->create($book);
            return $this->view->render('books/books', ['books' => $this->service->getAll(),'book'=> $book, '_success'=> 'User created.']);
        }
        catch(ValidationException $exc){
            return $this->view->render('books/books', ['books' => $this->service->getAll(),'book'=> $book, '_exception'=>$exc]);

        }
    }

    public function delete($id){
        $this->service->delete($id);
        return new RedirectResponse('/books');
    }
    public function edit($id){

        return $this->view->render('books/edit',
            [
                'book' => $this->service->getById($id)
            ]
        );
    }

    public function update(Book $book){

        try {

            $this->service->update($book);
            return $this->view->render('books/books',
                [
                    'books' => $this->service->getAll()
                ]
            );


            } catch (ValidationExceptionInterface $exc) {
            return $this->view->render('books/edit',
                [
                    'book' => $book,
                    '_exception' => $exc
                ]
            );
        }
    }
}