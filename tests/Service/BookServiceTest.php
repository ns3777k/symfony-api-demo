<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Book;
use App\Exception\BookNotFoundException;
use App\Exception\CategoryNotFoundException;
use App\Model\Book as BookModel;
use App\Repository\BookRepository;
use App\Repository\CategoryRepository;
use App\Service\BookService;
use App\Tests\AbstractTestCase;
use DateTime;
use DateTimeInterface;
use stdClass;

class BookServiceTest extends AbstractTestCase
{
    private function createBook(int $id, DateTimeInterface $publishedDate): Book
    {
        $book = (new Book())
            ->setAuthor('Testovich')
            ->setIsBestseller(true)
            ->setPrice(15.0)
            ->setIsbn('test')
            ->setPublishedDate($publishedDate)
            ->setTitle('Test');

        $this->setEntityId($book, $id);

        return $book;
    }

    private function mapBook(Book $book): BookModel
    {
        return (new BookModel())
            ->setTitle($book->getTitle())
            ->setAuthor($book->getAuthor())
            ->setPublishedDate($book->getPublishedDate())
            ->setIsBestseller($book->isBestseller())
            ->setIsbn($book->getIsbn())
            ->setId((int) $book->getId())
            ->setPrice($book->getPrice());
    }

    public function testGetByIdNotFound(): void
    {
        $this->expectExceptionObject(new BookNotFoundException());
        $bookRepository = $this->createMock(BookRepository::class);
        $bookRepository->expects($this->once())
            ->method('find')
            ->with(1234)
            ->willReturn(null);

        $categoryRepository = $this->createMock(CategoryRepository::class);

        $service = new BookService($bookRepository, $categoryRepository);
        $service->getById(1234);
    }

    public function testGetById(): void
    {
        $date = new DateTime();
        $book = $this->createBook(1234, $date);
        $bookRepository = $this->createMock(BookRepository::class);
        $bookRepository->expects($this->once())
            ->method('find')
            ->with($book->getId())
            ->willReturnCallback(fn () => $book);

        $categoryRepository = $this->createMock(CategoryRepository::class);

        $service = new BookService($bookRepository, $categoryRepository);
        $expected = $this->mapBook($book);

        $this->assertEquals($expected, $service->getById((int) $book->getId()));
    }

    public function testGetByCategoryIdNotFound(): void
    {
        $this->expectExceptionObject(new CategoryNotFoundException());
        $bookRepository = $this->createMock(BookRepository::class);
        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn(null);

        $service = new BookService($bookRepository, $categoryRepository);
        $service->getByCategoryId(1);
    }

    public function testGetByCategory(): void
    {
        $category = new stdClass();
        $date = new DateTime();
        $book = $this->createBook(1234, $date);
        $bookRepository = $this->createMock(BookRepository::class);
        $bookRepository->expects($this->once())
            ->method('findBy')
            ->with(['category' => $category])
            ->willReturnCallback(fn () => [$book]);

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($category);

        $service = new BookService($bookRepository, $categoryRepository);
        $actual = $service->getByCategoryId(1);
        $expected = [$this->mapBook($book)];

        $this->assertEquals($expected, $actual);
    }
}
