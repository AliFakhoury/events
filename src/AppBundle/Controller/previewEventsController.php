<?php
/**
 * Created by PhpStorm.
 * User: fakho
 * Date: 5/9/2018
 * Time: 10:37 PM
 */

namespace AppBundle\Controller;

use AppBundle\Entity\EventAttender;
use AppBundle\Entity\eventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class previewEventsController extends Controller {
    /**
     * @Route("/Events/SingleEvent/{eventID}", name="showEvent")
     */
    public function showEventAction(Request $request, $eventID){
        $eventsRepository = $this->getDoctrine()->getRepository('AppBundle:Event');
        
        $event = $eventsRepository->findEvent($eventID)[0];
        
        return $this->render('default/event.html.twig', [
            'event' => $event,
        ]);
    }
    
    /**
     * @Route("Events", name="showEventsPage")
     */
    public function showEventPageAction(Request $request){
        $session = new Session();
        
        $eventsRepository = $this->getDoctrine()->getRepository('AppBundle:Event');
        
        $form = $this->getForm();
        
        $countPages = $eventsRepository->countPages(null, 8);
        
        return $this->render('default/eventsNew.html.twig', [
            'form'   => $form->createView(),
            'countPages' => $countPages,
        ]);
    }
    
    /**
     * @Route("Events/registerEvents", name="RegisterEvent")
     */
    public function registerEventAction(Request $request){
        if($request->isXmlHttpRequest()) {
            $userID = $request->get('userId');
            $eventID = $request->get('eventId');
            
            $eventsRepo = $this->getDoctrine()->getRepository('AppBundle:Event');
    
            $em = $this->getDoctrine()->getManager();
    
            $event =  $eventsRepo->find($eventID);
    
            if($event->getAttenders() >= $event->getMaxAttenders()){
                return new JsonResponse(false);
            }
    
            $event->setAttenders($event->getAttenders()+1);
    
            $em->persist($event);
            $em->flush();
    
            $eventAttender = new EventAttender();
            $eventAttender->setEventID($eventID);
            $eventAttender->setUserID($userID);
    
            $em->persist($eventAttender);
            $em->flush();
    
            return new JsonResponse(true);
        }
    
        return new JsonResponse(false);
    }
    
    public function getForm(){
        $categoryRepo = $this->getDoctrine()->getRepository('AppBundle:Categories');
        
        $categories = $categoryRepo->findNotParentCategories();
    
        $categoryArray = array();
    
        foreach($categories as $value){
            $categoryArray[$value->getCategoryName()] = $value->getCategoryID();
        }
        
        $form = $this->createFormBuilder()
            ->add('eventName', TextType::class, [
                'required'  => false,
            ])
            ->add('category', ChoiceType::class, [
                'required'  => false,
                'choices' => $categoryArray,
            ])
            ->add('from',DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'html5' => false,
                'attr' =>['class' => 'js-datepicker']
            ])
            ->add('to', DateType::class, [
                'required'  => false,
                'html5' => false,
                'widget' => 'single_text',
                'attr' =>['class' => 'js-datepicker']
            ])
            ->add('Search', SubmitType::class)
            ->add('reset_form',SubmitType::class)
            ->getForm();
        
        return $form;
    }
    
    /**
     * @Route("/getEvents", name="getEvents")
     */
    public function getEvents(Request $request){
        if($request->isXmlHttpRequest()){
            $eventsRepo = $this->getDoctrine()->getRepository('AppBundle:Event');
            
            $page = $request->get('page');
            $data = $request->get('data');
            
            $events = $eventsRepo->findEventByData($data,$page, 8);
            return new JsonResponse($events);
        }
        
        return new JsonResponse(null);
    }
    
    /**
     * @Route("/getEventPages", name="getEventPages")
     */
    public function getEventPages(Request $request){
        if($request->isXmlHttpRequest()){
            $eventsRepo = $this->getDoctrine()->getRepository('AppBundle:Event');
            
            $data = $request->get('data');
            $eventPages= $eventsRepo->findPagesByData($data, 8);
            
            return new JsonResponse($eventPages);
        }
        
        return new JsonResponse(null);
    }
    
}