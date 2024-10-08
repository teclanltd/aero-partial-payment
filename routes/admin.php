<?php

use TeclanLtd\AeroPartialPayment\Http\Controllers\PartialPaymentController;
use Illuminate\Support\Facades\Route;

Route::post('teclan/partial-payment/order/update', [PartialPaymentController::class, 'update'])->name('admin.teclanpart.orders.update');
