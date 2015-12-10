<?php

namespace abd\app\controller;

/**
 * Description of Main
 *
 * @author Alexander Schlegel
 */
class Main {

    /**
     *
     * @var \phastl\ViewEngineInterface
     */
    private $view;

    public function __construct(\phastl\ViewEngineInterface $view) {
        $this->view = $view;
    }

    public function getIndexPage() {
        return $this->view->render('hello-world');
    }

}
