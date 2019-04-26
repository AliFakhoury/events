<?php
/**
 * Created by PhpStorm.
 * User: fakho
 * Date: 2/8/2018
 * Time: 11:12 AM
 */

namespace AppBundle\Entity;


use Doctrine\ORM\EntityRepository;

class QuestionsRepository extends EntityRepository {
    public function findAllQuestions($data, $perPage, $pageNumber){
        $questions = array();

        $conn = $this->getEntityManager()->getConnection();

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('q')
            ->from('AppBundle:Questions', 'q');

        if(isset($data["QuestionName"])){
            if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $data["QuestionName"])) {
                $qb = $qb->andWhere('q.quest_name LIKE \'' . $data["QuestionName"] . '%\'');
            }
        }

        if(isset($data["Question"])){
            if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $data["Question"])) {
                $qb = $qb->andWhere('q.question LIKE \'' . $data["Question"] . '%\'');
            }
        }

        if(isset($data["Category"])){
            if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $data["Category"])) {
                $qb = $qb->innerJoin(CategoryQuestions::class,'c','WITH','c.quest_id = q.quest_ID');
                $qb = $qb->innerJoin(Categories::class, 'cat', 'WITH','c.category_id = cat.category_ID');
                $qb = $qb->andWhere('c.category_id = '.$data["Category"]);
            }
        }

        $qb = $qb->setFirstResult($perPage*($pageNumber-1))
            ->setMaxResults($perPage);

        $qb = $qb->andWhere('q.is_deleted != 1');

        $qb = $qb->getQuery()->execute();

        for($i = 0; $i < count($qb); $i++){
            $sql = "SELECT category_name FROM categories 
                    INNER JOIN questions_in_category
                          ON categories.category_id = questions_in_category.category_id 
                          WHERE questions_in_category.quest_id = ".$qb[$i]->getQuestID();

            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $categories = $stmt->fetchAll(7);

            $questionObject = [
                'quest_id' => $qb[$i]->getQuestID(),
                'quest_name' => $qb[$i]->getQuestName(),
                'categories' => implode(", ",$categories),
                'question' => $qb[$i]->getQuestion()
            ];

            array_push($questions, $questionObject);
        }

        return $questions;
    }

    public function findQuestionsByCategory($data, $loadMore){
        
        if($loadMore == 1){
            $qb = $this->getEntityManager()->createQueryBuilder()
                ->select('q')
                ->from('AppBundle:Questions', 'q');
    
            if(isset($data)){
                if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $data)) {
                    $qb = $qb->innerJoin(CategoryQuestions::class,'c','WITH','c.quest_id = q.quest_ID')
                        ->innerJoin(Categories::class, 'cat', 'WITH','c.category_id = cat.category_ID')
                        ->andWhere('c.category_id = '.$data);
                }
            }
    
            $qb = $qb->andWhere('q.is_deleted != 1')
                ->setFirstResult(1);
    
            $qb = $qb->getQuery()->execute();
            return $qb;
        }
        
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('q')
            ->from('AppBundle:Questions', 'q');

        if(isset($data)){
            if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $data)) {
                $qb = $qb->innerJoin(CategoryQuestions::class,'c','WITH','c.quest_id = q.quest_ID')
                          ->innerJoin(Categories::class, 'cat', 'WITH','c.category_id = cat.category_ID')
                          ->andWhere('c.category_id = '.$data);
            }
        }
        
        $qb = $qb->andWhere('q.is_deleted != 1')
                 ->setFirstResult(0)
                 ->setMaxResults(3);
        
        $qb = $qb->getQuery()->execute();
        return $qb;
    }
    
    public function countPages($data, $perPage){
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(q)')
            ->from('AppBundle:Questions', 'q');

        if(isset($data["QuestionName"])){
            if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $data["QuestionName"])) {
                $qb = $qb->andWhere('q.quest_name LIKE \'' . $data["QuestionName"] . '%\'');
            }
        }

        if(isset($data["Question"])){
            if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $data["Question"])) {
                $qb = $qb->andWhere('q.question LIKE \'' . $data["Question"] . '%\'');
            }
        }

        if(isset($data["Category"])){
            if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $data["Category"])) {
                $qb = $qb->innerJoin(CategoryQuestions::class,'c','WITH','c.quest_id = q.quest_ID');
                $qb = $qb->innerJoin(Categories::class, 'cat', 'WITH','c.category_id = cat.category_ID');
                $qb = $qb->andWhere('c.category_id = '.$data["Category"]);
            }
        }

        $qb = $qb->andWhere('q.is_deleted != 1');
        $qb = $qb->getQuery()->execute();

        $numberPages = ceil($qb[0][1] / $perPage);

        return $numberPages;
    }
}