<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Category;
use App\Service\CategoryService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    /**
     * @Route("/api/v1/categories", name="categories", methods={"GET"})
     * @SWG\Tag(name="Контент")
     * @SWG\Response(
     *     response=200,
     *     description="Отдает список категорий",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Category::class))
     *     )
     * )
     */
    public function categories(CategoryService $categoryService): Response
    {
        return $this->json($categoryService->categories());
    }
}
