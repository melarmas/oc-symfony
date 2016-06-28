<?php

namespace OC\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class MainController extends Controller
{
    public function indexAction()
    {
        return $this->render(
            'OCCoreBundle:Main:index.html.twig'
        );
    }

    public function contactAction()
    {
        $this->addFlash(
            'notice',
            "La page de contact n'est pas encore disponible, merci de revenir plus tard!"
        );

        return $this->redirectToRoute('oc_core_accueil');
    }

}
