<?php

namespace Tests\Feature;

use App\Order;
use Tests\TestCase;
use App\Events\NewPurchase;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SendInvoiceNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SendsInvoiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function whenPurchaseIsMadeAnEventIsFired()
    {
        $initialDispatcher = Event::getFacadeRoot();
        Event::fake();
        Model::setEventDispatcher($initialDispatcher);

        $order = factory(Order::class)->create();

        Event::assertDispatched(NewPurchase::class, function ($e) use ($order) {
            return $e->order->id === $order->id;
        });
    }

    /** @test */
    public function whenPurchaseIsMadeTheNotificationIsFired()
    {
        Notification::fake();

        $order = factory(Order::class)->create();

        $order->load('customer');

        Notification::assertSentTo(
            $order->customer,
            SendInvoiceNotification::class,
            function ($notification, $channels) use ($order) {
                return $notification->order->id === $order->id;
            }
        );
    }
}
