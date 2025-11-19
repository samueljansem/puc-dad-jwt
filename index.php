<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JWT Authentication Demo</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 900px;
            width: 100%;
            padding: 40px;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .forms-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .forms-container {
                grid-template-columns: 1fr;
            }
        }

        .form-section {
            border: 2px solid #f0f0f0;
            border-radius: 8px;
            padding: 25px;
        }

        .form-section h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-size: 14px;
            font-weight: 500;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        button:hover {
            transform: translateY(-2px);
        }

        button:active {
            transform: translateY(0);
        }

        .response-container {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            display: none;
        }

        .response-container.show {
            display: block;
        }

        .response-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .response-header h3 {
            color: #333;
            font-size: 18px;
        }

        .status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status.success {
            background: #d4edda;
            color: #155724;
        }

        .status.error {
            background: #f8d7da;
            color: #721c24;
        }

        .token-container {
            margin-bottom: 15px;
        }

        .token-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 5px;
            font-size: 13px;
        }

        .token-box {
            display: flex;
            gap: 10px;
            align-items: stretch;
        }

        .token-value {
            flex: 1;
            background: white;
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            word-break: break-all;
            max-height: 100px;
            overflow-y: auto;
        }

        .copy-btn {
            width: auto;
            padding: 8px 16px;
            background: #667eea;
            font-size: 14px;
            white-space: nowrap;
        }

        .copy-btn.copied {
            background: #28a745;
        }

        .message {
            padding: 12px;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 14px;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>JWT Authentication Demo</h1>
        <p class="subtitle">Sign up or login to generate access and refresh tokens</p>

        <div class="forms-container">
            <div class="form-section">
                <h2>Sign Up</h2>
                <form id="signupForm">
                    <div class="form-group">
                        <label for="signup-username">Username</label>
                        <input type="text" id="signup-username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="signup-email">Email</label>
                        <input type="email" id="signup-email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="signup-password">Password</label>
                        <input type="password" id="signup-password" name="password" required>
                    </div>
                    <button type="submit">Sign Up</button>
                </form>
            </div>

            <div class="form-section">
                <h2>Login</h2>
                <form id="loginForm">
                    <div class="form-group">
                        <label for="login-email">Email</label>
                        <input type="email" id="login-email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="login-password">Password</label>
                        <input type="password" id="login-password" name="password" required>
                    </div>
                    <button type="submit">Login</button>
                </form>
            </div>
        </div>

        <div class="response-container" id="responseContainer">
            <div class="response-header">
                <h3>Response</h3>
                <span class="status" id="status"></span>
            </div>

            <div id="tokensContainer" style="display: none;">
                <div class="token-container">
                    <div class="token-label">Access Token:</div>
                    <div class="token-box">
                        <div class="token-value" id="accessToken"></div>
                        <button class="copy-btn" onclick="copyToken('accessToken')">Copy</button>
                    </div>
                </div>

                <div class="token-container">
                    <div class="token-label">Refresh Token:</div>
                    <div class="token-box">
                        <div class="token-value" id="refreshToken"></div>
                        <button class="copy-btn" onclick="copyToken('refreshToken')">Copy</button>
                    </div>
                </div>
            </div>

            <div class="message" id="message"></div>
        </div>
    </div>

    <script>
        document.getElementById('signupForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            await handleRequest('/api/signup.php', formData, 'Sign Up');
        });

        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            await handleRequest('/api/login.php', formData, 'Login');
        });

        async function handleRequest(url, formData, action) {
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                displayResponse(data, action);
            } catch (error) {
                displayResponse({ success: false, message: 'Network error: ' + error.message }, action);
            }
        }

        function displayResponse(data, action) {
            const container = document.getElementById('responseContainer');
            const status = document.getElementById('status');
            const message = document.getElementById('message');
            const tokensContainer = document.getElementById('tokensContainer');
            const accessToken = document.getElementById('accessToken');
            const refreshToken = document.getElementById('refreshToken');

            container.classList.add('show');

            if (data.success) {
                status.textContent = 'Success';
                status.className = 'status success';
                message.className = 'message success';
                message.textContent = data.message || `${action} successful!`;

                if (data.access_token && data.refresh_token) {
                    tokensContainer.style.display = 'block';
                    accessToken.textContent = data.access_token;
                    refreshToken.textContent = data.refresh_token;
                } else {
                    tokensContainer.style.display = 'none';
                }
            } else {
                status.textContent = 'Error';
                status.className = 'status error';
                message.className = 'message error';
                message.textContent = data.message || `${action} failed!`;
                tokensContainer.style.display = 'none';
            }
        }

        function copyToken(elementId) {
            const element = document.getElementById(elementId);
            const text = element.textContent;

            navigator.clipboard.writeText(text).then(() => {
                const btn = event.target;
                const originalText = btn.textContent;
                btn.textContent = 'Copied!';
                btn.classList.add('copied');

                setTimeout(() => {
                    btn.textContent = originalText;
                    btn.classList.remove('copied');
                }, 2000);
            });
        }
    </script>
</body>
</html>
