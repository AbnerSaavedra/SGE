<?php 

namespace App\Controllers;

use App\Models\{Person, Financial, User};
use Respect\Validation\Validator;

class FinancialController extends BaseController{

	public function getAddFinancialAction($request){
		$responseMessage = null;
		if ($request->getMethod() == 'POST') {
			$postData = $request->getParsedBody();
			$userValidator = Validator::key('email', Validator::stringType()->notEmpty())
											->key('pass1', Validator::stringType()->notEmpty());
			try {

				$userValidator->assert($postData);
				$postData = $request->getParsedBody();
				$modal = $postData['modal'];
				//To table Persons
				$person = new person();
				$person->lastname = $postData['lastname'];
				$person->name = $postData['firstname'];
				$person->idCard = $postData['idCard'];
				$person->email = $postData['email'];
				$person->phone = $postData['phone'];
				$person->language = $postData['language'];
				$person->rol = 'financial';
				$person->save();
				
				//To table Financial
				$financial = new financial();
				$financial->idCard = $postData['idCard'];
				$financial->addmissionDate = date("Y-m-d", strtotime($postData['adDate']));
				$financial->degree = $postData['degree'];
				$financial->save();

				//To table Users
				$user = new user();
				$user->email = $postData['email'];
				$user->password = password_hash($postData['pass1'], PASSWORD_DEFAULT);
				$user->rol = 'financial';
				$user->save();

				$responseMessage = "Saved";
				
			} catch (\Exception $e) {
				
				$responseMessage = $e->getMessage();
			}

		}

		if ($modal == 'yes') {
			return $this->renderHTML('financials/listFinancials.twig', ['responseMessage' => $responseMessage]);
		}else
			return $this->renderHTML('addFinancial.twig', ['responseMessage' => $responseMessage]);
	}

	public function getAllFinancialsAction(){

    	//$financials = Financial::where('status', '=', 'A')->get();
    	$rol = 'financial';
    	$persons = Person::where('rol', '=', $rol)
    					   ->where('status', '=', 'A')->get();
    	return $this->renderHTML('financials/listFinancials.twig', ['persons' => $persons]);
    }

    public function getEditOrDeleteFinancialAction($data){

    	$postData = $data->getParsedBody();

    	if ($postData['action']) {

    		$responseMessage = null;
	    	$person = Person::where('id', $postData['idStd'])->first();
	    	$person->status = 'D';
	    	$cedStd = $person->idCard;
	    	$person->save();
	    	$teacher = Financial::where('idCard', $cedStd)->first();
	    	$teacher->status = 'D';
	    	$teacher->save();
	    	$responseMessage = "Financial deleted successfully!";
	    	return $this->renderHTML('financials/listFinancials.twig', ['responseMessage' => $responseMessage]);

    	}else{

	    	$responseMessage = null;
	    	$person = Person::where('id', $postData['idStd'])->first();
	    	$person->lastName = $postData['lastname'];
			$person->name = $postData['firstname'];
			$person->phone = $postData['phone'];
			$person->language = $postData['language'];
	    	$person->save();
	    	$responseMessage = "Financial updated successfully!";
	    	return $this->renderHTML('financials/listFinancials.twig', ['responseMessage' => $responseMessage, 'person' => $person]);

    	}
    }

    public function getPanelFinancialAction(){

    	return $this->renderHTML('/financials/panelTeacher.twig');
    }

    public function getProfileFinancialAction(){

    	$userEmail = $_SESSION['userEmail'];
    	$teacher = Person::where('email', $userEmail)->first();
		return $this->renderHTML('/financials/profileTeacher.twig', ['teacher' => $teacher]);
    }

    public function getEditProfileFinancialAction(){

    	$userEmail = $_SESSION['userEmail'];
    	$teacher = Person::where('email', $userEmail)->first();
    	$user = User::where('email', $userEmail)->first();
		return $this->renderHTML('/financials/editProfileTeacher.twig', ['teacher' => $teacher, 'user' => $user]);
    }

    public function getUpdateProfileFinancialAction($request){

    	$responseMessage = null;
    	$postData = $request->getParsedBody();
    	//var_dump($postData);
		$validator = Validator::key('lastname', Validator::stringType()->notEmpty())
										->key('firstname', Validator::stringType()->notEmpty());									
    	try {
    		//To table Persons
    		$validator->assert($postData);
    		$postData = $request->getParsedBody();
    		/*var_dump($_SESSION['userId']);
    		echo "Session id: ".$_SESSION['userId'];*/
			$person = Person::where('email', $_SESSION['userEmail'])->first();
			$person->lastName = $postData['lastname'];
			$person->name = $postData['firstname'];
			$person->phone = $postData['phone'];
			$person->language = $postData['language'];
			$person->save();
			$files = $request->getUploadedFiles();
			
			$foto = $files['profilePicture'];
			if ($foto->getError() == UPLOAD_ERR_OK){
				$fileName = $foto->getClientFilename();
				$foto->moveTo("uploads/profileUsers/$fileName");
			}
			$user = User::where('email', $_SESSION['userEmail'])->first();
			$user->profilePicture = "uploads/profileUsers/$fileName";
			$user->save();
			$responseMessage = "Info updated successfully!";
    		
    	} catch (\Exception $e) {
    		
    		$responseMessage = $e->getMessage();
    	}

    	return $this->renderHTML('financials/editProfileTeacher.twig', ['responseMessage' => $responseMessage]);
    }

}