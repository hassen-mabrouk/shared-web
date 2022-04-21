<?php

namespace App\Controller;
use Knp\Component\Pager\PaginatorInterface;

use App\Entity\Event;
use App\Form\EventType;
use App\Service\MailerService;
use App\Entity\Reservation;
use App\Form\ReservationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class HostController extends AbstractController
{
    /**
     * @Route("/back", name="app_host")
     */
    public function index(): Response
    {
        return $this->render('host/index.html.twig', [
            'controller_name' => 'HostController',
        ]);
    }


    /**
     * @Route("/listevent", name="listevent")
     */
    public function listevent(Request $request,PaginatorInterface $paginator): Response
    {

        $repo = $this->getDoctrine()->getRepository(Event::class);
        $ev =$repo->findAll();
        $ev = $paginator->paginate(
            $ev, // Requête contenant les données à paginer
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            3 // Nombre de résultats par page
        );
        return $this->render('host/listevent.html.twig', array("events" => $ev));
    }


    /**
     * @Route("/listres", name="listres")
     */

    public function listresv(Request $request,PaginatorInterface $paginator): Response
    {

        $repo = $this->getDoctrine()->getRepository(Reservation::class);
        $r =$repo->findAll();
        $r = $paginator->paginate(
            $r, // Requête contenant les données à paginer
            $request->query->getInt('page', 1),
            3 // Nombre de résultats par page
        );
        return $this->render('host/listres.html.twig', array("res" => $r));
    }


    /**
     * @Route("/addevent", name="addevent")
     */


    public function addevent(Request $request,MailerService $mailer)
    {
        $event = new Event(); //objet create instance
        $form = $this->createForm(EventType::class, $event);

        $form->add('Ajouter', SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
        $nomevent= $form->get('nomevent')->getData();
           $photo = $form->get('image')->getData();
            $fileName = bin2hex(random_bytes(6)).'.'.$photo->guessExtension();
            $photo->move ($this->getParameter('images_directory'),$fileName);
            $event->setImage($fileName);
            $em = $this->getDoctrine()->getManager(); //get manager bch tbadel fel base
                $em->persist($event); //$c baathtou feragh tawa b des donnees
                $em->flush();
            $mailer->sendEmailevent("Confirmation d'ajout d'évenement",
                "tnsharedinc@gmail.com",
                "testbentest152@gmail.com"
            );

            return $this->redirectToRoute('listevent');
        }
            return $this->render("host/addevent.html.twig", array('form' => $form->createView())); //return the form

        }

    /**
     * @Route("/supp/{idevent}",name="delete")
     */
    public function delete($idevent): Response
    {
        $v = $this->getDoctrine()->getRepository(Event::class)->find($idevent);
        $em = $this->getDoctrine()->getManager();
        $em->remove($v);
        $em->flush();
        return $this->redirectToRoute("listevent");
    }

    /**
     * @Route("/update/{idevent}", name="update")
     */
    public function updateevent(Request $request,$idevent)
    {
        $event = $this->getDoctrine()->getRepository(Event::class)->find($idevent);
        $form = $this->createForm(EventType::class, $event);
        $form->add('modifier',SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $file = $form->get('image')->getData();
            $fileName = bin2hex(random_bytes(6)).'.'.$file->guessExtension();
            $file->move ($this->getParameter('images_directory'),$fileName);
            $event->setImage($fileName);

            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute('listevent');
        }
        return $this->render("host/modifyevent.html.twig",array('form'=>$form->createView()));
    }



}