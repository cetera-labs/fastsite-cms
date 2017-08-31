<?php
namespace Cetera\Mail; 

/**
 * Почта.
 * 
 * @package CeteraCMS
 */ 
class Event {
	
	use \Cetera\DbConnection;
	
	public static function attach()
	{
		\Cetera\Event::attach('*', array('\Cetera\Mail\Event','trigger'));
	}	
	
	/* 
	* DEPRECATED. Backward  compatibility.
	*/
	public static function register()
	{
	}		

	public static function trigger($id, $params)
	{
		$data = self::getDbConnection()->fetchAll('SELECT * FROM mail_templates WHERE active=1 and event = ?', array($id));
		
		foreach ($data as $template)
		{
			$twig = new \Twig_Environment(
				new \Twig_Loader_Array( $template ),
				array(
					'autoescape' => false,
				)
			);	
			$twig->addFunction(new \Twig_SimpleFunction('_', function($text) {
				return \Cetera\Application::getInstance()->getTranslator()->_($text);
			}));
		
			$mail = new \PHPMailer(true);
			
			$emailStr = $twig->render('mail_to', $params);
			if (!$emailStr) continue;
			$toEmails = preg_split("/[,;]/", $emailStr );
			if(!empty($toEmails)) {
				foreach($toEmails as $address) $mail->AddAddress($address);	
			}
			else {
				continue;
			}
			
			$mail->CharSet = 'utf-8';
			$mail->ContentType = $template['content_type'];
			$mail->From = $twig->render('mail_from_email', $params);
			$mail->FromName = $twig->render('mail_from_name', $params);
			$mail->Subject = $twig->render('mail_subject', $params);
			$mail->Body = $twig->render('mail_body', $params);
			$mail->Send();
		}
		return count($data);
	}		
    
}