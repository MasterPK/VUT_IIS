<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;


final class LectorPresenter extends BasePresenter
{
	public function startUp()
	{
		parent::startup();

		
		$this->startup->mainStartUp($this);
		if(!$this->startup->roleCheck($this,2))
		{
			$this->redirect("Homepage:default");
		}
	}

	public function renderLectorCourses()
	{
		$this->template->courses=$this->lectorModel->getLectorCourses($this->user->identity->id);
	}




	/*public function renderLector(): void
	{
		$courses = array();
		switch($this->template->rank)
		{
			case 5:
			case 4:
			case 3:
				$data = $this->database->query("SELECT id_course, course_name, course_type, course_status FROM course WHERE id_guarantor = ?",  $this->user->identity->id);
				if($data->getRowCount() > 0)
				{
					foreach($data as $course)
					{
						
						array_push($courses, $course);
					}
				}
			case 2:
				$data = $this->database->query("SELECT id_course, course_name, course_type, course_status FROM user NATURAL JOIN course_has_lecturer NATURAL JOIN course WHERE id_user = ? AND course_status != 0",  $this->user->identity->id);
				if($data->getRowCount() > 0)
				{
					foreach($data as $course)
					{
						array_push($courses, $course);
					}
				}
				break;
			default:
				break;
		}
		
		if(count($courses) > 0)
		{
			$this->template->courses=$courses;
		}
	}*/
}