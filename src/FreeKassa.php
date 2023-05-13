<?php

namespace FreeKassa;

class FreeKassa
{
    protected int $merchant;
    protected string $key;
    protected string $secret;
    protected string $secret2;
    protected string $currency;
    protected string $lang;
    protected string $success_url;
    protected string $failure_url;
    protected string $notification_url;
    protected array $allowedIps = ['168.119.157.136', '168.119.60.227', '138.201.88.124', '178.154.197.79'];
    private bool $sandbox = true;


    /**
     * @param int $merchant
     * @param string $key
     * @param string $secret
     * @param string $secret2
     * @param string $currency
     * @param string $lang
     * @param string $success_url
     * @param string $failure_url
     * @param string $notification_url
     */
    function __construct(int $merchant, string $key, string $secret, string $secret2, string $currency = 'RUB', string $lang = 'ru', string $success_url = '', string $failure_url = '', string $notification_url = '')
    {
        $this->merchant = $merchant;
        $this->key = $key;
        $this->secret = $secret;
        $this->secret2 = $secret2;
        $this->currency = $currency;
        $this->lang = $lang;
        $this->success_url = $success_url ?? 'http://' . $_SERVER['HTTP_HOST'] . '/success-pay/';
        $this->failure_url = $failure_url ?? 'http://' . $_SERVER['HTTP_HOST'] . '/failure-pay/';
        $this->notification_url = $notification_url ?? 'http://' . $_SERVER['HTTP_HOST'] . '/pay/';
    }
    /**
     * @param $callback
     * @return void
     */
    public function handler(array $request, callable $callback = null): void
    {


        if (!$this->sandbox) {
            if (!$this->allowIP()) {
                echo "IP not allowed\n";
                exit();
            }
        }
        $requestAmount = $request['AMOUNT'];
        $requestOrderId = $request['MERCHANT_ORDER_ID'];
        $requestSign = $request['SIGN'];
        $sign = $this->callbackSignature($requestAmount, $requestOrderId);

        if (!$this->checkRequestSign($sign, $requestSign)) {
            echo "bad sign\n";
            exit();
        }

        if (is_callable($callback)) {
            call_user_func_array($callback, [$requestOrderId]);
        }

        echo "OK$requestOrderId\n";
        exit();

    }

    /**
     * @return string
     */
    protected function getRealIpAddr(): string
    {
        if (isset($_SERVER['HTTP_X_REAL_IP'])) {
            return $_SERVER['HTTP_X_REAL_IP'];
        }
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * @return bool
     */
    public function allowIP(): bool
    {
        $ip = $this->getRealIpAddr();
        if ($ip == '127.0.0.1') {
            return true;
        }
        return in_array($ip, $this->allowedIps);
    }

    /**
     * @param float $orderAmount
     * @param string $orderId
     * @return string
     */
    public function signature(float $orderAmount, string $orderId): string
    {
        return md5($this->merchant . ':' . $orderAmount . ':' . $this->secret . ':' . $this->currency . ':' . $orderId);
    }


    /**
     * @param float $orderAmount
     * @param string $orderId
     * @return string
     */
    public function callbackSignature(float $orderAmount, string $orderId): string
    {
        return md5($this->merchant . ':' . $orderAmount . ':' . $this->secret2 . ':' . $orderId);
    }

    /**
     * @param string $sign
     * @return bool
     */
    private function checkRequestSign(string $sign, string $requestSign): bool
    {
        return $sign == $requestSign;
    }

    /**
     * @param string $url
     * @return void
     */
    protected function redirect(string $url): void
    {
        if (headers_sent() === false) {
            header('Location: ' . $url);
        } else {
            echo '<script type="text/javascript">window.location = "' . $url . '"</script>';
        }
    }

}