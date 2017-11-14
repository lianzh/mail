<?php 

namespace LianzhMail;

use Swift_Mailer;
use Swift_Message;
use Psr\Log\LoggerInterface;

class Mailer 
{

	/**
	 * The Swift Mailer instance.
	 *
	 * @var \Swift_Mailer
	 */
	protected $swift;

	/**
	 * The global from address and name.
	 *
	 * @var array
	 */
	protected $from;

	/**
	 * The log writer instance.
	 *
	 * @var LoggerInterface
	 */
	protected $logger;

	/**
	 * Indicates if the actual sending is disabled.
	 *
	 * @var bool
	 */
	protected $pretending = false;

	/**
	 * Array of failed recipients.
	 *
	 * @var array
	 */
	protected $failedRecipients = array();

	/**
	 * Create a new Mailer instance.
	 *
	 * @param  array  $config
	 * @return void
	 */
	public function __construct(array $config)
	{	
		$this->swift = Provider::registerSwiftMailer($config);
		
		$logger = self::val($config, 'logger' , false);
        if ($logger)
        {
            $this->setLogger($logger);
        }
		
		$from = self::val($config, 'from' , false);

		if (is_array($from) && isset($from['address']))
		{
			$this->alwaysFrom($from['address'], $from['name']);
		}

		// Here we will determine if the mailer should be in "pretend" mode for this
		// environment, which will simply write out e-mail to the logs instead of
		// sending it over the web, which is useful for local dev environments.
		$pretend = self::val($config, 'pretend' , false);

		$this->pretend($pretend);
	}

	public static function val($arr, $key, $defaults= null)
    {
        return isset($arr[$key]) ? $arr[$key] : $defaults;
    }

	/**
	 * Set the global from address and name.
	 *
	 * @param  string  $address
	 * @param  string  $name
	 * @return void
	 */
	public function alwaysFrom($address, $name = null)
	{
		$this->from = compact('address', 'name');
	}

	/**
	 * Send a new message.
	 *
	 * @param  callback  $callback
	 * @return int
	 */
	public function send($callback)
	{
		$message = $this->createMessage();
		$data['message'] = $message;
		
		if ($callback && is_callable($callback))
		{
			call_user_func($callback,$message);
		}
		else
		{
			throw new \InvalidArgumentException('Invalid mail send callback.');
		}
		
		$message = $message->getSwiftMessage();

		return $this->sendSwiftMessage($message);
	}

	/**
	 * Send a Swift Message instance.
	 *
	 * @param  \Swift_Message  $message
	 * @return int
	 */
	protected function sendSwiftMessage($message)
	{
		if ( !$this->pretending)
		{
			return $this->swift->send($message, $this->failedRecipients);
		}
		elseif (!empty($this->logger))
		{
			$this->logMessage($message);

			return 1;
		}
	}

	/**
	 * Log that a message was sent.
	 *
	 * @param  \Swift_Message  $message
	 * @return void
	 */
	protected function logMessage($message)
	{
		$emails = implode(', ', array_keys((array) $message->getTo()));
		$this->logger->info("Pretending to mail message [{$message->getSubject()}] to: {$emails}");
	}

	/**
	 * Create a new message instance.
	 *
	 * @return Message
	 */
	protected function createMessage()
	{
		$message = new Message(new Swift_Message);

		// If a global from address has been specified we will set it on every message
		// instances so the developer does not have to repeat themselves every time
		// they create a new message. We will just go ahead and push the address.
		if (isset($this->from['address']))
		{
			$message->from($this->from['address'], $this->from['name']);
		}

		return $message;
	}

	/**
	 * Tell the mailer to not really send messages.
	 *
	 * @param  bool  $value
	 * @return void
	 */
	public function pretend($value = true)
	{
		$this->pretending = $value;
	}

	/**
	 * Get the Swift Mailer instance.
	 *
	 * @return \Swift_Mailer
	 */
	public function getSwiftMailer()
	{
		return $this->swift;
	}

	/**
	 * Get the array of failed recipients.
	 *
	 * @return array
	 */
	public function failures()
	{
		return $this->failedRecipients;
	}

	/**
	 * Set the log writer instance.
	 *
	 * @param  LoggerInterface  $logger
	 */
	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

}
