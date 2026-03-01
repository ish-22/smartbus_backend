<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: system-ui, sans-serif, Arial; font-size: 14px; background:#f4f6f9; padding:20px; margin:0;">
  
  <div style="max-width:600px; margin:auto; background:#ffffff; padding:20px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.05);">

    <p style="padding-top: 14px; border-top: 1px solid #eaeaea; font-size:16px;">
      Your Smart Bus QR Code is Ready!
    </p>

    <p>
      Hello <strong>{{ $booking->user->name }}</strong>,
    </p>

    <p>
      Thank you for booking with <strong>Smart Bus Transport System</strong>.
      Please use the QR code below to board your bus.
    </p>

    <div style="text-align:center; margin:20px 0;">
      <img src="{{ $qrCodeUrl }}" alt="Smart Bus QR Code" style="width:200px; height:200px; border:1px solid #ddd; padding:10px; border-radius:6px;" />
      <p style="font-weight:bold; margin-top:10px;">{{ $ticketId }}</p>
    </div>

    <p><strong>Route:</strong> {{ $routeInfo }}</p>
    <p><strong>Bus Number:</strong> {{ $busName }}</p>
    <p><strong>Seat:</strong> {{ $booking->seat_number }}</p>
    <p><strong>Date:</strong> {{ $booking->travel_date }}</p>
    <p><strong>Departure Time:</strong> 08:00 AM</p>
    <p><strong>Fare:</strong> Rs. {{ $booking->fare }}</p>

    <p style="color:#d9534f;">
      This QR code is valid only for your scheduled trip. Please present it when boarding.
    </p>

    <p>
      Do not share this QR code with anyone. If you did not make this booking, please contact our support team immediately.
    </p>

    <p style="margin-top:20px;">
      Thank you for choosing Smart Bus 🚍<br/>
      Safe Travels!
    </p>

  </div>
</body>
</html>
