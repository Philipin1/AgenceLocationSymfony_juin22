<?php

namespace App\Controller;

use DateTime;
use App\Entity\Vehicule;
use App\Form\VehiculeType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VehiculeController extends AbstractController
{
    
    /**
     * @Route("/liste_vehicules", name="liste_vehicules")
     * @Route("admin/vehicules", name="admin_app_vehicules")
     */
    public function allVehicules(ManagerRegistry $doctrine): Response
    {
        $vehicules = $doctrine->getRepository(Vehicule::class)->findAll();
        return $this->render('vehicule/admin/adminVehicules.html.twig', [
            'vehicules' =>  $vehicules
        ]);
    }

    /**
     * @Route("/ajout-vehicule", name="admin_ajout_vehicule")
     */
    public function ajout(ManagerRegistry $doctrine, Request $request, SluggerInterface $slugger)
    {
        // si l'utilsateur n'est pas connecté
        if ( !$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->addFlash('error', 'veuillez vous connecter avant de pouvoir accéder à cette page !');
            return $this->redirectToRoute('app_login');

        }

        if (!this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'vous êtes un intrus, que faites vous ici ?');
        return $this->redirectToRoute('app_home');
    
        }

        // on crée un objet vehicule
        $vehicule = new Vehicule();
        
        // on crée le formulaire en liant le formType à l'objet créée
        $form = $this->createForm(VehiculeType::class, $vehicule);

        // on donne accès aux données du formulaire pour validation des données
        $form->handleRequest($request);

        // Si le formulaire est valide 
        if ( $form->isSubmitted() && $form->isValid())
        {
            // on s'occupe d'affecter les données manquantes (qui ne parviennent pas du formulaire)
            $vehicule->setDateEnregistrement(new DateTime("now"));

               // on recupere l'image depuis le formulaire
            $file = $form->get('imageForm')->getData();
            //dd($file);
            //dd($vehicule);
            // le slug permet de modifier une chaine de caractéres : mot clé => mot-cle
            $fileName = $slugger->slug( $vehicule->getTitre() ) . uniqid() . '.' . $file->guessExtension();

            try{
                // on deplace le fichier image recuperé depuis le formulaire dans le dossier parametré dans la partie Parameters du fichier config/service.yaml, avec pour nom $fileName
                $file->move($this->getParameter('photos_vehicules'),  $fileName);
            }catch(FileExeption $e)
            {
                // gérer les exeptions en cas d'erreur durant l'upload
            }

            $vehicule->setPhoto($fileName);


            // on recupère le manager du doctrine 
            $manager = $doctrine->getManager();

            // on persist  l'objet
            $manager->persist($vehicule);

            // on envoie dans la BDD 
            $manager->flush();

           // $this->addFlash('success', 'le vehicule a bien été ajouté !');

           // return $this->redirectToRoute("admin_app_vehicules");

        //}


            return $this->redirectToRoute("admin_app_vehicules");

        }

        return $this->render("vehicule/formulaire.html.twig", [
            'formVehicule' => $form->createView()
        ]);
    }
        /**
         * @Route("/update-vehicule/{id<\d+>}", name="admin_update_vehicule")
         */
        public function update(ManagerRegistry $doctrine, $id, Request $request, SluggerInterface $slugger)
        {
            // On récupère 
            $vehicule = $doctrine->getRepository(Vehicule::class)->find($id);
            //dd($article);

   // On crée le formulaire en liant le formType à l'objet créée
    $form = $this->createForm(VehiculeType::class, $vehicule);

    // on donne accès aux données du formulaire pour la validation des données
    $form->handleRequest($request);
    // si le formulaire est valide
    if ( $form->isSubmitted() && $form->isValid())
    {

         // si une image a bien été ajouté au formulaire
            if($form->get('imageForm')->getData() )
            {
                // on recupere l'image du formulaire
                $imageFile = $form->get('imageForm')->getData();
    
                //on crée un nouveau nom pour l'image
                $fileName = $slugger->slug($vehicule->getTitre()) . uniqid() . '.' . $imageFile->guessExtension();
    
                //on deplace l'image dans le dossier parametré dans service.yaml
                try{
                    $imageFile->move($this->getParameter('photos_vehicules'), $fileName);
                }catch(FileException $e){
                    // gestion des erreur upload
                }
                $vehicule->setPhoto($fileName);
                
            }

    // on récupère le manager du doctrine
        $manager = $doctrine->getManager();
        // on persist l'objet
        $manager->persist($vehicule);
        // on envoie en bdd
        $manager->flush();

        return $this->redirectToRoute("admin_app_vehicules");
    }

    return $this->render("vehicule/formulaire.html.twig", [
        'formVehicule' => $form->createView()
    ]);
} 

/**
 * @Route("/delete_vehicule_{id<\d+>}", name="admin_delete_vehicule")
 */
public function delete($id, VehiculeRepository $repo)
{
    $vehicule = $repo->find($id);
    $repo-remove($vehicule, 1);

 
   // On récupère le vehicule à supprimer
 //  $vehicule = $doctrine->getRepository(Vehicule::class)->find($id);
   // on récupère le manager de doctrine
  // $manager = $doctrine->getManager();
   // on prépare la suppression du vehicule
  // $manager->remove($vehicule);
   // on execute l'action (suppression)
   // $manager->flush();

  // return $this->redirectToRoute("app_vehicules");
}

    /**
     * @route("/vehicule_{id}", name="app_vehicule")
     */
    public function showVehicule($id, ManagerRegistry $doctrine)
    {
        $vehicule = $doctrine->getRespository(Vehicule::class)->find($id);

        return $this->render("vehicule/allVehicules.html.twig", [
            'vehicule' =>$vehicule
        ]);
    }


}

        
        


        




    

