<?php

namespace Rosamarsky\JMS;

use Carbon\Carbon;
use JMS\Serializer\Context;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\VisitorInterface;

/**
 * @author      Roman Samarsky <rosamarsky@gmail.com>
 * @license     http://mit-license.org/
 * @link        https://bitbucket.org/xatep/jms-carbon-handler
 */
class CarbonHandler implements SubscribingHandlerInterface
{
    /**
     * @var string
     */
    private $defaultFormat;

    /**
     * @var \DateTimeZone
     */
    private $defaultTimezone;

    /**
     * @param string $defaultFormat
     * @param string $defaultTimezone
     */
    public function __construct($defaultFormat = \DateTime::ISO8601, $defaultTimezone = 'UTC')
    {
        $this->defaultFormat = $defaultFormat;
        $this->defaultTimezone = new \DateTimeZone($defaultTimezone);
    }

    /**
     * @return array
     */
    public static function getSubscribingMethods()
    {
        $methods = [];
        $types = [Carbon::class, 'Carbon'];

        foreach ($types as $type) {
            $methods[] = [
                'type' => $type,
                'format' => 'json',
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'method' => 'deserializeCarbon'
            ];

            $methods[] = [
                'type' => $type,
                'format' => 'json',
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'method' => 'serializeCarbon'
            ];
        }

        return $methods;
    }

    /**
     * @param VisitorInterface $visitor
     * @param \DateTime $date
     * @param array $type
     * @param Context $context
     *
     * @return string
     */
    public function serializeCarbon(VisitorInterface $visitor, \DateTime $date, array $type, Context $context)
    {
        $date = clone $date;
        $date->setTimezone($this->defaultTimezone);

        $format = $this->getFormat($type);

        return $visitor->visitString($date->format($format), $type, $context);
    }

    /**
     * @param VisitorInterface $visitor
     * @param string $data
     * @param array $type
     *
     * @return \DateTime|null
     */
    public function deserializeCarbon(VisitorInterface $visitor, $data, array $type)
    {
        if ($data === null) {
            return null;
        }

        $timezone = isset($type['params'][1]) ? new \DateTimeZone($type['params'][1]) : $this->defaultTimezone;
        $format = $this->getFormat($type);
        $datetime = Carbon::createFromFormat($format, (string)$data, $timezone);

        if ($datetime === false) {
            throw new RuntimeException(sprintf('Invalid datetime "%s", expected format %s.', $data, $format));
        }

        $datetime->setTimezone(new \DateTimeZone(date_default_timezone_get()));

        return $datetime;
    }

    /**
     * @param array $type
     *
     * @return string
     */
    private function getFormat(array $type)
    {
        return isset($type['params'][0]) ? $type['params'][0] : $this->defaultFormat;
    }
}