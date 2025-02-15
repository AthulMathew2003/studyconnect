<?php
include 'connectdb.php';
session_start();

// Check if profile_photo column exists in tbl_tutors
$check_column_sql = "SHOW COLUMNS FROM tbl_tutors LIKE 'profile_photo'";
$column_result = mysqli_query($conn, $check_column_sql);
if (mysqli_num_rows($column_result) == 0) {
    $add_column_sql = "ALTER TABLE tbl_tutors ADD COLUMN profile_photo VARCHAR(255)";
    mysqli_query($conn, $add_column_sql);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = array('success' => false, 'message' => '');
    
    // Validate required fields
    $required_fields = ['mobile', 'age', 'qualification', 'teaching_mode', 'experience', 'hourly_rate', 'subjects', 'about', 'pincode', 'city', 'state', 'country'];
    $missing_fields = array();
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field]) && $field !== 'subjects') {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        $response['message'] = 'Please fill in all required fields: ' . implode(', ', $missing_fields);
        echo json_encode($response);
        exit;
    }
    
    // Sanitize and validate input
    $userid = $_SESSION['userid'] ?? 0;
    
    // Handle profile photo upload
    $profile_photo = null;
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_photo'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        
        if (!in_array($file['type'], $allowed_types)) {
            $response['message'] = 'Invalid file type. Only JPG, PNG and GIF are allowed.';
            echo json_encode($response);
            exit;
        }
        
        // Create profilepic directory if it doesn't exist
        if (!file_exists('profilepic')) {
            mkdir('profilepic', 0777, true);
        }
        
        // Delete old profile photo if exists
        if ($tutor_exists) {
            $old_photo_sql = "SELECT profile_photo FROM tbl_tutors WHERE userid = '$userid'";
            $old_photo_result = mysqli_query($conn, $old_photo_sql);
            $old_photo = mysqli_fetch_assoc($old_photo_result);
            if ($old_photo && $old_photo['profile_photo']) {
                $old_photo_path = 'profilepic/' . $old_photo['profile_photo'];
                if (file_exists($old_photo_path)) {
                    unlink($old_photo_path);
                }
            }
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . $userid . '_' . time() . '.' . $extension;
        $target_path = 'profilepic/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            $profile_photo = $filename;
        } else {
            $response['message'] = 'Failed to upload profile photo';
            echo json_encode($response);
            exit;
        }
    }
    
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $age = intval($_POST['age']);
    $qualification = mysqli_real_escape_string($conn, $_POST['qualification']);
    $teaching_mode = mysqli_real_escape_string($conn, $_POST['teaching_mode']);
    $experience = intval($_POST['experience']);
    $hourly_rate = floatval($_POST['hourly_rate']);
    $about = mysqli_real_escape_string($conn, $_POST['about']);
    $pincode = mysqli_real_escape_string($conn, $_POST['pincode']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);
    $subjects = isset($_POST['subjects']) ? $_POST['subjects'] : array();
    
    // Validate numeric fields
    if ($age < 0 || $age > 90) {
        $response['message'] = 'Invalid age value';
        echo json_encode($response);
        exit;
    }
    
    if ($experience < 0) {
        $response['message'] = 'Invalid experience value';
        echo json_encode($response);
        exit;
    }
    
    if ($hourly_rate < 0) {
        $response['message'] = 'Invalid hourly rate value';
        echo json_encode($response);
        exit;
    }
    
    // Begin transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Check if tutor exists
        $check_sql = "SELECT tutor_id FROM tbl_tutors WHERE userid = '$userid'";
        $check_result = mysqli_query($conn, $check_sql);
        $tutor_exists = mysqli_fetch_assoc($check_result);
        
        if ($tutor_exists) {
            // Update existing tutor
            $photo_update = $profile_photo ? ", profile_photo = '$profile_photo'" : "";
            $update_sql = "UPDATE tbl_tutors SET 
                          mobile = '$mobile', 
                          age = $age, 
                          qualification = '$qualification', 
                          teaching_mode = '$teaching_mode', 
                          experience = $experience, 
                          hourly_rate = $hourly_rate, 
                          about = '$about'
                          $photo_update 
                          WHERE userid = '$userid'";
            mysqli_query($conn, $update_sql);
            
            $tutor_id = $tutor_exists['tutor_id'];
        } else {
            // Insert new tutor
            $photo_field = $profile_photo ? ", profile_photo" : "";
            $photo_value = $profile_photo ? ", '$profile_photo'" : "";
            $insert_sql = "INSERT INTO tbl_tutors (userid, mobile, age, qualification, 
                          teaching_mode, experience, hourly_rate, about$photo_field) 
                          VALUES ('$userid', '$mobile', $age, '$qualification', 
                          '$teaching_mode', $experience, $hourly_rate, '$about'$photo_value)";
            mysqli_query($conn, $insert_sql);
            
            $tutor_id = mysqli_insert_id($conn);
        }
        
        // Update location information
        $location_sql = "INSERT INTO tbl_locations (userid, pincode, city, state, country) 
                        VALUES ('$userid', '$pincode', '$city', '$state', '$country') 
                        ON DUPLICATE KEY UPDATE 
                        pincode = '$pincode', 
                        city = '$city', 
                        state = '$state', 
                        country = '$country'";
        mysqli_query($conn, $location_sql);
        
        // Update subjects
        // First delete existing subjects
        $delete_subjects_sql = "DELETE FROM tbl_tutorsubject WHERE tutor_id = '$tutor_id'";
        mysqli_query($conn, $delete_subjects_sql);
        
        // Insert new subjects
        if (!empty($subjects)) {
            foreach ($subjects as $subject_id) {
                // Validate that subject_id is numeric
                if (!is_numeric($subject_id)) {
                    throw new Exception("Invalid subject ID");
                }
                $subject_id = mysqli_real_escape_string($conn, $subject_id);
                $insert_subjects_sql = "INSERT INTO tbl_tutorsubject (tutor_id, subject_id) 
                                      VALUES ('$tutor_id', '$subject_id')";
                mysqli_query($conn, $insert_subjects_sql);
            }
        }
        
        mysqli_commit($conn);
        $response['success'] = true;
        $response['message'] = 'Profile updated successfully!';
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $response['message'] = 'An error occurred: ' . $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}

// Fetch user data from database
$userid = $_SESSION['userid'] ?? 0;
$sql = "SELECT username, email FROM users WHERE userid = '$userid'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

// If user not found, redirect to login
if (!$user) {
    header("Location: login.php");
    exit();
}

// Fetch tutor data from database
$sql = "SELECT t.*, l.pincode, l.city, l.state, l.country 
        FROM tbl_tutors t 
        LEFT JOIN tbl_locations l ON t.userid = l.userid 
        WHERE t.userid = '$userid'";
$result = mysqli_query($conn, $sql);
$tutor = mysqli_fetch_assoc($result);

if ($tutor) {
    // Fetch subjects separately to avoid duplicates
    $subjects_sql = "SELECT subject_id FROM tbl_tutorsubject WHERE tutor_id = '{$tutor['tutor_id']}'";
    $subjects_result = mysqli_query($conn, $subjects_sql);
    $tutor_subjects = array();
    while ($row = mysqli_fetch_assoc($subjects_result)) {
        $tutor_subjects[] = $row['subject_id'];
    }
    
    $city = $tutor['city'] ?? '';
    $state = $tutor['state'] ?? '';
    $country = $tutor['country'] ?? '';
    $mobile = $tutor['mobile'] ?? '';
    $age = $tutor['age'] ?? '';
    $about = $tutor['about'] ?? '';
    $pincode = $tutor['pincode'] ?? '';
} else {
    $city = '';
    $state = '';
    $country = '';
    $mobile = '';
    $age = '';
    $about = '';
    $pincode = '';
    $tutor_subjects = array();
}

// Set default profile image if none exists
$profile_image = $tutor && $tutor['profile_photo'] ? 'profilepic/' . $tutor['profile_photo'] : '/api/placeholder/150/150';

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

        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--multiple {
            min-height: 45px;
            border-radius: 12px;
            border-color: var(--border-color);
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #f0edff;
            border-color: var(--border-color);
            color: var(--text-primary);
            border-radius: 8px;
            padding: 0.25rem 0.5rem;
            margin-top: 0.5rem;
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

        #profile_photo {
            display: none;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            color: white;
            font-weight: 500;
            transform: translateX(150%);
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification.success {
            background: linear-gradient(135deg, #7cd992, #5eb77a);
        }

        .notification.error {
            background: linear-gradient(135deg, #ff7c7c, #ff5c5c);
        }

        .char-counter {
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-align: right;
            margin-top: 0.25rem;
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
        }

        .error-message {
            color: #ff5c5c;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        input.error, select.error, textarea.error {
            border-color: #ff5c5c !important;
        }

        input.error:focus, select.error:focus, textarea.error:focus {
            box-shadow: 0 0 0 3px rgba(255, 92, 92, 0.2) !important;
        }

        .select2-container--default.error .select2-selection {
            border-color: #ff5c5c !important;
        }

        .select2-container--default.error .select2-selection:focus {
            box-shadow: 0 0 0 3px rgba(255, 92, 92, 0.2) !important;
        }

        /* Style for non-editable fields */
        input[readonly].static-field {
            background-color: #f8f9ff;
            cursor: not-allowed;
        }
        
        #back-button {
            background: linear-gradient(135deg, #6e7ff3, #b3a5ff);
            color: white;
        }
        
        #back-button:hover {
            box-shadow: 0 4px 12px rgba(110, 127, 243, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header>
            <h1>Tutor Profile</h1>
            <div id="save-controls">
                <a href="teacherdashboard.php" id="back-button" class="btn" style="margin-right: 1rem; opacity: 0.5; pointer-events: none;" title="Complete your profile to access dashboard">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back to Dashboard
                </a>
                <button id="save-button" class="btn btn-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    Save Changes
                </button>
            </div>
        </header>

        <!-- Profile Card -->
        <div class="profile-card">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-flex">
                    <div class="profile-image-container">
                        <img id="profile-image-preview" src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Photo" class="profile-image">
                        <label for="profile_photo" class="profile-image-overlay">
                            <span>Change Photo</span>
                        </label>
                        <input type="file" id="profile_photo" name="profile_photo" accept="image/*">
                    </div>
                    <div class="profile-info">
                        <h2 id="display-username"><?php echo htmlspecialchars($user['username']); ?></h2>
                        <p id="display-email"><?php echo htmlspecialchars($user['email']); ?></p>
                        <p id="display-role">Mathematics Tutor</p>
                    </div>
                </div>
            </div>

            <!-- Profile Content -->
            <div class="profile-content">
                <form id="profile-form" method="post" enctype="multipart/form-data">
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
                                <input type="text" id="username" name="username" class="static-field" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" class="static-field" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="mobile">Mobile Number*</label>
                                <input type="tel" id="mobile" name="mobile" value="<?php echo htmlspecialchars($mobile); ?>">
                            </div>
                            <div class="form-group">
                                <label for="age">Age*</label>
                                <input type="number" id="age" name="age" min="0" max="90" value="<?php echo htmlspecialchars($age); ?>">
                            </div>
                            <div class="form-group">
                                <label for="role">Role</label>
                                <input type="text" id="role" name="role" class="static-field" value="<?php echo htmlspecialchars($tutor['role'] ?? 'Tutor'); ?>" readonly>
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
                                <select id="qualification" name="qualification">
                                    <option value="">Select Qualification</option>
                                    <option value="10th" <?php echo ($tutor['qualification'] ?? '') == '10th' ? 'selected' : ''; ?>>10th</option>
                                    <option value="12th" <?php echo ($tutor['qualification'] ?? '') == '12th' ? 'selected' : ''; ?>>12th</option>
                                    <option value="UG" <?php echo ($tutor['qualification'] ?? '') == 'UG' ? 'selected' : ''; ?>>UG</option>
                                    <option value="PG" <?php echo ($tutor['qualification'] ?? '') == 'PG' ? 'selected' : ''; ?>>PG</option>
                                    <option value="PhD" <?php echo ($tutor['qualification'] ?? '') == 'PhD' ? 'selected' : ''; ?>>PhD</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="teaching_mode">Teaching Mode*</label>
                                <select id="teaching_mode" name="teaching_mode">
                                    <option value="">Select Teaching Mode</option>
                                    <option value="Online" <?php echo ($tutor['teaching_mode'] ?? '') == 'Online' ? 'selected' : ''; ?>>Online</option>
                                    <option value="Offline" <?php echo ($tutor['teaching_mode'] ?? '') == 'Offline' ? 'selected' : ''; ?>>Offline</option>
                                    <option value="Both" <?php echo ($tutor['teaching_mode'] ?? '') == 'Both' ? 'selected' : ''; ?>>Both</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="experience">Experience (years)*</label>
                                <input type="number" id="experience" name="experience" min="0" value="<?php echo htmlspecialchars($tutor['experience'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="hourly_rate">Hourly Rate ($)*</label>
                                <input type="number" id="hourly_rate" name="hourly_rate" step="0.01" min="0" value="<?php echo htmlspecialchars($tutor['hourly_rate'] ?? ''); ?>">
                            </div>
                            <div class="form-group full-width">
                                <label for="subjects">Subjects*</label>
                                <select id="subjects" name="subjects[]" multiple>
                                    <?php
                                    include 'connectdb.php';
                                    $sql = "SELECT subject_id, subject FROM tbl_subject";
                                    $result = $conn->query($sql);
                                    
                                    if ($result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            $subject_id = $row['subject_id'];
                                            $subject = $row['subject'];
                                            $selected = in_array($subject_id, $tutor_subjects ?? []) ? 'selected' : '';
                                            echo "<option value='$subject_id' $selected>$subject</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group full-width">
                                <label for="about">About*</label>
                                <textarea id="about" name="about" rows="4" maxlength="500"><?php echo htmlspecialchars($about); ?></textarea>
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
                                <input type="text" id="pincode" name="pincode" value="<?php echo htmlspecialchars($pincode); ?>">
                            </div>
                            <div class="form-group">
                                <label for="city">City*</label>
                                <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($city); ?>">
                            </div>
                            <div class="form-group">
                                <label for="state">State*</label>
                                <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($state); ?>">
                            </div>
                            <div class="form-group">
                                <label for="country">Country*</label>
                                <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($country); ?>">
                            </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript Dependencies -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize Select2 for subjects
            $('#subjects').select2({
                placeholder: 'Select subjects',
                width: '100%'
            });

            // Add error message display function
            function showFieldError(field, message) {
                let errorDiv = field.next('.error-message');
                if (!errorDiv.length) {
                    errorDiv = $('<div class="error-message"></div>');
                    field.after(errorDiv);
                }
                errorDiv.text(message).addClass('show');
                field.addClass('error');
            }

            function clearFieldError(field) {
                field.next('.error-message').removeClass('show');
                field.removeClass('error');
            }

            // Live validation for age
            $('#age').on('input', function() {
                const age = parseInt($(this).val());
                if (age < 18) {
                    showFieldError($(this), 'Age must be at least 18 years');
                } else if (age > 90) {
                    showFieldError($(this), 'Age cannot be more than 90 years');
                } else {
                    clearFieldError($(this));
                }
            });

            // Live validation for experience
            $('#experience').on('input', function() {
                const experience = parseInt($(this).val());
                const age = parseInt($('#age').val());
                
                if (experience < 0) {
                    showFieldError($(this), 'Experience cannot be negative');
                } else if (experience > (age - 18)) {
                    showFieldError($(this), 'Experience cannot be more than ' + (age - 18) + ' years');
                } else {
                    clearFieldError($(this));
                }
            });

            // Live validation for hourly rate
            $('#hourly_rate').on('input', function() {
                const rate = parseFloat($(this).val());
                if (rate < 0) {
                    showFieldError($(this), 'Hourly rate cannot be negative');
                } else if (rate > 1000) {
                    showFieldError($(this), 'Hourly rate cannot exceed $1000');
                } else {
                    clearFieldError($(this));
                }
            });

            // Live validation for mobile
            $('#mobile').on('input', function() {
                const mobile = $(this).val();
                const mobileRegex = /^[0-9]{10}$/;
                
                if (!mobileRegex.test(mobile)) {
                    showFieldError($(this), 'Please enter a valid 10-digit mobile number');
                } else {
                    clearFieldError($(this));
                }
            });

            // Live validation for pincode
            $('#pincode').on('input', function() {
                const pincode = $(this).val();
                const pincodeRegex = /^[0-9]{6}$/;
                
                if (!pincodeRegex.test(pincode)) {
                    showFieldError($(this), 'Please enter a valid 6-digit pincode');
                } else {
                    clearFieldError($(this));
                }
            });

            // Live validation for city, state, and country
            $('#city, #state, #country').on('input', function() {
                const value = $(this).val();
                const nameRegex = /^[a-zA-Z\s]{2,50}$/;
                
                if (!nameRegex.test(value)) {
                    showFieldError($(this), 'Please enter a valid name (2-50 characters, letters only)');
                } else {
                    clearFieldError($(this));
                }
            });

            // Live validation for about
            $('#about').on('input', function() {
                const about = $(this).val();
                if (about.length < 50) {
                    showFieldError($(this), 'About section must be at least 50 characters');
                } else if (about.length > 500) {
                    showFieldError($(this), 'About section cannot exceed 500 characters');
                } else {
                    clearFieldError($(this));
                }
                
                // Update character counter
                const maxLength = 500;
                const currentLength = $(this).val().length;
                const wordCount = $(this).val().trim().split(/\s+/).length;
                
                $('.char-counter').html(
                    currentLength + ' / ' + maxLength + ' characters | ' +
                    '<span class="word-counter">' + wordCount + ' words</span>'
                );
            });

            // Live validation for qualification and teaching_mode
            $('#qualification, #teaching_mode').on('change', function() {
                const value = $(this).val();
                if (!value) {
                    showFieldError($(this), 'This field is required');
                } else {
                    clearFieldError($(this));
                }
            });

            // Live validation for subjects
            $('#subjects').on('change', function() {
                const subjects = $(this).val();
                if (!subjects || subjects.length === 0) {
                    $(this).next('.select2-container').addClass('error');
                    showFieldError($(this), 'Please select at least one subject');
                } else {
                    $(this).next('.select2-container').removeClass('error');
                    clearFieldError($(this));
                }
            });

            // Handle profile photo preview
            $('#profile_photo').on('change', function(e) {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    
                    if (!allowedTypes.includes(file.type)) {
                        showNotification('Invalid file type. Only JPG, PNG and GIF are allowed.', 'error');
                        this.value = '';
                        return;
                    }
                    
                    if (file.size > 5 * 1024 * 1024) { // 5MB
                        showNotification('File is too large. Maximum size is 5MB.', 'error');
                        this.value = '';
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#profile-image-preview').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });

            // Check if profile is complete
            function isProfileComplete() {
                const requiredFields = {
                    mobile: /^[0-9]{10}$/,
                    age: value => parseInt(value) >= 18 && parseInt(value) <= 90,
                    qualification: value => value.length > 0,
                    teaching_mode: value => value.length > 0,
                    experience: value => {
                        const exp = parseInt(value);
                        const age = parseInt($('#age').val());
                        return exp >= 0 && exp <= (age - 18);
                    },
                    hourly_rate: value => {
                        const rate = parseFloat(value);
                        return rate > 0 && rate <= 1000;
                    },
                    about: value => value.length >= 50 && value.length <= 500,
                    pincode: /^[0-9]{6}$/,
                    city: /^[a-zA-Z\s]{2,50}$/,
                    state: /^[a-zA-Z\s]{2,50}$/,
                    country: /^[a-zA-Z\s]{2,50}$/
                };

                let isComplete = true;
                
                // Check all required fields
                for (const [field, validator] of Object.entries(requiredFields)) {
                    const value = $(`#${field}`).val();
                    if (validator instanceof RegExp) {
                        if (!validator.test(value)) {
                            isComplete = false;
                            break;
                        }
                    } else {
                        if (!validator(value)) {
                            isComplete = false;
                            break;
                        }
                    }
                }

                // Check subjects
                const subjects = $('#subjects').val();
                if (!subjects || subjects.length === 0) {
                    isComplete = false;
                }

                // Update back button state
                const backButton = $('#back-button');
                if (isComplete) {
                    backButton.css('opacity', '1').css('pointer-events', 'auto')
                             .attr('title', 'Go back to dashboard');
                } else {
                    backButton.css('opacity', '0.5').css('pointer-events', 'none')
                             .attr('title', 'Complete your profile to access dashboard');
                }

                return isComplete;
            }

            // Check profile completion on any input change
            $('input, select, textarea').on('input change', function() {
                isProfileComplete();
            });

            // Initial check
            isProfileComplete();

            // Form submission
            $('#save-button').click(function(e) {
                e.preventDefault();
                
                if (!validateForm()) {
                    showNotification('Please correct all errors before submitting', 'error');
                    return;
                }
                
                // Get form data
                const formData = new FormData($('#profile-form')[0]);
                
                // Add the subjects array and profile photo
                const subjects = $('#subjects').val();
                if (subjects) {
                    subjects.forEach(subject => {
                        formData.append('subjects[]', subject);
                    });
                }
                
                // Add profile photo if selected
                const profilePhotoInput = $('#profile_photo')[0];
                if (profilePhotoInput.files.length > 0) {
                    formData.append('profile_photo', profilePhotoInput.files[0]);
                }
                
                // Send AJAX request
                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        const result = JSON.parse(response);
                        showNotification(result.message, result.success ? 'success' : 'error');
                        
                        if (result.success) {
                            // Reload the page after successful update to show new profile photo
                            setTimeout(() => window.location.reload(), 2000);
                        }
                    },
                    error: function() {
                        showNotification('An error occurred while saving changes', 'error');
                    }
                });
            });

            // Show notification
            function showNotification(message, type) {
                const notification = $('<div class="notification ' + type + '">' + message + '</div>');
                $('body').append(notification);
                
                setTimeout(function() {
                    notification.addClass('show');
                }, 100);
                
                setTimeout(function() {
                    notification.removeClass('show');
                    setTimeout(function() {
                        notification.remove();
                    }, 300);
                }, 3000);
            }

            // Form validation
            function validateForm() {
                let isValid = true;
                
                // Trigger validation on all fields
                $('#age').trigger('input');
                $('#experience').trigger('input');
                $('#hourly_rate').trigger('input');
                $('#mobile').trigger('input');
                $('#pincode').trigger('input');
                $('#city').trigger('input');
                $('#state').trigger('input');
                $('#country').trigger('input');
                $('#about').trigger('input');
                $('#qualification').trigger('change');
                $('#teaching_mode').trigger('change');
                $('#subjects').trigger('change');
                
                // Check for any error messages
                if ($('.error-message.show').length > 0) {
                    isValid = false;
                }
                
                return isValid;
            }
        });
    </script>
</body>
</html>