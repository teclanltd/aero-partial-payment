<?php

namespace TeclanLtd\AeroPartialPayment;

use Aero\Payment\Models\Payment;
use Aero\Payment\PaymentDriver;
use Aero\Payment\Responses\PaymentResponse;
use Aero\Payment\SupportsMotoMode;
use Aero\Payment\SupportsRefunding;
use Aero\Cart\Models\OrderStatus;
use Illuminate\Support\Str;

class NoPaymentDriver extends PaymentDriver
{
    /**
     * The name of the payment driver.
     */
    public const NAME = 'no-payment';

        /**
     * Toggle MOTO mode (offline/admin visibility).
     */
    protected function supportsMerchantModeMoto(): bool
    {
        return true;
    }

    /**
     * Toggle merchant mode (store visibility).
     */
    protected function supportsMerchantModeEcom(): bool
    {
        return false;
    }

    /**
     * Register the payment.
     *
     * @return \Aero\Payment\Contracts\Response
     */
    public function register()
    {
        $response = new PaymentResponse();

        $response->setView("payments-{$this->getMerchantMode()}::paymentless");

        return $response;
    }

    /**
     * Complete the payment.
     *
     * @return \Aero\Payment\Contracts\Response
     */
    public function complete()
    {
        $response = new PaymentResponse();

        $response->setSuccessful(true);

        return $response;
    }
}