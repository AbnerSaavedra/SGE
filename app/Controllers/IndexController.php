<?php 

namespace App\Controllers;

class IndexController extends BaseController{

    public function indexAction(){
		
		return $this->renderHTML('index.twig');
    }
}