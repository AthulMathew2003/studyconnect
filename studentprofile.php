<?php
session_start();
include 'connectdb.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$userid = $_SESSION['userid'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mobile = $_POST['mobile'];
    $learning_mode = $_POST['learning_mode'];
    $pincode = $_POST['pincode'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $country = $_POST['country'];

    // Sanitize inputs
    $mobile = mysqli_real_escape_string($conn, $mobile);
    $learning_mode = mysqli_real_escape_string($conn, $learning_mode);
    $pincode = mysqli_real_escape_string($conn, $pincode);
    $city = mysqli_real_escape_string($conn, $city);
    $state = mysqli_real_escape_string($conn, $state);
    $country = mysqli_real_escape_string($conn, $country);

    // Handle profile photo upload
    $profile_photo = null;
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['profile_photo']['type'], $allowed_types)) {
            die("Error: Only JPG, PNG and GIF images are allowed.");
        }
        
        if ($_FILES['profile_photo']['size'] > $max_size) {
            die("Error: File size must be less than 5MB.");
        }
        
        // Create uploads directory if it doesn't exist
        $upload_dir = 'uploads/profile_photos/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename
        $file_extension = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('profile_') . '.' . $file_extension;
        $target_path = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_path)) {
            $profile_photo = $target_path;
        } else {
            die("Error: Failed to upload file.");
        }
    }

    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Check if user exists in tbl_student
        $check_student = "SELECT student_id FROM tbl_student WHERE userid = '" . (int)$userid . "'";
        $result_student = $conn->query($check_student);
        
        if (!$result_student) {
            throw new Exception("Error checking student: " . $conn->error);
        }
        
        $student_id = null;

        if ($result_student->num_rows === 0) {
            // Insert new student record
            $insert_student = "INSERT INTO tbl_student (userid, mobile, mode_of_learning, profilephoto) 
                             VALUES ('" . (int)$userid . "', '$mobile', '$learning_mode', " . 
                             ($profile_photo ? "'$profile_photo'" : "NULL") . ")";
                             
            if (!$conn->query($insert_student)) {
                throw new Exception("Error creating student record: " . $conn->error);
            }
            $student_id = $conn->insert_id;
        } else {
            // Update existing student record
            $student_row = $result_student->fetch_assoc();
            $student_id = $student_row['student_id'];
            $update_photo = $profile_photo ? ", profilephoto = '$profile_photo'" : "";
            
            $update_student = "UPDATE tbl_student 
                             SET mobile = '$mobile', 
                                 mode_of_learning = '$learning_mode'
                                 $update_photo
                             WHERE userid = '" . (int)$userid . "'";
                             
            if (!$conn->query($update_student)) {
                throw new Exception("Error updating student record: " . $conn->error);
            }
        }

        // Check if location exists in tbl_studentlocation
        $check_location = "SELECT * FROM tbl_studentlocation WHERE student_id = '" . (int)$student_id . "'";
        $result_location = $conn->query($check_location);
        
        if (!$result_location) {
            throw new Exception("Error checking location: " . $conn->error);
        }

        if ($result_location->num_rows === 0) {
            // Insert new location record
            $insert_location = "INSERT INTO tbl_studentlocation (student_id, pincode, city, state, country) 
                              VALUES ('" . (int)$student_id . "', '$pincode', '$city', '$state', '$country')";
                              
            if (!$conn->query($insert_location)) {
                throw new Exception("Error creating location record: " . $conn->error);
            }
        } else {
            // Update existing location record
            $update_location = "UPDATE tbl_studentlocation 
                              SET pincode = '$pincode',
                                  city = '$city',
                                  state = '$state',
                                  country = '$country'
                              WHERE student_id = '" . (int)$student_id . "'";
                              
            if (!$conn->query($update_location)) {
                throw new Exception("Error updating location record: " . $conn->error);
            }
        }

        // Commit transaction
        $conn->commit();
        header('Location: studentdashboard.php');
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $error_message = $e->getMessage();
        echo "<div class='error-message' style='background: #ff6b6b; color: white; padding: 10px; margin: 10px; border-radius: 5px;'>
                Error: " . htmlspecialchars($error_message) . "
              </div>";
    }
}

// Fetch user data
$result = $conn->query("SELECT username, email FROM users WHERE userid = $userid");
$user = $result->fetch_assoc();

// Initialize default data
$student_data = array(
    'mobile' => '',
    'mode_of_learning' => 'Both',
    'profilephoto' => ''
);

$location_data = array(
    'pincode' => '',
    'city' => '',
    'state' => '',
    'country' => ''
);

// Fetch existing student data if available
$student_query = "SELECT s.*, sl.pincode, sl.city, sl.state, sl.country 
                 FROM tbl_student s 
                 LEFT JOIN tbl_studentlocation sl ON s.student_id = sl.student_id 
                 WHERE s.userid = $userid";
$student_result = $conn->query($student_query);
        
if ($student_result->num_rows > 0) {
    $row = $student_result->fetch_assoc();
    $student_data = array(
        'mobile' => $row['mobile'],
        'mode_of_learning' => $row['mode_of_learning'],
        'profilephoto' => $row['profilephoto']
    );
    
    $location_data = array(
        'pincode' => $row['pincode'],
        'city' => $row['city'],
        'state' => $row['state'],
        'country' => $row['country']
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student Profile | StudyConnect</title>
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
            width: 500px;
            height: 100%;
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

        .save-button {
            display: block;
            width: calc(100% - 4rem);
            margin: 0 2rem 2rem;
            padding: 1rem;
            background: linear-gradient(135deg, var(--accent-color), var(--border-color));
            border: none;
            border-radius: var(--border-radius);
            color: var(--text-primary);
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .save-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--glow);
        }

        .avatar-upload {
            position: relative;
            cursor: pointer;
            display: flex;
        }

        .avatar-upload input[type="file"] {
            display: none;
        }

        .avatar-upload:hover .avatar::after {
            content: 'Change Photo';
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.profile-container');
            const mobileInput = form.querySelector('input[name="mobile"]');
            const pincodeInput = form.querySelector('input[name="pincode"]');
            const cityInput = form.querySelector('input[name="city"]');
            const stateInput = form.querySelector('input[name="state"]');
            const countryInput = form.querySelector('input[name="country"]');
            const saveButton = form.querySelector('.save-button');

            // Validation patterns
            const mobilePattern = /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/;
            const pincodePattern = /^[0-9]{6}$/;
            const textPattern = /^[A-Za-z\s]{2,50}$/;

            // Validation functions
            function validateMobile(value) {
                return mobilePattern.test(value);
            }

            function validatePincode(value) {
                return pincodePattern.test(value);
            }

            function validateText(value) {
                return textPattern.test(value);
            }

            // Error message display
            function showError(input, message) {
                const parent = input.parentElement;
                let error = parent.querySelector('.error-message');
                
                if (!error) {
                    error = document.createElement('div');
                    error.className = 'error-message';
                    parent.appendChild(error);
                }
                
                error.textContent = message;
                input.classList.remove('success');
                input.classList.add('error');
            }

            function clearError(input) {
                const parent = input.parentElement;
                const error = parent.querySelector('.error-message');
                if (error) {
                    error.remove();
                }
                input.classList.remove('error');
                input.classList.add('success');
            }

            // Real-time validation with debounce
            function debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }

            const validateInputWithDebounce = debounce((input, validator, errorMessage) => {
                if (!validator(input.value)) {
                    showError(input, errorMessage);
                } else {
                    clearError(input);
                }
            }, 300);

            // Input event listeners
            mobileInput.addEventListener('input', function() {
                validateInputWithDebounce(this, validateMobile, 'Please enter a valid mobile number');
            });

            pincodeInput.addEventListener('input', function() {
                validateInputWithDebounce(this, validatePincode, 'Please enter a valid 6-digit pincode');
            });

            cityInput.addEventListener('input', function() {
                validateInputWithDebounce(this, validateText, 'City should contain only letters and spaces (2-50 characters)');
            });

            stateInput.addEventListener('input', function() {
                validateInputWithDebounce(this, validateText, 'State should contain only letters and spaces (2-50 characters)');
            });

            countryInput.addEventListener('input', function() {
                validateInputWithDebounce(this, validateText, 'Country should contain only letters and spaces (2-50 characters)');
            });

            // Form submission validation
            form.addEventListener('submit', function(e) {
                let isValid = true;
                const inputs = [
                    { element: mobileInput, validator: validateMobile, message: 'Please enter a valid mobile number' },
                    { element: pincodeInput, validator: validatePincode, message: 'Please enter a valid 6-digit pincode' },
                    { element: cityInput, validator: validateText, message: 'City should contain only letters and spaces (2-50 characters)' },
                    { element: stateInput, validator: validateText, message: 'State should contain only letters and spaces (2-50 characters)' },
                    { element: countryInput, validator: validateText, message: 'Country should contain only letters and spaces (2-50 characters)' }
                ];

                inputs.forEach(({ element, validator, message }) => {
                    if (!validator(element.value)) {
                        showError(element, message);
                        isValid = false;
                        element.focus();
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    saveButton.classList.add('shake');
                    setTimeout(() => saveButton.classList.remove('shake'), 500);
                }
            });

            pincodeInput.addEventListener('blur', function() {
                const pincode = this.value;
                if (pincode.length === 6) {
                    fetch(`https://api.postalpincode.in/pincode/${pincode}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data[0].Status === "Success") {
                                const postOffice = data[0].PostOffice[0];
                                cityInput.value = postOffice.District;
                                stateInput.value = postOffice.State;
                                countryInput.value = postOffice.Country;

                                // Make fields read-only
                                cityInput.readOnly = true;
                                stateInput.readOnly = true;
                                countryInput.readOnly = true;
                            } else {
                                showError(this, "Invalid Pincode. Please enter a valid Indian pincode.");
                            }
                        })
                        .catch(error => {
                            showError(this, "Error fetching location data. Please try again.");
                        });
                }
            });

            const profilePhotoInput = document.getElementById('profilePhotoInput');
            const profilePhotoPreview = document.getElementById('profilePhotoPreview');

            profilePhotoInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        profilePhotoPreview.innerHTML = `<img src="${e.target.result}" alt="Profile Photo" style="width: 200px; height: 200px; border-radius: 50%;">`;
                    }
                    reader.readAsDataURL(file);
                } else {
                    profilePhotoPreview.innerHTML = '<i class="fas fa-user" style="opacity: 0.8;"></i>';
                }
            });
        });
    </script>
</head>
<body>
    <form class="profile-container" method="POST" action="" enctype="multipart/form-data">
        <div class="profile-header">
            <label class="avatar-container avatar-upload">
                <input type="file" name="profile_photo" accept="image/*" id="profilePhotoInput">
                <div class="avatar" id="profilePhotoPreview">
                    <?php if (!empty($student_data['profilephoto']) && file_exists($student_data['profilephoto'])): ?>
                        <img src="<?php echo htmlspecialchars($student_data['profilephoto']); ?>" alt="Profile Photo" style="width: 200px; height: 200px;  border-radius: 50%;">
                    <?php else: ?>
                        <i class="fas fa-user" style="opacity: 0.8;"></i>
                    <?php endif; ?>
                </div>
            </label>
            <input type="text" name="full_name" class="profile-name" value="<?php echo htmlspecialchars($user['username']); ?>" style="background: none; border: none; text-align: center; width: 100%; margin-bottom: 0.5rem;" readonly>
            <div class="profile-id"><?php echo htmlspecialchars($user['email']); ?></div>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <span class="status-badge">Active</span>
                <div class="info-label">
                    <i class="fas fa-user"></i>
                    Full Name
                </div>
                <div class="info-value">
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                </div>
            </div>

            <div class="info-card">
                <div class="info-label">
                    <i class="fas fa-envelope"></i>
                    Email Address
                </div>
                <div class="info-value">
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                </div>
            </div>

            <div class="info-card">
                <div class="info-label">
                    <i class="fas fa-phone"></i>
                    Mobile Number
                </div>
                <div class="info-value">
                    <input type="tel" name="mobile" value="<?php echo htmlspecialchars($student_data['mobile']); ?>" pattern="[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}" required>
                </div>
            </div>

            <div class="info-card">
                <div class="info-label">
                    <i class="fas fa-graduation-cap"></i>
                    Mode of Learning
                </div>
                <div class="info-value">
                    <select name="learning_mode" required>
                        <option value="Both" <?php echo $student_data['mode_of_learning'] === 'Both' ? 'selected' : ''; ?>>Both</option>
                        <option value="Online" <?php echo $student_data['mode_of_learning'] === 'Online' ? 'selected' : ''; ?>>Online</option>
                        <option value="Offline" <?php echo $student_data['mode_of_learning'] === 'Offline' ? 'selected' : ''; ?>>Offline</option>
                    </select>
                </div>
            </div>

            <div class="info-card">
                <div class="info-label">
                    <i class="fas fa-map-marker-alt"></i>
                    Location Details
                </div>
                <div class="info-value location-grid">
                    <input type="text" name="pincode" value="<?php echo htmlspecialchars($location_data['pincode']); ?>" placeholder="Pincode" pattern="[0-9]{6}" title="Please enter a valid 6-digit pincode" required>
                    <input type="text" name="city" value="<?php echo htmlspecialchars($location_data['city']); ?>" placeholder="City" required>
                    <input type="text" name="state" value="<?php echo htmlspecialchars($location_data['state']); ?>" placeholder="State" required>
                    <input type="text" name="country" value="<?php echo htmlspecialchars($location_data['country']); ?>" placeholder="Country" required>
                </div>
            </div>
        </div>

        <button type="submit" class="save-button">
            <i class="fas fa-save"></i> Save Changes
        </button>
    </form>
</body>
</html>