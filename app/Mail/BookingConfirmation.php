<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $ticketId;
    public $qrCodeUrl;
    public $busName;
    public $routeInfo;

    public function __construct($booking)
    {
        $this->booking = $booking;
        $this->ticketId = 'TKT-' . str_pad((string) $booking->id, 6, '0', STR_PAD_LEFT);
        $this->qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($this->ticketId);
        $this->busName = $booking->bus->bus_number ?? 'N/A';
        $route = $booking->route ?? $booking->bus->route;
        $this->routeInfo = $route ? ($route->start_point . ' - ' . $route->end_point) : 'N/A';
    }

    public function build()
    {
        return $this->subject('Smart Bus Booking Confirmation - ' . $this->ticketId)
                    ->view('emails.booking-qr');
    }
}
