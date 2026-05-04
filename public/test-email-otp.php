<?php
// Test Email OTP Configuration
// This script tests if PHPMailer can send OTP emails

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Helpers/MailHelper.php';

// Load environment variables
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

echo "<h1>Email OTP Configuration Test</h1>";
echo "<hr>";

// Check environment variables
echo "<h2>1. Environment Variables Check</h2>";
$mailConfig = [
    'MAIL_HOST' => getenv('MAIL_HOST'),
    'MAIL_PORT' => getenv('MAIL_PORT'),
    'MAIL_USERNAME' => getenv('MAIL_USERNAME'),
    'MAIL_PASSWORD' => getenv('MAIL_PASSWORD') ? '***' . substr(getenv('MAIL_PASSWORD'), -4) : 'NOT SET',
    'MAIL_FROM_ADDRESS' => getenv('MAIL_FROM_ADDRESS'),
    'MAIL_FROM_NAME' => getenv('MAIL_FROM_NAME'),
];

echo "<table border='1' cellpadding='10'>";
foreach ($mailConfig as $key => $value) {
    $status = $value ? '✅' : '❌';
    echo "<tr><td><strong>$key</strong></td><td>$value</td><td>$status</td></tr>";
}
echo "</table>";

// Check PHPMailer
echo "<hr><h2>2. PHPMailer Class Check</h2>";
if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    echo "✅ PHPMailer class is available<br>";
} else {
    echo "❌ PHPMailer class NOT found. Run: composer require phpmailer/phpmailer<br>";
    exit;
}

// Check MailHelper
echo "<hr><h2>3. MailHelper Class Check</h2>";
if (class_exists('MailHelper')) {
    echo "✅ MailHelper class is available<br>";
} else {
    echo "❌ MailHelper class NOT found<br>";
    exit;
}

// Test SMTP connection
echo "<hr><h2>4. SMTP Connection Test</h2>";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = getenv('MAIL_HOST');
    $mail->SMTPAuth = true;
    $mail->Username = getenv('MAIL_USERNAME');
    $mail->Password = getenv('MAIL_PASSWORD');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = getenv('MAIL_PORT');
    $mail->SMTPDebug = 2; // Enable verbose debug output
    
    echo "<pre>";
    $mail->smtpConnect();
    echo "</pre>";
    echo "✅ SMTP connection successful!<br>";
} catch (Exception $e) {
    echo "<pre style='color: red;'>";
    echo "❌ SMTP connection failed: " . $e->getMessage();
    echo "</pre>";
}

// Test sending OTP email
echo "<hr><h2>5. Send Test OTP Email</h2>";
echo "<form method='POST'>";
echo "Email Address: <input type='email' name='test_email' value='" . getenv('MAIL_USERNAME') . "' required style='width: 300px;'><br><br>";
echo "Recipient Name: <input type='text' name='test_name' value='Test User' required style='width: 300px;'><br><br>";
echo "<button type='submit' name='send_test' style='padding: 10px 20px; background: #a01422; color: white; border: none; cursor: pointer;'>Send Test OTP</button>";
echo "</form>";

if (isset($_POST['send_test'])) {
    $testEmail = $_POST['test_email'];
    $testName = $_POST['test_name'];
    $testOTP = rand(100000, 999999);
    
    echo "<hr><h3>Sending OTP to: $testEmail</h3>";
    echo "OTP Code: <strong style='font-size: 24px; color: #a01422;'>$testOTP</strong><br><br>";
    
    try {
        $result = MailHelper::sendOTPEmail($testEmail, $testName, $testOTP);
        
        if ($result) {
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
            echo "✅ <strong>SUCCESS!</strong> OTP email sent successfully!<br>";
            echo "Check your inbox (and spam folder) for the email.";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
            echo "❌ <strong>FAILED!</strong> Could not send OTP email.<br>";
            echo "Check PHP error logs for details.";
            echo "</div>";
        }
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
        echo "❌ <strong>ERROR:</strong> " . $e->getMessage();
        echo "</div>";
    }
}

echo "<hr><h2>6. Common Issues & Solutions</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffc107; border-radius: 5px;'>";
echo "<h3>If OTP emails are not being received:</h3>";
echo "<ol>";
echo "<li><strong>Gmail App Password:</strong> Make sure you're using an App Password, not your regular Gmail password</li>";
echo "<li><strong>2-Step Verification:</strong> Enable 2-Step Verification in your Google Account</li>";
echo "<li><strong>Generate App Password:</strong> Go to <a href='https://myaccount.google.com/apppasswords' target='_blank'>Google App Passwords</a></li>";
echo "<li><strong>Less Secure Apps:</strong> If using regular password, enable 'Less secure app access' (not recommended)</li>";
echo "<li><strong>Check Spam Folder:</strong> OTP emails might be in spam/junk folder</li>";
echo "<li><strong>PHP Error Logs:</strong> Check XAMPP error logs at C:\\xampp\\php\\logs\\php_error_log</li>";
echo "<li><strong>Firewall:</strong> Make sure port 587 is not blocked by firewall</li>";
echo "</ol>";
echo "</div>";

echo "<hr><h2>7. How to Generate Gmail App Password</h2>";
echo "<div style='background: #d1ecf1; padding: 15px; border: 1px solid #bee5eb; border-radius: 5px;'>";
echo "<ol>";
echo "<li>Go to your Google Account: <a href='https://myaccount.google.com' target='_blank'>myaccount.google.com</a></li>";
echo "<li>Click 'Security' in the left menu</li>";
echo "<li>Under 'Signing in to Google', enable '2-Step Verification' if not already enabled</li>";
echo "<li>After enabling 2-Step, go back to Security and click 'App passwords'</li>";
echo "<li>Select 'Mail' and 'Other (Custom name)', enter 'SPED LMS'</li>";
echo "<li>Click 'Generate' and copy the 16-character password</li>";
echo "<li>Update your .env file with this password (remove spaces): MAIL_PASSWORD=xxxx xxxx xxxx xxxx</li>";
echo "</ol>";
echo "</div>";
?>
