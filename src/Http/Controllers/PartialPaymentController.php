<?php

namespace TeclanLtd\AeroPartialPayment\Http\Controllers;

use Aero\Cart\Models\Order;
use Aero\Admin\Http\Controllers\Controller;
use Aero\Payment\Models\Payment;
use Illuminate\Support\Facades\DB;
use Aero\Payment\Responses\PaymentResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PartialPaymentController extends Controller
{

    public function update(Request $request, Order $order)
    {
        $id = (string) Str::uuid();
        $orderid = $request->input('order_id');
        $reference = $request->input('reference');
        $description = $request->input('description');
        $amountInput = $request->input('transaction-amount');
        $transactionId = $id;
        $paymentMethod = "";

        if ( setting('teclan-maunal-payment.enable_payment_options') ) {
            $paymentMethod = $request->input('paymentmethod');
        }


        $order = Order::find($orderid);

        $amount = $amountInput * 100;

        $partPayId = DB::table('payment_methods')->where('driver', 'partial-payment')->value('id');

        /** @var $payment \Aero\Payment\Models\Payment */
        $payment = $order->payments()->updateOrCreate([
            'id' => $id,
        ], [
            'reference' => $reference ? $reference : $id,
            'payment_method_id' => $partPayId,
            'state' => Payment::AUTHORIZED,
            'amount' => $amount,
            'currency_code' => 'GBP',
            'exchange_rate' => '1.00',
            'purchase_id' => $orderid,
            'data' => $transactionId ? [
                'transaction_id' => $transactionId,
                'refernce' => $reference,
                'payment_method' => $paymentMethod,
                'description' => $description,
                'amount' => '£' . $amountInput
            ] : null,
        ]);

        return $this->capture($amount, $payment, $orderid);
    }

    /**
     * Capture a payment that has been authorized.
     *
     * @param  int  $amount
     * @param  \Aero\Payment\Models\Payment  $payment
     */
    public function capture(int $amount, Payment $payment, int $orderid)
    {
        $response = new PaymentResponse($payment->id);

        $payment->capture([
            'amount' => $amount,
        ]);

        $response->setSuccessful(true);

        return redirect()->route('admin.orders.view', $orderid)->with('message', 'updated');
    }
}
