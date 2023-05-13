<?php

namespace FreeKassa;

class FreeKassaApiMethods extends FreeKassa
{
    private string $url = 'https://api.freekassa.ru/v1/';

    /**
     * @param $data
     * @return string
     */
    public function sign($data): string
    {
        ksort($data);
        $sign = hash_hmac('sha256', implode('|', $data), $this->key);
        return $sign;
    }

    /**
     * @param int $orderId
     * @param string|null $paymentId
     * @param int|null $orderStatus
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @param int|null $page
     * @return array
     */
    public function getOrderList(int $orderId, string $paymentId = null, int $orderStatus = null, string $dateFrom = null, string $dateTo = null, int $page = null)
    {
        $data['shopId'] = $this->merchant; // * ID магазина
        $data['nonce'] = time(); // * Уникальный ID запроса, должен всегда быть больше предыдущего значения
        if (!empty($orderId)) {
            $data['orderId'] = $orderId; // Номер заказа Freekassa
        }
        if (!empty($paymentId)) {
            $data['paymentId'] = $paymentId; // Номер заказа в Вашем магазине
        }
        if (!empty($orderStatus)) {
            $data['orderStatus'] = $orderStatus; //Статус заказа
        }
        if (!empty($dateFrom)) {
            $data['dateFrom'] = $dateFrom; // Дата с
        }
        if (!empty($dateTo)) {
            $data['dateTo'] = $dateTo; // Дата по
        }
        if (!empty($page)) {
            $data['page'] = $page; // Страница
        }
        $data['signature'] = $this->sign($data); //* Подпись запроса
        return $this->sendCurl($data, 'orders');
    }

    /**
     * @param string $order_id
     * @param float $amount
     * @param string $email
     * @param int|null $idPaySys
     * @param string|null $tel
     * @return array
     */
    public function createOrder(string $order_id, float $amount, string $email, int $idPaySys = null, string $tel = null): array
    {
        // * -  required fields
        $data = [];
        $data['shopId'] = $this->merchant; // * ID магазина
        $data['nonce'] = time(); // * Уникальный ID запроса, должен всегда быть больше предыдущего значения
        $data['paymentId'] = $order_id; // Номер заказа в Вашем магазине
        if (!empty($idPaySys)) {
            $data['i'] = $idPaySys; // * ID платежной системы
        }
        $data['email'] = $email; // * Email покупателя
        $data['ip'] = $_SERVER['REMOTE_ADDR']; // *IP покупателя
        $data['amount'] = $amount; // * Сумма оплаты
        $data['currency'] = $this->currency; // * Валюта оплаты
        if (!empty($tel)) {
            $data['tel'] = $tel; // Телефон плательщика, требуется в некоторых способах оплат
        }
        if (!empty($this->success_url)) {
            $data['success_url'] = $this->success_url; // Переопределение урла успеха (для включения данного параметра обратитесь в поддержку)
        }
        if (!empty($this->failure_url)) {
            $data['failure_url'] = $this->failure_url; //Переопределение урла ошибки (для включения данного параметра обратитесь в поддержку)
        }
        if (!empty($this->notification_url)) {
            $data['notification_url'] = $this->notification_url; // Переопределение урла уведомлений (для включения данного параметра обратитесь в поддержку)
        }
        $data['signature'] = $this->sign($data); //* Подпись запроса
        return $this->sendCurl($data, 'orders/create');
    }

    /**
     * @param array $data
     * @param string $path
     * @return array
     */
    private function sendCurl(array $data, string $path): array
    {
        $request = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url . $path);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FAILONERROR, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        $result = trim(curl_exec($ch));
        curl_close($ch);

        $response = json_decode($result, true);
        return $response;
    }

    /**
     * @param int $order_id
     * @param float $amount
     * @param string $email
     * @param int|null $idPaySys
     * @param string|null $tel
     * @return void
     */
    public function redirectToPayUrl(int $order_id , float $amount, string $email, int $idPaySys = null, string $tel = null): void
    {
        $order = $this->createOrder($order_id, $amount, $email, $idPaySys, $tel);
        if ($order['type'] != 'success') {
            echo 'type false';
            exit();
        }
        $url = $order['location'];
        if (headers_sent() === false) {
            header('Location: ' . $url);
        } else {
            echo '<script type="text/javascript">window.location = "' . $url . '"</script>';
        }
    }

}