<?php
/**
 * Contact Form Processor
 * Handles form submission, validation, database storage, and email notifications
 */

header('Content-Type: application/json');

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/email.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// CSRF protection - check for valid token in production
// For now, we'll validate the request origin

// Get and sanitize input
$input = [
    'name'         => trim($_POST['name'] ?? ''),
    'email'        => trim($_POST['email'] ?? ''),
    'phone'        => trim($_POST['phone'] ?? ''),
    'service_type' => trim($_POST['service_type'] ?? ''),
    'message'      => trim($_POST['message'] ?? ''),
];

// Validation
$errors = [];

if (empty($input['name'])) {
    $errors[] = 'Name is required';
} elseif (strlen($input['name']) > 100) {
    $errors[] = 'Name must be less than 100 characters';
}

if (empty($input['email'])) {
    $errors[] = 'Email is required';
} elseif (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address';
}

if (!empty($input['phone']) && !preg_match('/^[\d\s\-\+\(\)]{7,20}$/', $input['phone'])) {
    $errors[] = 'Please enter a valid phone number';
}

if (empty($input['message'])) {
    $errors[] = 'Message is required';
} elseif (strlen($input['message']) < 10) {
    $errors[] = 'Message must be at least 10 characters';
} elseif (strlen($input['message']) > 2000) {
    $errors[] = 'Message must be less than 2000 characters';
}

// Honeypot check (if a hidden field is filled, it's likely a bot)
if (!empty($_POST['website'])) {
    // Silently reject but return success to fool bots
    echo json_encode(['success' => true, 'message' => 'Thank you for your message!']);
    exit;
}

// Return validation errors
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode('. ', $errors)]);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Insert into database
    $stmt = $pdo->prepare("
        INSERT INTO contact_submissions (name, email, phone, service_type, message, ip_address)
        VALUES (:name, :email, :phone, :service_type, :message, :ip_address)
    ");
    
    $stmt->execute([
        ':name'         => $input['name'],
        ':email'        => $input['email'],
        ':phone'        => $input['phone'] ?: null,
        ':service_type' => $input['service_type'] ?: null,
        ':message'      => $input['message'],
        ':ip_address'   => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);
    
    $submissionId = $pdo->lastInsertId();
    
    // Send email notifications
    $emailSent = sendAdminNotification($input);
    sendUserConfirmation($input['email'], $input['name']);
    
    // Log if email failed (don't fail the whole request)
    if (!$emailSent) {
        error_log("Failed to send admin notification email for submission ID: $submissionId");
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your message! We will get back to you within 24-48 hours.'
    ]);
    
} catch (Exception $e) {
    error_log("Contact form error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again later or contact us directly.'
    ]);
}
