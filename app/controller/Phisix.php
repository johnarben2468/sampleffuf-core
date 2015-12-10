<?php
/**
 * Created by PhpStorm.
 * User: johnarben
 * Date: 12/7/15
 * Time: 4:02 PM
 */

namespace abd\app\controller;


use phastl\ViewEngineInterface;
use xvsys\validator\ValidationException;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Phisix{

    /**
     * @var \phastl\ViewEngineInterface
     */
    private $view;



    /**
     * Users constructor.
     * @param \abd\app\service\BookServiceInterface $service
     * @param \phastl\ViewEngineInterface $view
     * @inject("service", "\abd\app\service\BookService)
     */
    public function __construct(ViewEngineInterface $view ){
        $this->view = $view;

    }

    public function getList(){
         $endpoint = "stocks.json";
         $result = $this->get_json($endpoint);
         return $result;
    }

    public function getSpec($symbol){
        $endpoint = "stocks/".$symbol.".json";
        $result = $this->get_json($endpoint);
        return $result;
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


}