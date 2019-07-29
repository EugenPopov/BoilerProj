<?php


namespace App\Mappers;


use App\DTO\Category as CategoryDto;
use App\DTO\CategoryForm;
use App\Entity\Category as CategoryEntity;

class Category
{
    static function entityToDto(CategoryEntity $entity, string $slug): CategoryDto
    {
        return new CategoryDto(
            $entity->getName(),
            $entity->getIsVisible(),
            $entity->getCreatedAt(),
            $entity->getUpdatedAt(),
            $slug,
            $entity->getParent() ? $entity->getParent()->getId() : null,
            $entity->getDescription(),
            $entity->getImage()
        );
    }

    static function entityToFormDTO(CategoryEntity $entity): CategoryForm{
        return new CategoryForm(
            $entity->getName(),
            $entity->getDescription(),
            $entity->getUsdValue(),
            $entity->getEurValue(),
            $entity->getIsVisible(),
            null,
            $entity->getSeoDescription()

        );
    }
}