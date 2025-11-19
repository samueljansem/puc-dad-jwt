<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/JWT.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($email) || empty($password)) {
        throw new Exception('Email and password are required');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    $db = getDatabase();

    // Find user
    $stmt = $db->prepare("SELECT id, username, email, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        throw new Exception('Invalid email or password');
    }

    // Generate tokens
    $accessToken = JWT::generateAccessToken($user['id'], $user['email'], $user['username']);
    $refreshToken = JWT::generateRefreshToken($user['id']);

    // Store refresh token
    $refreshPayload = JWT::verify($refreshToken);
    $expiresAt = date('Y-m-d H:i:s', $refreshPayload['exp']);

    $stmt = $db->prepare("INSERT INTO refresh_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$user['id'], $refreshToken, $expiresAt]);

    // Clean up old expired tokens
    $stmt = $db->prepare("DELETE FROM refresh_tokens WHERE expires_at < datetime('now')");
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Login successful!',
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken,
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email']
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
