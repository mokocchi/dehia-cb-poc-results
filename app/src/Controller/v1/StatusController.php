<?php

namespace App\Controller\v1;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/results-status")
 */
class StatusController extends AbstractFOSRestController
{

    /**
     * 
     * @Rest\Get(name="results_status")
     * 
     * @return Response
     */
    public function getStatus()
    {
        return $this->getViewHandler()->handle($this->view(["status" => "OK"]));
    }
}
