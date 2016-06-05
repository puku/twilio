<?php
/**
 * Created by PhpStorm.
 * User: ivan
 * Date: 04.06.16
 * Time: 12:52
 */

namespace App\Http\Controllers;


use App\Exceptions\NoTwilioAvailableNumberException;
use App\Http\Requests;
use App\Jobs\SendThankingMessage;
use App\Models\CallHistory;
use App\Models\Countries;
use App\Models\PhoneNumbers;
use App\Services\Twilio;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Services_Twilio;

class TwilioController extends Controller
{
    public function index()
    {
        $countries = Countries::all();
        return view('index', ['countries' => $countries]);
    }

    public function phoneNumber(Request $request)
    {

        /** @var ParameterBag $params */
        $params = $request->request;
        $code = $params->get('code');
        if ($code) {
            $phoneNumber = null;
            $errorMessage = null;
            if ($code) {
                $client = new Services_Twilio(env('TWILIO_ACCOUNT_SID'), env('TWILIO_AUTH_TOKEN'));
                $service = new Twilio($client);
                try {
                    $country = $this->findCompanyByShortCode($code);
                    $phoneNumber = PhoneNumbers::where(['country_id' => $country->id])->value('number');
                    if (!$phoneNumber) {
                        $phoneNumber = $service->createPhoneNumber($country);
                    }
                } catch (NoTwilioAvailableNumberException $e) {
                    $errorMessage = $e->getMessage();
                }
            }
            return view('phone-number', [
                'phoneNumber' => $phoneNumber,
                'errorMessage' => $errorMessage,
            ]);
        }
        throw new BadRequestHttpException("Missing required param code");
    }

    public function findCompanyByShortCode($code)
    {
        $country = Countries::where(['short_code' => $code])->first();
        if ($country) {
            return $country;
        }
        throw new BadRequestHttpException("Requested country is not exists in the database");
    }

    public function incoming(Request $request)
    {
        /** @var ParameterBag $requestData */
        $requestData = $request->request;
        $params = $this->prepareParams($requestData->all());

        $model = new CallHistory();
        $model->fill($params);
        if ($model->save()) {
            if (CallHistory::where(['from' => $model->from])->count() <= 1) {
                $this->addMessageSendingJob($model);
            }
        }

        $client = new Services_Twilio(env('TWILIO_ACCOUNT_SID'), env('TWILIO_AUTH_TOKEN'));
        $service = new Twilio($client);
        $incomingNumber = $service->findIncomingPhoneNumber();

        $content = '
            <Response>
                <Dial callerId="' . $incomingNumber . '">
                    <Number>' . $model->from . '</Number>
                </Dial>
            </Response>';

        $response = new Response($content, Response::HTTP_OK);
        return $response->header('Content-Type', $request->getMimeType('xml'));
    }

    public function prepareParams($params)
    {
        $result = [];
        foreach ($params as $key => $value) {
            if ($value) {
                $result[snake_case($key)] = $value;
            }
        }
        return $result;
    }

    public function addMessageSendingJob($model, $delay = 0)
    {
        if (!$delay) {
            $delay = SendThankingMessage::DELAY;
        }
        $job = (new SendThankingMessage($model))->delay($delay);
        $this->dispatch($job);
    }

}
