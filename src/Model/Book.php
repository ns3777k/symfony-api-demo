<?php

declare(strict_types=1);

namespace App\Model;

use DateTimeInterface;
use Swagger\Annotations as SWG;

class Book
{
    /**
     * @SWG\Property(description="Идентификатор")
     */
    private int $id;

    /**
     * @SWG\Property(description="Название", example="Kubernetes in Action")
     */
    private string $title;

    /**
     * @SWG\Property(description="Дата публикации")
     */
    private DateTimeInterface $publishedDate;

    /**
     * @SWG\Property(description="Автор", example="Marko Lukša")
     */
    private string $author;

    /**
     * @SWG\Property(description="Бестселлер?")
     */
    private bool $isBestseller;

    /**
     * @SWG\Property(description="Цена")
     */
    private float $price;

    /**
     * @SWG\Property(description="ISBN")
     */
    private string $isbn;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPublishedDate(): DateTimeInterface
    {
        return $this->publishedDate;
    }

    public function setPublishedDate(DateTimeInterface $publishedDate): self
    {
        $this->publishedDate = $publishedDate;

        return $this;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function isBestseller(): bool
    {
        return $this->isBestseller;
    }

    public function setIsBestseller(bool $isBestseller): self
    {
        $this->isBestseller = $isBestseller;

        return $this;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getIsbn(): string
    {
        return $this->isbn;
    }

    public function setIsbn(string $isbn): self
    {
        $this->isbn = $isbn;

        return $this;
    }
}
