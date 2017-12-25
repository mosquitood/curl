<?php
// +----------------------------------------------------------------------
// | Mosqutood Package 
// +----------------------------------------------------------------------
// | Copyright (c) 2011-Now Mosquitood (http://www.mosquitood.com)
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author Mosquitood <mosquitood@gmail.com>
// +----------------------------------------------------------------------

namespace Mosquitood\Curl;

class Curl extends Adapter{

    public $curlError     = false;

    public $curlErrorCode = 0;

    public $curlErrorMsg  = '';

    public $time = 0;


    /**
     * 设置认证帐户和密码
     *
     * @access public 
     * @param  string $username
     * @param  string $password
     */

    public function setAuthorization($username, $password)
    {
        $this->authorizationToken = "[$username]:[$password]";
        return $this;
    }

    /**
     * 设置curl参数
     *
     * @access public 
     * @param  string $key
     * @param  value $value
     * @return $obj 
     */

    public function setOption($key, $value)
    {
        if($key === CURLOPT_HTTPHEADER){
            $this->header = array_merge($this->header, $value);
        }else{
            $this->option[$key] = $value;
        }
        return $this;
    }

    /**
     * GET Request 
     *
     * @access public 
     * @param  string $url 
     * @param  array  $data 
     * @return void 
     */

    public function get($url, $data = array())
    {
        $this->setMethod('GET');
        $requestUrl  = [];
        $requestData = $this->formatMultiUrlAndData($url, $data);
        foreach($requestData as $url => $data){
            $requestUrl[] = empty($data) ? $url : ($url . '?' . http_build_query($data)); 
        }
        $this->setRequestUrl($requestUrl);
        $this->setOption(CURLOPT_HTTPGET, true);
        $this->exec();
    } 


    /**
     * POST, 支持多个URL
     *
     * @access public 
     * @param  $url  string | array
     * @param  $data array 
     * @param  $json boolean
     * @return void  
     */

    public function post($url, $data = array(), $json = true){
        $this->setMethod('POST');
        $this->setOption(CURLOPT_HTTPHEADER, ['Expect:']);
        $this->setOption(CURLOPT_POST, true);
        $requestUrl  = [];
        $requestData = $this->formatMultiUrlAndData($url, $data, true);
        foreach($requestData as $key => $data){
            $requestData[$key] = $json ? json_encode($data) : http_build_query($data); 
        }
        $this->setRequestUrl((array)$url);
        $this->setRequestData($requestData);
        $this->exec();
    }

    /**
     * PUT, 支持多个URL
     *
     * @access public 
     * @param  $url  string | array
     * @param  $data array 
     * @param  $json boolean
     * @return void  
     */

    public function put($url, $data = array(), $json = true)
    {
        $this->setMethod('PUT');
        $this->setOption(CURLOPT_HTTPHEADER, ['Expect:']);
        $this->setOption(CURLOPT_PUT, true);
        $requestData = $this->formatMultiUrlAndData($url, $data, false);
        foreach($requestData as $key => $data){
            $requestData[$key] = $json ? json_encode($data) : http_build_query($data); 
        }
        $this->setRequestUrl((array)$url);
        $this->setRequestData($requestData);
        $this->exec();
    }


    /**
     * DELETE, 支持多个URL
     *
     * @access public 
     * @param  $url  string | array
     * @param  $data array 
     * @param  $json boolean
     * @return void  
     */

    public function delete($url, $data = array(), $json = true)
    {
        $this->setMethod('DELETE');
        $requestData = $this->formatMultiUrlAndData($url, $data, false);
        foreach($requestData as $key => $data){
            $requestData[$key] = $json ? json_encode($data) : http_build_query($data); 
        }
        $this->setRequestUrl((array)$url);
        $this->setRequestData($requestData);
        $this->exec();
    }

    /**
     * PATCH, 支持多个URL
     *
     * @access public 
     * @param  $url  string | array
     * @param  $data array 
     * @param  $json boolean
     * @return void  
     */

    public function patch($url, $data = array(), $json = true)
    {
        $this->setMethod('PATCH');
        $requestData = $this->formatMultiUrlAndData($url, $data, false);
        foreach($requestData as $key => $data){
            $requestData[$key] = $json ? json_encode($data) : http_build_query($data); 
        }
        $this->setRequestUrl((array)$url);
        $this->setRequestData($requestData);
        $this->exec();
    }


    /**
     * 创建一个CURL对象
     *
     * @access public 
     * @param  string $url 
     * @return curl_init()
     */

    private function _create($url)
    {
        $matches = parse_url($url);
        $host    = $matches['host'];
        $internalUrl = $this->getInternalUrl($url);
        if($this->hostIp){
            array_push($this->header, 'Host: ' . $host);
            $internalUrl = str_replace($host, $this->hostIp, $internalUrl);
        }
        $ch = curl_init ();
        curl_setopt($ch, CURLOPT_URL, $internalUrl);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        // 抓取跳转后的页面
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $this->connectTimeout);
        // 认证
        if(!is_null($this->authorizationToken)){ 
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $this->authorizationToken);
        }
        if($matches['scheme'] == 'https'){
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        if($this->proxyHost && $this->proxyPort){
            curl_setopt($ch, CURLOPT_PROXY,     $this->proxyHost);
            curl_setopt($ch, CURLOPT_PROXYPORT, $this->proxyPort);
        }
        if($this->cookie){
            curl_setopt($ch, CURLOPT_COOKIE, http_build_query((array)$this->cookie, '', ';'));
        }
        if($this->referer){
            curl_setopt($ch, CURLOPT_REFERER, $this->referer);
        }else{
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        }
        if($this->userAgent){
            curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        }elseif(array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER ['HTTP_USER_AGENT']);
        }else{
            curl_setopt($ch, CURLOPT_USERAGENT, "PHP/" . PHP_VERSION . " Mosquitood HTTPClient/1.0");
        }

        foreach($this->option as $key => $value){
            curl_setopt($ch, $key, $value);
            unset($key, $value);
        }
        if($this->header){
            $header = [];
            foreach($this->header as $item){
                // 防止有重复的header
                if(preg_match( '#(^[^:]*):.*$#', $item, $m)){
                    $header[$m[1]] = $item;
                }
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_values($header));
        }
        //设置POST数据
        if(isset($this->requestData[$url])){
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->requestData[$url]);
        }
        return $ch;
    }

    /**
     * 支持多线程获取网页
     * Note：PHP版本不同，会导致循环参数的不同，慎用
     *
     * @see http://cn.php.net/manual/en/function.curl-multi-exec.php#88453
     * @access public 
     * @param  array $urls
     * @return void  
     */

    protected function multiExec()
    {
        // 去重
        $urls = array_unique($this->requestUrl);
        if($urls){
            $mh = curl_multi_init ();
            // 监听列表
            $listenerList = [];
            // 返回值
            $result = [];
            // 总列队数
            $listNum = 0;
            // 排队列表
            $multiList = [];
            foreach($urls as $url){
                // 创建一个curl对象
                $current = $this->_create ($url);
                if($this->multiExecNum > 0 && $listNum >= $this->multiExecNum){
                    // 加入排队列表
                    $multiList[] = $url;
                } else {
                    // 列队数控制
                    curl_multi_add_handle ($mh, $current);
                    $listenerList[$url] = $current;
                    $listNum++;
                }
                $result[$url]= null;
            }
            unset($current);
            $running = null;
            // 已完成数
            $doneNum = 0;

            do{
                while(($execrun = curl_multi_exec($mh, $running)) == CURLM_CALL_MULTI_PERFORM);
                if($execrun != CURLM_OK){
                    break;
                }
                while(true == ($done = curl_multi_info_read($mh))){
                    foreach($listenerList as $doneUrl => $listener){
                        if($listener === $done ['handle']){
                            // 获取内容
                            $result[$doneUrl] = $this->parseResponse(curl_multi_getcontent($done['handle']), $done['handle']);

                            curl_close($done['handle']);
                            curl_multi_remove_handle($mh, $done['handle']);
                            // 把监听列表里移除
                            unset ($listenerList[$doneUrl], $listener);
                            $doneNum ++;
                            // 如果还有排队列表，则继续加入
                            if ($multiList) {
                                // 获取列队中的一条URL
                                $currentUrl = array_shift($multiList);
                                // 创建CURL对象
                                $current = $this->_create($currentUrl);
                                // 加入到列队
                                curl_multi_add_handle($mh, $current);
                                // 更新监听列队信息
                                $listenerList[$currentUrl] = $current;
                                unset ($current);
                                // 更新列队数
                                $listNum ++;
                            }
                            break;
                        }
                    }
                }
                if ($doneNum >= $listNum)
                    break;
            } while ( true );
            // 关闭列队
            curl_multi_close($mh);
            return $result;
        }
    }

    /**
     * 执行curl请求
     *
     * @access protected 
     * @param  void 
     * @return void 
     */ 

    protected function exec()
    {
        if(count($this->requestUrl) === 1){
            $ch = $this->_create(current($this->requestUrl)); 
            $response = curl_exec($ch);
            list(
                $this->response
                , $this->requestHeader
                , $this->responseHeader
                , $this->error 
                , $this->errorCode
                , $this->errorMsg
                , $this->curlError
                , $this->curlErrorCode
                , $this->curlErrorMsg
                , $this->httpError
                , $this->httpStatusCode
                , $this->httpErrorMsg
                , $this->time
            ) = $this->parseResponse($response, $ch);
            curl_close($ch);
        }else{
            $result = $this->multiExec(); 
            $this->response  = $this->requestHeader  = $this->responseHeader = []; 
            $this->error     = $this->errorCode      = $this->errorMsg       = [];
            $this->curlError = $this->curlErrorCode  = $this->curlErrorMsg  = [];
            $this->httpError = $this->httpStatusCode = $this->httpErrorMsg = $this->time = []; 
            foreach($result as $url => $value){
                list(
                    $this->response[$url]
                    , $this->requestHeader[$url]
                    , $this->responseHeader[$url]
                    , $this->error[$url] 
                    , $this->errorCode[$url]
                    , $this->errorMsg[$url]
                    , $this->curlError[$url]
                    , $this->curlErrorCode[$url]
                    , $this->curlErrorMsg[$url]
                    , $this->httpError[$url]
                    , $this->httpStatusCode[$url]
                    , $this->httpErrorMsg[$url]
                    , $this->time[$url]
                ) = $value;
            }
        } 
    }

    /**
     * 解析 ResponsTTPCliente
     *
     * @access protected 
     * @param  $response string 
     * @param  $ch 
     * @return array 
     */ 

    protected function parseResponse($response, $ch)
    {
        $sourceResponse = $response;
        $responseHeader = '';
        if(!(strpos($response, "\r\n\r\n") === false)) {
            list($responseHeader, $response) = explode("\r\n\r\n", $response, 2);
            while(strtolower(trim($responseHeader)) === 'http/1.1 100 continue'){
                list($responseHeader, $response) = explode("\r\n\r\n", $response, 2);
            }
            $responseHeader = preg_split('/\r\n/', $responseHeader, null, PREG_SPLIT_NO_EMPTY);
        }
        $headerSize    = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $response      = substr($sourceResponse, $headerSize);
        $curlErrorCode = curl_errno($ch);
        $curlErrorMsg  = curl_error($ch);
        $curlError     = !($curlErrorCode === 0);
        $httpCode      = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpError     = in_array(floor($httpCode / 100), array(4, 5));
        $error         = $httpError || $curlError;
        $httpErrorMsg  = $error ? (isset($responseHeaders['0']) ? $responseHeaders['0'] : '') : '';
        $errorCode     = $error ? ($curlError ? $curlErrorCode : $httpCode) : 0;
        $errorMsg      = $curlError ? $curlErrorMsg : $httpErrorMsg;
        $requestHeader = preg_split('/\r\n/', curl_getinfo($ch, CURLINFO_HEADER_OUT), null, PREG_SPLIT_NO_EMPTY);
        $time          = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        return [
            $response,
            $requestHeader,
            $responseHeader, 
            $error,
            $errorCode,
            $errorMsg,
            $curlError,
            $curlErrorCode,
            $curlErrorMsg,
            $httpError,
            $httpCode,
            $httpErrorMsg,
            $time,
        ]; 
    }

    /**
     * 格式化 请求url和请求数据
     *
     * @access protected 
     * @param  $url string | array 
     * @param  $data 
     * @param  $files boolean 是否支持文件上传 
     * @return array 
     */ 

    protected function formatMultiUrlAndData($url, $data = array(), $files = false)
    {

        $requestData = [];
        if(is_array($url)){
            foreach($url as $key => $value){
                if(isset($data[$key])){
                    if(!is_array($data[$key])){
                        parse_str($data[$key], $tmp);
                        $data[$key] = $tmp;
                    }
                    $requestData[$value] = $files && $this->files 
                        ? array_merge($data[$key], (array)$this->files)
                        : $data[$key];
                } 
            }
        }else{
            if(!is_array($data)){
                parse_str($data, $tmp);
                $data = $tmp; 
            } 
            $requestData[$url] = $files && $this->files
                ? array_merge($data, (array)$this->files)
                : $data;
        }
        return $requestData;
    }

    public function __destruct()
    {
        $this->reset();
    }
}
