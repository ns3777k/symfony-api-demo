<?php

declare(strict_types=1);

namespace App\Model;

use Swagger\Annotations as SWG;

class Category
{
    /**
     * @SWG\Property(description="Идентификатор")
     */
    private int $id;

    /**
     * @SWG\Property(description="Название", example="Cloud Native")
     */
    private string $title;

    /**
     * @SWG\Property(description="Символьный код", example="cloud-native")
     */
    private string $slug;

    public function __construct(int $id, string $title, string $slug)
    {
        $this->id = $id;
        $this->title = $title;
        $this->slug = $slug;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }
}
