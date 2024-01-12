<?php

namespace App\Service;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class PaginationService
{
  private $paginator;

  public function __construct(PaginatorInterface $paginator)
  {
    $this->paginator = $paginator;
  }

  public function paginate($query, Request $request, $itemsPerPage = 7)
  {
    $page = $request->query->getInt('page', 1);

    return $this->paginator->paginate(
      $query,
      $page,
      $itemsPerPage
    );
  }
}
