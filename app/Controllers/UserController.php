<?php

namespace App\Controllers;

use App\Models\User;
use Respect\Validation\Validator;

class UserController extends BaseController{
	
	public function getAddUserAction($request){
		$responseMessage = null;
		if ($request->getMethod() == 'POST') {
			$postData = $request->getParsedBody();
			$userValidator = Validator::key('email', Validator::stringType()->notEmpty())
											->key('password', Validator::stringType()->notEmpty());
			try {

				$userValidator->assert($postData);
				$postData = $request->getParsedBody();
				$files = $request->getUploadedFiles();
				$foto = $files['foto'];

				if ($foto->getError() == UPLOAD_ERR_OK){
					$fileName = $foto->getClientFilename();
					$foto->moveTo("uploads/profileUsers/$fileName");
				}
				
				$user = new User();
				$user->email = $postData['email'];
				$user->password = password_hash($postData['password'], PASSWORD_DEFAULT);
				$user->foto = "uploads/profileUsers/$fileName";
				$user->save();

				$responseMessage = "Saved";
				
			} catch (\Exception $e) {
				
				$responseMessage = $e->getMessage();
			}

		}
		
		return $this->renderHTML('addUser.twig', ['responseMessage' => $responseMessage]);
	}
}