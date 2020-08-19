hyperf 使用教程.
1) 准备工作: 统一开发ide, PhpStorm. 安装annotations plugin (File->Settings->plugins, 在市场上搜索php annotations关键词, 进行安装即可)
2) 切换composer国内源 composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
    2.1) composer require时出现内存溢出解决方案: php -d memory_limit=-1 /usr/bin/composer require xxxx 临时取消内存限制
    2.2) 如遇vendor目录代码报错, 则可能是依赖代码版本过旧, 需要 composer update -o
3) 统一编辑器配置
    3.1) File->Settings->Editor->File And Code Templates->includes->PHP File Header, 在空白处新增  declare(strict_types=1);
    3.2) 统一注释模板: File->Settings->Editor->File And Code Templates->PHP Function Doc Comment 新增方法描述, 方法作者, 方法编写日期. 整个模板如下:
        /**
        describe 
        author derick
        date ${DATE}
        ${PARAM_DOC}
        #if (${TYPE_HINT} != "void") * @return ${TYPE_HINT}
        #end
        ${THROWS_DOC}
        */
4) 项目结构介绍
--app       业务逻辑代码目录
    -- Amqp           rabbitmq 生产者与消费者逻辑业务目录
    -- Annotation     自定义注解目录
    -- Aspect         方法切面目录, 存放某些方法执行前与执行后补充某些业务的类库
    -- Constants      类常量定义目录, 还有部分常量定义在config目录下func文件
    -- Controller     控制器目录
    -- Dto            中台数据对象转换目录
    -- Exception      自定义异常与捕捉异常类目录
    -- Factory        工厂类目录, 存放一些替换vendor里组件的类
    -- Helper         存放一些工具类目录
    -- JavaService    存放中台接口服务目录
    -- Library        存放一些公共类库目录
    -- Listener       监听器目录
    -- Middleware     中间件目录
    -- Model          数据库model目录
    -- Task           定时任务目录
--bin       hyperf服务启动脚本目录
--config    框架以及业务配置目录
--runtime   运行时日志以及缓存文件目录
--storage   语言包目录
--test      测试用例目录
--vendor    第三方组件目录

2) hyperf 学习必备基础
    2.1) linux基础命令
    2.2) 网络通信以及协议的相关知识
    2.3) php线程, 进程, 协程等相关知识
    2.4) 扎实的面向对象知识
    2.5) swoole扩展的相关了解
    2.6) 官方视频教程: https://course.swoole-cloud.com/videos/5/new?from=hyperf.io
    
3) hyperf项目开发注意事项以及编码规范
    3.1) 新建php文件时, 头部加入代码declare(strict_types=1); 开启弱类型校验. 如有不明白什么是弱类型校验, 自行百度.
    3.2) 在给方法加注释说明的时候, 请勿随意添加@符号, @表示的是注解, 如需要给方法或者类加上相关说明时, 请更换其他符号或者不使用符号进行说明. 
    3.3) 在没有完全了解hyperf的机制前, 请勿使用new语法进行对象的创建, 如果需要创建对象, 请使用make()方法进行创建.
    3.4) 方法参数需加上数据类型以及标记方法返回值 例如: func(int $age, String $name) : int {}
    3.5) 前端接口返回字段, 以下划线区分变量名, 同数据库字段一致.
    3.6) 在使用中台接口数据时, 尽量使用内部dto进行数据转换, 不要直接使用中台字段.
    
4) hyperf 路由篇教程
    3.1) 路由教程: https://hyperf.wiki/#/zh/router, 推荐使用注解的方式进行定义路由. 清晰简洁, 也可防止配置爆炸问题. 但是在对注解进行命令的时候请勿太过随意, 防止路由过多时无法快速定位到controller. 所以注解命名请保持注解名和controller命一致.
    3.2) 总结: controller上加入@Controller注解, method上加入Mapping类注解, 例如:@RequestMapping or @GetMapping or @PostMapping  or @PutMapping or @PatchMapping or @DeleteMapping.即可实现路由自定义的使用. demo: controller注解 @Controller(prefix="/v1/user") + action注解 @GetMapping(path="info"), 请求地址为: {url}/v1/user/info
    3.3) 注意事项
        3.3.1) method注解使用时, path参数不要随意使用/开头, 例如 path="/info", 使用/开头表示为绝对路径即可访问,不会再受controller注解prefix限制:  {url}/info 即可访问到此方法. 此类用法通常用于一级页面.

5) hyperf 表单校验篇教程
    5.1) https://hyperf.wiki/#/zh-cn/validation?id=%e9%aa%8c%e8%af%81%e8%a7%84%e5%88%99

6) hyperf model使用篇教程
    6.1) model基于laravel model实现. 可参考 https://laravel.com/docs/5.8/queries#deletes 以及 https://laravel.com/docs/5.8/eloquent
    6.2) model生成代码 php bin/hyperf.php gen:model --prefix=yoshop_ yoshop_personal_commission_queue
      
7) hyperf amqp组件使用 
    7.1) 官方简易教程: https://hyperf.wiki/#/zh-cn/amqp?id=%e5%ae%89%e8%a3%85   补充教程: https://www.jianshu.com/p/a63785edd946
    7.2) rabbitmq的几大模式介绍. https://www.rabbitmq.com/getstarted.html 简单概括如下:
        简单模式：一个生产者，一个消费者
        work模式：一个生产者，多个消费者，每个消费者获取到的消息唯一。
        订阅模式：一个生产者发送的消息会被多个消费者获取。
        路由模式：发送消息到交换机并且要指定路由key ，消费者将队列绑定到交换机时需要指定路由key
        topic模式：将路由键和某模式进行匹配，此时队列需要绑定在一个模式上，“#”匹配一个词或多个词，“*”只匹配一个词 
    7.3) 命名规范:  
        exchange命名: php.yoshop."exchangeType",  exchangeType为交换机类型: 例如topic类型交换机, 命名为: php.yoshop.topic
        routeKey命名: rk."exchangeType"."queueName" exchangeType为交换机类型,同上.  queueName为队列名称
        queueName命名:queue."sys".xxx sys为业务方名称, 例如yoshop平台的队列 queue.yoshop.xxx,  xxx为业务场景

8) view 使用教程篇
	8.1) ThinkTemplate 使用教程 https://www.kancloud.cn/manual/think-template/1286403
	8.2) 不需要使用全局布局文件的view页面, 在页面顶部添加{__NOLAYOUT__}关键词即可.