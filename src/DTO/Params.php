<?php

declare(strict_types=1);

namespace DoctrinePagination\DTO;

use Doctrine\ORM\AbstractQuery;

class Params
{
    /**
     * @var int|null
     */
    private $page = 1;

    /**
     * @var int|null
     */
    private $per_page = 20;

    /**
     * @var array|null
     */
    private $criteria = [];

    /**
     * @var string|null
     */
    private $sort = '';

    /**
     * @var string|null
     */
    private  $order = 'ASC';

    /**
     * @var string|null
     */
    private $search = '';

    /**
     * @var string|null
     */
    private $search_field = '';

    /**
     * @var int|null
     */
    private $hydrateMode = AbstractQuery::HYDRATE_OBJECT;

    public function __construct(?array $dados = [])
    {
        if (empty($dados))
            return;

        foreach ($dados as $key => $dado) {
            $key = trim($key);
            $dado = trim($dado);

            if (!isset($this->$key) || $dado === "undefined") {
                continue;
            }

            $this->$key = $this->treatData($key, $dado);
        }
    }

    /**
     * @return int|null
     */
    public function getPage(): ?int
    {
        return $this->page;
    }

    /**
     * @param int|null $page
     * @return $this
     */
    public function setPage(?int $page): Params
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPerPage(): ?int
    {
        return $this->per_page;
    }

    /**
     * @param int|null $per_page
     * @return $this
     */
    public function setPerPage(?int $per_page): Params
    {
        $this->per_page = $per_page;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getCriteria(): ?array
    {
        if (empty($this->getSearch()) || empty($this->getSearchField())) {
            return $this->criteria;
        }

        return array_merge($this->criteria, [
            $this->getSearchField() => ["ILIKE", $this->getSearch()]
        ]);
    }

    /**
     * @param array|null $criteria
     * @return $this
     */
    public function setCriteria(?array $criteria): Params
    {
        $this->criteria = $criteria;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSort(): ?string
    {
        return $this->sort;
    }

    /**
     * @param string|null $sort
     * @return $this
     */
    public function setSort(?string $sort): Params
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOrder(): ?string
    {
        return $this->order;
    }

    /**
     * @param string|null $order
     * @return $this
     */
    public function setOrder(?string $order): Params
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return array|null[]|string[]
     */
    public function getOrderBy(): array
    {
        if ($this->getSort() && $this->getOrder()) {
            return [$this->getSort() => $this->getOrder()];
        }

        return [];
    }

    /**
     * @return string|null
     */
    public function getSearch(): ?string
    {
        return $this->search;
    }

    /**
     * @param string|null $search
     * @return $this
     */
    public function setSearch(?string $search): Params
    {
        $this->search = $search;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSearchField(): ?string
    {
        return $this->search_field;
    }

    /**
     * @param string|null $search_field
     * @return $this
     */
    public function setSearchField(?string $search_field): Params
    {
        $this->search_field = $search_field;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getHydrateMode(): ?int
    {
        return $this->hydrateMode;
    }

    /**
     * @param int|null $hydrateMode
     * @return $this
     */
    public function setHydrateMode(?int $hydrateMode): Params
    {
        $this->hydrateMode = $hydrateMode;
        return $this;
    }

    /**
     * @param $key
     * @param $dado
     * @return array|false|mixed
     */
    private function treatData($key, $dado)
    {
        $typeDado = gettype($this->$key);

        switch ($typeDado) {
            case "integer":
            case "string":
                $method = sprintf("%sval", substr($typeDado, 0, 3));

                return call_user_func($method, $dado);
            case "array":
                return is_array($dado) ? $dado : (array)$dado;
            default:
                return $dado;
        }

    }
}
