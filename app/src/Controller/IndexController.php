<?php

namespace App\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class IndexController extends AbstractFOSRestController
{

    /**
     * Index route
     * @Rest\Get(name="index")
     * 
     * @return Response
     */
    public function getIndex()
    {
        return $this->getViewHandler()->handle($this->view("Results Index"));
    }
}
