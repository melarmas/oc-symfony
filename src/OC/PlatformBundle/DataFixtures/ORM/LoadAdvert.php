<?php
/**
 * Created by PhpStorm.
 * User: mel
 * Date: 30/06/16
 * Time: 20:16
 */

namespace OC\PlatformBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\AdvertSkill;
use OC\PlatformBundle\Entity\Application;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadAdvert extends  AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Generator
     */
    private $faker;

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->faker = $this->container->get('faker.generator');

        for($i = 0; $i < 10; $i++){
            $advert = new Advert();

            $advert->setTitle($this->faker->sentence($nbWords = 6, $variableNbWords = true))
                ->setContent($this->faker->paragraph($nbSentences = 3, $variableNbSentences = true))
                ->setAuthor($this->faker->firstNameMale())
            ;

            $manager->persist($advert);

            $nb = mt_rand(0, 5); $offset = mt_rand($nb, 5);

            //categories
            $categories = $manager->getRepository('OCPlatformBundle:Category')
                ->findBy(array(), array(), $nb, $offset);

            foreach($categories as $c){
                $advert->addCategory($c);
            }

            //skills
            $skills = $manager->getRepository('OCPlatformBundle:Skill')
                ->findBy(array(), array(), $nb, $offset);

            foreach($skills as $s){
                $advertSkill = new AdvertSkill();

                $advertSkill->setLevel('Expert');
                $advert->addAdvertSkill($advertSkill);
                $s->addAdvertSkill($advertSkill);

                $manager->persist($advertSkill);
            }

            //applications
            for($j = 0; $j < $nb; $j++){
                $app = new Application();

                $app->setDate(new \DateTime('now'))
                    ->setAuthor($this->faker->firstNameMale())
                    ->setContent($this->faker->paragraph($nbSentences = 3, $variableNbSentences = true))
                ;

                $advert->addApplication($app);

                $manager->persist($app);
            }
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 30;
    }


}