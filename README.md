yii-smsc
=======

Yii-расширение для работы с api сервиса [smsc.ru](http://smsc.ru)

## Установка

Загрузите yii-smsc из этого репозитория github:

    cd protected/extensions
    git clone git://github.com/pimax/yii-smsc.git

В protected/config/main.php внесите следующие строки:

    'components' => array
    (
        'sms' => array
        (
            'class'    => 'application.extensions.yii-sms.SmsC',
            'login'     => 'username',      // Логин на сайте smsc.ru
            'password'   => 'password',     // Пароль
        )
    );

## Использование

Отправка SMS:

    Yii::app()->sms->send('79251234567', 'Проверка отправки');
	Yii::app()->sms->send('79251234567', 'Проверка отправки', 'Имя отправителя', time());

Статус SMS:

    Yii::app()->sms->status('sms id');

Стоимость SMS:

    Yii::app()->sms->cost('79251234567', 'Проверка отправки');

Баланс:

    Yii::app()->sms->balance();

Отправители:

    Yii::app()->sms->senders();