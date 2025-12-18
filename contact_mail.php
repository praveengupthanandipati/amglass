<?php
header('Content-Type: application/json; charset=utf-8');

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

// Helper: safely retrieve POST value
function get_post($key) {
    if (!isset($_POST[$key])) return '';
    $v = $_POST[$key];
    // Remove any CR/LF to avoid header injection
    $v = preg_replace('/[\r\n]+/', ' ', $v);
    // Strip tags and trim
    return trim(strip_tags($v));
}

$name = get_post('name');
$phone = get_post('phone');
$email = get_post('email');
$message = get_post('message');

if (empty($name) || empty($phone) || empty($message)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Please provide name, phone and message.']);
    exit;
}

// Validate email if provided
$replyTo = '';
if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $replyTo = $email;
}

// Compose email
$to = 'info@amglasses.in';
$subject = 'Website Inquiry: ' . ($name ?: 'Contact form');
$body  = "You received a new message from your website contact form:\n\n";
$body .= "Name: {$name}\n";
$body .= "Phone: {$phone}\n";
$body .= "Email: " . ($email ?: 'N/A') . "\n\n";
$body .= "Message:\n{$message}\n";

$headers = [];
$headers[] = 'From: noreply@' . ($_SERVER['SERVER_NAME'] ?? 'localhost');
if ($replyTo) {
    $headers[] = 'Reply-To: ' . $replyTo;
}
$headers[] = 'X-Mailer: PHP/' . phpversion();

$ok = false;
try {
    // Use mail(); hosting must support it. If mail() is disabled, this will return false.
    $ok = mail($to, $subject, $body, implode("\r\n", $headers));
} catch (Throwable $e) {
    $ok = false;
}

if ($ok) {
    echo json_encode(['status' => 'success', 'message' => 'Thank you — your message was sent successfully.']);
    exit;
}

// Fallback: try to log the message if mail() fails
$logLine = date('Y-m-d H:i:s') . "\t" . str_replace("\n", ' / ', $body) . "\n";
@file_put_contents(__DIR__ . '/contact_log.txt', $logLine, FILE_APPEND | LOCK_EX);

http_response_code(500);
echo json_encode(['status' => 'error', 'message' => 'Failed to send message. Please try again later.']);
exit;
<?php
// Response header to return JSON
header('Content-Type: application/json');

// Check if it is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form fields
    $name = isset($_POST['name']) ? strip_tags(trim($_POST['name'])) : '';
    $phone = isset($_POST['phone']) ? strip_tags(trim($_POST['phone'])) : '';
    $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
    $message = isset($_POST['message']) ? strip_tags(trim($_POST['message'])) : '';

    // Simple Validation
    if (empty($name) || empty($phone) || empty($message)) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields (Name, Phone, Message).']);
        exit;
    }

    // Email Configuration
    $to = 'info@amglass.in'; // Target email address
    $subject = "New Contact Query from $name";
    
    // Email Body
    $email_content = "Name: $name\n";
    $email_content .= "Phone: $phone\n";
    $email_content .= "Email: $email\n\n";
    $email_content .= "Message:\n$message\n";

    // Email Headers
    $headers = "From: $name <$email>"; // Note: Some hosts require From to be a valid domain email, reply-to can be the user.
    // Ideally: $headers = "From: no-reply@yourdomain.com\r\n" . "Reply-To: $email\r\n";
    // Keeping it simple for standard PHP mail behavior.

    // Send Email
    if (mail($to, $subject, $email_content, $headers)) {
        echo json_encode(['status' => 'success', 'message' => 'Thank you! Your message has been sent successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Oops! Something went wrong and we couldn\'t send your message.']);
    }
} else {
    // Not a POST request
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
