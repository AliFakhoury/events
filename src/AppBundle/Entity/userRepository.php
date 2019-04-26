<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\user;

class userRepository extends EntityRepository{
    public function findAllCriteria($data, $pageNumber, $perPage){
        $users = array();

        $conn = $this->getEntityManager()->getConnection();

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('u')
            ->from('AppBundle:User', 'u');

        if (isset($data["Name"])) {
            if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $data["Name"])) {
                $qb = $qb->andWhere('u.first_name LIKE \'' . $data["Name"] . '%\'');
                $qb = $qb->orWhere('u.last_name LIKE \'' . $data["Name"] . '%\'');
            }
        }

        if (isset($data["lastName"])) {
            if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $data["lastName"])) {
            }
        }

        if (isset($data["email"])) {
            if (!preg_match('/[\'^£$%&*()}{#~?><>,|=_+¬-]/', $data["email"])) {
                $qb = $qb->andWhere('u.user_email LIKE \'' . $data["email"] . '%\''); //print an error or something if one of them exists.
            }
        }

        if (isset($data["status"])) {
            if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $data["status"])) {
                $qb = $qb->andWhere('u.status_id = ' . $data["status"]);
            }
        }

        if (isset($data["category"])) {
            if(sizeof($data["category"]) == 1){
                $category = Array($data["category"]);
                $query = "u.category_id = ".$category[0];
            }else{
                $query = "u.category_id = " . implode(" OR u.category_id = ", $data["category"]);
            }

            $qb = $qb->andWhere($query);
        }else if(isset($data["parent"])){
            if(count($data["parent"]) == 1){
                $qb = $qb->innerJoin(Categories::class, 'c', 'WITH', 'c.parent_ID = '.$data["parent"]);
            }else{
                $query = "c.parent_ID = " . implode(" OR c.parent_ID = ", $data["parent"]);
                $qb = $qb->innerJoin(Categories::class, 'c', 'WITH', $query);
            }
        }

        if (isset($data["from"], $data["to"])) {
            $qb = $qb->andWhere('u.registration_Date between \'' . $data["from"]->format('Y-m-d H:i:s') . '\' and \'' . $data["to"]->format('Y-m-d H:i:s') . '\'');
        } else if (isset($data["from"]) && !isset($data["to"])) {
            $qb = $qb->andWhere('u.registration_Date > \'' . $data["from"]->format('Y-m-d H:i:s').'\'');
        } else if (!isset($data["from"]) && isset($data["to"])) {
            $qb = $qb->andWhere('u.registration_Date < \'' . $data["to"]->format('Y-m-d H:i:s') . '\'');
        }

        $qb = $qb->setFirstResult($perPage*($pageNumber-1))
            ->setMaxResults($perPage);

        $qb = $qb->andWhere('u.is_deleted != 1');

        if(isset($data["parent"]) && !isset($data["category"])){
            $qb = $qb->andWhere('u.category_id = c.category_ID');
        }

        $qb = $qb->getQuery()->execute();

        for($i = 0; $i < sizeof($qb); $i++){
            $sql ='SELECT users.user_id, users.first_name, users.last_name, users.user_email, users.registration_date, user_status.user_status_name, countries.country_name,
                  categories.category_name
            FROM users
            JOIN user_status
              ON user_status.user_status_id = '.$qb[$i]->getStatusId().'
            JOIN countries
              ON countries.country_id = '.$qb[$i]->getCountryId().'
            JOIN categories
              ON categories.category_id = '.$qb[$i]->getCategory().'
            WHERE users.user_ID = '.$qb[$i]->getUserId();

            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $user = $stmt->fetch();

            if(!empty($user)){
                array_push($users, $user);
            }
        }

        return $users;
    }


    public function findByPage($page, $perPage){
        $users = array();
        $conn = $this->getEntityManager()->getConnection();
        $repo = $this->getEntityManager()->getRepository('AppBundle:User');

        $AllUsers = $repo->findBy(
            array(),
            array(),
            $perPage,
            $perPage*($page-1)
        );

        for($i = 0; $i < sizeof($AllUsers); $i++){
            $sql ='SELECT users.user_id, users.first_name, users.last_name, users.registration_date, user_status.user_status_name, countries.country_name,
                  categories.category_name
            FROM users
            JOIN user_status
              ON user_status.user_status_id = '.$AllUsers[$i]->getStatusId().'
            JOIN countries
              ON countries.country_id = '.$AllUsers[$i]->getCountryId().'
            JOIN categories
              ON categories.category_id = '.$AllUsers[$i]->getCategory().'
            WHERE users.user_ID = '.$AllUsers[$i]->getUserId();

            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $user = $stmt->fetch();

            if(!empty($user)){
                array_push($users, $user);
            }
        }

        return $users;

    }

    public function countPages($data, $perPage){
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(u)')
            ->from('AppBundle:User', 'u');

        if (isset($data["Name"])) {
            if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $data["Name"])) {
                $qb = $qb->andWhere('u.first_name LIKE \'' . $data["Name"] . '%\'');
                $qb = $qb->orWhere('u.last_name LIKE \'' . $data["Name"] . '%\'');
            }
        }

        if (isset($data["email"])) {
            if (!preg_match('/[\'^£$%&*()}{ #~?><>,|=_+¬-]/', $data["email"])) {
                $qb = $qb->andWhere('u.user_email LIKE \'' . $data["email"] . '%\''); //print an error or something if one of them exists.
            }
        }

        if (isset($data["status"])) {
            if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $data["status"])) {
                $qb = $qb->andWhere('u.status_id = ' . $data["status"]);
            }
        }

        if (isset($data["category"])) {
            if(sizeof($data["category"]) == 1){
                $category = Array($data["category"]);
                $query = "u.category_id = " . implode(" OR u.category_id = ", $category);
            }else{
                $query = "u.category_id = " . implode(" OR u.category_id = ", $data["category"]);
            }
            $qb = $qb->andWhere($query);
        }else if(isset($data["parent"])){
            if(count($data["parent"]) == 1){
                $qb = $qb->innerJoin(Categories::class, 'c', 'WITH', 'c.parent_ID = '.$data["parent"]);
            }else{
                $query = "c.parent_ID = " . implode(" OR c.parent_ID = ", $data["parent"]);
                $qb = $qb->innerJoin(Categories::class, 'c', 'WITH', $query);
            }
        }

        if (isset($data["from"], $data["to"])) {
            $qb = $qb->andWhere('u.registration_Date between \'' . $data["from"]->format('Y-m-d H:i:s') . '\' and \'' . $data["to"]->format('Y-m-d H:i:s') . '\'');
        } else if (isset($data["from"]) && !isset($data["to"])) {
            $qb = $qb->andWhere('u.registration_Date > \'' . $data["from"]->format('Y-m-d H:i:s').'\'');
        } else if (!isset($data["from"]) && isset($data["to"])) {
            $qb = $qb->andWhere('u.registration_Date < \'' . $data["to"]->format('Y-m-d H:i:s') . '\'');
        }

        $qb = $qb->andWhere('u.is_deleted != 1');

        if(isset($data["parent"]) && !isset($data["category"])){
            $qb = $qb->andWhere('u.category_id = c.category_ID');
        }
        $qb = $qb->getQuery()->execute();

        $numberPages = ceil($qb[0][1] / $perPage);
        return $numberPages;
    }

    public function findByIDView($id){

        if(!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $id)){
            $user = $this->find($id);
            $conn = $this->getEntityManager()->getConnection();
    
            $sql ='SELECT users.user_id, users.age, users.user_email, users.birth_date, users.first_name, users.last_name, users.registration_date, user_status.user_status_name, countries.country_name,
                  categories.category_name
            FROM users
            JOIN user_status
              ON user_status.user_status_id = '.$user->getStatusId().'
            JOIN countries
              ON countries.country_id = '.$user->getCountryId().'
            JOIN categories
              ON categories.category_id = '.$user->getCategory().'
            WHERE users.user_ID = '.$user->getUserId();
    
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $userData = $stmt->fetch();
    
            return $userData;
        }
        
        return null;
    }
    
    public function findByQuestions($data, $page, $perPage){
        if(!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $page) && !preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $perPage)) {
            $conn = $this->getEntityManager()->getConnection();
    
            $arrayOfProfiles = array();
    
            $foundQuestion = false;
    
            $sql = "SELECT DISTINCT users.user_id, users.first_name, users.last_name, country_name, category_name  FROM users
                    INNER JOIN countries ON countries.country_id = users.country_id
                    INNER JOIN categories ON categories.category_id = users.category_id
                    INNER JOIN category_answers ON category_answers.user_id = users.user_id
                    INNER JOIN questions ON questions.quest_id = category_answers.question_id
                    INNER JOIN type_of_questions ON type_of_questions.type_question_id = questions.question_type_id
                    ";
    
            $arrayOfSQL = array();
    
            for($i = 0; $i < sizeof($data); $i++){
                if((string)(int)$data[$i]["name"] == $data[$i]["name"] && $data[$i]["value"] !== "" && !preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $data[$i]["name"]) && !preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $data[$i]["value"])) {
            
                    if($this->countKeyInArray($data, $data[$i]["name"]) === 2 && $data[$i]["value"] == 0 && $data[$i+1]["value"] == 0) {
                        $i++;
                        continue;
                    }
            
                    $foundQuestion = true;
            
                    if($this->countKeyInArray($data, $data[$i]["name"]) === 2){
                
                        if($data[$i]["value"] >= 0 && $data[$i+1]["value"] > 0 && $data[$i]["value"] <= $data[$i+1]["value"]) {
                            $sqlSelect = "(
						SELECT DISTINCT users.user_id FROM users
                        			INNER JOIN category_answers ON category_answers.user_id = users.user_id
									INNER JOIN questions ON questions.quest_id = category_answers.question_id
                        			WHERE questions.quest_id = " . $data[$i]["name"] . " AND category_answers.answer BETWEEN " . $data[$i]["value"] . "
                        			AND  " . $data[$i + 1]["value"] . " AND users.is_deleted != 1
                        )";
                        }else{
                            continue;
                        }
                
                        $i++;
                    }else{
                        $sqlSelect = "(
						SELECT DISTINCT users.user_id FROM users
                        			INNER JOIN category_answers ON category_answers.user_id = users.user_id
									INNER JOIN questions ON questions.quest_id = category_answers.question_id
                        			WHERE questions.quest_id = ".$data[$i]["name"]." AND category_answers.answer = '".$data[$i]["value"]."'
                        			AND users.is_deleted != 1
                    )";
                    }
            
            
                    array_push($arrayOfSQL, $sqlSelect);
                }
            }
    
            if(!$foundQuestion){
                $category = $data[0]["value"];
    
                if(!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $category)) {
    
                    $sql = "SELECT DISTINCT users.user_id, users.first_name, users.last_name, country_name, category_name  FROM users
                    INNER JOIN countries ON countries.country_id = users.country_id
                    INNER JOIN categories ON categories.category_id = users.category_id ";
    
                    if ($category !== null) {
                        $sql .= "WHERE users.category_id = " . $category;
                    }
    
                    $sql .= " LIMIT " . $perPage . " OFFSET " . $perPage * ($page - 1);
    
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
    
                    return $stmt->fetchAll();
                }
            }
    
            if(sizeof($arrayOfSQL) === 0){
                $sql .= " LIMIT ".$perPage." OFFSET ".$perPage*($page-1);
            }else{
                $sql .= " WHERE users.user_id IN ";
        
                $fullSQL = implode('AND users.user_id IN ', $arrayOfSQL);
                $sql .= $fullSQL;
        
                $sql .= " LIMIT ".$perPage." OFFSET ".$perPage*($page-1);
            }
    
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $profiles = $stmt->fetchAll();
    
            if($profiles) {
                array_push($arrayOfProfiles, $profiles);
            }
    
            if(sizeof($arrayOfProfiles) > 0){
                return $arrayOfProfiles[0];
            }
        }
        
        return null;
    }
    
    public function countPagesQuestions($data, $perPage){
        $conn = $this->getEntityManager()->getConnection();
    
        $arrayOfProfiles = array();
    
        $foundQuestion = false;
    
        $sql = "SELECT DISTINCT users.user_id, users.first_name, users.last_name, country_name, category_name  FROM users
                    INNER JOIN countries ON countries.country_id = users.country_id
                    INNER JOIN categories ON categories.category_id = users.category_id
                    INNER JOIN category_answers ON category_answers.user_id = users.user_id
                    INNER JOIN questions ON questions.quest_id = category_answers.question_id
                    INNER JOIN type_of_questions ON type_of_questions.type_question_id = questions.question_type_id
                    ";
    
        $arrayOfSQL = array();
    
        for($i = 0; $i < sizeof($data); $i++){
            if((string)(int)$data[$i]["name"] == $data[$i]["name"] && $data[$i]["value"] !== "" && !preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $data[$i]["name"]) && !preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $data[$i]["value"])) {
            
                if($this->countKeyInArray($data, $data[$i]["name"]) === 2 && $data[$i]["value"] == 0 && $data[$i+1]["value"] == 0) {
                    $i++;
                    continue;
                }
            
                $foundQuestion = true;
            
                if($this->countKeyInArray($data, $data[$i]["name"]) === 2){
                
                    if($data[$i]["value"] >= 0 && $data[$i+1]["value"] > 0 && $data[$i]["value"] <= $data[$i+1]["value"]) {
                        $sqlSelect = "(
						SELECT DISTINCT users.user_id FROM users
                        			INNER JOIN category_answers ON category_answers.user_id = users.user_id
									INNER JOIN questions ON questions.quest_id = category_answers.question_id
                        			WHERE questions.quest_id = " . $data[$i]["name"] . " AND category_answers.answer BETWEEN " . $data[$i]["value"] . "
                        			AND  " . $data[$i + 1]["value"] . " AND users.is_deleted != 1
                        )";
                    }else{
                        continue;
                    }
                
                    $i++;
                }else{
                    $sqlSelect = "(
						SELECT DISTINCT users.user_id FROM users
                        			INNER JOIN category_answers ON category_answers.user_id = users.user_id
									INNER JOIN questions ON questions.quest_id = category_answers.question_id
                        			WHERE questions.quest_id = ".$data[$i]["name"]." AND category_answers.answer = '".$data[$i]["value"]."'
                        			AND users.is_deleted != 1
                    )";
                }
            
            
                array_push($arrayOfSQL, $sqlSelect);
            }
        }
    
        if(!$foundQuestion){
            $category = $data[0]["value"];
        
            if(!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $category)){
                $sql = "SELECT DISTINCT users.user_id, users.first_name, users.last_name, country_name, category_name  FROM users
                    INNER JOIN countries ON countries.country_id = users.country_id
                    INNER JOIN categories ON categories.category_id = users.category_id ";
    
                if($category !== null){
                    $sql .= "WHERE users.category_id = ".$category;
                }
    
                $stmt = $conn->prepare($sql);
                $stmt->execute();
    
                $profiles = $stmt->fetchAll();
    
                return ceil(sizeof($profiles)/$perPage);
            }
    
            return null;
        }
    
        if(sizeof($arrayOfSQL) !== 0){
            $sql .= " WHERE users.user_id IN ";
        
            $fullSQL = implode('AND users.user_id IN ', $arrayOfSQL);
            $sql .= $fullSQL;
        }
    
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $profiles = $stmt->fetchAll();
    
        if($profiles) {
            array_push($arrayOfProfiles, $profiles);
        }
    
        return ceil(sizeof($arrayOfProfiles));
    }
    
    public function countKeyInArray($array, $key){
        $count = 0;
        
        for($i = 0; $i < sizeof($array); $i++){
            if($array[$i]["name"] == $key){
                $count++;
            }
        }
        
        return $count;
        }
    
    public function findUserByIdInArray($array){
        $conn = $this->getEntityManager()->getConnection();
        
        $usersArray = [];
        
        for($i = 0; $i < sizeof($array); $i++){
            $sql = "SELECT * FROM users INNER JOIN categories ON users.category_id = categories.category_id WHERE user_id = ".$array[$i]['user_id'];
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            
            $user = $stmt->fetchAll()[0];
            $user["rating"] = $array[$i]["rating"];
            
            array_push($usersArray, $user);
        }
        
        return $usersArray;
    }

    

}



?>