<?php

namespace App\Controllers;

use App\Models\{Person, Student, User};
use Respect\Validation\Validator;

class StudentController extends BaseController{
	
	public function getAddStudentAction($request){
		$responseMessage = null;
		if ($request->getMethod() == 'POST') {
			$postData = $request->getParsedBody();
			$userValidator = Validator::key('email', Validator::stringType()->notEmpty())
											->key('pass1', Validator::stringType()->notEmpty());
			try {

				$userValidator->assert($postData);
				$postData = $request->getParsedBody();

				//To table Persons
				$person = new person();
				$person->lastname = $postData['lastname'];
				$person->name = $postData['firstname'];
				$person->idCard = $postData['idCard'];
				$person->email = $postData['email'];
				$person->phone = $postData['phone'];
				$person->language = $postData['language'];
				$person->rol = 'student';
				$person->save();
				
				//To table Students
				$student = new student();
				$student->idCard = $postData['idCard'];
				$student->schedule = $postData['schedule'];
				$student->level = $postData['level'];
				$student->specialCode = $postData['officialCode'];
				$student->save();

				//To table Users
				$user = new user();
				$user->email = $postData['email'];
				$user->password = password_hash($postData['pass1'], PASSWORD_DEFAULT);
				$user->rol = 'student';
				$user->save();

				$responseMessage = "Saved";
				
			} catch (\Exception $e) {
				
				$responseMessage = $e->getMessage();
			}

		}
		
		return $this->renderHTML('addStudent.twig', ['responseMessage' => $responseMessage]);
	}

	public function getPanelStudentAction(){

		return $this->renderHTML('/students/panelStudent.twig');
    }

    public function getProfileStudentAction(){

    	$userEmail = $_SESSION['userEmail'];
    	$student = Person::where('email', $userEmail)->first();
		return $this->renderHTML('/students/profileStudent.twig', ['student' => $student]);
    }

    public function getEditProfileStudentAction(){

    	$userEmail = $_SESSION['userEmail'];
    	$student = Person::where('email', $userEmail)->first();
    	$user = User::where('email', $userEmail)->first();
		return $this->renderHTML('/students/editProfileStudent.twig', ['student' => $student, 'user' => $user]);
    }

 	public function getUpdateProfileStudentAction($request){

    	$responseMessage = null;
    	$postData = $request->getParsedBody();
		$studentValidator = Validator::key('lastname', Validator::stringType()->notEmpty())
											->key('firstname', Validator::stringType()->notEmpty());
    	try {
    		//To table Persons
    		$studentValidator->assert($postData);
    		$postData = $request->getParsedBody();
			$person = Person::where('id', $_SESSION['userId'])->first();
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

    	return $this->renderHTML('students/editProfileStudent.twig', ['responseMessage' => $responseMessage]);
    }

    public function getAllStudentsAction(){

    	$students = Student::all();
    	$rol = 'student';
    	$persons = Person::where('rol', '=', $rol)->get();
    	return $this->renderHTML('students/listStudents.twig', 
    							['students' => $students,
    							  'persons' => $persons]);
    }
}