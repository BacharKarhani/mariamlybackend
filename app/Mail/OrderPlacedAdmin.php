<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderPlacedAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        $subject = 'New Order #' . $this->order->order_id . ' placed';
        return $this->subject($subject)
            ->view('emails.order_placed_admin', [
                'order' => $this->order,
            ]);
    }
}
