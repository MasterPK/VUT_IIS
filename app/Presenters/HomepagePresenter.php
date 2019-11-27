<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

use Nette\Application\UI;


class HomepagePresenter implements Interfaces\HomepagePresenterI
{
	public run($request){}

	/** @var \App\Model\StartUp @inject */
	public $startup;
	
	/** @var \App\Model\VisitorModel @inject */
	public $visitorModel;
	
	/** @var \App\Model\MainModel @inject */
    public $mainModel;

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
			$this->template->courses=$this->mainModel->getAllCoursesByFilter($filter, $search);
		}
		else
		{
			//zobraz vsetky schvalene kurzy
			$this->template->courses=$this->mainModel->getAllApprovedCourses();
		}
	}

	public function renderShowcourse($id): void
	{
		$this->visitorModel->renderShowcourse($this,$id);
		
	}

	public function createComponentSearchCourseForm(): Nette\Application\UI\Form
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
