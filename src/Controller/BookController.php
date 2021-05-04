<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Book;
use App\Service\BookService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    /**
     * @Route("/api/v1/book/{id}", name="book", methods={"GET"})
     * @SWG\Tag(name="Контент")
     * @SWG\Response(
     *     response=200,
     *     description="Отдает список книг",
     *     @SWG\Schema(ref=@Model(type=Book::class))
     * )
     */
    public function book(int $id, BookService $bookService): Response
    {
        return $this->json($bookService->getById($id));
    }

    /**
     * @Route("/api/v1/booksByCategory/{id}", name="books", methods={"GET"})
     * @SWG\Tag(name="Контент")
     * @SWG\Response(
     *     response=200,
     *     description="Отдает список книг по категории",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Book::class))
     *     )
     * )
     */
    public function books(int $id, BookService $bookService): Response
    {
        return $this->json($bookService->getByCategoryId($id));
    }
}
