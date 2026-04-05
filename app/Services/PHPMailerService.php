<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;
use Throwable;

class PHPMailerService
{
    public function sendBookingConfirmation(Booking $booking, string $recipientEmail): bool
    {
        $booking->loadMissing(['user', 'bus.route', 'route']);

        $ticketId = 'TKT-' . str_pad((string) $booking->id, 6, '0', STR_PAD_LEFT);
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($ticketId);
        $busName = $booking->bus->bus_number ?? 'N/A';
        $route = $booking->route ?? $booking->bus->route;
        $routeInfo = $route ? ($route->start_point . ' - ' . $route->end_point) : 'N/A';

        $htmlBody = view('emails.booking-qr', [
            'booking' => $booking,
            'ticketId' => $ticketId,
            'qrCodeUrl' => $qrCodeUrl,
            'busName' => $busName,
            'routeInfo' => $routeInfo,
        ])->render();

        $mail = new PHPMailer(true);

        try {
            $this->configureTransport($mail);

            $smtpHost = strtolower((string) config('services.phpmailer.host'));
            $smtpUsername = (string) config('services.phpmailer.username');
            $fromAddress = (string) config('services.phpmailer.from_address');
            $fromName = (string) config('services.phpmailer.from_name');

            // Gmail commonly rejects messages when the authenticated account and
            // sender address do not match, so default to the SMTP username.
            if (
                str_contains($smtpHost, 'gmail.com') &&
                filter_var($smtpUsername, FILTER_VALIDATE_EMAIL)
            ) {
                $fromAddress = $smtpUsername;
            }

            $mail->setFrom($fromAddress, $fromName);
            $mail->addAddress($recipientEmail, $booking->user->name ?? 'Passenger');
            $mail->isHTML(true);
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $mail->Subject = 'Smart Bus Booking Confirmation - ' . $ticketId;
            $mail->Body = $htmlBody;
            $mail->AltBody = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], PHP_EOL, $htmlBody)));

            $mail->send();

            Log::info('Booking confirmation email sent via PHPMailer.', [
                'booking_id' => $booking->id,
                'recipient' => $recipientEmail,
            ]);

            return true;
        } catch (PHPMailerException $e) {
            Log::error('PHPMailer failed to send booking confirmation.', [
                'booking_id' => $booking->id,
                'recipient' => $recipientEmail,
                'error' => $e->getMessage(),
            ]);

            return false;
        } catch (Throwable $e) {
            Log::error('Unexpected booking email error.', [
                'booking_id' => $booking->id,
                'recipient' => $recipientEmail,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function configureTransport(PHPMailer $mail): void
    {
        $host = (string) config('services.phpmailer.host');
        $port = (int) config('services.phpmailer.port');
        $username = (string) config('services.phpmailer.username');
        $password = (string) config('services.phpmailer.password');
        $encryption = config('services.phpmailer.encryption');
        $smtpAuth = (bool) config('services.phpmailer.smtp_auth');

        $mail->isSMTP();
        $mail->Host = $host;
        $mail->Port = $port;
        $mail->SMTPAuth = $smtpAuth;
        $mail->Username = $username;
        $mail->Password = $password;
        $mail->SMTPAutoTLS = true;

        if ($encryption === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($encryption === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }
    }
}
