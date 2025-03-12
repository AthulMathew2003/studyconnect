<?php
session_start();
require_once 'connectdb.php';
include 'connectdb.php';

// Check if tutor_userid is coming from POST
if (isset($_POST['tutor_userid'])) {
    $_SESSION['view_tutor_userid'] = $_POST['tutor_userid'];
}

// Check if view_tutor_userid exists in session
if (!isset($_SESSION['view_tutor_userid'])) {
    // Redirect back to dashboard if no tutor ID is found
    header('Location: studentdashboard.php');
    exit();
}

// Get tutor information
$userid = (int)$_SESSION['view_tutor_userid']; // Cast to integer for security
$sql = "SELECT u.username, u.email, t.*, l.*, t.profile_photo 
        FROM users u 
        LEFT JOIN tbl_tutors t ON u.userid = t.userid 
        LEFT JOIN tbl_locations l ON u.userid = l.userid 
        WHERE u.userid = $userid";
$result = $conn->query($sql);
$tutorData = $result->fetch_assoc();

// Get tutor subjects
$sql = "SELECT s.subject 
        FROM tbl_tutorsubject ts 
        JOIN tbl_subject s ON ts.subject_id = s.subject_id 
        JOIN tbl_tutors t ON ts.tutor_id = t.tutor_id 
        WHERE t.userid = $userid";
$subjectResult = $conn->query($sql);
$subjects = [];
while($row = $subjectResult->fetch_assoc()) {
    $subjects[] = $row['subject'];
}

// Convert subjects array to JSON for JavaScript use
$subjectsJson = json_encode($subjects);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyConnect - Tutor Profile</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent-color: #b3a5ff;
            --bg-color: #ffffff;
            --card-bg: #f8f9ff;
            --text-primary: #2d2d2d;
            --text-secondary: #666666;
            --border-color: #e0dbff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: var(--bg-color);
            color: var(--text-primary);
            min-height: 100vh;
            padding: 2rem;
            background-image: 
                radial-gradient(circle at 20% 20%, #f0edff 0%, transparent 25%),
                radial-gradient(circle at 80% 80%, #e8e4ff 0%, transparent 25%);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        h1 {
            font-size: 2rem;
            font-weight: 600;
            background: linear-gradient(135deg, #9d86ff, #b3a5ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-success {
            background: linear-gradient(135deg, #7cd992, #5eb77a);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(94, 183, 122, 0.3);
        }

        .profile-card {
            background: var(--card-bg);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(179, 165, 255, 0.1);
        }

        .profile-header {
            background: linear-gradient(135deg, #a594ff, #c4bbff);
            padding: 3rem 2rem;
            position: relative;
            overflow: hidden;
        }

        .profile-flex {
            display: flex;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .profile-image-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin-right: 2rem;
        }

        .profile-image {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .profile-image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .profile-image-container:hover .profile-image-overlay {
            opacity: 1;
        }

        .profile-image-overlay span {
            color: white;
            font-size: 0.875rem;
        }

        .profile-info h2 {
            color: white;
            font-size: 2rem;
            margin-bottom: 0.25rem;
        }

        .profile-info p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
        }

        .profile-content {
            padding: 2rem;
        }

        .section {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #f0edff;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 1rem;
        }

        .section-icon svg {
            width: 18px;
            height: 18px;
            color: var(--accent-color);
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        @media (min-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .form-group {
            margin-bottom: 1rem;
            position: relative;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        input, select, textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            background: white;
            color: var(--text-primary);
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        input:focus, select:focus, textarea:focus {
            border-color: var(--accent-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(179, 165, 255, 0.2);
        }

        #back-button {
            background: linear-gradient(135deg, #6e7ff3, #b3a5ff);
            color: white;
        }
        
        #back-button:hover {
            box-shadow: 0 4px 12px rgba(110, 127, 243, 0.3);
        }

        @media (max-width: 768px) {
            .profile-flex {
                flex-direction: column;
                text-align: center;
            }

            .profile-image-container {
                margin-right: 0;
                margin-bottom: 1.5rem;
            }

            .btn {
                padding: 0.5rem 1rem;
                font-size: 0.875rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 1rem;
            }

            .profile-header {
                padding: 2rem 1rem;
            }

            .profile-image-container {
                width: 120px;
                height: 120px;
            }

            .profile-content {
                padding: 1rem;
            }

            .section {
                padding: 1rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .subjects-table th,
            .subjects-table td {
                padding: 0.75rem 1rem;
            }
        }

        .subjects-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(179, 165, 255, 0.1);
        }

        .subjects-table th,
        .subjects-table td {
            padding: 1rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.875rem;
        }

        .subjects-table th {
            background: linear-gradient(135deg, #9d86ff, #b3a5ff);
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .subjects-table tr:last-child td {
            border-bottom: none;
        }

        .subjects-table tr:hover td {
            background: var(--card-bg);
            transition: background-color 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Tutor Profile</h1>
            <div id="save-controls">
                <a href="teacherdashboard.php" id="back-button" class="btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back to Dashboard
                </a>
              
            </div>
        </header>

        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-flex">
                    <div class="profile-image-container">
                        <img id="profile-image-preview" src="<?php echo !empty($tutorData['profile_photo']) ? 'uploads/' . $tutorData['profile_photo'] : '/api/placeholder/150/150'; ?>" alt="Profile Photo" class="profile-image">
                        <label for="profile_photo" class="profile-image-overlay">
                            
                        </label>
                        
                    </div>
                    <div class="profile-info">
                        <h2 id="display-username">Username</h2>
                        <p id="display-email">email@example.com</p>
                    </div>
                </div>
            </div>

            <div class="profile-content">
                <form id="profile-form">
                    <!-- Basic Information Section -->
                    <div class="section" id="basic-info-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </div>
                            <h3 class="section-title">Basic Information</h3>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" readonly>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" readonly>
                            </div>
                            <div class="form-group">
                                <label for="mobile">Mobile Number*</label>
                                <input type="tel" id="mobile" name="mobile" readonly>
                            </div>
                            <div class="form-group">
                                <label for="age">Age*</label>
                                <input type="number" id="age" name="age" min="0" max="90" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Tutor Information Section -->
                    <div class="section" id="tutor-info-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                                </svg>
                            </div>
                            <h3 class="section-title">Tutor Information</h3>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="qualification">Qualification*</label>
                                <select id="qualification" name="qualification" disabled>
                                    <option value="">Select Qualification</option>
                                    <option value="10th">10th</option>
                                    <option value="12th">12th</option>
                                    <option value="UG">UG</option>
                                    <option value="PG">PG</option>
                                    <option value="PhD">PhD</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="teaching_mode">Teaching Mode*</label>
                                <select id="teaching_mode" name="teaching_mode" disabled>
                                    <option value="">Select Teaching Mode</option>
                                    <option value="Online">Online</option>
                                    <option value="Offline">Offline</option>
                                    <option value="Both">Both</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="experience">Experience (years)*</label>
                                <input type="number" id="experience" name="experience" min="0" readonly>
                            </div>
                            <div class="form-group">
                                <label for="hourly_rate">Hourly Rate ($)*</label>
                                <input type="number" id="hourly_rate" name="hourly_rate" step="0.01" min="0" readonly>
                            </div>
                            <div class="form-group full-width">
                                <label for="subjects">Subjects*</label>
                                <table class="subjects-table">
                                  
                                    <tbody>
                                        <?php
                                        if ($subjectResult && $subjectResult->num_rows > 0) {
                                            foreach ($subjects as $subject) {
                                                echo "<tr><td>$subject</td></tr>";
                                            }
                                        } else {
                                            echo "<tr><td>No subjects found</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="form-group full-width">
                                <label for="about">About*</label>
                                <textarea id="about" name="about" rows="4" maxlength="500" readonly></textarea>
                                <div class="char-counter">0 / 500 characters | <span class="word-counter">0 words</span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Location Information Section -->
                    <div class="section" id="location-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </div>
                            <h3 class="section-title">Location Information</h3>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="pincode">Pincode*</label>
                                <input type="text" id="pincode" name="pincode" readonly>
                            </div>
                            <div class="form-group">
                                <label for="city">City*</label>
                                <input type="text" id="city" name="city" readonly>
                            </div>
                            <div class="form-group">
                                <label for="state">State*</label>
                                <input type="text" id="state" name="state" readonly>
                            </div>
                            <div class="form-group">
                                <label for="country">Country*</label>
                                <input type="text" id="country" name="country" readonly>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Add this before closing body tag
    document.addEventListener('DOMContentLoaded', function() {
        // Fill in the profile data
        document.getElementById('username').value = '<?php echo $tutorData['username'] ?? ""; ?>';
        document.getElementById('display-username').textContent = '<?php echo $tutorData['username'] ?? ""; ?>';
        document.getElementById('email').value = '<?php echo $tutorData['email'] ?? ""; ?>';
        document.getElementById('display-email').textContent = '<?php echo $tutorData['email'] ?? ""; ?>';
        document.getElementById('mobile').value = '<?php echo $tutorData['mobile'] ?? ""; ?>';
        document.getElementById('age').value = '<?php echo $tutorData['age'] ?? ""; ?>';
        document.getElementById('qualification').value = '<?php echo $tutorData['qualification'] ?? ""; ?>';
        document.getElementById('teaching_mode').value = '<?php echo $tutorData['teaching_mode'] ?? ""; ?>';
        document.getElementById('experience').value = '<?php echo $tutorData['experience'] ?? ""; ?>';
        document.getElementById('hourly_rate').value = '<?php echo $tutorData['hourly_rate'] ?? ""; ?>';
        document.getElementById('about').value = '<?php echo $tutorData['about'] ?? ""; ?>';
        document.getElementById('pincode').value = '<?php echo $tutorData['pincode'] ?? ""; ?>';
        document.getElementById('city').value = '<?php echo $tutorData['city'] ?? ""; ?>';
        document.getElementById('state').value = '<?php echo $tutorData['state'] ?? ""; ?>';
        document.getElementById('country').value = '<?php echo $tutorData['country'] ?? ""; ?>';

        // Set profile image if exists, otherwise use placeholder
        const profileImage = '<?php echo !empty($tutorData['profile_photo']) ? "uploads/" . $tutorData['profile_photo'] : "/api/placeholder/150/150"; ?>';
        if (profileImage) {
            document.getElementById('profile-image-preview').src = profileImage;
        }
    });
    </script>
</body>
</html>