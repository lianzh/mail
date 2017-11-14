<?php

namespace LianzhMail;

/**
 * 邮件工具
 *
<code>
# 配置
'config' => array(
    'driver'       => 'smtp', #Supported: "smtp", "mail", "sendmail"
    'host' => 'smtp.exmail.qq.com',
    'port' => 465,
    'encryption' => 'ssl',
    'username' => "yourname",
    'password' =>"yourpass",
    // 'sendmail' => '/usr/sbin/sendmail -bs',#When using the "sendmail" driver to send e-mails
    'pretend' => 1,#启用此选项,邮件不会真正发送,而是写到日志文件中
    // 'logger'     => \Psr\Log\LoggerInterface , #使用日志对象

    'from' => array('address' => 'youraddress', 'name' => 'yoursitetitle'),
),
</code>
 */
class MailHelper
{
    
	/**
     * 当前请求
     *
     * @var Mailer
     */
    public $mailer;
	
	/**
	 * Create a new Mailer instance.
	 *
	 * @param array $config
	 */
	public function __construct(array $config) {
		$this->mailer = new Mailer($config);
	}

    /**
     * 发送邮件
     * 
     * @param  Email   $email      发送对象
     * @param  array   $to         接收人数组
     * @param  string  $subject    邮件标题
     * @param  string  $htmlBody   邮件内容
     * @param  array   $attachData 附件数据
     * 
     * @return int
     */
    public function sendmail($to, $subject, $htmlBody, $attachData=false)
    {
        $ret = $this->mailer->send(function($message) use($to,$subject, $htmlBody, $attachData)
        {
            $message->to($to)->subject($subject);
            $message->setBody($htmlBody, 'text/html');

            if (is_array($attachData) && count($attachData) == 3)
            {
                list ($data, $originName, $asName) = $attachData;
                $message->attachData($data, $originName, array('as' => $message->encodeAttachmentName($asName)));
            }
            
        });

        return $ret;
    }

    public static function thisIsExample(Email $mail)
    {
        $subject = '系统出错了,亲';
        $to = [
            'xiaoming@demo.com'
        ];

        $htmlBody = '<h2 style="color:#ff0000">擦,尼玛系统又出故障了,具体看附件</h2>';
        $attachData = [
                '我是附件数据', 'alert.txt', '尼玛看这里.txt'
            ];

        $mail->sendmail($to, $subject, $htmlBody, $attachData);
    }

}
