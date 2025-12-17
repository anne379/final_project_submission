<?php
/**
 * Simple Mailer Wrapper
 * Designed to be easily upgraded to PHPMailer if SMTP credentials are provided.
 */
class NotificationMailer {
    
    /**
     * Send an email notification
     */
    public static function send($to, $subject, $message) {
        // Headers
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: BankAssist <no-reply@localhost>' . "\r\n";

        // Simple Template
        $body = "
        <html>
        <head>
          <style>
            body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
            .container { background: white; padding: 30px; border-radius: 8px; max-width: 600px; margin: 0 auto; }
            .header { border-bottom: 2px solid #3b82f6; padding-bottom: 10px; margin-bottom: 20px; }
            .footer { margin-top: 30px; font-size: 12px; color: #888; text-align: center; }
          </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2 style='color: #3b82f6;'>BankAssist Notification</h2>
                </div>
                <div class='content'>
                    $message
                </div>
                <div class='footer'>
                    &copy; " . date('Y') . " BankAssist. Do not reply to this email.
                </div>
            </div>
        </body>
        </html>
        ";

        // Try to send (Localhost usually puts this in a mail output folder or needs config)
        // We suppress errors to not break the app flow if mail server isn't set up
        @mail($to, $subject, $body, $headers);
    }

    /**
     * Send specific Request Update notification
     */
    public static function sendRequestUpdate($customerEmail, $requestId, $subject) {
        $msg = "
            <h3>Update on Request #$requestId</h3>
            <p><strong>Subject:</strong> " . h($subject) . "</p>
            <p>There is a new message or status update on your service request.</p>
            <p><a href='http://localhost:8888/customer_request_details.php?id=$requestId'>Click here to view the request</a></p>
        ";
        self::send($customerEmail, "Update on Request #$requestId", $msg);
    }
}
?>
