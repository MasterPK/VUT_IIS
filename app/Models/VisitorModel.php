<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Application\UI\Form;

class VisitorModel
{

    /** @var Nette\Database\Context @inject */
    public $database;
    
    
    /**
     * Return list of all courses in database
     * Exception on error
     * @return void
     */
    public function getAllCourses()
    {
        $data = $this->database->table("course")->fetchAll();
            
        if($data)
        {
            return $data;
        }
        else
        {
            throw new \Exception("Error in SQL query");
        }
    }

    /**
     * Return list of all approved courses in database
     * Exception on error
     * @return void
     */
    public function getAllApprovedCourses()
    {
        $data = $this->database->table("course")
			->where("course_status != 0")->fetchAll();
            
        if($data)
        {
            return $data;
        }
        else
        {
            throw new \Exception("Error in SQL query");
        }
    }

    /**
     * Return list of all approved courses in database that name matches filter
     * Return NULL when no result
     * @param [string] $filter
     * @return void
     */
    public function getAllCoursesByFilter(string $filter, string $search)
    {
        $data = $this->database->table("course")->where( $filter . " LIKE ? AND course_status >= ?",  "%" . $search . "%", 1)->fetchAll();

        if($data)
        {
            return $data;
        }
        else
        {
            return NULL;
        }
    }

    /**
     * Return list of details about specified course 
     * Exception on error
     * @param integer $course_id
     * @return void
     */
    public function getCourseDetails(int $course_id)
    {
        $data = $this->database->table("course")->where("id_course=?", $course_id)->fetch();

        if($data)
        {
            return $data;
        }
        else
        {
            throw new \Exception("Error in SQL query");
        }
    }

    /**
     * Return full name of course guarantor
     * Exception on error
     * @param integer $id_guarantor
     * @return string
     */
    public function getCourseGuarantorName(int $id_guarantor):string
    {
        $data = $this->database->table("user")->where("id_user=?", $id_guarantor)->fetch();

        if($data)
        {
            return $data->first_name." ".$data->surname;
        }
        else
        {
            throw new \Exception("Error in SQL query");
        }
    }

}