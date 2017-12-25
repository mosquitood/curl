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

abstract class Adapter{

    /**
     * User Agent 浏览器的身份标识
     *
     * @var string
     */

    protected $userAgent;

    /**
     * 页面来源
     *
     * @var string
     */

    protected $referer;

    /**
     * cookie
     *
     * @var array 
     */

    protected $cookie  = [];

    /**
     * 待上传的文件
     *
     * @var array 
     */

    protected $files   = []; 

    /**
     * 设置hostIp,请求时把域名转换为ip,减少dns解析耗时
     * 
     * @var string 
     */ 

    protected $hostIp  = '';

    /**
     * HTTP Headers 
     *
     * @var array 
     */ 

    protected $header  = [];


    /**
     * Request Options 
     *
     * @var array 
     */ 

    protected $option  = [];

    /**
     * Request timeout 
     *
     * @var int 
     */

    protected $timeout = 30;

    /**
     * Request URLs 
     * 
     * @var array 
     */ 

    protected $requestUrl = [];
    /**
     * 待提交的数据
     *
     * @var array
     */
    protected $requestData = [ ];

    /**
     * 多列队任务进程数，0表示不限制
     *
     * @var int
     */

    protected $multiExecNum = 20;

    /**
     * 默认请求方法
     *
     * @var string
     */

    protected $method = 'GET';

    /**
     * 默认连接超时时间，毫秒
     *
     * @var int
     */

    protected $connectTimeout = 3000;

    /**
     * 代理host 
     *
     * @var string 
     */ 

    protected $proxyHost;

    /**
     * 代理端口
     *
     * @var int 
     */ 

    protected $proxyPort;


    /**
     * Token 
     *
     * @var string 
     */ 

    protected $authorizationToken;

    /**
     * 内部域名 
     *
     * @var array 
     */ 

    protected $internalUrlMap = [];


    /**
     * HTTP Response 
     *
     * @var mixed 
     */ 

    public $response = null;

    /**
     * HTTP Response headers 
     *
     * @var array 
     */ 

    public $responseHeader = [];


    /**
     * 请求是否有错 
     *
     * @var boolean 
     */ 

    public $error = false;

    /**
     * 错误码 
     *
     * @var int 
     */ 

    public $errorCode = 0;

    /**
     * 错误信息 
     *
     * @var string 
     */ 

    public $errorMsg = '';

    /**
     * HTTP Error 
     *
     * @var boolean
     */

    public $httpError = false;

    /**
     * HTTP Code 
     *
     * @var int 
     */ 

    public $httpStatusCode = 200;

    /**
     * HTTP Msg 
     *
     * @var string 
     */ 

    public $httpErrorMsg =  '';

    /**
     * 设置REST的类型
     *
     * @access public 
     * @param  string $method GET|POST|DELETE|PUT 等，不传则返回当前method
     * @return string
     * @return $this; 
     **/

    public function setMethod($method = null)
    {
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * 设置Header
     *
     * @access public 
     * @param  string key  
     * @param  mixed  value 
     * @return $this; 
     **/

    public function setHeader($item, $value)
    {
        $this->header = array_merge($this->header, [$item . ": " . $value]);
        return $this;
    }

    /**
     * 设置Header
     *
     * @access public 
     * @param  array $header
     * @return $this
     **/

    public function setHeaders($headers){
        $this->header = array_merge($this->header, (array)$headers);
        return $this;
    }

    /**
     * 设置代理服务器访问
     *
     * @access public 
     * @param  string $host
     * @param  string $port
     * @return $this 
     **/

    public function setHttpProxy($host, $port)
    {
        $this->proxyHost = $host;
        $this->proxyPort = $port;
        return $this;
    }

    /**
     * 设置IP
     *
     * @access public 
     * @param  string $ip
     * @return $this 
     **/

    public function setHostIp($ip)
    {
        $this->hostIp = $ip;
        return $this;
    }

    /**
     * 设置User Agent
     *
     * @access public 
     * @param  string $userAgent
     * @return $this 
     **/

    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    /**
     * 设置Http Referer
     *
     * @access public
     * @param  string $referer
     * @return $this 
     **/

    public function setReferer($referer)
    {
        $this->referer = $referer;
        return $this;
    }

    /**
     * 设置Cookie
     *
     * @access public 
     * @param  string $cookie
     * @return $this
     **/

    public function setCookie($cookie)
    {
        $this->cookie = $cookie;
        return $this;
    }

    /**
     * 设置多个列队默认排队数上限
     *
     * @access public 
     * @param  int $num
     * @return $this 
     **/

    public function setMultiMaxNum($num = 0)
    {
        $this->multiExecNum = (int)$num;
        return $this;
    }

    /**
     * 设置超时时间
     *
     * @access public 
     * @param  int $timeoutp
     * @return $this 
     **/

    public function setTimeout($timeout){
        $this->timeout = $timeout;
    }

    public function setRequestUrl($urls)
    {
        $this->requestUrl = $urls;
        return $this;
    }

    public function setRequestData($data)
    {
        $this->requestData = $data;
        return $this; 
    }

    public function setInternalUrlMap($map = array())
    {
        $this->internalUrlMap = $map;
        return $this;
    }

    public function getInternalUrl($url)
    {
        return isset($this->internalUrlMap[$url]) && $this->internalUrlMap[$url] 
            ? $this->internalUrlMap[$url]
            : $url;
    }

    /**
     * 重置设置
     **/

    public function reset()
    {
        $this->option      = [];
        $this->header      = [];
        $this->files       = [];
        $this->hostIp      = '';
        $this->cookie      = '';
        $this->referer     = '';
        $this->method      = 'GET';
        $this->requestData = [];
        $this->requestUrl  = [];
        $this->response    = null;
        $this->error       = false;
        $this->errorCode   = 0;
        $this->errorMsg    = '';
        $this->httpError   = false;
        $this->httpMsg     = '';
        $this->httpStatusCode = 200;
    }

    abstract public function setAuthorization($username, $password);
    abstract public function get   ($url, $data = array());
    abstract public function put   ($url, $data = array(), $json = true);
    abstract public function post  ($url, $data = array(), $json = true);
    abstract public function patch ($url, $data = array(), $json = true);
    abstract public function delete($url, $data = array(), $json = true);
}
