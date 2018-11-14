<?php 

namespace App\Controllers;

use App\Models\User;
use Respect\Validation\Validator;
use Zend\Diactoros\Response\RedirectResponse;

class AuthController extends BaseController{
	
	public function getLoginAction($request){
		$responseMessage = null;
		if ($request->getMethod() == 'POST') {
			
			$postData = $request->getParsedBody();
			$user = User::where('email', $postData['email'])->first();
			
				 if ($user){
				 		if (\password_verify($postData['password'], $user->password)) {
				 			$_SESSION['userEmail'] = $user->email;
				 			$_SESSION['userId'] = $user->id;
				 				$rolUser = $user->rol;
				 				 if ($postData['rol'] == $rolUser) {
				 				 	
				 				 	switch ($rolUser){
				 					case 'student':
							 			//En respuesta se redirecciona.
							 			return new RedirectResponse('/SGE/panelStudent');
				 						break;
				 					case 'teacher':
							 			//En respuesta se redirecciona.
							 			return new RedirectResponse('/SGE/addStudent');
				 						break;
				 					case 'financial':
							 			//En respuesta se redirecciona.
							 			return new RedirectResponse('/SGE/panelFinancial');
				 						break;
				 					case 'admin':
							 			//En respuesta se redirecciona.
							 			return new RedirectResponse('/SGE/panelAdmin');
				 						break;
				 				}
				 			}else{

				 				$responseMessage = "Bad credentials!";
				 			}
				 				
				 		}else{

				 			$responseMessage = "Bad credentials!";
				 		}
				 }else{

				 	$responseMessage = "Bad credentials!";
				 }
				}

				 //En respuesta se rederiza, se entrega.
		return $this->renderHTML('login.twig', [
			'responseMessage' => $responseMessage
		]);
	}
		
		public function getLogoutAction(){

		unset($_SESSION['userId']);
		return new RedirectResponse('sign-in');

	}
}