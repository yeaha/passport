<?php
class HttpRequest {
    protected $default_options = array(
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
    );

    protected $response = null;

    public function __construct(array $default_options) {
        if (!extension_loaded('curl'))
            throw new RuntimeException('HttpRequest: Require CURL extension!');

        if ($default_options) $this->default_options = $default_options;
    }

    protected function request($method, $url, array $params = null, array $options = array()) {
        $this->response = null;

        $opt = $this->default_options;
        foreach ($options as $k => $v) {
            if (is_array($v) && isset($opt[$k])) {
                $opt[$k] = array_merge($opt[$k], $v);
            } else {
                $opt[$k] = $v;
            }
        }
        $options = $opt;

        if ($method == 'get') {
            if ($params) $url = $url .'?'. http_build_query($params);
        } elseif ($method == 'post') {
            $options[CURLOPT_POST] = 1;
            if ($params) $options[CURLOPT_POSTFIELDS] = $params;
        }

        $options[CURLOPT_URL] = $url;

        $req = curl_init();
        curl_setopt_array($req, $options);

        $result = curl_exec($req);

        if ($result === false) {
            $message = curl_error($req);
            $code = curl_errno($req);
            curl_close($req);

            throw new RuntimeException($message, $code);
        }

        $this->response = array(
            'url' => curl_getinfo($req, CURLINFO_EFFECTIVE_URL),
            'code' => curl_getinfo($req, CURLINFO_HTTP_CODE),
            'size_upload' => curl_getinfo($req, CURLINFO_SIZE_UPLOAD),
            'size_download' => curl_getinfo($req, CURLINFO_SIZE_DOWNLOAD),
            'speed_upload' => curl_getinfo($req, CURLINFO_SPEED_UPLOAD),
            'speed_download' => curl_getinfo($req, CURLINFO_SPEED_DOWNLOAD),
            'total_time' => curl_getinfo($req, CURLINFO_TOTAL_TIME),
            'body' => $result,
        );
        curl_close($req);

        return $this->response;
    }

    public function get($url, array $params = null, array $options = array()) {
        return $this->request('get', $url, $params, $options);
    }

    public function post($url, array $params = null, array $options = array()) {
        return $this->request('post', $url, $params, $options);
    }

    public function put($url, array $params = null, array $options = array()) {
        $options[CURLOPT_HTTPHEADER][] = 'X-HTTP-METHOD-OVERRIDE: PUT';
        return $this->request('post', $url, $params, $options);
    }

    public function delete($url, array $params = null, array $options = array()) {
        $options[CURLOPT_HTTPHEADER][] = 'X-HTTP-METHOD-OVERRIDE: DELETE';
        return $this->request('post', $url, $params, $options);
    }

    public function getResponse() {
        return $this->response;
    }
}

class PassportClient {
    protected $request;
    protected $url;

    public function __construct($host) {
        $this->url = "http://{$host}/passport";
        $this->request = new HttpRequest(array(
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => array('Accept: application/json')
        ));
    }

    protected function requestSuccess(array $response) {
        $code = $response['code'];
        return $code >= 200 && $code < 300;
    }

    protected function getEntityUrl($token) {
        return sprintf('%s/%s', $this->url, $token);
    }

    public function find($token) {
        $response = $this->request->get($this->getEntityUrl($token));

        if (!$this->requestSuccess($response))
            throw new PassportClientException('Passport not found', $response['code'], $response);

        return json_decode($response['body'], true);
    }

    public function create($email, $passwd) {
        $response = $this->request->post(
            $this->url,
            array('email' => $email, 'passwd' => $passwd)
        );

        if (!$this->requestSuccess($response))
            throw new PassportClientException('Passport create failed', $response['code'], $response);

        return json_decode($response['body'], true);
    }

    public function modify($token, array $props) {
        $response = $this->request->put(
            $this->getEntityUrl($token),
            $props
        );

        if (!$this->requestSuccess($response))
            throw new PassportClientException('Passport modify failed', $response['code'], $response);

        return json_decode($response['body'], true);
    }
}

class PassportClientException extends Exception {
    protected $response;
    public function __construct($message, $code, $response) {
        $this->response = $response;
        parent::__construct($message, $code);
    }

    public function getResponse() {
        return $this->response;
    }
}

//$cli = new PassportClient('passport.demo.ly');

//try {
//    $result = $cli->create('yangyi@surveypie.com', 'abc');
//    var_dump($result);
//} catch (PassportClientException $ex) {
//    var_dump($ex->getResponse());
//}

//try {
//    $result = $cli->modify('yangyi@surveypie.com', array('passwd' => 'def'));
//    var_dump($result);
//} catch (PassportClientException $ex) {
//    var_dump($ex->getResponse());
//}
