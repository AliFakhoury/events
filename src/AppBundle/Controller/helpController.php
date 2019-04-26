<?php
/**
 * Created by PhpStorm.
 * User: fakho
 * Date: 6/25/2018
 * Time: 3:13 PM
 */

namespace AppBundle\Controller;


class helpController implements \JsonSerializable
{
    
    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize(){
        return (Object) get_object_vars($this);
    }
}