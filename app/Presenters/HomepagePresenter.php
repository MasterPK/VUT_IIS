<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

use Nette\Application\UI;


class HomepagePresenter extends BasePresenter
{
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
