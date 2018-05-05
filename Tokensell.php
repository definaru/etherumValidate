<?php
// Get the API - https://etherscan.io
// create date 04.05.2018 12:45 AM
// author Inc. Defina
// support E-mail: info@defina.ru
// web site https://defina.ru


namespace budyaga_cust\users\models;

use Yii;
use budyaga_cust\users\models\Defina;


class Tokensell extends \yii\db\ActiveRecord
{
    
    
    const APIurl = 'https://api-rinkeby.etherscan.io/api';
    const YourApiKeyToken = ''; // обязательно укажите ваш API

    
    public static function tableName()
    {
        return '{yourTable}'; // MqSQL
    }
    
    
    public static function currentBalance()
    {
        // Tokensell::currentBalance()
        // получаем текущий баланс пользователя
        return Yii::$app->user->identity->balance;
    }
    

    public static function formUrlAddressETH($sender = '')
    {
        // Tokensell::formUrlAddressETH()
        // формируем HTTP запрос в браузере
        return self::APIurl.'?module=contract&action=getabi&address='.$sender.'&apikey='.self::YourApiKeyToken;
    }
    
    
    public static function viewUrlContent($sender = '')
    {
        // считываем содержимое сгенерированного адреса
        return file_get_contents(self::formUrlAddressETH($sender));
    }
    
    
    public function rightAdress($sender = '') 
    {
        $json = self::viewUrlContent($sender); // присваеваем содержимое страницы переменной
        $obj = json_decode($json); // декодируем в json файл
        return ($obj->result == 'Invalid Address format') ? '0' : '1'; // получаем ответ где 0 = лож, 1 = правда
    }


    public function validateSender($attribute, $params)
    {
        $money = self::rightAdress($this->sender); // проверяем адрес
        if ($money == 0) { // если есть ошибка
            $this->addError($attribute, 'Неправильный адрес Ethereum-кошелька.');
        }
    }            


    public function validateBalance($attribute, $params)
    {
        $balance = self::currentBalance();
        if ($this->price > $balance || $balance == 0) { // если введное число больше баланса или равно нулю
            $this->addError('price', 'Недостаточно средств');
        }
    }            
            
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['type', 'user', 'crypto', 'ip', 'time', 'adress', 'status'], 'required'],
            ['sender', 'required', 'message' => 'Вы не указали ваш криптокошелёк'],
            ['price', 'required', 'message' => 'Вы не указали количество токенов '.Defina::Curs],
            ['summa', 'required', 'message' => 'Необходимо указать сумму (считается автоматически) укажите '.Defina::Curs],
            ['sender', 'validateSender'], // проверяем криптокашелёк
            ['price', 'validateBalance'], // проверяем баланс
            [['price', 'message'], 'string'],
        ];
    }


    public function attributeLabels()
    {
        return [
            'id' => Yii::t('yii', 'ID'),
            'type' => Yii::t('yii', 'TYPE'),
            'user' => Yii::t('yii', 'USER'),
            'crypto' => Yii::t('yii', 'CRYPTO'),
            'summa' => Yii::t('yii', 'SUMMA'),
            'ip' => Yii::t('yii', 'IP'),
            'time' => Yii::t('yii', 'TIME'),
            'adress' => Yii::t('yii', 'ADRESS'),
            'sender' => Yii::t('yii', 'SENDER'),
            'price' => Yii::t('yii', 'PRICE'),
            'message' => Yii::t('yii', 'NOTICE'),
            'status' => Yii::t('yii', 'STATUS'),
        ];
    }
}
