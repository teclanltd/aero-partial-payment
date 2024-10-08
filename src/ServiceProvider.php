<?php

namespace TeclanLtd\AeroPartialPayment;

use Aero\Common\Facades\Settings;
use Aero\Common\Providers\ModuleServiceProvider;
use Aero\Common\Settings\SettingGroup;
use Aero\Payment\PaymentProcessor;
use Aero\Admin\AdminSlot;
use Illuminate\Routing\Router;

class ServiceProvider extends ModuleServiceProvider
{
    protected $listen = [
        //\Aero\Cart\Events\OrderPlaced::class => [
        //    \TeclanLtd\AeroPartialPayment\Listeners\ExportOrder::class,
        //],
    ];

    public function setup()
    {
        Router::addAdminRoutes(__DIR__ . '/../routes/admin.php');

        PaymentProcessor::registerDriver(Driver::NAME, Driver::class);

        PaymentProcessor::registerDriver(NoPaymentDriver::NAME, NoPaymentDriver::class);

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'partial-payment');

        AdminSlot::inject('orders.order.view.cards.extra.bottom', 'partial-payment::order-payment');

        Settings::group('teclan-maunal-payment', static function (SettingGroup $group) {
            $group->boolean('enable_payment_options')->default(FALSE);
            $group->array('payment_options');
        });
    }
}
