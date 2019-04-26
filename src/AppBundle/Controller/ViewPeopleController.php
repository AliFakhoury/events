<?php
/**
 * Created by PhpStorm.
 * User: fakho
 * Date: 6/6/2018
 * Time: 11:04 PM
 */

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class ViewPeopleController extends Controller {
    /**
     * @Route("/Profiles", name="viewPeople")
     */
    public function showProfilesAction(Request $request){
        $session = new Session();
        
        $profileRepository = $this->getDoctrine()->getRepository('AppBundle:User');
        
        $form = $this->getForm();
        
        $countPages = $profileRepository->countPages(null, 8);
        
        return $this->render('profiles/viewProfiles.html.twig', [
            'form'   => $form->createView(),
            'countPages' => $countPages,
        ]);
    }
    public function getForm(){
        $categoryRepo = $this->getDoctrine()->getRepository('AppBundle:Categories');
        
        $categories = $categoryRepo->findNotParentCategories();
        
        $categoryArray = array();
        
        foreach($categories as $value){
            $categoryArray[$value->getCategoryName()] = $value->getCategoryID();
        }
        
        $form = $this->createFormBuilder()
            ->add('category', ChoiceType::class, [
                'required'  => false,
                'choices' => $categoryArray])
            ->add('Search', SubmitType::class)
            ->add('reset_form',SubmitType::class)
            ->getForm();
    
        return $form;
    }
    
    /**
     * @Route("/getQuestions", name="getQuestions")
     */
    public function getFormQuestions(Request $request){
        $questionsRepo = $this->getDoctrine()->getRepository('AppBundle:Questions');
        
        $category = $request->get('category');
        $loadMore = $request->get('loadMore');
        
        if($request->isXmlHttpRequest()){
            $questions = $questionsRepo->findQuestionsByCategory($category, $loadMore);
            return new JsonResponse($questions);
        }
        
        return new JsonResponse(null);
    }
    
    /**
     * @Route("/getProfiles", name="getProfiles")
     */
    public function getProfiles(Request $request){
        if($request->isXmlHttpRequest()){
            $profilesRepo = $this->getDoctrine()->getRepository('AppBundle:User');
            
            $page = $request->get('page');
            $data = $request->get('data');
            
            $profiles = $profilesRepo->findByQuestions($data, $page, 8);
            
            return new JsonResponse($profiles);
        }
        
        return new JsonResponse(null);
    }
    
    /**
     * @Route("/getNumPages", name="numPages")
     */
    public function getPages(Request $request){
        if($request->isXmlHttpRequest()){
            $profilesRepo = $this->getDoctrine()->getRepository('AppBundle:User');
            
            $data = $request->get('data');

            $countPages = $profilesRepo->countPagesQuestions($data, 8);
            
            return new JsonResponse($countPages);
        }
        
        return new JsonResponse(null);
    }
    
    /**
     * @Route("/getQuestionType", name="getQuestionType")
     */
    public function getQuestionType(Request $request){
        if($request->isXmlHttpRequest()){
            $typeOfQuestionRepo = $this->getDoctrine()->getRepository('AppBundle:typeOfQuestion');
        
            $id = $request->get('data');
        
            $typeOfQuestion = $typeOfQuestionRepo->findTypeById($id);
            
            return new JsonResponse($typeOfQuestion);
        }
    
        return new JsonResponse(null);
    }
    
    /**
     * @Route("/getQuestionCount", name="getQuestionCount")
     */
    public function getQuestionCount(Request $request){
        if($request->isXmlHttpRequest()){
            $questionsInCategoryRepo = $this->getDoctrine()->getRepository('AppBundle:CategoryQuestions');
            
            $categoryId = $request->get("category");
            
            $countQuestions = $questionsInCategoryRepo->getCountQuestionsByCategory($categoryId);
            
            return new JsonResponse($countQuestions);
        }
        
        return new JsonResponse(null);
    }
}