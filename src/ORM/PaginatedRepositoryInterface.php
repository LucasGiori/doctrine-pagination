<?php

namespace DoctrinePagination\ORM;

use Doctrine\ORM\AbstractQuery;
use Doctrine\Persistence\ObjectRepository;
use DoctrinePagination\Collection\PaginatedArrayCollection;
use DoctrinePagination\DTO\Params;

interface PaginatedRepositoryInterface extends ObjectRepository
{
    /**
     * @param Params|null $params
     * @return PaginatedArrayCollection
     */
    public function findPageWithDTO(?Params $params): PaginatedArrayCollection;

    /**
     * @param int|null $page
     * @param int|null $per_page
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $hydrateMode
     * @return PaginatedArrayCollection
     */
    public function findPageBy(
        ?int $page = 1, ?int $per_page = 20, array $criteria = [], ?array $orderBy = null, ?int $hydrateMode = AbstractQuery::HYDRATE_OBJECT
    ): PaginatedArrayCollection;

    /**
     * @param array $criteria
     * @return int
     */
    public function countBy(array $criteria = []): int;
}
