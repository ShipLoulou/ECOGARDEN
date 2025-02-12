<?php

namespace App\DataFixtures;

use App\Entity\Advice;
use App\Entity\Month;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher
    ) {}
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('user@gmail.com')
            ->setPassword($this->userPasswordHasher->hashPassword($user, "password"))
            ->setPostalCode(44000)
            ->setCity("Nantes")
        ;

        $manager->persist($user);

        $arrayMonth = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        $index = 1;

        $allMonth = [];
        foreach ($arrayMonth as $item) {
            $month = (new Month())
                ->setId($index)
                ->setLibelle($item);

            $manager->persist($month);
            $allMonth[] = $month;
            $index++;
        }

        $firstAdvice = (new Advice())
            ->setContent('Protégez les cultures de la sécheresse avec un bon paillage.')
            ->addMonth($allMonth[5])
            ->addMonth($allMonth[6]);

        $manager->persist($firstAdvice);

        $secondAdvice = (new Advice())
            ->setContent('Protégez vos plantes du froid avec un paillage épais. Taillez les arbres et arbustes caducs, et plantez les bulbes de printemps.')
            ->addMonth($allMonth[9])
            ->addMonth($allMonth[10])
            ->addMonth($allMonth[11]);

        $manager->persist($secondAdvice);

        $manager->flush();
    }
}
