<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Your Order #{{ $order->order_id }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: Arial, sans-serif; color:#222; line-height:1.5; margin:0; padding:24px;">
  <h2 style="margin:0 0 8px 0;">
    Thanks for your order, 
    {{
      trim(($order->user->fname ?? '').' '.($order->user->lname ?? '')) 
        ?: ($order->user->name ?? 'Customer')
    }}!
  </h2>

  <p style="margin:0 0 16px 0;">
    Order <strong>#{{ $order->order_id }}</strong> has been placed successfully.
  </p>

  <!-- Client Info Section -->
  <h3 style="margin:24px 0 8px 0;">Client Information</h3>
  <div style="margin:0 0 16px 0; padding:12px; background-color:#f8f9fa; border-left:4px solid #007bff;">
    <p style="margin:0 0 4px 0;"><strong>Name:</strong> {{ trim(($order->user->fname ?? '').' '.($order->user->lname ?? '')) ?: ($order->user->name ?? 'Customer') }}</p>
    <p style="margin:0 0 4px 0;"><strong>Email:</strong> {{ $order->user->email ?? 'N/A' }}</p>
    <p style="margin:0;"><strong>Order Date:</strong> {{ $order->date_added }}</p>
  </div>

  <!-- Delivery Address Section -->
  @if($order->address)
    <h3 style="margin:24px 0 8px 0;">Delivery Address</h3>
    <div style="margin:0 0 16px 0; padding:12px; background-color:#f8f9fa; border-left:4px solid #28a745;">
      <p style="margin:0 0 4px 0;"><strong>{{ $order->address->first_name ?? '' }} {{ $order->address->last_name ?? '' }}</strong></p>
      <p style="margin:0 0 4px 0;">{{ $order->address->full_address ?? '' }}</p>
      @if(!empty($order->address->more_details))
        <p style="margin:0 0 4px 0;"><em>{{ $order->address->more_details }}</em></p>
      @endif
      @if(!empty($order->address->phone_number))
        <p style="margin:0 0 4px 0;"><strong>Phone:</strong> {{ $order->address->phone_number }}</p>
      @endif
      @if(!empty($order->address->zone?->name))
        <p style="margin:0;"><strong>Zone:</strong> {{ $order->address->zone->name }}</p>
      @endif
    </div>
  @endif

  <!-- Order Details Section -->
  @if(!empty($order->orderProducts) && $order->orderProducts->count())
    <h3 style="margin:24px 0 8px 0;">Order Details</h3>
    <table width="100%" cellpadding="8" cellspacing="0" border="1" style="border-collapse: collapse; margin-bottom:16px;">
      <thead style="background-color:#f8f9fa;">
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

  <!-- Summary Section -->
  <h3 style="margin:24px 0 8px 0;">Order Summary</h3>
  <div style="margin:0 0 16px 0; padding:12px; background-color:#f8f9fa; border-left:4px solid #ffc107;">
    <ul style="margin:0; padding-left:20px;">
      <li><strong>Subtotal:</strong> ${{ number_format((float) $order->subtotal, 2) }}</li>
      <li><strong>Shipping:</strong> ${{ number_format((float) $order->shipping, 2) }}</li>
      <li><strong>Total:</strong> ${{ number_format((float) $order->total, 2) }}</li>
      <li><strong>Payment Method:</strong> {{ strtoupper($order->payment_code) }}</li>
      <li><strong>Status:</strong> {{ ucfirst($order->order_status) }}</li>
    </ul>
  </div>

  <p style="margin:24px 0 0 0;">We'll keep you posted once your order is on the way.</p>
  <p style="color:#666;">Mariamly</p>
</body>
</html>
