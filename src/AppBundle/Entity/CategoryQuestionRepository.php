<?php
/**
 * Created by PhpStorm.
 * User: fakho
 * Date: 2/8/2018
 * Time: 11:11 AM
 */

namespace AppBundle\Entity;


use Doctrine\ORM\EntityRepository;

class CategoryQuestionRepository extends EntityRepository {
    public function getCategoriesOfQuestion($questionID){
        $conn = $this->getEntityManager()->getConnection();
    
        if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $questionID)) {
            $sql = "SELECT category_id FROM questions_in_category WHERE quest_id = ".$questionID;
    
            $stmt = $conn->prepare($sql);
            $stmt->execute();
    
            return $stmt->fetchAll(7);
        }
     
        return null;
    }
    
    public function getCountQuestionsByCategory($categoryID){
        $conn = $this->getEntityManager()->getConnection();
    
        if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $categoryID)) {
            $sql = "SELECT COUNT(*) FROM questions_in_category WHERE category_id = ".$categoryID;
    
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(7);
        }
        
        return null;
    }
}