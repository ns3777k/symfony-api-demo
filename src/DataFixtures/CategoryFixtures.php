<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public const TECH_CATEGORY = 'technology';

    public function load(ObjectManager $manager): void
    {
        $category = (new Category())
            ->setTitle('Technology')
            ->setSlug('technology');

        $manager->persist($category);
        $manager->flush();

        $this->addReference(self::TECH_CATEGORY, $category);
    }
}
