# Класс для платежной системы FreeKassa

Отправка на страницу оплаты и принятие оповещений о платежах для ([freekassa.ru](https://freekassa.ru/))

#### PHP >= 7.4

## Документация
[docs.freekassa.ru](https://docs.freekassa.ru/)

## Установка
- Скопировать папку scr, настроить пути через autoload или require_once

## Начало работы
- Создайте аккаунт на [freekassa.ru](https://freekassa.ru/)
- Создайте кассу, скопируйте параметры `ID кассы`, `API КЛЮЧ КАССЫ`, `СЕКРЕТНОЕ СЛОВО 1` ,`СЕКРЕТНОЕ СЛОВО 2`


## Использование

- Для работы по API использовать FreeKassaApiMethods

1) Создание ссылки на оплату

```php
$orderId = '1234'; // uniq system order id
$order_amount = 1000; // Payment`s amount
$email = 'test@test.ru';
$ClassicKassa = new \FreeKassa\FreeKassaClassicMethods($merchant, $key, $secret, $secret2);
$payUrl = $ClassicKassa->getPayUrl($orderId,$order_amount,$email);
$ClassicKassa->redirectToPayUrl($orderId,$order_amount,$email);
```

2) Обработка ответа от FreeKassa

```php
$FreeKassa = new \FreeKassa\FreeKassa($merchant, $key, $secret, $secret2);

$FreeKassa->handler($_REQUEST,function ($id){
//your code
}); 
```
по умолчанию включен тестовый режим, поэтому проверки на разрешенные IP адреса от FreeKassa выключен


