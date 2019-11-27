<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

use Nette\Application\UI;


interface HomepagePresenterI
{
	
	public function __construct(Nette\Database\Context $database);

	public function startUp();


	public function renderDefault();

	public function renderCourses($search, $filter);

	public function renderShowcourse($id);

	protected function createComponentSearchCourseForm();
	
	public function searchCourseForm(Nette\Application\UI\Form $form);
	


	public function handleOpen($id);

    public function handleClose($id);
}
