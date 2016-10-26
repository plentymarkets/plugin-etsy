<?php

namespace Etsy\Storage;

/**
 * Class Firebase
 */
abstract class Firebase
{
    const FIREBASE_BASE = 'https://etsy-e7036.firebaseio.com/';

	/**
	 * @var string
	 */
    protected static $dataName = '';

	/**
	 * @return mixed
	 */
    public function get()
    {
        $reqString = static::FIREBASE_BASE . static::$dataName . '.json';
        $ch = curl_init($reqString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);
        return $response;
    }

	/**
	 * @param array $data
	 * @return mixed
	 */
    public function post(array $data)
    {
        $data = json_encode($data);
        $reqString = static::FIREBASE_BASE . static::$dataName . '.json';
        $ch = curl_init($reqString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          'Content-Type: application/json',
          'Content-Length: ' . strlen($data))
        );
        $res = curl_exec($ch);
        return $res;
    }
}
