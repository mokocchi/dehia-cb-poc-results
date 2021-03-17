<?php

namespace App\FaultTolerance;

use DateTime;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\SQLite3Cache;
use Symfony\Component\Cache\Adapter\DoctrineAdapter;
use Exception;

class CircuitBreakerDoctrineStore
{

    private static $cache;

    public static function initialize()
    {
        $file = __DIR__ . '/../../db/results.sqlite';
        $provider = new SQLite3Cache(new \SQLite3($file), 'circuitBreakers');
        self::$cache = new DoctrineAdapter($provider);
    }

    public static function createCircuitBreakerForName($name)
    {
        $circuitBreaker = new CircuitBreaker($name);
        $item  = self::$cache->getItem($name);
        $state = $circuitBreaker->getState();
        $stringItem = "$state,";
        $item->set($stringItem);
        self::$cache->save($item, "", 900);
    }

    public static function deleteCircuitBreakerForName($name)
    {
        self::$cache->deleteItem($name);
    }

    static function updateBreakerForName(string $name, int $state, DateTime $tripDate)
    {
        $item  = self::$cache->getItem($name);
        $timestamp = $tripDate->getTimestamp();
        $stringItem = "$state, $timestamp";
        $item->set($stringItem);
        self::$cache->save($item);
    }

    public static function executeAction($name, $action, $parameters)
    {
        $stringItem = self::$cache->getItem($name)->get();
        $items = explode(",", $stringItem);
        $state = intval($items[0]);
        $datetime = new DateTime();
        $datetime->setTimestamp(intval($items[1]));
        $circuitBreaker = new CircuitBreaker($name, $state, $datetime);
        return $circuitBreaker->executeAction($action, $parameters);
    }
}
