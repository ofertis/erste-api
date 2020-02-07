<?php
namespace ErsteApi;


class ApiRequester extends ErsteApi
{
    /**
     * Sends a request to API server and returns JSON response
     *
     * @param string $apiUrl
     * @param array  $urlParameters
     * @param array  $postData
     *
     * @return string
     */
    public function apiRequest($apiUrl, array $urlParameters = [], array $postData = null)
    {
        $tokenType = $this->accessToken['token_type'];
        $accessToken = $this->accessToken['access_token'];

        $url = vsprintf($this->config[$apiUrl], $this->buildQuery($urlParameters));
        $postFields = $postData ? json_encode($postData) : null;
        $httpHeader[] = "web-api-key: " . $this->config['webApiKey'];
        $httpHeader[] = "Authorization: " . $tokenType . ' ' . $accessToken;
        $httpHeader[] = isset($postData) ? "Content-Type: application/json" : "";

        return $this->sendRequest($url, $postFields, $httpHeader);
    }

    /**
     * Transforms inner array of 'query' key into http query
     *
     * @param array $arr
     *
     * @return array
     */
    protected function buildQuery(array $arr)
    {
        if(isset($arr['query'])){
            $arr['query'] = http_build_query($arr['query']);
        }
        return $arr;
    }

    /**
     * Sends request using curl and returns response (JSON)
     *
     * @param string $url
     * @param string $postFields
     * @param array  $httpHeader
     *
     * @return string
     */
    protected function sendRequest($url, $postFields, array $httpHeader)
    {
        $ch = curl_init();

        if (FALSE === $ch)
            trigger_error('Failed to initialize Curl');

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        if($postFields){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);

        $response = curl_exec($ch);

        if (FALSE === $response)
            echo "Curl failed with error " . curl_error($ch) . ": ".curl_errno($ch);

        curl_close($ch);

        return $response;
    }
}