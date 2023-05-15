<?php

namespace FreeKassa;

class FreeKassaClassicMethods extends FreeKassa
{
    private string $url = 'https://pay.freekassa.ru/';

    /**
     * @param string $orderId
     * @param float $order_amount
     * @param string $mail
     * @param array $user_parameters
     * @return string
     */
    public function sendPayForm(string $orderId, float $order_amount, string $mail, array $user_parameters = []): string
    {
        $order_amount = number_format($order_amount, 2, '.', '');
        $sign = $this->signature($order_amount, $orderId);
        $form = sprintf(
            '<form id="freekassa-pay" method="get" action="%s" style="display: none !important;">
                <input type="hidden" name="m" value="%s">
                <input type="hidden" name="oa" value="%s">
                <input type="hidden" name="o" value="%s">
                <input type="hidden" name="s" value="%s">
                <input type="hidden" name="currency" value="%s">
                <input type="hidden" name="lang" value="%s">
                <input type="hidden" name="em" value="%s">',
            htmlspecialchars($this->url),
            htmlspecialchars($this->merchant),
            htmlspecialchars($order_amount),
            htmlspecialchars($orderId),
            htmlspecialchars($sign),
            htmlspecialchars($this->currency),
            htmlspecialchars($this->lang),
            htmlspecialchars($mail)
        );

        foreach ($user_parameters as $key => $item) {
            $form .= sprintf('<input type="hidden" name="us_%s" value="%s">', htmlspecialchars($key), htmlspecialchars($item));
        }

        $form .= '<input type="submit" name="pay" value="Оплатить"></form><script>document.getElementById("freekassa-pay").submit()</script>';

        return $form;
    }

    /**
     * @param string $orderId
     * @param float $order_amount
     * @param string $email
     * @param int|null $idPaySys
     * @param string|null $phone
     * @param array $user_parameters
     * @return void
     */
    public function redirectToPayUrl(string $orderId, float $order_amount, string $email, int $idPaySys = null, string $phone = null, array $user_parameters = []): void
    {
        $url = $this->getPayUrl($orderId, $order_amount, $email, $idPaySys, $phone, $user_parameters);
        $this->redirect($url);
    }

    /**
     * @param string $orderId
     * @param float $order_amount
     * @param string $email
     * @param int|null $idPaySys
     * @param string|null $phone
     * @param array $user_parameters
     * @return string
     */
    public function getPayUrl(string $orderId, float $order_amount, string $email, int $idPaySys = null, string $phone = null, array $user_parameters = []): string
    {
        $order_amount = number_format($order_amount, 2, '.', '');
        $sign = $this->signature($order_amount, $orderId);
        $query = [];
        $query['m'] = $this->merchant;
        $query['oa'] = $order_amount;
        $query['o'] = $orderId;
        $query['s'] = $sign;
        $query['currency'] = $this->currency;
        $query['lang'] = $this->lang;
        if (!empty($email)) {
            $query['email'] = $email;
        }
        if (!empty($idPaySys)) {
            $query['i'] = $idPaySys;
        }
        if (!empty($phone)) {
            $query['phone'] = $phone;
        }
        if (!empty($user_parameters)) {
            foreach ($user_parameters as $key => $parameter) {
                if (empty($parameter)) {
                    continue;
                }
                $query['us' . $key] = $parameter;
            }
        }
        return $this->url . '?' . http_build_query($query);
    }

}