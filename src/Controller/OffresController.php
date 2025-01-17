<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Classroom;
use App\Entity\Offres;
use App\Entity\User;
use App\Form\AvisType;
use App\Form\OffresType;
use App\Repository\OffresRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerAwareInterface;
use mofodojodino\ProfanityFilter\Check;

/**
 * @Route("/offres")
 */
class OffresController extends AbstractController
{

    // jawha behy hedhy sur
    /**
     * @Route("/Offres_mobile", name="Offres_mobile")
     */
    public function AfficherOffre(EntityManagerInterface $entityManager,OffresRepository $repo,NormalizerInterface $Normalizer)
    {

        $offres = $this->getDoctrine()->getRepository(Offres::class)->findAll();

        //transforme  les objets(destinations) en un array json
        $json = $Normalizer->normalize($offres, 'json', ['groups' => 'offres']);

        return new Response(json_encode($json));
    }
    // jawha behy hedhy sur
    /**
     * @Route("/addOffres_mobile", name="mobile_create")
     */
    public function addOffre(Request $request, NormalizerInterface $Normalizer): Response
    {
        $entitymanager = $this->getDoctrine()->getManager();
        $offre = new Offres();
        $offre->setNom($request->query->get('nom'));
        $offre->setDescription($request->query->get('description'));
        $prixfloat = floatval($request->query->get('prix'));
        $offre->setPrix($prixfloat);
        $offre->setEtat(false);
        $offre->setVille($request->query->get('ville'));
        $offre->setCateg($request->query->get('categ'));


      /*  $file = strval($request->files->get('image'));
        $newFilename = md5(uniqid()).'.'.$file->guessExtension();
        try {
            $file->move(
                $this->getParameter('upload_directory'),
                $newFilename
            );
        } catch (FileException $e) {
        }
        $offre->setImage($newFilename);*/

        $entitymanager->persist($offre);
        $entitymanager->flush();
        //return new Response('categorie added successfully');
        $json = $Normalizer->normalize($offre, 'json', ['groups' => 'offres']);

        return new Response(json_encode($json));
    }
// jawha behy hedhy sur
    /**
     * @Route("/deleteOffre/{id}", name="mobile_delete")
     */
    public function deleteOffre(Request $request, NormalizerInterface $Normalizer, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $offre = $this->getDoctrine()->getRepository(Offres::class)->find($id);
        $em->remove($offre);
        $em->flush();
        return $this->json(["response" => "Offre deleted successfully"]);
    }

    /**
     * @Route("/send/{id}", name="send", methods={"GET", "POST"})
     */

    public function Approu(Request $request,Offres $offre,  OffresRepository $repo,\Swift_Mailer $mailer): Response
    {


        $message = (new \Swift_Message('Confirmation'))

        ->setFrom('yeektheb@gmail.com')
        ->setTo($offre->getIdUser()->getEmail())
        ->setBody($this->renderView('offres/test.html.twig',
        ['offre' => $offre,
         'user'=>$offre->getIdUser()
            ])
            ,'text/html');
        $mailer ->send($message);

        $nbr = $repo->Approuver($offre->getIdOffre());

        return $this->redirectToRoute('Approuver');

      
    }

    /**
     * @Route("/", name="app_offres_index", methods={"GET"})
     */
    public function index(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $cin = $this->getUser()->getRoles();
        $offres = $entityManager
            ->getRepository(Offres::class)
            ->findAll();
        dump($offres);
        if ($this->getUser() ){
            $userCon = $this->getUser()->getCin();
            $userName = $this->getUser()->getNom();
            $ci = $this->getUser();

        }else {
            $userCon = 0;
            $userName = "";
        }
        return $this->render('offres/GridOffres.html.twig', [
            'offres' => $offres,
            'userCon' => $userCon,
            'userName' => $userName,
            'Usercin' =>$ci,'userRole' =>$cin,
        ]);
    }
    /**
     * @Route("/mine", name="mine", methods={"GET"})
     */
    public function MesOffre(EntityManagerInterface $entityManager): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $cin = $this->getUser()->getRoles();
        $Usercin = $this->getUser();

        $offres = $entityManager
            ->getRepository(Offres::class)
            ->findBy([
                'idUser' => $Usercin,
            ]);

        return $this->render('offres/MesOffres.html.twig', [
            'offres' => $offres,
            'user'=>$cin,
            'Usercin'=>$Usercin,
        ]);
    }
        /**
     * @Route("/dashboard", name="dashboard", methods={"GET"})
     */
    public function MesStatistique(OffresRepository $repo): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

      
        $cin = $this->getUser()->getRoles();
     
      
        $Maison = $repo->findBy([
            'idUser' => $cin,
            'categ' => "Maison",
        ]);
        $Appartement = $repo->findBy([
            'idUser' => $cin,
            'categ' => "Appartement",
        ]);
        $Chambre = $repo->findBy([
            'idUser' => $cin,
            'categ' => "Chambre",
        ]);
        $Voiture = $repo->findBy([
            'idUser' => $cin,
            'categ' => "Voiture",
        ]);
        $Vélo = $repo->findBy([
            'idUser' => $cin,
            'categ' => "Vélo",
        ]);
        $Moto = $repo->findBy([
            'idUser' => $cin,
            'categ' => "Moto",
        ]);

                return $this->render('offres/Dashboard.html.twig', [
                'Maison' => count($Maison),
                'Appartement' => count($Appartement),
                'Chambre' => count($Chambre),
                'Voiture' => count($Voiture),
                'Vélo' => count($Vélo),
                'Moto' => count($Moto),
                'user'=>$cin,
                ]);
    }

     /**
     * @Route("/Approuver", name="Approuver", methods={"GET", "POST"})
     */
    public function Approuver(EntityManagerInterface $entityManager): Response
    {

        
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $cin = $this->getUser()->getRoles();
        $ci = $this->getUser();

        $offres = $entityManager
            ->getRepository(Offres::class)
            ->findAll([
                'etat' => "0",
            ]);

       

                return $this->render('offres/ApprouverOffres.html.twig', [
                    'offres' => $offres,
                    'user' =>$cin,
                    'Usercin' =>$ci,

                ]);
    }

    /**
     * @Route("/list", name="liste_offre", methods={"GET"})
     */
    public function listeindex(Request $request,EntityManagerInterface $entityManager,PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $cin = $this->getUser()->getRoles();
        $offres = $entityManager
            ->getRepository(Offres::class)
            ->findAll();
        dump($offres);
        if ($this->getUser() ){
            $userCon = $this->getUser()->getCin();
            $userName = $this->getUser()->getNom();
            $ci = $this->getUser();
        }else {
            $userCon = 0;
            $userName = "";
        }


       $liste_Offres = $paginator->paginate($offres,$request->query->getInt('page',1),2);
        return $this->render('offres/ListesOffres.html.twig', [
            'offres' => $offres,
            'filtre'=>$liste_Offres,
            'userCon' => $userCon,
            'userName' => $userName,
            'Usercin' =>$ci,
            'userRole'=>$cin,

        ]);
    }


    /**
     * @Route("/new", name="app_offres_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {


   
        $cin = $this->getUser();
        $us = $this->getUser()->getRoles();
        $offre = new Offres();


        $form = $this->createForm(OffresType::class, $offre);
        $form->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {


            $file = $form->get('image')->getData();
            $newFilename = md5(uniqid()).'.'.$file->guessExtension();
            try {
                $file->move(
                    $this->getParameter('upload_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
            }


            $offre->setIdUser($cin);
            $offre->setEtat(false);
             if($offre->getCateg() == 'Appartement' || $offre->getCateg() == 'Maison' || $offre->getCateg() == 'Chambre'){
                 $offre->setType("Logement");
             }
            elseif($offre->getCateg() == 'Voiture' || $offre->getCateg() == 'Moto' || $offre->getCateg() == 'Velo'){
                $offre->setType("MoyenDeTransport");
            }
            else{
                $offre->setType("Horeca");
            }
            $offre->setImage($newFilename);

            

            $entityManager->persist($offre);
            $entityManager->flush();



           



         
        }

        return $this->render('offres/new.html.twig', [
            'offre' => $offre,
            'form' => $form->createView(),
            'user'=>$us,
        ]);
    }

    /**
     * @Route("/{idOffre}", name="app_offres_show", methods={"GET"})
     */

    public function show(Offres $offre,Request $request ,EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $cin = $this->getUser()->getRoles();

        $cinn = $this->getUser()->getCin();

        $Usercin = $this->getUser();


        $query= $entityManager->createQuery('Select avg(a.note) FROM App\Entity\Avis a where a.idoffre=:of')
        ->setParameter('of',$offre->getIdOffre());
        $moyenne=$query->getSingleScalarResult();
        $moyenne=round($moyenne,2);
        $query= $entityManager->createQuery('Select count(a.note) FROM App\Entity\Avis a where a.idoffre=:off')
        ->setParameter('off',$offre->getIdOffre());
        $nbr=$query->getSingleScalarResult();
        $avisafficher = $entityManager
            ->getRepository(Avis::class)
            ->findBy(["idoffre"=>$offre->getIdOffre()]);

        $check = new Check( '../config/profanities.php');
        $avis = new Avis();

        $form = $this->createForm(AvisType::class,$avis);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $verifier = $form['commentaire']->getData();
            $hasProfanity = $check->hasProfanity($verifier);
            if ($hasProfanity == false) {

                $finduer = $this -> getDoctrine()->getRepository(User::class)->find(9637898);
                $idoffre2 = $this -> getDoctrine()->getRepository(Offres::class)->find(10);
                $avis->setIdguest($cinn);
                $avis->setIdoffre($offre->getIdOffre());
                $avis->setDatecreation(new \DateTime());
                $entityManager->persist($avis);
                $entityManager->flush() ;
                $good="good";
                return $this->redirectToRoute('app_avis',[
                    "good"=>$good
                ]);
            }else {
                $bad="bad";
                return $this->redirectToRoute('app_avis',[
                    "bad"=>$bad
                ]);
            }
        }
        if ($this->getUser() ){
            $userCon = $this->getUser()->getCin();
            $userName = $this->getUser()->getNom();
            $ci = $this->getUser();
        }else {
            $userCon = 0;
            $userName = "";
        }

        $users = $this->getDoctrine()
            ->getRepository(User::class)
            ->findBy(['cin' => $offre->getIdUser()]);


        return $this->render('offres/show.html.twig', [
            'offre' => $offre,
            'user'=>$offre->getIdUser(),'avis2' => $avis,'avis' => $avisafficher,'moyenne' => $moyenne,'nbr' => $nbr,
            'form' => $form->createView(),'userName' => $userName,
        ]);
    }
    /**
     * @Route("/{idOffre}/edit", name="app_offres_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Offres $offre, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OffresType::class, $offre);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $file = $form->get('image')->getData();
            $newFilename = md5(uniqid()).'.'.$file->guessExtension();
            try {
                $file->move(
                    $this->getParameter('upload_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
            }
            $offre->setImage($newFilename);


            $entityManager->flush();


            return $this->redirectToRoute('mine', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('offres/edit.html.twig', [
            'offre' => $offre,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{idOffre}", name="app_offres_delete", methods={"POST"})
     */
    public function delete(Request $request, Offres $offre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$offre->getIdOffre(), $request->request->get('_token'))) {
            $entityManager->remove($offre);
            $entityManager->flush();
        }

        return $this->redirectToRoute('mine', [], Response::HTTP_SEE_OTHER);
    }


    /*_______________________________________Mobile_____________________________________*/

}
