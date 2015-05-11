<?php

class MarketoRestAPI {
  /**
   * @access   private
   * @var      string    $client_id    Marketo Client ID, used in the REST call.
   */
  private $client_id;

  /**
   * @access   private
   * @var      string    $client_secret    Marketo Client Secret, used in the REST call.
   */
  private $client_secret;

  /**
   * @access   private
   * @var      string    $munchkin_id    Marketo Munchkin ID, used in the REST call.
   */
  private $munchkin_id;

  public function __construct ($credentials = array()) {
    if(is_array($credentials)) {
      $this->client_id = $credentials['client_id'];
      $this->client_secret = $credentials['client_secret'];
      $this->munchkin_id = $credentials['munchkin_id'];
    } else {
      echo "Could not initialize";
    }
  }

  private function getAccessToken() {
    $identity_service_url = $this->getMarketoInstance('identity') . 'oauth/token?grant_type=client_credentials';
    $token = $this->run($identity_service_url, array(), true);
    return $token;
  }

  private function run ($url, $params = array(), $auth = false) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    if(isset($auth)) {
      curl_setopt($ch, CURLOPT_USERPWD, $this->client_id . ":" . $this->client_secret);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    if(!empty($params)) {
      $post_body = json_encode($params);
      curl_setopt($ch, CURLOPT_POST, count($params));
      curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body);
    }
    $raw_response = curl_exec($ch);
    if($raw_response === false) {
      echo 'Curl error: ' . curl_error($ch);
    }
    $response = json_decode($raw_response);
    if (isset($response->result)) {
      return $response->result;
    } else if(isset($response->access_token)) {
      return $response->access_token;
    } else {
      return false;
    }
  }

  private function getMarketoInstance ($endpoint = 'rest/v1') {
    return 'https://' . $this->munchkin_id . '.mktorest.com/' . $endpoint . '/';
  }

  public function describe() {
    $token = $this->getAccessToken();
    $describe_url = $this->getMarketoInstance() . 'leads/describe.json';
    $describe_url .= '?access_token=' . $token;
    $fields = $this->run($describe_url);
    return $fields;
  }

  public function push($lead) {
    $token = $this->getAccessToken();
    $lead_push_url = $this->getMarketoInstance() . 'leads.json';
    $lead_push_url .= '?access_token=' . $token;
    $response = $this->run($lead_push_url, array(
        'action' => 'createOnly',
        'lookupField' => 'email',
        'input' => array($lead),
      )
    );
    // echo "<pre>"; print_r($response); echo "</pre>";
  }

  public function testConnection() {
    return true;
  }
}
