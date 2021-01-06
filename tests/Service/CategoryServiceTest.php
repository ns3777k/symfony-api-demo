<?php

namespace App\Tests\Service;

use App\Entity\Category;
use App\Model\Category as CategoryModel;
use App\Repository\CategoryRepository;
use App\Service\CategoryService;
use App\Tests\AbstractTestCase;
use DateTime;
use Doctrine\Common\Collections\Criteria;

class CategoryServiceTest extends AbstractTestCase
{
    private function createCategory(): Category
    {
        $category = (new Category())
            ->setTitle('Test Category')
            ->setSlug('test-category')
            ->setCreatedAt(new DateTime());

        $this->setEntityId($category, 1234);

        return $category;
    }

    public function testCategories(): void
    {
        $repository = $this->createMock(CategoryRepository::class);
        $repository->expects($this->once())
            ->method('findBy')
            ->with([], ['title' => Criteria::ASC])
            ->willReturnCallback(fn () => [$this->createCategory()]);

        $categoryService = new CategoryService($repository);
        $actualCategories = $categoryService->categories();
        $expectedCategories = [
            (new CategoryModel(1234, 'Test Category', 'test-category')),
        ];

        $this->assertEquals($expectedCategories, $actualCategories);
    }
}
