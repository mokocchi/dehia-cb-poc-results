<?php

namespace App\FaultTolerance;

use DateInterval;
use DateTime;
use Exception;

class CircuitBreaker
{
    //requests pass through
    const STATE_CLOSED = 0;
    //after a timeout, waiting for resource to recover
    const STATE_HALF_OPEN = 1;
    //requests are redirected
    const STATE_OPEN = 2;

    const INTERVAL = 3 * 60 * 1000;

    //for Poc purposes (identifying the user that enabled it)
    private $ID;

    //closed, half-open or open
    private $state;

    private $lastChangedDate;

    public function __construct($ID, $state = self::STATE_CLOSED, $lastChangedDate = null)
    {
        $this->state = $state;
        $this->ID = $ID;
        $this->lastChangedDate = $lastChangedDate;
    }

    private function trip()
    {
        $this->state = self::STATE_OPEN;
        $this->lastChangedDate = new DateTime();
        CircuitBreakerDoctrineStore::updateBreakerForName($this->ID, $this->state, $this->lastChangedDate);
    }

    private function reset()
    {
        $this->state = self::STATE_CLOSED;
    }

    private function halfOpen()
    {
        $this->state = self::STATE_HALF_OPEN;
    }

    public function executeAction($action, $parameters)
    {
        if ($this->isOpen()) {
            $expirationTime = $this->lastChangedDate;
            $expirationTime->add(date_interval_create_from_date_string('3 minutes'));
            if ($this->lastChangedDate > $expirationTime) {
                try {
                    $this->halfOpen();

                    //attempt the operation
                    $action($parameters);

                    //action succeeded, returning to closed
                    $this->reset();
                } catch (Exception $e) {
                    //trip the circuit breaker
                    //in a real-world example the exceptions should be tracked and counted
                    $this->trip();
                    throw $e;
                }
            } else {
                throw new CircuitBreakerOpenException();
            }
        }

        //can execute
        try {
            return $action($parameters);
        } catch (Exception $e) {
            //trip the circuit breaker
            //in a real-world example the exceptions should be tracked and counted
            $this->trip();
            throw $e;
        }
    }

    private function isOpen(): bool
    {
        return !$this->isClosed();
    }

    private function isClosed(): bool
    {
        return $this->state === self::STATE_CLOSED;
    }

    function getState(): int
    {
        return $this->state;
    }
}
