<?php

namespace DoctrinePagination\Collection;

class PaginatedArrayCollection
{
    /**
     * @var int|null
     */
    protected $total;

    /**
     * @var int|null
     */
    protected $last_page;

    /**
     * @var int|null
     */
    protected $per_page;

    /**
     * @var int|null
     */
    protected $current_page;

    /**
     * @var string|null
     */
    protected  $next_page_url;

    /**
     * @var string|null
     */
    protected  $prev_page_url;

    /**
     * @var array|null
     */
    protected  $criteria = [];

    /**
     * @var array|null
     */
    protected $orderBy = [];

    /**
     * @var array|null
     */
    protected $data = null;

    public function __construct(
        array $elements = [],
        int $current_page = null,
        int $per_page = 10,
        int $total = null,
        ?array $criteria = [],
        ?array $orderBy = []
    )
    {
        $this->data = $elements;

        $this->total = $total;
        $this->per_page = $per_page;
        $this->current_page = $current_page;
        $this->criteria = $criteria;
        $this->orderBy = $orderBy;

        $this->last_page = $this->getLastPage();
        $this->next_page_url = $this->getNextPageUrl();
        $this->prev_page_url = $this->getPrevPageUrl();

        $this->criteria = null;
        $this->orderBy = null;
    }

    /**
     * @return int|null
     */
    public function getTotal(): ?int
    {
        return $this->total;
    }

    /**
     * @return int|null
     */
    public function getLastPage(): ?int
    {
        if (!$this->getPerPage()) {
            throw new \LogicException('ResultsPerPage was not setted');
        }

        if (!$this->getTotal()) {
            return 0;
        }

        $this->last_page = ceil($this->getTotal() / $this->getPerPage());

        return $this->last_page;
    }

    /**
     * @return int|null
     */
    public function getPerPage(): ?int
    {
        return $this->per_page;
    }

    /**
     * @return int|null
     */
    public function getCurrentPage(): ?int
    {
        return $this->current_page;
    }

    /**
     * @return string|null
     */
    public function getNextPageUrl(): ?string
    {
        $this->next_page_url = $this->mountUrl($this->getCurrentPage() + 1);

        return $this->next_page_url;
    }

    /**
     * @return string|null
     */
    public function getPrevPageUrl(): ?string
    {
        $this->prev_page_url = $this->mountUrl($this->getCurrentPage() - 1);

        return $this->prev_page_url;
    }

    /**
     * @return array|null
     */
    public function getCriteria(): ?array
    {
        return $this->criteria;
    }

    /**
     * @param array|null $criteria
     * @return $this
     */
    public function setCriteria(?array $criteria): PaginatedArrayCollection
    {
        $this->criteria = $criteria;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getOrderBy(): ?array
    {
        return $this->orderBy;
    }

    /**
     * @param array|null $orderBy
     * @return $this
     */
    public function setOrderBy(?array $orderBy): PaginatedArrayCollection
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    /**
     * @param int $page
     * @return string
     */
    private function mountUrl(int $page): string
    {
        $order = '';
        $criteria = '';

        if ($page < 1) {
            $page = 1;
        }

        if ($page > $this->getTotal()) {
            $page = $this->getTotal();
        }

        if (!empty($this->criteria)) {
            foreach ($this->criteria as $key => $data) {
                // @TODO se precisar enviar idcompany como atributo será necessário remover
                if ($key === "idcompany") {
                    continue;
                }
                $criteria .= sprintf("&search=%s&search_field=%s", $data[1] ?? $data, $key);
            }
        }

        if (!empty($this->orderBy)) {
            foreach ($this->orderBy as $key => $data) {
                // @TODO se precisar enviar idcompany como atributo será necessário remover
                if ($key === "idcompany") {
                    continue;
                }
                $order .= sprintf("&sort=%s&order=%s", $key, $data);
            }
        }

        return sprintf("?page=%s&per_page=%s%s%s", $page, $this->getPerPage(), $order, $criteria);
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
