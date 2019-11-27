<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Application\UI\Form;

class VisitorModel
{

    private $database, $mainModel;
    public function __construct(Nette\Database\Context $database, \App\Model\MainModel $mainModel)
    {
        $this->database = $database;
        $this->mainModel = $mainModel;
    }

    /**
     * Handle Showcourse
     *
     * @param [type] $presenter
     * @param [type] $id
     * @return void
     */
    public function renderShowcourse($presenter, $id)
    {
        if (empty($id)) {
            $presenter->redirect('Homepage:courses');
        }
        $presenter->template->course = $this->mainModel->getCourseDetails($id);
        $presenter->template->guarantor = $this->mainModel->getCourseGuarantorName($presenter->template->course->id_guarantor);

        switch ($presenter->template->course->course_type) {
            case "P":
                $presenter->template->type = "Povinný";
                break;
            case "V":
                $presenter->template->type = "Volitelný";
                break;
        }
    }
}
