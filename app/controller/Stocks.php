<?php
/**
 * Created by PhpStorm.
 * User: johnarben
 * Date: 12/7/15
 * Time: 4:02 PM
 */

namespace abd\app\controller;

use abd\app\service\StockServiceInterface;
use phastl\ViewEngineInterface;
use xvsys\validator\ValidationException;
use abd\app\model\Stock;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Stocks{

    /**
     * @var \phastl\ViewEngineInterface
     */
    private $view;

    /**
     * @var \abd\app\service\StockServiceInterface
     */
    private $service;

    /**
     * Users constructor.
     * @param \abd\app\service\StockServiceInterface $service
     * @param \phastl\ViewEngineInterface $view
     * @inject("service", "\abd\app\service\StockService)
     */
    public function __construct(StockServiceInterface $service, ViewEngineInterface $view ){
        $this->view = $view;
        $this->service = $service;
    }


    public function getList(){
        return $this->view->render('Stocks/Stocks',
            [
                'Stocks' => $this->service->getAll()
            ]
        );
    }

    public function create(Stock $Stock){
        try{
            $this->service->create($Stock);
            return $this->view->render('Stocks/Stocks', ['Stocks' => $this->service->getAll(),'Stock'=> $Stock, '_success'=> 'User created.']);
        }
        catch(ValidationException $exc){
            return $this->view->render('Stocks/Stocks', ['Stocks' => $this->service->getAll(),'Stock'=> $Stock, '_exception'=>$exc]);

        }
    }

    public function delete($id){
        $this->service->delete($id);
        return new RedirectResponse('/Stocks');
    }
    public function edit($id){

        return $this->view->render('Stocks/edit',
            [
                'Stock' => $this->service->getById($id)
            ]
        );
    }

    public function update(Stock $Stock){

        try {

            $this->service->update($Stock);
            return $this->view->render('Stocks/Stocks',
                [
                    'Stocks' => $this->service->getAll()
                ]
            );


        } catch (ValidationExceptionInterface $exc) {
            return $this->view->render('Stocks/edit',
                [
                    'Stock' => $Stock,
                    '_exception' => $exc
                ]
            );
        }
    }
}