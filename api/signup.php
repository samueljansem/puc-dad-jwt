<?php
ob_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/JWT.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        throw new Exception('All fields are required');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    if (strlen($password) < 6) {
        throw new Exception('Password must be at least 6 characters');
    }

    if (strlen($username) < 3) {
        throw new Exception('Username must be at least 3 characters');
    }

    $db = getDatabase();

    // Check if user already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    if ($stmt->fetch()) {
        throw new Exception('User with this email or username already exists');
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert user
    $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $hashedPassword]);

    $userId = $db->lastInsertId();

    // Generate tokens
    $accessToken = JWT::generateAccessToken($userId, $email, $username);
    $refreshToken = JWT::generateRefreshToken($userId);

    // Store refresh token
    $refreshPayload = JWT::verify($refreshToken);
    $expiresAt = date('Y-m-d H:i:s', $refreshPayload['exp']);

    $stmt = $db->prepare("INSERT INTO refresh_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $refreshToken, $expiresAt]);

    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'User registered successfully!',
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken,
        'user' => [
            'id' => $userId,
            'username' => $username,
            'email' => $email
        ]
    ]);

} catch (PDOException $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    ob_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
