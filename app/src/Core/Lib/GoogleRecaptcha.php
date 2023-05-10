<?php

namespace Core\Lib;

abstract class GoogleRecaptcha
{
    static public function validReCaptcha($secretyKey, $response)
    {       
        try {
            $data = [
                'secret' => $secretyKey,
                'response' => $response
            ];

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));

            $response = curl_exec($curl);
            $response = json_decode($response, true);

            return $response['success'] === false ? false : true;
        } catch (\Exception $e) {
            return false;
        }
    }
}