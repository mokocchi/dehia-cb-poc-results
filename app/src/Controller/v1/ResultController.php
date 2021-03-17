<?php

namespace App\Controller\v1;

use App\Api\ApiProblem;
use Exception;
use App\Entity\CircuitBreakerSwitch;
use App\FaultTolerance\CircuitBreakerDoctrineStore;
use App\FaultTolerance\CircuitBreakerOpenException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/results")
 */
class ResultController extends AbstractFOSRestController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * 
     * @Rest\Get(name="results")
     * 
     * @return Response
     */
    public function getResults()
    {
        $name = $this->getUser()->getUsername();
        $token = $this->getUser()->getToken();
        $em = $this->getDoctrine()->getManager();
        $switch = $em->getRepository(CircuitBreakerSwitch::class)->findBy(["token" => $token]);

        $action = function ($parameters) {
            $token = $parameters[0];
            $client = new Client([
                'base_uri' => $_ENV['COLLECT_POC_URL'],
                'timeout' => 5.0
            ]);
            $response = $client->get("/api/v1.0/entry", [
                'headers' =>
                ["authorization" => "Bearer $token"]
            ]);
            $res = [];
            $data = json_decode($response->getBody(), true);
            $results = $data["results"];
            foreach ($results as $item) {
                array_push($res, $item);
            }
            return $this->getViewHandler()->handle($this->view(["results" => $res]));
        };
        //without circuit breaker
        if (count($switch) == 0) {
            try {
                return $action([$token]);
            } catch (ConnectException $e) {
                $this->logger->warning($e);
                return $this->getViewHandler()->handle($this->view(
                    new ApiProblem(500, "Connection error: Couldn't reach Collect service", "Connection error: Couldn't reach Collect service"),
                    500));
            }
        }
        //with circuit breaker
        try {
            CircuitBreakerDoctrineStore::initialize();
            return CircuitBreakerDoctrineStore::executeAction($name, $action, [$token]);
        } catch (ConnectException $e) {
            $this->logger->error("Couln't reach Collect Service");
            $this->logger->warning($e);
            //retrieve old results
            return $this->getViewHandler()->handle($this->view(["results" => ["old", "results"]]));   
        } catch (CircuitBreakerOpenException $e) {
            $this->logger->error("Collect Service Down (circuit breaker)");
            $this->logger->warning($e);
            //retrieve old results
            return $this->getViewHandler()->handle($this->view(["results" => ["old", "results"]]));
        } catch (Exception $e) {
            $this->logger->error($e);
            return $this->getViewHandler()->handle($this->view(
                new ApiProblem(500, "Unknown error", "Unknown error"),
                500
            ));
        }
    }
}
