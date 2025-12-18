<?php
header('Content-Type: application/json; charset=utf-8');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

// Helper to safely get POST values
function get_post($k) {
    if (!isset($_POST[$k])) return '';
    $v = $_POST[$k];
    $v = trim($v);
    // Prevent header injection
    $v = preg_replace('/[\r\n]+/', ' ', $v);
    return strip_tags($v);
}

$name = get_post('name');
$phone = get_post('phone');
$email = get_post('email');
$message = get_post('message');
$subject = get_post('sub');

if (empty($name) || empty($phone) || empty($message)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Please provide name, phone and message.']);
    exit;
}

// Validate/sanitize email
$replyTo = '';
if (!empty($email)) {
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $replyTo = $email;
    } else {
        $email = '';
    }
}

if (empty($subject)) {
    $subject = 'Website Inquiry: ' . ($name ?: 'Contact form');
}

// Build HTML and plain text bodies (mirror supplied example)
$htmlBody = '<html><body>';
$htmlBody .= '<p><strong>New contact form submission</strong></p>';
$htmlBody .= '<table cellpadding="6" style="border-collapse:collapse;">';
$htmlBody .= '<tr><th align="left">Name</th><td>' . htmlspecialchars($name) . '</td></tr>';
$htmlBody .= '<tr><th align="left">Phone</th><td>' . htmlspecialchars($phone) . '</td></tr>';
$htmlBody .= '<tr><th align="left">Email</th><td>' . ($email ? htmlspecialchars($email) : 'N/A') . '</td></tr>';
$htmlBody .= '<tr><th align="left">Subject</th><td>' . htmlspecialchars($subject) . '</td></tr>';
$htmlBody .= '<tr><th align="left">Message</th><td>' . nl2br(htmlspecialchars($message)) . '</td></tr>';
$htmlBody .= '</table>';
$htmlBody .= '</body></html>';

$plainBody = "New contact form submission\n\n";
$plainBody .= "Name: {$name}\n";
$plainBody .= "Phone: {$phone}\n";
$plainBody .= "Email: " . ($email ?: 'N/A') . "\n";
$plainBody .= "Subject: {$subject}\n\n";
$plainBody .= "Message:\n{$message}\n";

// Recipient
$to = 'info@amglasses.in';

// Default headers for fallback mail()
$serverName = isset($_SERVER['SERVER_NAME']) ? preg_replace('/[^a-zA-Z0-9.\\-]/', '', $_SERVER['SERVER_NAME']) : 'localhost';
$fromAddress = 'noreply@' . $serverName;
$mailHeaders = [];
$mailHeaders[] = 'From: ' . $fromAddress;
if ($replyTo) $mailHeaders[] = 'Reply-To: ' . $replyTo;
$mailHeaders[] = 'MIME-Version: 1.0';
$mailHeaders[] = 'Content-type: text/html; charset=utf-8';
$mailHeaders[] = 'X-Mailer: PHP/' . phpversion();

$ok = false;
try {
    $sent = false;
    $vendor = __DIR__ . '/vendor/autoload.php';
    if (file_exists($vendor)) {
        require_once $vendor;
        // optional smtp config
        if (file_exists(__DIR__ . '/smtp_config.php')) {
            require_once __DIR__ . '/smtp_config.php';
        }
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = defined('SMTP_HOST') ? SMTP_HOST : 'localhost';
            $mail->SMTPAuth = defined('SMTP_USER') && defined('SMTP_PASS');
            if (defined('SMTP_USER')) $mail->Username = SMTP_USER;
            if (defined('SMTP_PASS')) $mail->Password = SMTP_PASS;
            $mail->SMTPSecure = defined('SMTP_SECURE') ? SMTP_SECURE : '';
            $mail->Port = defined('SMTP_PORT') ? SMTP_PORT : 25;
            $fromEmail = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : $fromAddress;
            $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'Website Contact';
            $mail->setFrom($fromEmail, $fromName);
            if ($replyTo) $mail->addReplyTo($replyTo);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $htmlBody;
            $mail->AltBody = $plainBody;
            $mail->CharSet = 'UTF-8';
            $sent = $mail->send();
        } catch (Exception $e) {
            $sent = false;
        }
    }

    if (!$sent) {
        // fallback to PHP mail() with HTML body
        $sent = mail($to, $subject, $htmlBody, implode("\r\n", $mailHeaders));
    }

    $ok = (bool)$sent;
} catch (Throwable $e) {
    $ok = false;
}

if ($ok) {
    echo json_encode(['status' => 'success', 'message' => 'Thank you — your message was sent successfully.']);
    exit;
}

// Log failure for debugging
$logLine = date('Y-m-d H:i:s') . "\t" . str_replace("\n", ' / ', $plainBody) . "\n";
@file_put_contents(__DIR__ . '/contact_log.txt', $logLine, FILE_APPEND | LOCK_EX);

http_response_code(500);
echo json_encode(['status' => 'error', 'message' => 'Failed to send message. Please try again later.']);
exit;


