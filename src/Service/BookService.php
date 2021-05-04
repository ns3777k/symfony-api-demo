<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Book as BookEntity;
use App\Exception\BookNotFoundException;
use App\Exception\CategoryNotFoundException;
use App\Model\Book;
use App\Repository\BookRepository;
use App\Repository\CategoryRepository;

class BookService
{
    private BookRepository $bookRepository;

    private CategoryRepository $categoryRepository;

    public function __construct(BookRepository $bookRepository, CategoryRepository $categoryRepository)
    {
        $this->bookRepository = $bookRepository;
        $this->categoryRepository = $categoryRepository;
    }

    private function mapBook(BookEntity $entity): Book
    {
        return (new Book())
            ->setId((int) $entity->getId())
            ->setTitle($entity->getTitle())
            ->setAuthor($entity->getAuthor())
            ->setIsBestseller($entity->isBestseller())
            ->setPrice($entity->getPrice())
            ->setIsbn($entity->getIsbn())
            ->setPublishedDate($entity->getPublishedDate());
    }

    public function getById(int $id): Book
    {
        $book = $this->bookRepository->find($id);
        if (null === $book) {
            throw new BookNotFoundException();
        }

        return $this->mapBook($book);
    }

    /**
     * @return Book[]
     * @psalm-return array<Book>
     */
    public function getByCategoryId(int $id): array
    {
        $category = $this->categoryRepository->find($id);
        if (null === $category) {
            throw new CategoryNotFoundException();
        }

        return array_map(
            [$this, 'mapBook'],
            $this->bookRepository->findBy(['category' => $category])
        );
    }
}
