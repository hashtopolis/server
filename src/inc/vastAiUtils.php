<?php

use DBA\Factory;

// exists to make it easier to reference the json objects
// from within the template
class VastAiGPUArrayClass {
    private $internalArray = array();

    public function __construct($array) {
        $this->internalArray = $array;
    }

    public function getUptime() {
      if (! $this->getVal('start_date')) {
          return null;
      }
      $start = (int) $this->getVal('start_date');
      $start = round($start, 0);
      $current = time();
      $delta = $current - $start;

      $days = floor($delta / (60 * 60 * 24));
      $delta %= (60 * 60 * 24);

      $hours = floor($delta / (60 * 60));
      $delta %= (60 * 60);

      $minutes = floor($delta / 60);
      $seconds = $delta % 60;

      $duration = '';

      if ($days > 0) {
          $duration .= $days . ' day' . ($days > 1 ? 's' : '') . ' ';
      }

      $duration .= sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

      return $duration;
    }

    public function getFloatValOld($key, $precision) {
        return round(floatval($this->internalArray[$key]), $precision);
    }

    public function getStartingVoucher() {
        return $this->getVal('extra_env')[1][1];
    }

    public function getFloatval($key, $precision) {
      $number = $this->getVal($key);
      $retNumber = (float) number_format($number,0,'.','');
      for ($n = 0; $n <= 8; $n++) {
	    if ($retNumber === 0.0){
	      $retNumber = (double) number_format($number,$n,'.','');
	    } else {
	      return number_format($number,$n+(max($precision-2,0)),'.','');
   	    }
      }
      return "<0.00000000";
    }

    public function getFloatVal100($key, $precision) {
        return round(floatval($this->internalArray[$key])*100, $precision);
    }

    public function getVal($key) {
        if (array_key_exists($key, $this->internalArray)) {
            return $this->internalArray[$key];
        } else {
            return null;
        }
    }
}

function vastAiDestroyInstance($apiKey, $id) {
    $url = 'https://console.vast.ai/api/v0/instances/' . $id . '/?api_key=' . $apiKey;
    $headers = array('Accept: application/json');
    $options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CUSTOMREQUEST => "DELETE"
    );

    $ch = curl_init($url);
    $options[CURLOPT_HTTPHEADER] = $headers;
    curl_setopt_array($ch, $options);

    $response = curl_exec($ch);
    curl_close($ch);

    if($response === false) {
        return 'vastaiAgentSearch curl error: ' . curl_error($ch);
    }

    return json_decode($response, true);
}

function makeVastaiVoucher($id){
  $sub = substr(md5(rand()),0,10);
  $voucher = $randomSubStr = 'vast' . $sub . $id;
  AgentUtils::createVoucher($voucher);
  return $voucher;
}

// retrieves the instances that the account is currently renting
function getVastAiAgents($apiKey) {
    $url = 'https://console.vast.ai/api/v0/instances?api_key=' . $apiKey;
    $headers = array('Accept: application/json');
    $options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
    );

    $ch = curl_init($url);
    $options[CURLOPT_HTTPHEADER] = $headers;
    curl_setopt_array($ch, $options);

    $response = curl_exec($ch);
    curl_close($ch);

    if($response === false) {
        return'vastaiAgentSearch curl error: ' . curl_error($ch);
    }

    return json_decode($response, true);
}

// search current GPUs, this does not require an api key
// an example query input value would be
// $query = '{"verified": {"eq": true}, "external": {"eq": false}, "rentable": {"eq": true}, "compute_cap": {"gt": "600"}, "disk_space": {"gt": "10000"}, "order": [["score", "desc"]], "type": "on-demand"}';
function searchGpus($query) {
    $url = 'https://console.vast.ai/api/v0/bundles?q=' . urlencode($query);
    $headers = array('Accept: application/json');
    $options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
    );

    $ch = curl_init($url);
    $options[CURLOPT_HTTPHEADER] = $headers;
    curl_setopt_array($ch, $options);

    $response = curl_exec($ch);
    curl_close($ch);

    if($response === false) {
        return'vastaiAgentSearch curl error: ' . curl_error($ch);
    }

    return json_decode($response, true);
}

function rentMachine($apiKey, $id, $imageUrl, $price, $disk, $voucher, $baseUrl, $imageLogin) {
    // API endpoint
    $url = 'https://console.vast.ai/api/v0/asks/' . $id . '/?api_key=' . $apiKey;
    if ( $imageLogin === ''){
        $imageLogin = null;
    }

    // Data to be sent
    $data = array(
        "client_id" => "me",
        "image" => $imageUrl,
        "env" => array(
            "TZ" => "UTC",
            "VOUCHER" => $voucher,
            "HCATURL" => $baseUrl
        ),
        "disk" => (int) $disk,
        "label" => "instance-" . $id,
        "extra" => null,
        "image_login" => $imageLogin,
        "onstart" => "/root/hcat/run.sh",
        "runtype" => "ssh",
        "python_utf8" => false,
        "lang_utf8" => false,
        "use_jupyter_lab" => false,
        "jupyter_dir" => null
    );

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Content-Type: application/json'
    ));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    // Execute the cURL request
    $response = curl_exec($ch);
    // REMOVE THIS LINE, for debugging
    curl_close($ch);

    // Check for any errors
    if ($response === false) {
        return 'cURL error: ' . curl_error($ch);
    }

    // Close cURL session
    return json_decode($response, true);
}

function doesVoucherExist($voucher){
  $vouchers = Factory::getRegVoucherFactory()->filter([]);
  foreach ($vouchers as $voucher){
    $voucherValue = $voucher->getVoucher();
    if (str_contains($voucherValue, "vast.ai") == false){
      continue;
    }
    $index = strpos($voucherValue, "-");
    $voucherInstanceId = substr($voucherValue, $index);
    if ($voucherInstanceId === $intsanceId){
        return $voucherValue;
    }
  }
  return null;
}


// given an instanceId it goes through all vouchers
// containing the substr vast.ai and returns the 
// voucher that contains the instanceId
function getVoucherForInstance($instanceId){
  $vouchers = Factory::getRegVoucherFactory()->filter([]);
  foreach ($vouchers as $voucher){
    $voucherValue = $voucher->getVoucher();
    if (str_contains($voucherValue, "vast.ai") == false){
      continue;
    }
    $index = strpos($voucherValue, "-");
    $voucherInstanceId = substr($voucherValue, $index);
    if ($voucherInstanceId === $intsanceId){
        return $voucherValue;
    }
  }
  return null;
}

function get_or($arr,$key,$default){
    if (!$arr[$key]){
        return $default;
    }
    return $arr[$key];
}

?>
