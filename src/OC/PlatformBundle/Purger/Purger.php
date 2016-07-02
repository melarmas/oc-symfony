<?php
/**
 * Created by PhpStorm.
 * User: mel
 * Date: 01/07/16
 * Time: 11:38
 */

namespace OC\PlatformBundle\Purger;


use Doctrine\ORM\EntityManager;
use OC\PlatformBundle\Entity\Advert;

class Purger
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em )
    {
        $this->em = $em;
    }

    /**
     * @param $days
     * @return int
     *
     * On enlève les adverts vieux
     * et on retourne la quantité enlèvée
     */
    public function purge($days)
    {
        //on calcule la date qui correspond à x jours avant de la date actuelle
        $date = new \DateTime(sprintf('today - %d days', $days));

        //on recupère les advert vieux
        $outdated = $this->em->getRepository('OCPlatformBundle:Advert')
            ->getOutdated($date);

        //on retourne la quantité d'adverts enlèvé
        $total = count($outdated);

        //on enlève les adverts vieux
        /**
         * @var Advert $advert
         */
        foreach($outdated as $advert){
            $this->em->remove($advert);

            //on enlève les adverts skills
            $listAdvertSkills = $advert->getAdvertSkills();

            foreach($listAdvertSkills as $as){
                $this->em->remove($as);
            }
        }

        $this->em->flush();

        return $total;
    }

}