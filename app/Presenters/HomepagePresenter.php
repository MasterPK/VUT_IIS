<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

use Nette\Application\UI;


final class HomepagePresenter extends Nette\Application\UI\Presenter
{
	/** @var \App\Model\StartUp @inject */
	public $startup;
	
	/** @var \App\Model\VisitorModel @inject */
    public $visitorModel;

	private $database;
	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	private $current_course_id;


	public function startUp()
	{
		parent::startup();

		$this->startup->mainStartUp($this);
		
	}


	public function renderDefault(): void
	{ 

	}

	public function renderCourses($search, $filter): void
	{
		if($search)
		{
			$this->template->courses=$this->visitorModel->getAllCoursesByFilter($filter, $search);
		}
		else
		{
			//zobraz vsetky schvalene kurzy
			$this->template->courses=$this->visitorModel->getAllApprovedCourses();
		}
	}

	public function renderShowcourse($id): void
	{
		$this->visitorModel->renderShowcourse($this,$id);
		
	}

	protected function createComponentSearchCourseForm(): Nette\Application\UI\Form
    {
        $form = new Nette\Application\UI\Form;

        $form->addSelect('filter', 'Filter', [
		    'course_name' => 'Název',
		    'id_course' => 'Zkratka',
		    'course_type' => 'Typ',
		    'course_price' => 'Cena',
		]);

        $form->addText('search', 'Hledat:')
        ->setHtmlAttribute('class', 'form-control')
        ->setRequired();

        $form->addSubmit('send', 'Hledat')
        ->setHtmlAttribute('class', 'btn btn-block btn-primary');
        
        $form->onSuccess[] = [$this, 'searchCourseForm'];
        return $form;
	}
	
	public function searchCourseForm(Nette\Application\UI\Form $form): void
    {
    	$values = $form->getValues();
    	$this->redirect("Homepage:courses", $values->search, $values->filter);
	}
	

	

    public function addNotification($form): void
    {
		
		$values = $form->getValues();
    	$get = $this->database->query("SELECT `id` FROM `course_has_student` WHERE `id_course` = ? AND `id_user` = ?", $values->id_course, $this->user->identity->id);

    	if($get->getRowCount() == 0)
    	{
			$data = $this->database->query("INSERT INTO course_has_student ( id, id_course, id_user, student_status) VALUES ('', ?, ?, 0)", $values->id_course, $this->user->identity->id);
			$this->template->error_notif = 2;
    	}
    	else
    	{
    		$this->template->error_notif = 1;
		}
		
		if ($this->isAjax())
		{
			$this->payload->message = true;
            $this->redrawControl('error_notif_snippet');
        }
    	
	}

	public function handleOpen($id)
    {
    	$get = $this->database->query("UPDATE course SET course_status = 2 WHERE id_course = ?", $id);

    	if($get->getRowCount() == 1)
    	{
    		$this->template->course_open_success = true;
    	}
    	else
    	{
    		$this->template->course_open_success = false;
    	}	

    	if ($this->isAjax())
		{
            $this->redrawControl('course_open_success_snippet');
        }
    }

    public function handleClose($id)
    {
    	$get = $this->database->query("UPDATE course SET course_status = 3 WHERE id_course = ?", $id);

    	if($get->getRowCount() == 1)
    	{
    		$this->template->course_close_success = true;
    	}
    	else
    	{
    		$this->template->course_close_success = false;
    	}	

    	if ($this->isAjax())
		{
            $this->redrawControl('course_close_success_snippet');
        }
    }
}
