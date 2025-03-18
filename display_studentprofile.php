<?php
require_once 'connectdb.php';

// Get student_id from URL parameter
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;

// Prepare query to get student details
$stmt = $conn->prepare("
    SELECT s.*, u.username, u.email, sl.*
    FROM tbl_student s
    JOIN users u ON s.userid = u.userid
    JOIN tbl_studentlocation sl ON s.student_id = sl.student_id
    WHERE s.student_id = ?
");

$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Check if student exists
if (!$student) {
    header("Location: studentdashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile | StudyConnect</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --accent-color: #b3a5ff;
            --bg-color: #ffffff;
            --card-bg: #f8f9ff;
            --text-primary: #2d2d2d;
            --text-secondary: #666666;
            --border-color: #e0dbff;
            --border-radius: 20px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --glow: 0 0 20px rgba(179, 165, 255, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', system-ui, sans-serif;
        }

        body {
            background: var(--bg-color);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(179, 165, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 90% 80%, rgba(224, 219, 255, 0.1) 0%, transparent 50%);
        }

        .profile-container {
            width: 100%;
            max-width: 1100px;
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--glow);
            overflow: hidden;
            position: relative;
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
        }

        .profile-header {
            background: linear-gradient(135deg, var(--accent-color), var(--border-color));
            padding: 3rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill='rgba(255, 255, 255, 0.1)' d='M42.7,-76.2C54.9,-69.2,64.2,-56.7,71.1,-43C78,-29.3,82.5,-14.7,81.9,-0.3C81.3,14,75.6,28,67.8,40.5C60,53,50.1,64,37.8,69.7C25.4,75.4,10.7,75.8,-3.2,80.6C-17.1,85.4,-30.2,94.7,-43.5,89.7C-56.8,84.7,-70.3,65.4,-79.3,51.4C-88.3,37.4,-92.8,18.7,-92.1,0.4C-91.4,-17.9,-85.5,-35.8,-75.6,-50.9C-65.7,-66,-51.8,-78.3,-36.8,-83.5C-21.8,-88.7,-5.4,-86.8,9.4,-82.1C24.2,-77.4,48.4,-70,48.4,-70Z' transform='translate(100 100)'/%3E%3C/svg%3E") no-repeat center center;
            opacity: 0.3;
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .avatar-container {
            width: 120px;
            height: 120px;
            margin: 0 auto 2rem;
            position: relative;
            z-index: 1;
        }

        .avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.8);
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--text-primary);
            position: relative;
            overflow: hidden;
            transition: var(--transition);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .avatar::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: shine 2s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .profile-name {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            text-shadow: 0 0 30px rgba(179, 165, 255, 0.5);
        }

        .profile-id {
            font-size: 1.1rem;
            color: var(--text-primary);
            padding: 0.5rem 1.5rem;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50px;
            display: inline-block;
            backdrop-filter: blur(5px);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            padding: 2rem;
        }

        .info-card {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, var(--accent-color), transparent);
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--glow);
        }

        .info-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-label i {
            color: var(--accent-color);
            font-size: 1.2rem;
        }

        .info-value {
            font-size: 1.2rem;
            color: var(--text-primary);
            font-weight: 500;
        }

        .info-value input, .info-value select {
            width: 100%;
            background: var(--bg-color);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 0.5rem 1rem;
            color: var(--text-primary);
            font-size: 1.2rem;
            transition: var(--transition);
        }

        .info-value input:focus, .info-value select:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 10px rgba(179, 165, 255, 0.2);
        }

        .info-value input.error {
            border-color: #ff6b6b;
            background-color: #fff5f5;
        }

        .info-value input.success {
            border-color: #51cf66;
            background-color: #f4fdf6;
        }

        .error-message {
            color: #ff6b6b;
            font-size: 0.8rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            animation: fadeIn 0.3s ease-in-out;
        }

        .error-message::before {
            content: '\f071';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            font-size: 0.9rem;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .info-value input:focus.error {
            box-shadow: 0 0 10px rgba(255, 107, 107, 0.2);
        }

        .info-value input:focus.success {
            box-shadow: 0 0 10px rgba(81, 207, 102, 0.2);
        }

        .status-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.5rem 1rem;
            background: rgba(179, 165, 255, 0.1);
            border-radius: 50px;
            font-size: 0.9rem;
            color: var(--accent-color);
            border: 1px solid var(--border-color);
        }

        .location-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .location-grid input {
            font-size: 1rem !important;
        }

        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

            .location-grid {
                grid-template-columns: 1fr;
            }

            body {
                padding: 1rem;
            }

            .profile-name {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div style="position: absolute; top: 20px; right: 20px; display: flex; flex-direction: column; gap: 10px;">
        <a href="teacherdashboard.php#my-students-content" class="back-button" style="padding: 10px 15px; background-color: #b3a5ff; color: white; border-radius: 5px; text-decoration: none;">Back to Dashboard</a>
    </div>
    <div class="profile-container">
        <div class="profile-header">
            <div class="avatar-container">
                <div class="avatar" id="profilePhotoPreview">
                    <?php if (!empty($student['profilephoto'])): ?>
                        <img src="<?php echo htmlspecialchars($student['profilephoto']); ?>" alt="Profile Photo" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <i class="fas fa-user" style="opacity: 0.8;"></i>
                    <?php endif; ?>
                </div>
            </div>
            <h1 class="profile-name"><?php echo htmlspecialchars($student['username']); ?></h1>
            <div class="profile-id">Student ID: <?php echo htmlspecialchars($student['student_id']); ?></div>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <span class="status-badge">Active</span>
                <div class="info-label">
                    <i class="fas fa-user"></i>
                    Full Name
                </div>
                <div class="info-value">
                    <input type="text" value="<?php echo htmlspecialchars($student['username']); ?>" readonly>
                </div>
            </div>

            <div class="info-card">
                <div class="info-label">
                    <i class="fas fa-envelope"></i>
                    Email Address
                </div>
                <div class="info-value">
                    <input type="email" value="<?php echo htmlspecialchars($student['email']); ?>" readonly>
                </div>
            </div>

            <div class="info-card">
                <div class="info-label">
                    <i class="fas fa-phone"></i>
                    Mobile Number
                </div>
                <div class="info-value">
                    <input type="tel" value="<?php echo htmlspecialchars($student['mobile']); ?>" readonly>
                </div>
            </div>

            <div class="info-card">
                <div class="info-label">
                    <i class="fas fa-graduation-cap"></i>
                    Mode of Learning
                </div>
                <div class="info-value">
                    <input type="text" value="<?php echo htmlspecialchars($student['mode_of_learning']); ?>" readonly>
                </div>
            </div>

            <div class="info-card">
                <div class="info-label">
                    <i class="fas fa-map-marker-alt"></i>
                    Location Details
                </div>
                <div class="info-value location-grid">
                    <input type="text" value="<?php echo htmlspecialchars($student['pincode']); ?>" readonly>
                    <input type="text" value="<?php echo htmlspecialchars($student['city']); ?>" readonly>
                    <input type="text" value="<?php echo htmlspecialchars($student['state']); ?>" readonly>
                    <input type="text" value="<?php echo htmlspecialchars($student['country']); ?>" readonly>
                </div>
            </div>
        </div>
    </div>
</body>
</html>