<?php
/**
 * Created by PhpStorm.
 * User: fakho
 * Date: 7/24/2018
 * Time: 8:35 PM
 */

namespace AppBundle\Entity;


use Doctrine\ORM\EntityRepository;

class questionTypeRepository extends EntityRepository{
    public function findTypeById($id){
        $conn = $this->getEntityManager()->getConnection();
        
        if(!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $id)){
            $sql = "SELECT question_type FROM type_of_questions WHERE type_question_id = ".$id;
    
            $stmt = $conn->prepare($sql);
    
            $stmt->execute();
    
            return $stmt->fetch();
        }
        
        return null;
    }
}