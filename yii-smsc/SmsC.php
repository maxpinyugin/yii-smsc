<?php

class SmsC extends CApplicationComponent
{
    const HOST  = 'http://smsc.ru/';
	const SEND = 'sys/send.php';
	const STATUS = 'sys/status.php';
	const COST = 'sys/send.php';
	const BALANCE = 'sys/balance.php';
	const SENDERS = 'sys/senders.php';
    
    public $id;
    
    public $login;
    
    public $password;
    
    public function init()
    {
        if (!function_exists ('curl_init'))
        {
            throw new CException ('Для работы расширения требуется cURL');
        }

        parent::init();
    }
    
    public function send($to, $text, $from = null, $time = null)
    {
        $url = self::HOST . self::SEND;
        $this->id = null;

        $params = $this->get_default_params();
        $params['phones'] = $to;
        $params['mes'] = $text;

        if ($from)
            $params['sender'] = $from;

        if ($time && $time < (time() + 7 * 60 * 60 * 24))
            $params['time'] = date("d.m.Y H:i", $time);

        $params['fmt'] = 1;
        $params['cost'] = 3;

        $result = $this->request($url, $params);
        $result = explode(",", $result);

        return array(
            'id' => $result[0],
            'balance' => $result[3]
        );
    }
    
    public function cost($to, $text)
    {
        $url = self::HOST . self::COST;
        $this->id = null;

        $params = $this->get_default_params();
        $params['phones'] = $to;
        $params['mes'] = $text;


        $params['fmt'] = 1;
        $params['cost'] = 1;

        $result = $this->request($url, $params);
        $result = explode(",", $result);

        return array(
            'price' => $result[0],
            'number' => $to
        );
    }
    
    public function status($id)
    {
        $url = self::HOST.self::STATUS;

        $params = $this->get_default_params();
        $params['id'] = $id;
        $result = $this->request($url, $params);

        return $result;
    }
    
    public function balance()
    {
        $url = self::HOST . self::BALANCE;

        $params = $this->get_default_params();
        $result = $this->request($url, $params);

        return $result;
    }
    
    public function senders() 
    {
        $url = self::HOST . self::SENDERS;
        $params = $this->get_default_params();
        $params['get_senders'] = 1;
        $result = $this->request( $url, $params );
        $result = explode("\n", rtrim($result));

        return array_map(function ($e) { return substr($e, 7); }, $result);
    }
    
    protected function get_default_params() 
    {
        return array(
            'login' => $this->login,
            'psw' => $this->password
        );
    }
    
    protected function request($url, $params = array()) 
    {
        $ch = curl_init($url);
        $options = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_POSTFIELDS => $params
        );
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}