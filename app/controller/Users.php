<?php
/**
 * Created by PhpStorm.
 * User: johnarben
 * Date: 12/7/15
 * Time: 4:02 PM
 */

namespace abd\app\controller;

use abd\app\service\UserServiceInterface;
use phastl\ViewEngineInterface;
use xvsys\validator\ValidationException;
use abd\app\model\Sampletable;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Users{

    /**
     * @var \phastl\ViewEngineInterface
     */
    private $view;

    /**
     * @var \abd\app\service\UserServiceInterface
     */
    private $service;

    /**
     * Users constructor.
     * @param \abd\app\service\UserServiceInterface $service
     * @param \phastl\ViewEngineInterface $view
     * @inject("service", "\abd\app\service\UserService)
     */
    public function __construct(UserServiceInterface $service, ViewEngineInterface $view ){
        $this->view = $view;
        $this->service = $service;
    }

    public function get_json( $endpoint){
        $qry_str = $endpoint;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://phisix-api.appspot.com/' . $qry_str);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, '3');
        $content = trim(curl_exec($ch));
        curl_close($ch);
        return $content;
    }

    public function getList(){




        return $this->view->render('usermanage',
            [
                'users' => $this->service->getAll()
            ]
            );


        }

    public function create(Sampletable $sampletable){
        try{
            $this->service->create($sampletable);
            return $this->view->render('usermanage', ['users'=> $sampletable, '_success'=> 'User created.']);
        }
        catch(ValidationException $exc){
            return $this->view->render('usermanage', ['user'=> $sampletable, '_exception'=>$exc]);

        }
    }
    public function display(){
      return $this->view->render('display', ['users'=> $this->service->getAll()]);
      }

    public function delete($id){
        $this->service->delete($id);
        return new RedirectResponse('/display');
    }

}