<?php
/**
 * Created by PhpStorm.
 * User: ivan
 * Date: 04.06.16
 * Time: 15:24
 */

namespace App\Services;

use App\Models\Countries;
use App\Models\PhoneNumbers;
use Services_Twilio;
use Exception;
use App\Exceptions\NoTwilioAvailableNumberException;
use App\Exceptions\NoTwilioIncomingNumberException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Twilio
{

    const THANKING_MESSAGE_DURATION = 120; //60 * 2 seconds

    const DEFAULT_APPLICATION_SID = 'AP6452316e22874c8e9f4c3425832b0b28';

    const SHORT_CONVERSATION_MESSAGE = 'Thank you very much';
    const LONG_CONVERSATION_MESSAGE = 'We were glad to here you';

    const PHONE_NUMBER_TYPE_LOCAL = 'Local';
    const PHONE_NUMBER_TYPE_TOLL_FREE = 'TollFree';
    const PHONE_NUMBER_TYPE_MOBILE = 'Mobile';
    
    /**
     * @var Services_Twilio
     */
    private $client;

    /**
     * Twilio constructor.
     * @param Services_Twilio $twilio
     */
    public function __construct(Services_Twilio $twilio)
    {
        $this->client = $twilio;

    }

    /**
     * @param Countries $country
     * @return mixed|null
     * @throws Exception
     */
    public function createPhoneNumber(Countries $country)
    {
        if ($number = $this->buyNumber($country)) {
            $phoneNumberModel = new PhoneNumbers();
            $phoneNumberModel->number = $number;
            $phoneNumberModel->country_id = $country->id;
            if ($phoneNumberModel->save()) {
                return $number;
            }
            throw  new Exception("Model wasn't save due unknown reason");
        }
        throw  new Exception('Can not buy number due to unknown reason');
    }

    /**
     * @param $countryCode
     * @return mixed|null
     * @throws Exception
     * @throws NoTwilioAvailableNumberException
     */
    public function buyNumber($countryCode)
    {
        $number = $this->findAvailablePhoneNumber($countryCode);
        $result = $this->client->account->incoming_phone_numbers->create([
            "PhoneNumber" => $number,
            'VoiceApplicationSid' => self::DEFAULT_APPLICATION_SID,
            'SmsApplicationSid' => self::DEFAULT_APPLICATION_SID,
        ]);
        if ($result) {
            return $number;
        }
        return null;
    }

    /**
     * @param $countryCode
     * @return mixed
     * @throws Exception
     * @throws NoTwilioAvailableNumberException
     */
    public function findAvailablePhoneNumber($countryCode)
    {
        $numbers = $this->client->account->available_phone_numbers->getList((string)$countryCode, 'Local', []);
        if (count($numbers->available_phone_numbers) > 0) {
            $phoneNumber = array_get($numbers->available_phone_numbers, 0);
            if ($phoneNumber) {
                return $phoneNumber->phone_number;
            }
            throw  new Exception("Number wasn't save due unknown reason");
        }
        throw new NoTwilioAvailableNumberException("No numbers are available to purchase");
    }

    /**
     * @param $callId
     * @throws NoTwilioIncomingNumberException
     */
    public function sendThankingMessage($callId)
    {

        if ($call = $this->findCall($callId)) {
            $message = $this->getThankingMessage($call->duration);
            if ($number = $this->findIncomingPhoneNumber()) {
                $from = $number->phone_number;
                $to = $call->from;
                $this->client->account->messages->sendMessage($from, $to, $message);
            }
        }
    }

    /**
     * @param string $type
     * @return mixed
     * @throws NoTwilioIncomingNumberException
     */
    public function findIncomingPhoneNumber($type = self::PHONE_NUMBER_TYPE_LOCAL)
    {
        $inputNumber = $this->client->account->incoming_phone_numbers->getList($type);
        if ($inputNumber) {
            $number = array_get($inputNumber->incoming_phone_numbers, 0);
            if ($number) {
                return $number->phone_number;
            }
        }
        throw new NoTwilioIncomingNumberException("No numbers are available to purchase");
    }

    /**
     * @param $callId
     * @return mixed
     */
    public function findCall($callId)
    {
        if ($call = $this->client->account->calls->get($callId)) {
            return $call;
        }
        throw  new NotFoundHttpException("No calls where found");
    }

    /**
     * @param $duration
     * @return string
     */
    public function getThankingMessage($duration)
    {
        if ($duration >= self::THANKING_MESSAGE_DURATION) {
            return self::LONG_CONVERSATION_MESSAGE;
        }
        return self::SHORT_CONVERSATION_MESSAGE;

    }
}