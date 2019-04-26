<?php
/**
 * Created by PhpStorm.
 * User: fakho
 * Date: 9/24/2018
 * Time: 10:52 PM
 */

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LeaderBoardController extends Controller {
    /**
     * @Route("/leaderBoard",name="CreateEvent")
     */
    public function leaderBoardAction(Request $request){
        $uploadRepository = $this->getDoctrine()->getRepository('AppBundle:Upload');
        $usersRepository = $this->getDoctrine()->getRepository('AppBundle:User');
        $categoryRepository = $this->getDoctrine()->getRepository('AppBundle:Categories');
        
        $highestRatedUsers = $uploadRepository->getHighestRating(0);
        $categories = $categoryRepository->getAllCategoriesNotObject();
        
        dump($categories);
        
        return $this->render('Leaderboard/leaderBoard.html.twig', [
            'categories' => $categories,
        ]);
    }
    
    /**
     * @Route("/getHighestRatingByCategory", name="getHighestRating")
     */
    public function getRatings(Request $request){
        if($request->isXmlHttpRequest()) {
            $category = $request->get("category");
    
            $usersRepository = $this->getDoctrine()->getRepository('AppBundle:User');
            $uploadRepository = $this->getDoctrine()->getRepository('AppBundle:Upload');
    
            if($category == -1){
                $highestRatedUsers = $uploadRepository->getHighestRating();
                $users = $usersRepository->findUserByIdInArray($highestRatedUsers);
    
                return new JsonResponse($users);
            }
            
            $highestRatedUsers = $uploadRepository->getHighestRatingByCategory($category);
            $users = $usersRepository->findUserByIdInArray($highestRatedUsers);
            
            return new JsonResponse($users);
        }
        
        return null;
    }
}