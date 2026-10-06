import os

base_dir = "c:/Users/weera/OneDrive/เดสก์ท็อป/SmartWork-Hub"

directories = [
    "frontend/css",
    "frontend/js",
    "frontend/assets/images",
    "frontend/assets/icons",
    "backend/api",
    "backend/controllers",
    "backend/models",
    "backend/middleware",
    "backend/config",
    "backend/services",
    "database",
    "docs"
]

for d in directories:
    os.makedirs(os.path.join(base_dir, d), exist_ok=True)

files = {
    ".gitignore": """
.env
vendor/
node_modules/
logs/
*.log
.DS_Store
""",
    ".env.example": """
DB_HOST=127.0.0.1
DB_PORT=4000
DB_NAME=smartwork_hub
DB_USER=root
DB_PASSWORD=
""",
    "README.md": """
# SmartWork-Hub
Full Stack Web Application for Employee and Task Management.

## Technology Stack
- Frontend: HTML5, CSS3, Vanilla JS
- Backend: PHP
- Database: TiDB Cloud (MySQL/PDO)
- Version Control: Git + GitHub

## Project Structure
- `frontend/`: UI files (HTML, CSS, JS)
- `backend/`: PHP backend API, Models, Controllers
- `database/`: SQL schema
- `docs/`: Documentation

## Setup
1. Create a database in TiDB Cloud
2. Run `database/schema.sql`
3. Copy `.env.example` to `.env` and fill in DB credentials
4. Start a local PHP server in the project root: `php -S localhost:8000`
5. Open `http://localhost:8000/frontend/login.html`
""",
    "database/schema.sql": """
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'employee') DEFAULT 'employee',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    phone VARCHAR(20),
    position VARCHAR(100),
    department VARCHAR(100),
    start_date DATE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    assigned_to INT,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES employees(id) ON DELETE SET NULL
);

CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    date DATE NOT NULL,
    time_in TIME,
    time_out TIME,
    status ENUM('present', 'absent', 'late', 'leave') DEFAULT 'present',
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);

CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT,
    posted_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE SET NULL
);
""",
    "backend/config/database.php": """<?php
$envPath = __DIR__ . '/../../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(sprintf('%s=%s', trim($name), trim($value)));
    }
}

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '4000';
$db   = getenv('DB_NAME') ?: 'smartwork_hub';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_SSL_CA       => is_file('/etc/ssl/certs/ca-certificates.crt') ? '/etc/ssl/certs/ca-certificates.crt' : null
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
    exit;
}
?>""",
    "backend/api/auth.php": """<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['action']) && $data['action'] === 'login') {
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            echo json_encode(['success' => true, 'role' => $user['role']]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
        }
    } else if (isset($data['action']) && $data['action'] === 'logout') {
        session_destroy();
        echo json_encode(['success' => true]);
    }
} else if ($method === 'GET') {
    if (isset($_SESSION['user_id'])) {
        echo json_encode(['authenticated' => true, 'role' => $_SESSION['role']]);
    } else {
        echo json_encode(['authenticated' => false]);
    }
}
?>""",
    "frontend/js/api.js": """
const API_BASE = '../backend/api';

async function fetchAPI(endpoint, options = {}) {
    try {
        const response = await fetch(`${API_BASE}/${endpoint}`, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            }
        });
        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.error || 'API Error');
        }
        return data;
    } catch (err) {
        console.error(err);
        throw err;
    }
}
""",
    "frontend/login.html": """<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SmartWork-Hub</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f4f7f6; margin: 0; }
        .login-card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; color: #333; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; color: #666; }
        input { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 0.75rem; background-color: #0056b3; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; }
        button:hover { background-color: #004494; }
        .error { color: red; margin-top: 1rem; text-align: center; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>SmartWork-Hub</h2>
        <form id="loginForm">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" required>
            </div>
            <button type="submit">Login</button>
            <div id="errorMsg" class="error"></div>
        </form>
    </div>
    <script src="js/api.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            try {
                const res = await fetchAPI('auth.php', {
                    method: 'POST',
                    body: JSON.stringify({ action: 'login', username, password })
                });
                if (res.success) {
                    window.location.href = 'dashboard.html';
                }
            } catch (err) {
                document.getElementById('errorMsg').textContent = err.message;
            }
        });
    </script>
</body>
</html>""",
    "frontend/dashboard.html": """<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - SmartWork-Hub</title>
</head>
<body>
    <h1>Dashboard</h1>
    <button onclick="logout()">Logout</button>
    <script src="js/api.js"></script>
    <script>
        async function checkAuth() {
            const res = await fetchAPI('auth.php');
            if (!res.authenticated) {
                window.location.href = 'login.html';
            }
        }
        async function logout() {
            await fetchAPI('auth.php', {
                method: 'POST',
                body: JSON.stringify({ action: 'logout' })
            });
            window.location.href = 'login.html';
        }
        checkAuth();
    </script>
</body>
</html>"""
}

for path, content in files.items():
    full_path = os.path.join(base_dir, path)
    with open(full_path, "w", encoding="utf-8") as f:
        f.write(content.strip() + "\\n")

print("Files generated.")
