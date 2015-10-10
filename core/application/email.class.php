<?php

class OPAL_Email {

	private $subject = "";
	private $headers = "";
	private $message = "";
	
	public function __construct($subject,$message){
		$this->subject = $subject;
		$this->message = $message;
		$this->setCharset("UTF-8");
	}
	
	private function setCharset($charset){
		$this->headers .= "Content-Type: text/plain; charset=$charset; \r\n";
		$this->headers .= "MIME-Version: 1.0 \r\n";
		$this->headers .= "Content-Transfer-Encoding: 8BIT \r\n";
	}	
	
	public function setReturnPath($email){
		$this->headers .= "From: $email \r\n";
		$this->headers .= "Reply-To: $email \r\n";
	}
	
	public function send($to){
		return @mail($to,"=?UTF-8?B?".base64_encode($this->subject)."?=",$this->message,$this->headers);
	}
	
}