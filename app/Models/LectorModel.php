<?php

namespace App\Model;


use Nette;
use Nette\Application\UI\Form;
use Ublaboo\DataGrid\DataGrid;

class LectorModel
{
    

    private $mainModel;
    private $studentModel;
    private $database;
    public function __construct(Nette\Database\Context $database, \App\Model\MainModel $mainModel , \App\Model\StudentModel $studentModel)
    {
        $this->database = $database;
        $this->mainModel=$mainModel;
        $this->studentModel=$studentModel;
    }

    public function getCoursesOfLector($id_lector)
    {
        return $this->mainModel->getCoursesOfStudent($id_lector);
    }

    /**
     * Return list of lector courses
     *
     * @param [int] $id_lector
     * @return void
     */
    public function getLectorCourses($id_lector)
    {
        $data = $this->database->query("SELECT id_course, course_name, course_type, course_status FROM user NATURAL JOIN course_has_lecturer NATURAL JOIN course WHERE id_user = ? AND course_status != 0",  $id_lector)->fetchAll();
        
        if(count($data) > 0)
        {
            return $data;
        }
    }

 

    public function renderShowCourse($presenter,$id)
    {
        $this->studentModel->renderShowcourse($presenter,$id);

        $course_lector = $this->database->query("SELECT id_user FROM user NATURAL JOIN course_has_lecturer NATURAL JOIN course WHERE id_user = ? AND id_course = ?",  $presenter->user->identity->id, $id)->fetch();
        //ani lektor sa nemoze registrovat na kurz, ktory uci
        if($course_lector == NULL)
        {
            $presenter->template->userIsLectorInCourse=false;
        }
        else
        {
            $presenter->template->userIsLectorInCourse=true;
        }   
    }

    public function createComponentTaskStudents($presenter, $name, $id_task)
    {
        
        if($id_task == NULL)
        {
            $httpRequest = $presenter->getHttpRequest();
            $id_task = $httpRequest->getQuery('id_task');
        }

        $grid = new DataGrid($presenter, $name);
        $grid->setPrimaryKey('user.id_user');
        //$grid->setDataSource($this->database->query("SELECT id_user, email, first_name, surname, points FROM user NATURAL JOIN student_has_task"));
        $grid->setDataSource($presenter->database->table("student_has_task")->where("student_has_task.id_task = ?", $id_task)->select("user.id_user,user.email,user.first_name,user.surname,student_has_task.points"));

        $grid->addColumnText('email', 'Email studenta')
        ->setSortable()
        ->setFilterText();

        $grid->addColumnText('first_name', 'Jméno studenta')
        ->setSortable()
        ->setFilterText();
        
        $grid->addColumnText('surname', 'Přijmení studenta')
        ->setSortable()
        ->setFilterText();
        
        $grid->addColumnText('points', 'Body')
        ->setSortable()
        ->setFilterText();

        $grid->addGroupTextAction('Nastavit body')
            ->onSelect[] = function ($students, $value): void {
                $httpRequest = $presenter->getHttpRequest();
                $id_task = $httpRequest->getQuery('id_task');
                $maxpoints = $presenter->database->query("SELECT task_points FROM task WHERE id_task = ?", $id_task)->fetch();
                if($maxpoints->task_points >= $value)
                {
                    foreach($students as $student)
                    {
                        $presenter->database->query("UPDATE student_has_task SET points = ? WHERE id_user = ? AND id_task = ?", $value, $student, $id_task);
                    }
                }
                else
                {
                    $presenter->template->error_set = true;
                    $presenter->redrawControl('error_snippet');
                }
            };

        $grid->addInlineEdit()
            ->onControlAdd[] = function (Nette\Forms\Container $container): void {
            $httpRequest = $this->getHttpRequest();
            $id_task = $httpRequest->getQuery('id_task');
            $maxpoints = $this->database->query("SELECT task_points FROM task WHERE id_task = ?", $id_task)->fetch();
            $container->addText('points', '')
                    ->addRule(Form::RANGE, "Zadejte počet bodů v rozmezí 0 - ".$maxpoints->task_points, [0,$maxpoints->task_points]);
        };

        $grid->getInlineEdit()->onSetDefaults[] = function (Nette\Forms\Container $container, $item): void {

            $container->setDefaults([
                'points' => $item->points
            ]);
        };

        $grid->getInlineEdit()->onSubmit[] = function ($id, Nette\Utils\ArrayHash $values): void {
            $httpRequest = $this->getHttpRequest();
            $id_task = $httpRequest->getQuery('id_task');
            
            $this->database->query("UPDATE student_has_task SET points = ? WHERE id_user = ? AND id_task = ?", $values->points, $id, $id_task);
                
        };

        $grid->setTranslator($presenter->dataGridModel->dataGridTranslator);

        return $grid;
    }
}