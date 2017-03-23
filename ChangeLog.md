## 2016-11-10 (2.6)
* 完全移除 RSA 支持，删除 sys/rsa 目录，删除 lib/Rsa 类。
* ctr 新增默认 sql 属性，用于全局使用 sql 类 (需自行初始化)。
* mod 基类 getList 方法新增参数 array，可设置返回为数组而不是对象。
* const 新增 HTTPS\_PATH 表示安全的 HTTPS 链接（对应 HTTP_PATH）。
* URL 路径新增下划线（\_）的识别。
* 默认关闭访问写入访问日志，推荐您使用 Apache 的访问日志。
* set.php 新增 MUST_HTTPS 常量，设置为 true 后保证全站仅支持 https 访问。
* mod 支持更好的 use modKey 模式，支持主键为您定义的随机字串或唯一任意字段为随机字串的设置（新增识别 __key 内部变量）。
* Net::post 新增 $upload 参数（默认 false），当需要上传文件时设置为 true。
* Text 类新增 phoneSP 方法，用来判断是联通、电信还是移动的手机号。
* Text 类新增 phoneSPGroup 方法，用来将不同运营商的电话列表重新分组，同一个运营商的放在同一个数组。
* Aes 类将支持字符串类型的加密，而不仅仅是数组，需要加密数组请用 json_encode 或者序列化。
* Net 的 post 将自动识别是文件上传还是非普通 post。
* log 增加对 HTTP_USER_AGENT 的记录。
* 【重大更新】采用全新的路由机制，在 set.php 当中定义路由。

## 2016-09-14 (2.5)
* set 移除 RSA 相关支持，您需要用更优秀的 https 方案作为替代方案。
* ctr 类移除 writeAesJson() 方法，您需要用更优秀的 https 方案作为替代方案。
* ctr 类新增 isHttps() 方法，判断当前连接是否是安全的。
* ctr 类新增 mustHttps() 方法，若当前连接不安全则强制重定向到安全连接。

## 2016-08-30 (2.4)

* set 新增 STATIC_PATH。

## 2016-08-28 (2.3)
  
* 新增 $this->action;。
* 优化 .htaccess。
  
## 2016-02-10
  
* 优化大部分更新，版本号变为 2.1。
  
## 2016-02-10
  
* 大部分重写 Chameleon，版本号变更为 2.0。
  
## 2015-07-15
  
* 添加 Model::set 方法，可以根据条件设置属性。
* 修改 Model::update 方法，添加对多条件的支持。
* 修改 Model::create 方法，将之定为专用于 auto_increment 表的插入方法。
* 引入 trait ModelWithPKey，用于取代 Model::create。
  
## 2015-07-14
  
* Add Chinese supports to the JSON encoder
* Improve speed by removing useless judgements
* Fixed the Memcached engine
* Add "add" method for Lib.Memcached and Lib.Memcached.Emulator
* Update "my_PhpStorm.php"
* Remove config file's namespace
* Fix instanceof BUG
* Fix can not use M()->load to load Module BUG
* Add "__autoload" for load Model
* 新增 model 的主模型