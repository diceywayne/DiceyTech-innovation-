<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'diceytech_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'login':
            if ($_POST['username'] === 'king' && $_POST['password'] === 'king123') {
                $_SESSION['admin'] = true;
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
            }
            break;
            
        case 'register':
            $stmt = $pdo->prepare("INSERT INTO students (name, email, phone, status) VALUES (?, ?, ?, 'pending')");
            if ($stmt->execute([$_POST['name'], $_POST['email'], $_POST['phone']])) {
                echo json_encode(['success' => true, 'message' => 'Registration submitted for approval']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Registration failed']);
            }
            break;
            
        case 'approve_student':
            if (isset($_SESSION['admin'])) {
                $stmt = $pdo->prepare("UPDATE students SET status = 'approved' WHERE id = ?");
                $stmt->execute([$_POST['student_id']]);
                echo json_encode(['success' => true]);
            }
            break;
            
        case 'block_student':
            if (isset($_SESSION['admin'])) {
                $stmt = $pdo->prepare("UPDATE students SET status = 'blocked' WHERE id = ?");
                $stmt->execute([$_POST['student_id']]);
                echo json_encode(['success' => true]);
            }
            break;
            
        case 'delete_student':
            if (isset($_SESSION['admin'])) {
                $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
                $stmt->execute([$_POST['student_id']]);
                echo json_encode(['success' => true]);
            }
            break;
            
        case 'add_lesson':
            if (isset($_SESSION['admin'])) {
                $stmt = $pdo->prepare("INSERT INTO lessons (title, description, content, category, is_special) VALUES (?, ?, ?, ?, ?)");
                $special = isset($_POST['is_special']) ? 1 : 0;
                if ($stmt->execute([$_POST['title'], $_POST['description'], $_POST['content'], $_POST['category'], $special])) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false]);
                }
            }
            break;
            
        case 'delete_lesson':
            if (isset($_SESSION['admin'])) {
                $stmt = $pdo->prepare("DELETE FROM lessons WHERE id = ?");
                $stmt->execute([$_POST['lesson_id']]);
                echo json_encode(['success' => true]);
            }
            break;
            
        case 'broadcast_message':
            if (isset($_SESSION['admin'])) {
                $stmt = $pdo->prepare("INSERT INTO messages (title, content, created_at) VALUES (?, ?, NOW())");
                if ($stmt->execute([$_POST['title'], $_POST['message']])) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false]);
                }
            }
            break;
            
        case 'assign_special':
            if (isset($_SESSION['admin'])) {
                $stmt = $pdo->prepare("UPDATE students SET has_special = 1 WHERE id = ?");
                $stmt->execute([$_POST['student_id']]);
                echo json_encode(['success' => true]);
            }
            break;
    }
    exit;
}

// Get current page
$page = $_GET['page'] ?? 'home';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DiceyTech Innovation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            overflow-x: hidden;
        }

        .dark-theme {
            background: linear-gradient(135deg, #0c0c0c 0%, #1a1a1a 100%);
            color: #00ff00;
        }

        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(10px);
            z-index: 1000;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: slideDown 0.8s ease;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #00ff88;
            text-shadow: 0 0 10px #00ff88;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            color: #fff;
            text-decoration: none;
            transition: all 0.3s;
            position: relative;
        }

        .nav-links a:hover {
            color: #00ff88;
            text-shadow: 0 0 5px #00ff88;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: #00ff88;
            transition: width 0.3s;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .hero {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a" cx="50%" cy="50%"><stop offset="0%" stop-color="%2300ff88" stop-opacity="0.1"/><stop offset="100%" stop-color="%2300ff88" stop-opacity="0"/></radialGradient></defs><circle cx="200" cy="200" r="100" fill="url(%23a)"><animate attributeName="cx" values="200;800;200" dur="10s" repeatCount="indefinite"/></circle><circle cx="800" cy="600" r="150" fill="url(%23a)"><animate attributeName="cy" values="600;200;600" dur="8s" repeatCount="indefinite"/></circle></svg>');
            animation: float 6s ease-in-out infinite;
        }

        .hero-content {
            z-index: 2;
            animation: fadeInUp 1s ease;
        }

        .hero h1 {
            font-size: 4rem;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, #00ff88, #0099ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: glow 2s ease-in-out infinite alternate;
        }

        .hero p {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: linear-gradient(45deg, #00ff88, #0099ff);
            box-shadow: 0 4px 15px rgba(0, 255, 136, 0.3);
        }

        .btn-secondary {
            background: transparent;
            border: 2px solid #00ff88;
            color: #00ff88;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 255, 136, 0.4);
        }

        .section {
            padding: 5rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            border: 1px solid rgba(0, 255, 136, 0.2);
            transition: all 0.3s;
            animation: fadeInUp 0.8s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            border-color: #00ff88;
            box-shadow: 0 10px 30px rgba(0, 255, 136, 0.2);
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #00ff88;
        }

        .software-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .software-card {
            background: rgba(0, 0, 0, 0.8);
            border-radius: 10px;
            padding: 1.5rem;
            border: 1px solid #00ff88;
            transition: all 0.3s;
        }

        .software-card:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 20px rgba(0, 255, 136, 0.3);
        }

        .contact-form {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #00ff88;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid rgba(0, 255, 136, 0.3);
            border-radius: 5px;
            background: rgba(0, 0, 0, 0.5);
            color: #fff;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #00ff88;
            box-shadow: 0 0 10px rgba(0, 255, 136, 0.3);
        }

        .admin-panel {
            max-width: 1000px;
            margin: 6rem auto 2rem;
            padding: 2rem;
        }

        .admin-tabs {
            display: flex;
            margin-bottom: 2rem;
            background: rgba(0, 0, 0, 0.8);
            border-radius: 10px;
            overflow: hidden;
        }

        .admin-tab {
            flex: 1;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            background: transparent;
            color: #00ff00;
            border: none;
            transition: all 0.3s;
        }

        .admin-tab.active {
            background: #00ff00;
            color: #000;
        }

        .tab-content {
            display: none;
            background: rgba(0, 0, 0, 0.9);
            border-radius: 10px;
            padding: 2rem;
            border: 1px solid #00ff00;
        }

        .tab-content.active {
            display: block;
        }

        .student-list {
            display: grid;
            gap: 1rem;
        }

        .student-item {
            background: rgba(0, 255, 0, 0.1);
            padding: 1rem;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #00ff00;
        }

        .student-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-small {
            padding: 5px 10px;
            font-size: 0.8rem;
            border-radius: 3px;
        }

        .btn-approve { background: #00ff00; color: #000; }
        .btn-block { background: #ff9900; color: #000; }
        .btn-delete { background: #ff0000; color: #fff; }
        .btn-special { background: #9900ff; color: #fff; }

        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
        }

        .modal-content {
            background: #1a1a1a;
            margin: 10% auto;
            padding: 2rem;
            border-radius: 10px;
            width: 80%;
            max-width: 500px;
            border: 1px solid #00ff00;
        }

        .close {
            color: #00ff00;
            float: right;
            font-size: 2rem;
            cursor: pointer;
        }

        .whatsapp-btn {
            background: #25d366;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            display: inline-block;
            margin-top: 1rem;
            transition: all 0.3s;
        }

        .whatsapp-btn:hover {
            background: #128c7e;
            transform: scale(1.05);
        }

        @keyframes slideDown {
            from { transform: translateY(-100%); }
            to { transform: translateY(0); }
        }

        @keyframes fadeInUp {
            from { 
                opacity: 0; 
                transform: translateY(30px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }

        @keyframes glow {
            from { text-shadow: 0 0 20px #00ff88; }
            to { text-shadow: 0 0 30px #00ff88, 0 0 40px #00ff88; }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .hidden { display: none; }
        .show { display: block; }

        @media (max-width: 768px) {
            .hero h1 { font-size: 2.5rem; }
            .hero p { font-size: 1.2rem; }
            .nav-links { display: none; }
            .features-grid { grid-template-columns: 1fr; }
            .cta-buttons { flex-direction: column; align-items: center; }
        }
    </style>
</head>
<body class="<?php echo in_array($page, ['school', 'admin']) ? 'dark-theme' : ''; ?>">

<nav class="navbar">
    <div class="logo">
        <img src="<?php echo in_array($page, ['school', 'admin']) ? 'logo2.png' : 'logo1.png'; ?>" alt="Logo" style="height: 30px; margin-right: 10px;">
        DiceyTech Innovation
    </div>
    <ul class="nav-links">
        <li><a href="?page=home">Home</a></li>
        <li><a href="?page=features">Features</a></li>
        <li><a href="?page=software">Software</a></li>
        <li><a href="?page=school">IT School</a></li>
        <li><a href="?page=contact">Contact</a></li>
        <?php if (isset($_SESSION['admin'])): ?>
            <li><a href="?page=admin">Admin Panel</a></li>
            <li><a href="?page=logout">Logout</a></li>
        <?php else: ?>
            <li><a href="?page=login">Admin Login</a></li>
        <?php endif; ?>
    </ul>
</nav>

<?php
// Handle logout
if ($page === 'logout') {
    session_destroy();
    header('Location: ?page=home');
    exit;
}

switch ($page):
    case 'home':
?>

<section class="hero">
    <div class="hero-content">
        <h1>DiceyTech Innovation</h1>
        <p>Your Gateway to Advanced Technology Solutions</p>
        <div class="cta-buttons">
            <a href="?page=school" class="btn btn-primary">Join IT School</a>
            <a href="?page=software" class="btn btn-secondary">Explore Software</a>
        </div>
    </div>
</section>

<section class="section">
    <h2 style="text-align: center; font-size: 2.5rem; margin-bottom: 2rem;">Welcome to the Future</h2>
    <p style="text-align: center; font-size: 1.2rem; opacity: 0.9;">
        DiceyTech Innovation is your premier destination for cutting-edge technology education and premium software solutions. 
        Join thousands of students mastering the art of ethical hacking, cybersecurity, and advanced IT skills.
    </p>
</section>

<?php break; case 'features': ?>

<div style="margin-top: 6rem;"></div>
<section class="section">
    <h2 style="text-align: center; font-size: 2.5rem; margin-bottom: 2rem;">Our Features</h2>
    
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">🛡️</div>
            <h3>Ethical Hacking Tutorials</h3>
            <p>Comprehensive courses on penetration testing, vulnerability assessment, and cybersecurity fundamentals.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">📱</div>
            <h3>iCloud Bypass Training</h3>
            <p>Learn advanced techniques for iCloud activation lock bypass for legitimate device recovery purposes.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">🔓</div>
            <h3>FRP Bypass Methods</h3>
            <p>Master Factory Reset Protection bypass techniques for Android devices in professional scenarios.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">🌐</div>
            <h3>Web Design Mastery</h3>
            <p>From HTML/CSS basics to advanced JavaScript frameworks and responsive design principles.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">💳</div>
            <h3>Card Security Research</h3>
            <p>Educational content on payment card security, fraud prevention, and security testing methodologies.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">⌨️</div>
            <h3>Termux Command Line</h3>
            <p>Master the Android terminal emulator with advanced Linux commands and penetration testing tools.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">🔗</div>
            <h3>GitHub Collaboration</h3>
            <p>Learn version control, collaborative coding, and open-source contribution best practices.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">🎓</div>
            <h3>Special Advanced Classes</h3>
            <p>Exclusive high-level courses available only to approved students with special access privileges.</p>
        </div>
    </div>
</section>

<?php break; case 'software': ?>

<div style="margin-top: 6rem;"></div>
<section class="section">
    <h2 style="text-align: center; font-size: 2.5rem; margin-bottom: 2rem;">Premium Software Solutions</h2>
    
    <div class="software-grid">
        <div class="software-card">
            <h3>🔧 Professional Toolkit</h3>
            <p>Complete penetration testing suite with advanced reconnaissance and exploitation tools.</p>
            <p><strong>Price: $299</strong></p>
            <a href="https://wa.me/2349155196678?text=I'm interested in Professional Toolkit" class="whatsapp-btn">Order via WhatsApp</a>
        </div>
        
        <div class="software-card">
            <h3>📱 Mobile Security Suite</h3>
            <p>Comprehensive mobile device security testing and bypass tools for professionals.</p>
            <p><strong>Price: $199</strong></p>
            <a href="https://wa.me/2349155196678?text=I'm interested in Mobile Security Suite" class="whatsapp-btn">Order via WhatsApp</a>
        </div>
        
        <div class="software-card">
            <h3>🌐 Web Developer Pro</h3>
            <p>Advanced web development framework with automated deployment and security scanning.</p>
            <p><strong>Price: $149</strong></p>
            <a href="https://wa.me/2349155196678?text=I'm interested in Web Developer Pro" class="whatsapp-btn">Order via WhatsApp</a>
        </div>
        
        <div class="software-card">
            <h3>💳 Security Analyzer</h3>
            <p>Professional-grade security analysis tools for payment systems and fraud detection.</p>
            <p><strong>Price: $399</strong></p>
            <a href="https://wa.me/2349155196678?text=I'm interested in Security Analyzer" class="whatsapp-btn">Order via WhatsApp</a>
        </div>
    </div>
    
    <div style="text-align: center; margin-top: 3rem;">
        <h3>Need Custom Software?</h3>
        <p>Contact us for bespoke software development tailored to your specific requirements.</p>
        <a href="https://wa.me/2349155196678?text=I need custom software development" class="whatsapp-btn">Contact for Custom Solutions</a>
    </div>
</section>

<?php break; case 'school': ?>

<div style="margin-top: 6rem;"></div>
<section class="section">
    <h2 style="text-align: center; font-size: 2.5rem; margin-bottom: 2rem; color: #00ff00;">🎓 DiceyTech IT Security School</h2>
    
    <?php if (!isset($_SESSION['student_id'])): ?>
    <div class="contact-form" style="max-width: 600px; margin: 2rem auto;">
        <h3 style="color: #00ff00; margin-bottom: 1rem;">Register for IT School</h3>
        <form id="registerForm">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" required>
            </div>
            <button type="submit" class="btn btn-primary">Submit Application</button>
        </form>
    </div>
    
    <div style="text-align: center; margin-top: 2rem;">
        <p style="color: #ffff00;">⚠️ All registrations require admin approval before accessing courses.</p>
    </div>
    
    <?php else: ?>
    <!-- Student Dashboard would go here -->
    <div class="contact-form">
        <h3 style="color: #00ff00;">Welcome to Student Dashboard</h3>
        <p>Your courses and materials will appear here once approved by admin.</p>
    </div>
    <?php endif; ?>
    
    <!-- Messages Section -->
    <div class="contact-form" style="margin-top: 2rem;">
        <h3 style="color: #00ff00;">📢 Latest Announcements</h3>
        <?php
        $stmt = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 5");
        $messages = $stmt->fetchAll();
        if ($messages):
            foreach ($messages as $msg):
        ?>
        <div style="background: rgba(0, 255, 0, 0.1); padding: 1rem; margin: 1rem 0; border-radius: 5px; border-left: 3px solid #00ff00;">
            <h4 style="color: #00ff00;"><?php echo htmlspecialchars($msg['title']); ?></h4>
            <p><?php echo htmlspecialchars($msg['content']); ?></p>
            <small style="opacity: 0.7;"><?php echo $msg['created_at']; ?></small>
        </div>
        <?php endforeach; else: ?>
        <p>No announcements yet.</p>
        <?php endif; ?>
    </div>
</section>

<?php break; case 'contact': ?>

<div style="margin-top: 6rem;"></div>
<section class="section">
    <h2 style="text-align: center; font-size: 2.5rem; margin-bottom: 2rem;">Get In Touch</h2>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
        <div class="contact-form">
            <h3 style="color: #00ff88; margin-bottom: 1rem;">Contact Information</h3>
            <div style="margin-bottom: 1rem;">
                <strong>📞 Phone:</strong><br>
                <a href="tel:+2349155196678" style="color: #00ff88;">+234 915 519 6678</a>
            </div>
            <div style="margin-bottom: 1rem;">
                <strong>📧 Email:</strong><br>
                <a href="mailto:umarmusab407@gmail.com" style="color: #00ff88;">umarmusab407@gmail.com</a>
            </div>
            <div>
                <strong>💬 WhatsApp:</strong><br>
                <a href="https://wa.me/2349155196678" class="whatsapp-btn">Chat on WhatsApp</a>
            </div>
        </div>
        
        <div class="contact-form">
            <h3 style="color: #00ff88; margin-bottom: 1rem;">Send Message</h3>
            <form>
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" required>
                </div>
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" required>
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea rows="5" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </div>
    </div>
</section>

<?php break; case 'login': ?>

<?php if (isset($_SESSION['admin'])): ?>
<script>window.location.href = '?page=admin';</script>
<?php endif; ?>

<div style="margin-top: 6rem;"></div>
<section class="section">
    <div class="contact-form" style="max-width: 400px; margin: 2rem auto;">
        <h2 style="text-align: center; color: #00ff00; margin-bottom: 2rem;">🔐 Admin Login</h2>
        <form id="loginForm">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
        </form>
    </div>
</section>

<?php break; case 'admin': ?>

<?php if (!isset($_SESSION['admin'])): ?>
<script>window.location.href = '?page=login';</script>
<?php endif; ?>

<div class="admin-panel">
    <h2 style="text-align: center; color: #00ff00; margin-bottom: 2rem;">🛡️ Admin Control Panel</h2>
    
    <div class="admin-tabs">
        <button class="admin-tab active" onclick="switchTab('students')">Students</button>
        <button class="admin-tab" onclick="switchTab('lessons')">Lessons</button>
        <button class="admin-tab" onclick="switchTab('messages')">Messages</button>
        <button class="admin-tab" onclick="switchTab('analytics')">Analytics</button>
    </div>
    
    <!-- Students Tab -->
    <div id="students-tab" class="tab-content active">
        <h3 style="color: #00ff00; margin-bottom: 1rem;">👥 Student Management</h3>
        <div class="student-list">
            <?php
            $stmt = $pdo->query("SELECT * FROM students ORDER BY created_at DESC");
            $students = $stmt->fetchAll();
            foreach ($students as $student):
            ?>
            <div class="student-item">
                <div>
                    <strong><?php echo htmlspecialchars($student['name']); ?></strong><br>
                    <small><?php echo htmlspecialchars($student['email']); ?> | <?php echo htmlspecialchars($student['phone']); ?></small><br>
                    <span style="color: <?php 
                        echo $student['status'] === 'approved' ? '#00ff00' : 
                             ($student['status'] === 'blocked' ? '#ff9900' : '#ffff00'); 
                    ?>;">
                        Status: <?php echo ucfirst($student['status']); ?>
                        <?php if ($student['has_special']): ?> | Special Access ✨<?php endif; ?>
                    </span>
                </div>
                <div class="student-actions">
                    <?php if ($student['status'] !== 'approved'): ?>
                    <button class="btn btn-small btn-approve" onclick="approveStudent(<?php echo $student['id']; ?>)">Approve</button>
                    <?php endif; ?>
                    <?php if ($student['status'] !== 'blocked'): ?>
                    <button class="btn btn-small btn-block" onclick="blockStudent(<?php echo $student['id']; ?>)">Block</button>
                    <?php endif; ?>
                    <?php if (!$student['has_special']): ?>
                    <button class="btn btn-small btn-special" onclick="assignSpecial(<?php echo $student['id']; ?>)">Special</button>
                    <?php endif; ?>
                    <button class="btn btn-small btn-delete" onclick="deleteStudent(<?php echo $student['id']; ?>)">Delete</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Lessons Tab -->
    <div id="lessons-tab" class="tab-content">
        <h3 style="color: #00ff00; margin-bottom: 1rem;">📚 Lesson Management</h3>
        
        <button class="btn btn-primary" onclick="showModal('addLessonModal')" style="margin-bottom: 1rem;">Add New Lesson</button>
        
        <div class="student-list">
            <?php
            $stmt = $pdo->query("SELECT * FROM lessons ORDER BY created_at DESC");
            $lessons = $stmt->fetchAll();
            foreach ($lessons as $lesson):
            ?>
            <div class="student-item">
                <div>
                    <strong><?php echo htmlspecialchars($lesson['title']); ?></strong>
                    <?php if ($lesson['is_special']): ?><span style="color: #9900ff;"> ✨ SPECIAL</span><?php endif; ?><br>
                    <small>Category: <?php echo htmlspecialchars($lesson['category']); ?></small><br>
                    <small><?php echo htmlspecialchars(substr($lesson['description'], 0, 100)); ?>...</small>
                </div>
                <div class="student-actions">
                    <button class="btn btn-small btn-delete" onclick="deleteLesson(<?php echo $lesson['id']; ?>)">Delete</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Messages Tab -->
    <div id="messages-tab" class="tab-content">
        <h3 style="color: #00ff00; margin-bottom: 1rem;">📢 Broadcast Messages</h3>
        
        <div class="contact-form" style="margin-bottom: 2rem;">
            <form id="broadcastForm">
                <div class="form-group">
                    <label>Message Title</label>
                    <input type="text" name="title" required>
                </div>
                <div class="form-group">
                    <label>Message Content</label>
                    <textarea name="message" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Broadcast Message</button>
            </form>
        </div>
        
        <h4 style="color: #00ff00;">Recent Messages</h4>
        <div class="student-list">
            <?php
            $stmt = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 10");
            $messages = $stmt->fetchAll();
            foreach ($messages as $msg):
            ?>
            <div class="student-item">
                <div>
                    <strong><?php echo htmlspecialchars($msg['title']); ?></strong><br>
                    <small><?php echo htmlspecialchars(substr($msg['content'], 0, 100)); ?>...</small><br>
                    <small style="opacity: 0.7;"><?php echo $msg['created_at']; ?></small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Analytics Tab -->
    <div id="analytics-tab" class="tab-content">
        <h3 style="color: #00ff00; margin-bottom: 1rem;">📊 Platform Analytics</h3>
        
        <?php
        $total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
        $approved_students = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'approved'")->fetchColumn();
        $pending_students = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'pending'")->fetchColumn();
        $total_lessons = $pdo->query("SELECT COUNT(*) FROM lessons")->fetchColumn();
        $special_lessons = $pdo->query("SELECT COUNT(*) FROM lessons WHERE is_special = 1")->fetchColumn();
        ?>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div class="feature-card">
                <div class="feature-icon">👥</div>
                <h3><?php echo $total_students; ?></h3>
                <p>Total Students</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">✅</div>
                <h3><?php echo $approved_students; ?></h3>
                <p>Approved Students</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⏳</div>
                <h3><?php echo $pending_students; ?></h3>
                <p>Pending Approval</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📚</div>
                <h3><?php echo $total_lessons; ?></h3>
                <p>Total Lessons</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⭐</div>
                <h3><?php echo $special_lessons; ?></h3>
                <p>Special Lessons</p>
            </div>
        </div>
    </div>
</div>

<!-- Add Lesson Modal -->
<div id="addLessonModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="hideModal('addLessonModal')">&times;</span>
        <h3 style="color: #00ff00;">Add New Lesson</h3>
        <form id="addLessonForm">
            <div class="form-group">
                <label>Lesson Title</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category" style="width: 100%; padding: 10px; background: rgba(0,0,0,0.5); color: #fff; border: 1px solid rgba(0,255,136,0.3); border-radius: 5px;">
                    <option value="Ethical Hacking">Ethical Hacking</option>
                    <option value="iCloud Bypass">iCloud Bypass</option>
                    <option value="FRP Bypass">FRP Bypass</option>
                    <option value="Web Design">Web Design</option>
                    <option value="Card Security">Card Security</option>
                    <option value="Termux">Termux Commands</option>
                    <option value="GitHub">GitHub Collaboration</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3" required></textarea>
            </div>
            <div class="form-group">
                <label>Content</label>
                <textarea name="content" rows="6" required></textarea>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_special" style="margin-right: 10px;">
                    Special Class (Admin Assigned Only)
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Add Lesson</button>
        </form>
    </div>
</div>

<?php break; endswitch; ?>

<script>
// Global JavaScript functions
function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.admin-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Show selected tab content
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Add active class to clicked tab
    event.target.classList.add('active');
}

function showModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
}

function hideModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function approveStudent(studentId) {
    if (confirm('Approve this student?')) {
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=approve_student&student_id=${studentId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

function blockStudent(studentId) {
    if (confirm('Block this student?')) {
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=block_student&student_id=${studentId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

function deleteStudent(studentId) {
    if (confirm('Delete this student? This action cannot be undone.')) {
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=delete_student&student_id=${studentId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

function assignSpecial(studentId) {
    if (confirm('Grant special class access to this student?')) {
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=assign_special&student_id=${studentId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

function deleteLesson(lessonId) {
    if (confirm('Delete this lesson? This action cannot be undone.')) {
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=delete_lesson&lesson_id=${lessonId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

// Form handlers
document.addEventListener('DOMContentLoaded', function() {
    // Login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'login');
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '?page=admin';
                } else {
                    alert(data.message || 'Login failed');
                }
            });
        });
    }
    
    // Register form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'register');
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    this.reset();
                } else {
                    alert(data.message || 'Registration failed');
                }
            });
        });
    }
    
    // Add lesson form
    const addLessonForm = document.getElementById('addLessonForm');
    if (addLessonForm) {
        addLessonForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'add_lesson');
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Lesson added successfully!');
                    hideModal('addLessonModal');
                    location.reload();
                } else {
                    alert('Failed to add lesson');
                }
            });
        });
    }
    
    // Broadcast form
    const broadcastForm = document.getElementById('broadcastForm');
    if (broadcastForm) {
        broadcastForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'broadcast_message');
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Message broadcasted successfully!');
                    this.reset();
                    location.reload();
                } else {
                    alert('Failed to broadcast message');
                }
            });
        });
    }
});

// Smooth scroll animation for navigation
document.querySelectorAll('a[href^="?"]').forEach(link => {
    link.addEventListener('click', function(e) {
        document.body.style.opacity = '0.8';
        setTimeout(() => {
            document.body.style.opacity = '1';
        }, 200);
    });
});

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
});

// Add loading animation to buttons
document.querySelectorAll('.btn').forEach(btn => {
    btn.addEventListener('click', function() {
        if (this.type === 'submit') {
            const originalText = this.textContent;
            this.textContent = 'Loading...';
            this.disabled = true;
            
            setTimeout(() => {
                this.textContent = originalText;
                this.disabled = false;
            }, 2000);
        }
    });
});

// Animated counter for analytics
function animateValue(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        element.textContent = Math.floor(progress * (end - start) + start);
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// Initialize animations when analytics tab is visible
const analyticsTab = document.getElementById('analytics-tab');
if (analyticsTab) {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counters = entry.target.querySelectorAll('.feature-card h3');
                counters.forEach(counter => {
                    const finalValue = parseInt(counter.textContent);
                    counter.textContent = '0';
                    animateValue(counter, 0, finalValue, 1000);
                });
                observer.unobserve(entry.target);
            }
        });
    });
    observer.observe(analyticsTab);
}
</script>

</body>
</html>