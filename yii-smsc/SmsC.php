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
    
    public $charset = 'utf-8';
    
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

        $params['fmt'] = 3;
        $params['cost'] = 3;

        $result = $this->request($url, $params);
	$res = explode(",", $result);
        if (isset($res['0']) && ($res['0'] == 0)) {
            $result = json_decode('{"error": '.$res[1].',"error_code": 0}');
        } else {
            $result = json_decode($result);
        }

        return $result;
    }
   

    public function send_voice($to, $text, $voice = 'm', $from = null, $time = null)
    {
        $url = self::HOST . self::SEND;
        $this->id = null;

        $params = $this->get_default_params();
        $params['phones'] = $to;
        $params['mes'] = $text;

        $params['fmt'] = 3;
        $params['cost'] = 3;

        $params['call'] = 1;
        $params['voice'] = $voice;

        $result = $this->request($url, $params);
        $res = explode(",", $result);
        if (isset($res['0']) && ($res['0'] == 0)) {
            $result = json_decode('{"error": '.$res[1].',"error_code": 0}');
        } else {
            $result = json_decode($result);
        }

        return $result;
    }
 
    public function cost($to, $text, $from = null, $call = null)
    {
        $url = self::HOST . self::COST;
        $this->id = null;

        $params = $this->get_default_params();
        $params['phones'] = $to;
        $params['mes'] = $text;
        
        if (isset($from)){
            $params['sender'] = $from;
        }

        $params['fmt'] = 1;
        $params['cost'] = 1;

        if (isset($call))
            $params['call'] = $call;

        $result = $this->request($url, $params);

	if ($result) {
            $result = explode(",", $result);
            return array(
                'price' => $result[0],
                'number' => $result[1]
            );
        } else {
            return array(
                'price' => 0,
                'number' => 0
            );
        }
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
    
    public function addSender($sender, $cmt = ''){
		$url = self::HOST . self::SENDERS;
        $params = $this->get_default_params();
        $params['add'] = 1;
        $params['sender'] = $sender;
        $params['cmt'] = $cmt;
        $result = $this->request( $url, $params );
        $result = explode("\n", rtrim($result));
	}
	
	public function addDigitSender($sender){
		$url = self::HOST . self::SENDERS;
        $params = $this->get_default_params();
        $params['send_code'] = 1;
        $params['sender'] = $sender;
        $result = $this->request( $url, $params );
        $result = explode("\n", rtrim($result));
	}
	
	public function confirmDigitSender($sender, $code){
		$url = self::HOST . self::SENDERS;
        $params = $this->get_default_params();
        $params['check_code'] = 1;
        $params['code'] = $code;
        $params['sender'] = $sender;
        $result = $this->request( $url, $params );
        $result = explode("\n", rtrim($result));
	}
    
    public function senders() 
    {
        $url = self::HOST . self::SENDERS;
        $params = $this->get_default_params();
        $params['get'] = 1;
        $result = $this->request( $url, $params );
        $result = explode("\n", rtrim($result));

        return array_map(function ($e) { return substr($e, 7); }, $result);
    }
    
    protected function get_default_params() 
    {
        return array(
            'login' => $this->login,
            'psw' => $this->password,
            'charset'=>$this->charset
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
        if (!$result) {
            $result = '0, '.curl_error($ch);
        }
        curl_close($ch);

        return $result;
    }
}
