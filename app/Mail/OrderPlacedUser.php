<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderPlacedUser extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        $subject = 'Your Order #' . $this->order->order_id . ' has been placed';
        return $this->subject($subject)
            ->view('emails.order_placed_user', [
                'order' => $this->order,
            ]);
    }
}
