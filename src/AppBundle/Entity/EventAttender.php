<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class EventAttender
 * @package AppBundle\Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="event_attenders")
 */
class EventAttender{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $event_ID;

    /**
     * @ORM\Column(type="integer")
     */
    protected $user_ID;
    
    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    
    /**
     * @param mixed $event_ID
     */
    public function setEventID($event_ID)
    {
        $this->event_ID = $event_ID;
    }
    
    /**
     * @param mixed $user_ID
     */
    public function setUserID($user_ID)
    {
        $this->user_ID = $user_ID;
    }
    
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getEventID()
    {
        return $this->event_ID;
    }

    /**
     * @return mixed
     */
    public function getUserID()
    {
        return $this->user_ID;
    }


}

?>