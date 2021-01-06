<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Category;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\Criteria;

class CategoryService
{
    private CategoryRepository $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @return Category[]
     */
    public function categories(): array
    {
        return array_map(
            fn ($category) => new Category($category->getId(), $category->getTitle(), $category->getSlug()),
            $this->categoryRepository->findBy([], ['title' => Criteria::ASC])
        );
    }
}
