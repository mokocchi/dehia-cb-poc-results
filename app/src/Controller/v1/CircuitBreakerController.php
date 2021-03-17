<?php

namespace App\Controller\v1;

use App\Entity\CircuitBreakerSwitch;
use App\FaultTolerance\CircuitBreakerDoctrineStore;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/circuit-breaker-switch")
 */
class CircuitBreakerController extends AbstractFOSRestController
{

    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @Rest\Post(name="post_circuit_breaker_switch")
     * 
     * @return Response
     */
    public function postCircuitBreaker()
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $switch = $em->getRepository(CircuitBreakerSwitch::class)->findBy(["token" => $user->getToken()]);
        if (count($switch) === 0) {
            CircuitBreakerDoctrineStore::initialize();
            CircuitBreakerDoctrineStore::createCircuitBreakerForName($user->getUsername());
            $switch = new CircuitBreakerSwitch();
            $switch->setToken($user->getToken());
            $em->persist($switch);
            $em->flush();
        }
        return $this->getViewHandler()->handle($this->view(["result" => [["token" => true]]]));
    }

    /**
     * @Rest\Delete(name="delete_circuit_breaker_switch")
     * 
     * @return Response
     */
    public function deleteSwitch()
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $switches = $em->getRepository(CircuitBreakerSwitch::class)->findBy(["token" => $user->getToken()]);
        if (count($switches) > 0) {
            CircuitBreakerDoctrineStore::initialize();
            CircuitBreakerDoctrineStore::deleteCircuitBreakerForName($user->getUsername());
            $this->logger->info("FOUND");
            foreach ($switches as $switch) {
                $em->remove($switch);
            }
            $em->flush();
        }
        return $this->getViewHandler()->handle($this->view(["result" => [["token" => false]]]));
    }

    /**
     * @Rest\Get(name="get_circuit_breaker_switch")
     * 
     * @return Response
     */
    public function getSwitches()
    {
        $token = $this->getUser()->getToken();
        $em = $this->getDoctrine()->getManager();
        $this->logger->info($token);
        $switches = $em->getRepository(CircuitBreakerSwitch::class)->findBy(["token" => $token]);
        if (count($switches) > 0) {
            return $this->getViewHandler()->handle($this->view(["status" => "enabled"]));
        } else {
            return $this->getViewHandler()->handle($this->view(["status" => "disabled"]));
        }
    }
}
