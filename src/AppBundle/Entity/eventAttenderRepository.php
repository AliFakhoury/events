<?php
/**
 * Created by PhpStorm.
 * User: fakho
 * Date: 9/11/2018
 * Time: 7:59 PM
 */

namespace AppBundle\Entity;


use Doctrine\ORM\EntityRepository;

class eventAttenderRepository extends EntityRepository {
    public function attendEvent($eventId, $attenderId){
        $conn = $this->getEntityManager()->getConnection();
        if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $eventId) && !preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $attenderId)) {
            $sql = "INSERT INTO event_attenders (`event_id`, `user_id`) VALUES ('".$eventId."', '".$attenderId."')";
    
            $stmt = $conn->prepare($sql);
            $stmt->execute();
        }
    }
}