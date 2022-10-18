<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Utils\Payment;

class MpesaController extends Controller
{
    public function lipaNaMpesaPassword()
    {
        //timestamp
        $timestamp = Carbon::rawParse('now')->format('YmdHms');
        //passkey

        $passKey = "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919";
        $businessShortCode = '9054103';
        //generate password
        $mpesaPassword = base64_encode($businessShortCode . $passKey . $timestamp);

        return $mpesaPassword;
    }


    public function newAccessToken()
    {
        $consumer_key = "m1xGfxiex5wwfGVJhqOZ4xwMc0ZHKsSo";
        $consumer_secret = "pvsLC8jpI8tPRFml";
        $credentials = base64_encode($consumer_key . ":" . $consumer_secret);
        $url = "https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";

        //@maryportal
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Basic " . $credentials, "Content-Type:application/json"));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $curl_response = curl_exec($curl);
        $access_token = json_decode($curl_response);
        curl_close($curl);

        return $access_token->access_token;
    }



    public function stkPush(Request $request)
    {
      

        //  $user = $request->user;
        $amount = 1; //$request->amount;
        //  $phone =  $request->phone;
        //  $formatedPhone = substr($phone, 1);
        $code = "254";
        $phoneNumber = '254727594417'; //$code.$formatedPhone;
        $url = 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        $curl_post_data = [
            'BusinessShortCode' => '9054103',
            'Password' => $this->lipaNaMpesaPassword(),
            'Timestamp' => Carbon::rawParse('now')->format('YmdHms'),
            'TransactionType' => 'CustomerBuyGoodsOnline',
            'Amount' => $amount,
            'PartyA' => $phoneNumber,
            'PartyB' => '9054103',
            'PhoneNumber' => $phoneNumber,
            'CallBackURL' => 'https://stockomdemo.appyetu.co.ke/api/stk/push/callback/url',
            'AccountReference' => "Invoice Payment",
            'TransactionDesc' => "lipa Na M-PESA"
        ];


        $data_string = json_encode($curl_post_data);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $this->newAccessToken()));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        $curl_response = curl_exec($curl);
        return ($curl_response);
    }

    public function MpesaRes(Request $request)
    {

        $response = json_decode($request->getContent());
        // Log::info(json_encode($response));
        $resData = $response->Body->stkCallback->CallbackMetadata;
        $reCode = $response->Body->stkCallback->ResultCode;
        $resMessage = $response->Body->stkCallback->ResultDesc;
        $amountPaid = $resData->Item[0]->Value;
        $mpesaTransactionId = $resData->Item[1]->Value;
        $TransactionDate = $resData->Item[3]->Value;
        $paymentPhoneNumber = $resData->Item[4]->Value;
        $formatedPhone = str_replace("254", "0", $paymentPhoneNumber);
        $formatedDate = date("Y/d/m H:i:s A", strtotime($TransactionDate));
        $payment = new Payment;
        $payment->trans_amount = $amountPaid;
        $payment->TransId = $mpesaTransactionId;
        $payment->TransTime = $formatedDate;
        $payment->ThirdPartyTransID = $formatedPhone;
        $payment->save();
    }

    public function confirm()
    {
        //Compare the codes here
        //If the codes are equal, validate the pay
        //If the TransactionIds are not equal, do something
    }


    public function getMpesaDetails()
    {
        $mpesa = Payment::get();
        return response()->json($mpesa);
    }
}
