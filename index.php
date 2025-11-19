<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autenticação JWT</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: white;
            border: 1px solid #ddd;
            max-width: 900px;
            width: 100%;
            padding: 40px;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 24px;
        }

        .subtitle {
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
            border: 1px solid #ddd;
            padding: 25px;
        }

        .form-section h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: normal;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-size: 14px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        input:focus {
            outline: none;
            border-color: #666;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #333;
            color: white;
            border: none;
            font-size: 14px;
            cursor: pointer;
        }

        button:hover {
            background: #555;
        }

        button:active {
            background: #000;
        }

        .response-container {
            background: #fafafa;
            border: 1px solid #ddd;
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
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }

        .response-header h3 {
            color: #333;
            font-size: 16px;
            font-weight: normal;
        }

        .status {
            padding: 4px 10px;
            font-size: 12px;
            border: 1px solid;
        }

        .status.success {
            background: #e8f5e9;
            color: #2e7d32;
            border-color: #2e7d32;
        }

        .status.error {
            background: #ffebee;
            color: #c62828;
            border-color: #c62828;
        }

        .token-container {
            margin-bottom: 15px;
        }

        .token-label {
            color: #333;
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
            padding: 10px;
            border: 1px solid #ccc;
            font-family: 'Courier New', monospace;
            font-size: 11px;
            word-break: break-all;
            max-height: 100px;
            overflow-y: auto;
        }

        .copy-btn {
            width: auto;
            padding: 8px 16px;
            background: #555;
            font-size: 12px;
            white-space: nowrap;
        }

        .copy-btn:hover {
            background: #777;
        }

        .copy-btn.copied {
            background: #2e7d32;
        }

        .message {
            padding: 10px;
            border: 1px solid;
            margin-top: 10px;
            font-size: 14px;
        }

        .message.success {
            background: #e8f5e9;
            color: #2e7d32;
            border-color: #2e7d32;
        }

        .message.error {
            background: #ffebee;
            color: #c62828;
            border-color: #c62828;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Autenticação JWT</h1>
        <p class="subtitle">Cadastre-se ou faça login para gerar tokens de acesso</p>

        <div class="forms-container">
            <div class="form-section">
                <h2>Cadastro</h2>
                <form id="signupForm">
                    <div class="form-group">
                        <label for="signup-username">Nome de usuário</label>
                        <input type="text" id="signup-username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="signup-email">E-mail</label>
                        <input type="email" id="signup-email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="signup-password">Senha</label>
                        <input type="password" id="signup-password" name="password" required>
                    </div>
                    <button type="submit">Cadastrar</button>
                </form>
            </div>

            <div class="form-section">
                <h2>Login</h2>
                <form id="loginForm">
                    <div class="form-group">
                        <label for="login-email">E-mail</label>
                        <input type="email" id="login-email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="login-password">Senha</label>
                        <input type="password" id="login-password" name="password" required>
                    </div>
                    <button type="submit">Entrar</button>
                </form>
            </div>
        </div>

        <div class="response-container" id="responseContainer">
            <div class="response-header">
                <h3>Resposta</h3>
                <span class="status" id="status"></span>
            </div>

            <div id="tokensContainer" style="display: none;">
                <div class="token-container">
                    <div class="token-label">Token de Acesso:</div>
                    <div class="token-box">
                        <div class="token-value" id="accessToken"></div>
                        <button class="copy-btn" onclick="copyToken('accessToken')">Copiar</button>
                    </div>
                </div>

                <div class="token-container">
                    <div class="token-label">Token de Atualização:</div>
                    <div class="token-box">
                        <div class="token-value" id="refreshToken"></div>
                        <button class="copy-btn" onclick="copyToken('refreshToken')">Copiar</button>
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
            await handleRequest('/api/signup.php', formData, 'Cadastro');
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
                displayResponse({ success: false, message: 'Erro de rede: ' + error.message }, action);
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
                status.textContent = 'Sucesso';
                status.className = 'status success';
                message.className = 'message success';
                message.textContent = data.message || `${action} realizado com sucesso!`;

                if (data.access_token && data.refresh_token) {
                    tokensContainer.style.display = 'block';
                    accessToken.textContent = data.access_token;
                    refreshToken.textContent = data.refresh_token;
                } else {
                    tokensContainer.style.display = 'none';
                }
            } else {
                status.textContent = 'Erro';
                status.className = 'status error';
                message.className = 'message error';
                message.textContent = data.message || `${action} falhou!`;
                tokensContainer.style.display = 'none';
            }
        }

        function copyToken(elementId) {
            const element = document.getElementById(elementId);
            const text = element.textContent;

            navigator.clipboard.writeText(text).then(() => {
                const btn = event.target;
                const originalText = btn.textContent;
                btn.textContent = 'Copiado!';
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
