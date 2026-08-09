<?php
// DO NOT ALTER WITHOUT APPROVAL — Security Module 1
// Last modified: 2026-07-03
// Part of: SPED LMS — Email Helper (Dual SMTP / Brevo HTTP API)

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailHelper {
    /**
     * Helper function to get environment variables (compatible with hosts that disable putenv)
     */
    private static function getEnvVar($key, $default = '') {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }

    /**
     * Internal generic mail dispatcher.
     * Routes email via Brevo HTTP API if API key is present, otherwise falls back to PHPMailer SMTP.
     */
    private static function sendMail($toEmail, $toName, $subject, $htmlBody) {
        $apiKey = self::getEnvVar('BREVO_API_KEY');
        if (!empty($apiKey)) {
            return self::sendMailViaBrevo($toEmail, $toName, $subject, $htmlBody);
        }
        return self::sendMailViaPHPMailer($toEmail, $toName, $subject, $htmlBody);
    }

    /**
     * Send email via Brevo HTTP API (compatible with InfinityFree firewall)
     */
    private static function sendMailViaBrevo($toEmail, $toName, $subject, $htmlBody) {
        $apiKey = self::getEnvVar('BREVO_API_KEY');
        $senderEmail = self::getEnvVar('MAIL_FROM_ADDRESS', 'allysacanonizado43@gmail.com');
        $senderName = self::getEnvVar('MAIL_FROM_NAME', 'SignED');
        
        $data = [
            'sender' => [
                'name' => $senderName,
                'email' => $senderEmail
            ],
            'to' => [
                [
                    'email' => $toEmail,
                    'name' => $toName
                ]
            ],
            'subject' => $subject,
            'htmlContent' => $htmlBody
        ];
        
        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: application/json',
            'api-key: ' . $apiKey,
            'content-type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        } else {
            error_log("Brevo mail sending failed (HTTP $httpCode): " . $response);
            return false;
        }
    }

    /**
     * Send email via PHPMailer SMTP (for local development/WAMP)
     */
    private static function sendMailViaPHPMailer($toEmail, $toName, $subject, $htmlBody) {
        $mail = self::getMailer();
        if (!$mail) return false;
        
        try {
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $htmlBody;
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("PHPMailer SMTP failed to send: " . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * PHPMailer configuration setup
     */
    private static function getMailer() {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = self::getEnvVar('MAIL_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth   = true;
            $mail->Username   = self::getEnvVar('MAIL_USERNAME');
            $mail->Password   = self::getEnvVar('MAIL_PASSWORD');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = self::getEnvVar('MAIL_PORT', 587);
            
            // Disable SSL verification for development (XAMPP Windows fix)
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            // Default sender
            $mail->setFrom(
                self::getEnvVar('MAIL_FROM_ADDRESS', 'noreply@spedlms.local'),
                self::getEnvVar('MAIL_FROM_NAME', 'SPED LMS')
            );
            
            return $mail;
        } catch (Exception $e) {
            error_log("Mailer configuration failed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Send role verification notification to admin
     */
    public static function sendRoleVerificationNotification($adminEmail, $userName, $requestedRole) {
        $subject = 'New Role Verification Request - SPED LMS';
        $body = "
            <h2>New Role Verification Request</h2>
            <p><strong>User:</strong> $userName</p>
            <p><strong>Requested Role:</strong> " . ucwords(str_replace('_', ' ', $requestedRole)) . "</p>
            <p>Please review the submitted documents and approve or reject the request.</p>
            <p><a href='" . self::getEnvVar('APP_URL') . "/admin/role-requests'>View Request</a></p>
        ";
        return self::sendMail($adminEmail, 'Admin', $subject, $body);
    }
    
    /**
     * Send role approval notification to user
     */
    public static function sendRoleApprovalNotification($userEmail, $userName, $approvedRole) {
        $subject = 'Role Verification Approved - SPED LMS';
        $body = "
            <h2>Role Verification Approved</h2>
            <p>Hello $userName,</p>
            <p>Your role verification request has been <strong>approved</strong>.</p>
            <p><strong>Approved Role:</strong> " . ucwords(str_replace('_', ' ', $approvedRole)) . "</p>
            <p>You can now access your dashboard and begin using the system.</p>
            <p><a href='" . self::getEnvVar('APP_URL') . "/dashboard'>Go to Dashboard</a></p>
        ";
        return self::sendMail($userEmail, $userName, $subject, $body);
    }
    
    /**
     * Send role rejection notification to user
     */
    public static function sendRoleRejectionNotification($userEmail, $userName, $rejectedRole, $reason) {
        $subject = 'Role Verification Rejected - SPED LMS';
        $body = "
            <h2>Role Verification Rejected</h2>
            <p>Hello $userName,</p>
            <p>Your role verification request has been <strong>rejected</strong>.</p>
            <p><strong>Requested Role:</strong> " . ucwords(str_replace('_', ' ', $rejectedRole)) . "</p>
            <p><strong>Reason:</strong> $reason</p>
            <p>You may submit a new request with updated documentation.</p>
            <p><a href='" . self::getEnvVar('APP_URL') . "/role/select'>Submit New Request</a></p>
        ";
        return self::sendMail($userEmail, $userName, $subject, $body);
    }
    
    /**
     * Send IEP meeting notification
     */
    public static function sendIEPMeetingNotification($recipientEmail, $recipientName, $studentName, $meetingDate) {
        $subject = 'IEP Meeting Scheduled - SPED LMS';
        $body = "
            <h2>IEP Meeting Scheduled</h2>
            <p>Hello $recipientName,</p>
            <p>An IEP meeting has been scheduled for:</p>
            <p><strong>Student:</strong> $studentName</p>
            <p><strong>Date & Time:</strong> $meetingDate</p>
            <p>Please ensure your availability for this meeting.</p>
            <p><a href='" . self::getEnvVar('APP_URL') . "/iep/meetings'>View Meeting Details</a></p>
        ";
        return self::sendMail($recipientEmail, $recipientName, $subject, $body);
    }
    
    /**
     * Send generic notification
     */
    public static function sendNotification($toEmail, $toName, $subject, $htmlBody) {
        return self::sendMail($toEmail, $toName, $subject, $htmlBody);
    }
    
    /**
     * Send OTP verification email
     */
    public static function sendOTPEmail($userEmail, $userName, $otp) {
        $subject = 'Verify Your Email - SPED LMS';
        $body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background: linear-gradient(135deg, #1e4072 0%, #a01422 100%); padding: 20px; text-align: center;'>
                    <h1 style='color: white; margin: 0;'>SPED LMS</h1>
                </div>
                <div style='padding: 30px; background: #f5f5f5;'>
                    <h2 style='color: #1e4072;'>Verify Your Email Address</h2>
                    <p>Hello <strong>$userName</strong>,</p>
                    <p>Thank you for registering with SPED LMS. Please use the verification code below to complete your registration:</p>
                    
                    <div style='background: white; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; border: 2px solid #a01422;'>
                        <h1 style='color: #a01422; font-size: 36px; letter-spacing: 8px; margin: 0;'>$otp</h1>
                    </div>
                    
                    <p><strong>This code will expire in 10 minutes.</strong></p>
                    <p>If you didn't request this verification, please ignore this email.</p>
                    
                    <hr style='border: none; border-top: 1px solid #ddd; margin: 30px 0;'>
                    <p style='color: #666; font-size: 12px;'>
                        This is an automated message from SPED LMS. Please do not reply to this email.
                    </p>
                </div>
            </div>
        ";
        return self::sendMail($userEmail, $userName, $subject, $body);
    }
    
    /**
     * Send welcome email after verification
     */
    public static function sendWelcomeEmail($userEmail, $userName) {
        $subject = 'Welcome to SPED LMS!';
        $body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background: linear-gradient(135deg, #1e4072 0%, #a01422 100%); padding: 20px; text-align: center;'>
                    <h1 style='color: white; margin: 0;'>Welcome to SPED LMS!</h1>
                </div>
                <div style='padding: 30px; background: #f5f5f5;'>
                    <h2 style='color: #1e4072;'>Your Account is Ready</h2>
                    <p>Hello <strong>$userName</strong>,</p>
                    <p>Welcome to the Special Education Learning Management System! Your email has been successfully verified and your account is now active.</p>
                    
                    <div style='background: white; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #a01422;'>
                        <h3 style='color: #1e4072; margin-top: 0;'>Next Steps:</h3>
                        <ul style='color: #333;'>
                            <li>Complete your profile information</li>
                            <li>Select your role (Parent or Staff)</li>
                            <li>Explore the dashboard and available features</li>
                        </ul>
                    </div>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='" . self::getEnvVar('APP_URL') . "/dashboard' style='background: #a01422; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; display: inline-block;'>
                            Go to Dashboard
                        </a>
                    </div>
                    
                    <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
                    
                    <hr style='border: none; border-top: 1px solid #ddd; margin: 30px 0;'>
                    <p style='color: #666; font-size: 12px;'>
                        This is an automated message from SPED LMS. Please do not reply to this email.<br>
                        For support, contact: <a href='mailto:admin@spedlms.local'>admin@spedlms.local</a>
                    </p>
                </div>
            </div>
        ";
        return self::sendMail($userEmail, $userName, $subject, $body);
    }
}
