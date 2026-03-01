<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
        .content { background: #f9fafb; padding: 30px; }
        .ticket-box { background: white; border: 2px solid #e5e7eb; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .qr-code { text-align: center; margin: 20px 0; }
        .qr-code img { max-width: 250px; }
        .details { margin: 15px 0; }
        .details-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
        .label { font-weight: bold; color: #6b7280; }
        .value { color: #111827; }
        .footer { text-align: center; color: #6b7280; font-size: 12px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎫 Booking Confirmed!</h1>
        </div>
        
        <div class="content">
            <p>Dear {{ $booking->user->name }},</p>
            <p>Your bus ticket has been successfully booked. Please find your ticket details below:</p>
            
            <div class="ticket-box">
                <div class="qr-code">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ $ticketId }}" alt="QR Code">
                    <p style="margin-top: 10px; font-size: 18px; font-weight: bold;">{{ $ticketId }}</p>
                </div>
                
                <div class="details">
                    <div class="details-row">
                        <span class="label">Passenger:</span>
                        <span class="value">{{ $booking->user->name }}</span>
                    </div>
                    <div class="details-row">
                        <span class="label">Bus:</span>
                        <span class="value">{{ $busName }}</span>
                    </div>
                    <div class="details-row">
                        <span class="label">Route:</span>
                        <span class="value">{{ $routeInfo }}</span>
                    </div>
                    <div class="details-row">
                        <span class="label">Seat Number:</span>
                        <span class="value">{{ $booking->seat_number }}</span>
                    </div>
                    <div class="details-row">
                        <span class="label">Fare:</span>
                        <span class="value">Rs. {{ $booking->fare }}</span>
                    </div>
                    <div class="details-row">
                        <span class="label">Travel Date:</span>
                        <span class="value">{{ $booking->travel_date }}</span>
                    </div>
                </div>
            </div>
            
            <p><strong>Important:</strong> Please show this QR code to the driver when boarding the bus.</p>
        </div>
        
        <div class="footer">
            <p>Thank you for choosing SmartBus!</p>
            <p>This is an automated email. Please do not reply.</p>
        </div>
    </div>
</body>
</html>
