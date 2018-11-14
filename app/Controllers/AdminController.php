<?php

namespace App\Controllers;

use App\Models\{Person, User};
use Respect\Validation\Validator;

class AdminController extends BaseController{

	public function getPanelAdminAction(){

		return $this->renderHTML('/admin/panelAdmin.twig');
    }
}