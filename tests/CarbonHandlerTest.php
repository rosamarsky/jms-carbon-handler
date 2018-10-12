<?php

namespace JMS\Serializer\Tests\Handler;

use Carbon\Carbon;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\VisitorInterface;
use Rosamarsky\JMS\CarbonHandler;

class CarbonHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CarbonHandler
     */
    private $handler;

    /**
     * @var \DateTimeZone()
     */
    private $timezone;

    public function setUp()
    {
        $this->handler = new CarbonHandler();
        $this->timezone = new \DateTimeZone('UTC');
    }

    public function getParams()
    {
        return [
            [['Y-m-d']],
            [['Y-m-d', '', 'Y-m-d|']],
            [['Y-m-d', '', 'Y']],
        ];
    }

    /**
     * @dataProvider getParams
     * @param array $params
     */
    public function testSerializeCarbon(array $params)
    {
        $context = $this->getMockBuilder(SerializationContext::class)->getMock();

        $visitor = $this->getMockBuilder(VisitorInterface::class)->getMock();
        $visitor->method('visitString')->with('2017-06-18');

        $datetime = new Carbon('2017-06-18 14:30:59', $this->timezone);
        $type = ['name' => 'Carbon', 'params' => $params];
        $this->handler->serializeCarbon($visitor, $datetime, $type, $context);
    }

    public function testTimePartGetsRemoved()
    {
        $visitor = $this->getMockBuilder(JsonDeserializationVisitor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $type = ['name' => 'Carbon', 'params' => ['Y-m-d', '', 'Y-m-d|']];
        $this->assertEquals(
            Carbon::createFromFormat('Y-m-d|', '2017-06-18', $this->timezone),
            $this->handler->deserializeCarbon($visitor, '2017-06-18', $type)
        );
    }

    public function testTimePartGetsPreserved()
    {
        $visitor = $this->getMockBuilder(JsonDeserializationVisitor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $expectedCarbon = Carbon::createFromFormat('Y-m-d', '2017-06-18', $this->timezone);

        if ($expectedCarbon->format("H:i:s") === "00:00:00") {
            sleep(1);
            $expectedCarbon = Carbon::createFromFormat('Y-m-d', '2017-06-18', $this->timezone);
        }

        // no custom deserialization format specified
        $type = ['name' => 'Carbon', 'params' => ['Y-m-d']];
        $this->assertEquals(
            $expectedCarbon,
            $this->handler->deserializeCarbon($visitor, '2017-06-18', $type)
        );

        // custom deserialization format specified
        $type = ['name' => 'Carbon', 'params' => ['Y-m-d', '', 'Y-m-d']];
        $this->assertEquals(
            $expectedCarbon,
            $this->handler->deserializeCarbon($visitor, '2017-06-18', $type)
        );
    }

    public function testTimeZoneGetsPreservedWithUnixTimestamp()
    {
        $visitor = $this->getMockBuilder(JsonDeserializationVisitor::class)
            ->disableOriginalConstructor()
            ->getMock();


        $timestamp = time();
        $timezone = 'Europe/Brussels';
        $type = ['name' => 'Carbon', 'params' => ['U', $timezone]];

        $expectedCarbon = Carbon::createFromFormat('U', $timestamp);
        $expectedCarbon->setTimezone(new \DateTimeZone($timezone));

        $actualCarbon = $this->handler->deserializeCarbon($visitor, $timestamp, $type);

        $this->assertEquals(
            $expectedCarbon->format(Carbon::RFC3339),
            $actualCarbon->format(Carbon::RFC3339)
        );
    }

    public function testImmutableTimeZoneGetsPreservedWithUnixTimestamp()
    {
        $visitor = $this->getMockBuilder(JsonDeserializationVisitor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $timestamp = time();
        $timezone = 'Europe/Brussels';
        $type = ['name' => 'Carbon', 'params' => ['U', $timezone]];

        $expectedCarbon = Carbon::createFromFormat('U', $timestamp);
        $expectedCarbon->setTimezone(new \DateTimeZone($timezone));

        $actualCarbon = $this->handler->deserializeCarbon($visitor, $timestamp, $type);

        $this->assertEquals(
            $expectedCarbon->format(Carbon::RFC3339),
            $actualCarbon->format(Carbon::RFC3339)
        );
    }
}
