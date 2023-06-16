<?php

namespace App\Wex\BaseBundle\Controller\Pages;

use App\Wex\BaseBundle\Controller\AbstractPagesController;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class TestController extends AbstractPagesController
{
    #[Route(path: '_core/test/view', name: '_core_test_view', options: self::ROUTE_OPTIONS_ONLY_EXPOSE)]
    public function view(): Response
    {
        return $this->render(
            '@WexBaseBundle/Resources/templates/pages/_core/test/view.html.twig'
        );
    }
}
