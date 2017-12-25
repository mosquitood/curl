Mosquitood Curl Component
========================

### 安装
```
composer require mosquitood/curl
```

### 使用
- GET 请求
```
require __DIR__ . '/../vendor/autoload.php';
$request = new Mosquitood\Curl\Curl;
$request->get('https://www.baidu.com");
$request->response;
```
- POST请求
```
require __DIR__ . '/../vendor/autoload.php';
$request = new Mosquitood\Curl\Curl;
$request->post('https://www.example.com", array('k1' => 'v1', 'k2' => 'v1'), $json = true/false);
$request->response; //response 未处理
$request->error; //是否出错
$request->errorCode; //错误码
$request->errorMsg; //错误信息
$request->requestHeader; //请求头
$request->responseHeader; //响应头
```

- DELETE请求
  1. 参照POST请求
  2. $request->delete()

- PATCH请求
  1. 参照POST请求
  2. $request->patch()

### 多url异步请求
 1. 不建议使用，因为multi_curl的某些参数会根据php版本变动
```
$url = array(
  'k1' => 'url1',
  'k2' => 'url2',
);
$data = array(
  'k1' => array('v1' => 'v11'),
  'k2' => array('v2' => 'v22')
);
$request->post($url, $data);
$request->response[$url];
```
