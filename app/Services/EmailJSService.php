<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmailJSService
{
    protected $serviceId;
    protected $templateId;
    protected $publicKey;

    public function __construct()
    {
        $this->serviceId = env('EMAILJS_SERVICE_ID', 'service_gqhomxf');
        $this->templateId = env('EMAILJS_TEMPLATE_ID', 'template_f2ytysg');
        $this->publicKey = env('EMAILJS_PUBLIC_KEY', 'mvIhLYuKrURwzPYG_');
    }

    public function sendBookingEmail($booking, $email)
    {
        try {
            $ticketId = 'TKT-' . str_pad((string) $booking->id, 6, '0', STR_PAD_LEFT);
            $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($ticketId);
            
            $route = $booking->route ?? $booking->bus->route;
            $routeInfo = $route ? ($route->start_point . ' - ' . $route->end_point) : 'N/A';
            
            $templateParams = [
                'passenger_name' => $booking->user->name,
                'to_email' => $email,
                'qr_code' => $qrCodeUrl,
                'route' => $routeInfo,
                'bus_number' => $booking->bus->bus_number ?? 'N/A',
                'travel_date' => $booking->travel_date,
                'departure_time' => '08:00 AM'
            ];
            
            Log::info('Sending email to: ' . $email);
            Log::info('Template params:', $templateParams);
            
            $response = Http::asJson()->post('https://api.emailjs.com/api/v1.0/email/send', [
                'service_id' => $this->serviceId,
                'template_id' => $this->templateId,
                'user_id' => $this->publicKey,
                'template_params' => $templateParams
            ]);

            Log::info('EmailJS Response Status: ' . $response->status());
            Log::info('EmailJS Response Body: ' . $response->body());

            if ($response->successful()) {
                Log::info('Email sent successfully to: ' . $email);
                return true;
            } else {
                Log::error('Failed to send email. Status: ' . $response->status() . ', Body: ' . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Email sending exception: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }
}
