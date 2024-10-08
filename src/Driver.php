<?php

namespace TeclanLtd\AeroPartialPayment;

use Aero\Payment\Models\Payment;
use Aero\Payment\PaymentDriver;
use Aero\Payment\Responses\PaymentResponse;
use Aero\Payment\SupportsMotoMode;
use Aero\Payment\SupportsRefunding;
use Aero\Cart\Models\OrderStatus;
use Illuminate\Support\Str;

class Driver extends PaymentDriver
{
    /**
     * The name of the payment driver.
     */
    public const NAME = 'partial-payment';

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
     * Toggle support for refunding.
     */
    public function supportsRefunding(): bool
    {
        return true;
    }

    /**
     * Register the payment.
     *
     * @return \Aero\Payment\Contracts\Response
     */
    public function register()
    {
        $id = (string) Str::uuid();

        $response = new PaymentResponse($id);

        $response->setView("partial-payment::payment");

        $response->setData([
            'id' => $id,
            'amount' => $this->order->unpaid_total / 100,
            'currency_code' => $this->order->currency_code,
        ]);

        return $response;
    }

    /**
     * Complete the payment.
     *
     * @return PaymentResponse
     */
    public function complete(): PaymentResponse
    {
        $id = $this->request->input('id', (string) Str::uuid());
        $reference = $this->request->input('reference');
        $transactionId = $this->request->input('transaction-id');
        $description = $this->request->input('description');
        $paymentMethod = "";

        if ( setting('teclan-maunal-payment.enable_payment_options') ) {
            $paymentMethod = $this->request->input('paymentmethod');
        }

        $amount = $this->request->amount
            ? $this->request->amount * 100
            : $this->order->total_rounded;

        /** @var $payment \Aero\Payment\Models\Payment */
        $payment = $this->order->payments()->updateOrCreate([
            'id' => $id,
        ], [
            'reference' => $reference ? $reference : $id,
            'payment_method_id' => $this->method->getKey(),
            'state' => Payment::AUTHORIZED,
            'amount' => $amount,
            'currency_code' => $this->order->currency->code,
            'exchange_rate' => $this->order->currency->exchange_rate,
            'data' => $transactionId ? [
                'transaction_id' => $transactionId,
                'description' => $description,
                'payment_method' => $paymentMethod
            ] : null,
        ]);

        return $this->capture($amount, $payment);
    }

    /**
     * Capture a payment that has been authorized.
     *
     * @param  int  $amount
     * @param  Payment  $payment
     * @return PaymentResponse
     */
    public function capture(int $amount, Payment $payment)
    {
        $response = new PaymentResponse($payment->id);

        $payment->capture([
            'amount' => $amount,
        ]);

        $response->setSuccessful(true);

        return $response;
    }

    /**
     * Refund a payment that has been captured.
     *
     * @param  int  $amount
     * @param  \Aero\Payment\Models\Payment  $payment
     * @return \Aero\Payment\Contracts\Response
     */
    public function refund(int $amount, Payment $payment)
    {
        $response = new PaymentResponse($payment->id);

        $response->setSuccessful(true);

        $payment->refund([
            'amount' => $amount,
        ]);

        return $response;
    }

}
