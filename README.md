# LianzhMail
a quick mail send php lib

```
<?php

require __DIR__ . '/vendor/autoload.php';

$settings = [

		'driver'       => 'smtp', #Supported: "smtp", "mail", "sendmail"
	    'host' => 'smtp.exmail.qq.com',
	    'port' => 465,
	    'encryption' => 'ssl',
	    'username' => "zhang3@qq.com",
	    'password' =>"XzFqsmf",
	    'pretend' => 0,#启用此选项,邮件不会真正发送,而是写到日志文件中

	    'from' => array('address' => 'zhang3@wiwide.com', 'name' => '张三'),
	];

$mailHelper = new \LianzhMail\MailHelper($settings);

\LianzhMail\MailHelper::thisIsExample($mailHelper, ['li4@qq.com']);

```