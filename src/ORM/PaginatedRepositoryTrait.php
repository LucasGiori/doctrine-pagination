<?php

namespace DoctrinePagination\ORM;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use DoctrinePagination\Collection\PaginatedArrayCollection;
use DoctrinePagination\DTO\Params;

trait PaginatedRepositoryTrait
{
    /**
     * @param Params|null $params
     * @return PaginatedArrayCollection
     */
    public function findPageWithDTO(?Params $params): PaginatedArrayCollection
    {
        return $this->findPageBy(
            $params->getPage(),
            $params->getPerPage(),
            $params->getCriteria(),
            $params->getOrderBy(),
            $params->getHydrateMode()
        );
    }

    /**
     * @param int|null $page
     * @param int|null $per_page
     * @param array|null $criteria
     * @param array|null $orderBy
     * @param int|null $hydrateMode
     * @return PaginatedArrayCollection
     */
    public function findPageBy(
        ?int $page = 1,
        ?int $per_page = 20,
        ?array $criteria = [],
        ?array $orderBy = null,
        ?int $hydrateMode = AbstractQuery::HYDRATE_OBJECT
    ): PaginatedArrayCollection
    {
        $qb = $this->createPaginatedQueryBuilder($criteria, null, $orderBy);
        $qb->addSelect($this->getEntityAlias());
        $this->processOrderBy($qb, $orderBy);

        // find all
        if ($per_page > 0) {
            $qb->addPagination($page, $per_page);
        }

        $results = $qb->getQuery()->getResult($hydrateMode);

        // count elements if needed
        $total = -1;
        if ($per_page > 0) {
            $total = count($results) < $per_page && $page == 1 ? count($results) : $this->countBy($criteria);
        }

        return new PaginatedArrayCollection($results, $page, $per_page, $total, $criteria, $orderBy);
    }

    /**
     * @param array|null $criteria
     * @return int
     */
    public function countBy(?array $criteria = []): int
    {
        try {
            $qb = $this->createPaginatedQueryBuilder($criteria);
            $qb->select('COUNT(' . $this->getEntityAlias() . ')');

            return (int)$qb->getQuery()->getSingleScalarResult();
        } catch (NonUniqueResultException | NoResultException $e) {
            return 0;
        }
    }

    /**
     * @param array $criteria
     * @param string|null $indexBy
     * @param array|null $orderBy
     * @return PaginatedQueryBuilder
     */
    protected function createPaginatedQueryBuilder(
        array $criteria = [], ?string $indexBy = null, ?array $orderBy = null
    ): PaginatedQueryBuilder
    {
        $qb = new PaginatedQueryBuilder($this->_em);
        $qb->from($this->_entityName, $this->getEntityAlias(), $indexBy);

        if (!empty($orderBy)) {
            $qb->addOrder($orderBy, $this->getEntityAlias());
        }

        $this->processCriteria($qb, $criteria);

        return $qb;
    }

    /**
     * @param PaginatedQueryBuilder $qb
     * @param array $criteria
     */
    protected function processCriteria(PaginatedQueryBuilder $qb, array $criteria): void
    {
        if ($this instanceof FilterRepositoryInterface) {
            $this->buildFilterCriteria($qb, $criteria);
        } else {
            foreach ($criteria as $field => $value) {
                $fieldParameter = 'f' . substr(md5($field), 0, 5);

                if (is_null($value)) {
                    $qb->andWhere(sprintf('%s.%s IS NULL', $this->getEntityAlias(), $field));
                } elseif (is_array($value) && in_array(strtoupper($value[0]), ["LIKE", "ILIKE"])) {
                    $qb->andWhere(
                        $qb->expr()->like(
                            sprintf('LOWER(%s.%s)',  $this->getEntityAlias(), $field),
                            $qb->expr()->literal(strtolower($value[1]) . '%')
                        )
                    );
                } elseif (is_array($value)) {
                    $qb->andWhere($qb->expr()->in(sprintf('%s.%s', $this->getEntityAlias(), $field), $value));
                } else {
                    $qb->andWhere(sprintf('%s.%s = :%s', $this->getEntityAlias(), $field, $fieldParameter));
                    $qb->setParameter($fieldParameter, $value);
                }
            }
        }
    }

    /**
     * @param PaginatedQueryBuilder $qb
     * @param array|null $orderBy
     */
    protected function processOrderBy(PaginatedQueryBuilder $qb, ?array $orderBy = null): void
    {
        if (is_array($orderBy)) {
            $qb->addOrder($orderBy, $this->getEntityAlias());
        }
    }

    /**
     * @return string
     */
    protected function getEntityAlias(): string
    {
        $entityName = explode('\\', $this->_entityName);

        return strtolower(substr(array_pop($entityName), 0, 1));
    }
}
