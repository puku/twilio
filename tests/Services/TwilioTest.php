<?php
/**
 * Created by PhpStorm.
 * User: ivan
 * Date: 05.06.16
 * Time: 17:56
 */

namespace tests\Services;

use App\Services\Twilio;
use TestCase;
use Services_Twilio;
use Services_Twilio_Rest_Call;

class TwilioTest extends TestCase
{

    public function testFindCall()
    {
        /*
        $client = $this->getMockBuilder(Services_Twilio::class)
            ->setConstructorArgs(self::getCredentials())
            ->setMethods(['account'])
            ->getMock();

        $response = new stdClass();
        $response->calls = new stdClass();


        $client->expects($this->once())
            ->method('account')
            ->willReturn($response);
*/
        $twilio = $this->getTwilioService();
        $result = $twilio->findCall('Test');

        $this->assertInstanceOf(Services_Twilio_Rest_Call::class, $result);
    }

    /**
     * @param $duration
     * @param $result
     *
     * @dataProvider thankingMessageDataProvide
     */
    public function testThankingMessage($duration, $result)
    {
        $twilio = $this->getTwilioService();
        $message = $twilio->getThankingMessage($duration);
        $this->assertEquals($result, $message);
    }


    public function thankingMessageDataProvide()
    {
        return [
            [10, Twilio::SHORT_CONVERSATION_MESSAGE],
            [100, Twilio::SHORT_CONVERSATION_MESSAGE],
            [120, Twilio::LONG_CONVERSATION_MESSAGE],
            [200, Twilio::LONG_CONVERSATION_MESSAGE],
            ['asds', Twilio::SHORT_CONVERSATION_MESSAGE],
            [-1000, Twilio::SHORT_CONVERSATION_MESSAGE],
        ];
    }

    public function getTwilioService($client = null)
    {
        if (!$client) {
            list($sid, $token) = self::getCredentials();
            $client = new Services_Twilio($sid, $token);
        }
        return new Twilio($client);
    }

    public static function getCredentials()
    {
        return [
            env('TWILIO_ACCOUNT_SID_DEBUG'),
            env('TWILIO_AUTH_TOKEN_DEBUG'),
        ];
    }
}