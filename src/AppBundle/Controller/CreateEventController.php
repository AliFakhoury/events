<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Event;
use Doctrine\DBAL\Types\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Created by PhpStorm.
 * User: fakho
 * Date: 5/9/2018
 * Time: 9:34 PM
 */
class CreateEventController extends Controller {
    /**
     * @Route("/Events/createEvent",name="CreateEvent")
     */
    public function createEventAction(Request $request){
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $form = $this->getForm();
    
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();
            $event = new Event();
            
            $event->setEventName($data['EventName']);
            $event->setEventCategoryID($data['EventCategory']);
            $event->setLocation($data['Location']);
            $event->setEventHolderID('1');
            $event->setDescription($data['Description']);
            $event->setEventDate($data['Date']);
            $event->setMaxAttenders($data['MaxAttenders']);
            $event->setEventStatusID(0);
            $event->setIsDeleted(0);
            $event->setAttenders(0);
            $em->persist($event);
            $em->flush();
        }
        
        return $this->render('default/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    public function getForm(){
        $categoryRepository = $this->getDoctrine()->getRepository('AppBundle:Categories');
        
        $categories = $categoryRepository->findNotParentCategories();
        
        $categoriesArray = [];
    
        for($i = 0; $i < count($categories); $i++){
            $categoriesArray[$categories[$i]->getCategoryName()] = $categories[$i]->getCategoryID();
        }
        
        $form = $this->createFormBuilder()
            ->add("EventName", TextType::class)
            ->add("EventCategory", ChoiceType::class, [
                'choices' => $categoriesArray,
            ])
            ->add("Location", TextType::class)
            ->add("Description", TextType::class)
            ->add("Date", DateType::class,[
                'required'  => false,
                'html5' => false,
            ])
            ->add("MaxAttenders", \Symfony\Component\Form\Extension\Core\Type\IntegerType::class,[
                'attr' => [
                    'min' => 0
                ]
            ])
            ->add("Submit", SubmitType::class)
            ->getForm();
        
        return $form;
    }
}