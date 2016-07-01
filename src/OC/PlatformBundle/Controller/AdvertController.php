<?php

namespace OC\PlatformBundle\Controller;


use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Image;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class AdvertController extends Controller
{

    /*
     * Vous avez maintenant toutes les informations nécessaire pour adapter le contrôleur.
     *  L'idée est de correctement utiliser notre méthode getAdverts,
     *  et de retourner une erreur 404 si la page demandée n'existe pas.
     *  Vous devez également modifier la vue pour afficher une liste de toutes les pages disponible,
     *  veillez bien à lui donner toutes les informations nécessaires depuis le contrôleur.
     * */
    public function indexAction($page, Request $request)
    {
        // On sait qu'une page doit être supérieure ou égale à 1
        if ($page < 1) {
            // On déclenche une exception NotFoundHttpException
            throw $this->createNotFoundException('Page "' . $page . '" inexistante.');
        }

        $nbPerPage = $request->query->get('perPage')
            ? $request->query->get('perPage')
            : 3;

        // Ici, on récupére la liste des annonces
        $listAdverts = $this->getDoctrine()
            ->getManager()
            ->getRepository('OCPlatformBundle:Advert')
            ->getAdverts($page, $nbPerPage);

        $nbPages= ceil(count($listAdverts) / $nbPerPage);

        if($page > $nbPages){
            throw $this->createNotFoundException("Page ".$page." n'existe pas.");
        }

        // On appele le template
        return $this->render(
            'OCPlatformBundle:Advert:index.html.twig',
            array(
                'listAdverts' => $listAdverts,
                'nbPages' => $nbPages,
                'page' => $page,
            )
        );
    }

    public function viewAction($id)
    {
        // Ici, on récupére l'annonce correspondante à l'id $id
        $advert = $this->getDoctrine()
            ->getManager()
            ->getRepository('OCPlatformBundle:Advert')
            ->find($id);

        //On vérifie que l'annonce avec cet id existe bien
        if(null === $advert){
            throw $this->createNotFoundException("L'annonce d'id ".$id." n'existe pas.");
        }

        // On appele le template
        return $this->render('OCPlatformBundle:Advert:view.html.twig', array(
            'advert' => $advert,
        ));
    }

    public function addAction(Request $request)
    {
        // La gestion d'un formulaire est particulière, mais l'idée est la suivante :

        // Si la requête est en POST, c'est que le visiteur a soumis le formulaire
        if ($request->isMethod('POST')) {
            // Ici, on s'occupera de la création et de la gestion du formulaire

            $this->addFlash('notice', 'Annonce bien enregistrée.');

            // Puis on redirige vers la page de visualisation de cettte annonce
            return $this->redirectToRoute('oc_platform_view', array('id' => 5));
        }

        // Si on n'est pas en POST, alors on affiche le formulaire
        return $this->render('OCPlatformBundle:Advert:add.html.twig');
    }

    public function editAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        // On récupère l'annonce $id
        $advert = $em->getRepository('OCPlatformBundle:Advert')
            ->find($id);

        if (null === $advert) {
            throw $this->createNotFoundException("L'annonce d'id ".$id." n'existe pas.");
        }


        // Même mécanisme que pour l'ajout
        if ($request->isMethod('POST')) {

            // Étape 2 : On déclenche l'enregistrement
            $em->flush();
            $this->addFlash('notice', 'Annonce bien modifiée.');

            return $this->redirectToRoute('oc_platform_view', array('id' => 5));
        }

        return $this->render(
            'OCPlatformBundle:Advert:edit.html.twig',
            array('advert' => $advert)
        );
    }

    public function deleteAction($id, Request $request)
    {
        // Ici, on récupérera l'annonce correspondant à $id

        $em = $this->getDoctrine()->getManager();

        // On récupère l'annonce $id
        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

        if (null === $advert) {
            throw $this->createNotFoundException("L'annonce d'id ".$id." n'existe pas.");
        }

        if ($request->isMethod('POST')) {
            // Si la requête est en POST, on deletea l'article
            $em->remove($advert);
            $em->flush();

            $request->getSession()->getFlashBag()->add('info', 'Annonce bien supprimée.');

            // Puis on redirige vers l'accueil
            return $this->redirect($this->generateUrl('oc_platform_home'));
        }

        // Si la requête est en GET, on affiche une page de confirmation avant de delete
        return $this->render('OCPlatformBundle:Advert:delete.html.twig', array(
            'advert' => $advert
        ));
    }

    //On récupére les dernières adverts ($limit) depuis la BDD
    //Note: j'ai renommé l'action menu  (par latest) ansi que le template menu (par list)
    // pour la reutiliser
    public function latestAction($limit)
    {
        $listAdverts = $this->getDoctrine()
            ->getManager()
            ->getRepository('OCPlatformBundle:Advert')
            ->findBy(
                array(),
                array('date' => 'desc',),
                $limit,
                0
        );

        return $this->render('OCPlatformBundle:Advert:list.html.twig', array(
            'listAdverts' => $listAdverts
        ));
    }

    public function purgeAction($days)
    {
       $total = $this->get('oc_platform.advert_purger')
            ->purge($days);

        return new Response(sprintf('%d deleted adverts', $total));
    }

}