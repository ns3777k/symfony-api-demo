<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\Category;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class BookFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var Category $category */
        $category = $this->getReference(CategoryFixtures::TECH_CATEGORY);

        $book = new Book();
        $book->setTitle('Kubernetes 2.0');
        $book->setAuthor('Vasya Pupkin');
        $book->setIsbn('978-1111105460');
        $book->setPublishedDate(new DateTime());
        $book->setPrice(56.0);
        $book->setIsBestseller(true);
        $book->setCategory($category);

        $manager->persist($book);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
        ];
    }
}
