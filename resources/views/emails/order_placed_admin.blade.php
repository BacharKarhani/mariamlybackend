<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>New Order #{{ $order->order_id }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: Arial, sans-serif; color:#222; line-height:1.5; margin:0; padding:24px;">
  @php
    $customerName = trim(($order->user->fname ?? '').' '.($order->user->lname ?? ''));
    if ($customerName === '') {
        $customerName = $order->user->name ?? ('User #'.$order->user_id);
    }
  @endphp

  <h2 style="margin:0 0 8px 0;">New Order Received: #{{ $order->order_id }}</h2>
  <p style="margin:0 0 16px 0;">
    Customer: <strong>{{ $customerName }}</strong>
    ({{ $order->user->email ?? 'N/A' }})
  </p>

  <h3 style="margin:24px 0 8px 0;">Order Summary</h3>
  <ul style="margin:0 0 16px 20px; padding:0;">
    <li>Subtotal: <strong>${{ number_format((float) $order->subtotal, 2) }}</strong></li>
    <li>Shipping: <strong>${{ number_format((float) $order->shipping, 2) }}</strong></li>
    <li>Total: <strong>${{ number_format((float) $order->total, 2) }}</strong></li>
    <li>Payment: <strong>{{ strtoupper($order->payment_code) }}</strong></li>
    <li>Status: <strong>{{ ucfirst($order->order_status) }}</strong></li>
    <li>Placed at: <strong>{{ $order->date_added }}</strong></li>
  </ul>

  @if($order->address)
    <h3 style="margin:24px 0 8px 0;">Delivery Address</h3>
    <p style="margin:0 0 16px 0;">
      {{ $order->address->first_name ?? '' }} {{ $order->address->last_name ?? '' }}<br>
      {{ $order->address->full_address ?? '' }}<br>
      @if(!empty($order->address->more_details))
        <em>{{ $order->address->more_details }}</em><br>
      @endif
      @if(!empty($order->address->phone_number))
        Phone: {{ $order->address->phone_number }}<br>
      @endif
      @if(!empty($order->address->zone?->name))
        Zone: {{ $order->address->zone->name }}
      @endif
    </p>
  @endif

  @if(!empty($order->orderProducts) && $order->orderProducts->count())
    <h3 style="margin:24px 0 8px 0;">Items</h3>
    <table width="100%" cellpadding="8" cellspacing="0" border="1" style="border-collapse: collapse;">
      <thead>
        <tr>
          <th align="left">Product</th>
          <th align="right">Qty</th>
          <th align="right">Price</th>
          <th align="right">Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach($order->orderProducts as $line)
          <tr>
            <td>
              {{ $line->product->name ?? ('#'.$line->product_id) }}
              @if($line->variant)
                <br><small style="color: #666;">Color: {{ $line->variant->color }}</small>
              @endif
            </td>
            <td align="right">{{ $line->quantity }}</td>
            <td align="right">${{ number_format((float) $line->price, 2) }}</td>
            <td align="right">${{ number_format((float) $line->total, 2) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif

  <p style="margin:24px 0 0 0; color:#666;">— Mariamly Bot</p>
</body>
</html>
