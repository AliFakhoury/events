<?php
/**
 * Created by PhpStorm.
 * User: fakho
 * Date: 5/3/2018
 * Time: 12:16 AM
 */

namespace AppBundle\Entity;


use Doctrine\ORM\EntityRepository;

class UploadRepository extends EntityRepository{
    public function addUpload($data){
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = "INSERT INTO `uploads` (`upload_title`,`video_url`,`category_id`) VALUES ('".$data["videoTitle"]."','".$data["videoID"]."', '".$data["videoCategory"]."')";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        $tags = explode(',', $data['videoTags']);
        
        for($i = 0; $i < count($tags); $i++){
            $sql = "INSERT INTO `tags` (`user_id`, `tag`) VALUES ('1', '".$tags[$i]."');";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
        }
    }
    
    public function getHighestRating(){
        $conn = $this->getEntityManager()->getConnection();
    
        $sql = "SELECT DISTINCT user_id, MAX(0.2*nb_comments + 0.4*nb_likes + 0.4*nb_video_views) as rating FROM uploads GROUP BY user_id ORDER BY rating DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stmt = $stmt->fetchAll();
        
        return $stmt;
    }
    
    public function getHighestRatingByCategory($categoryId){
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = "SELECT DISTINCT user_id, MAX(0.2*nb_comments + 0.4*nb_likes + 0.4*nb_video_views) as rating FROM uploads WHERE category_id = ".$categoryId."
         GROUP BY user_id ORDER BY rating DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stmt = $stmt->fetchAll();
        
        return $stmt;
    }
}