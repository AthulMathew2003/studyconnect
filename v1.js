// Get the form element
const form = document.getElementById("form");

// Regular expressions for validation
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const passwordRegex = /^.{6,12}$/;
const nameRegex = /^[A-Za-z][A-Za-z\s]*$/; // Updated regex for name validation

// Function to show error
const showError = (inputId, message) => {
  const errorElement = document.getElementById(`${inputId}-error`);
  errorElement.textContent = message;
};

// Function to clear error
const clearError = (inputId) => {
  const errorElement = document.getElementById(`${inputId}-error`);
  errorElement.textContent = "";
};

// Function to validate email
const validateEmail = (email) => {
  if (!email) {
    return "Email is required";
  }
  if (!emailRegex.test(email)) {
    return "Please enter a valid email address";
  }
  return "";
};

// Function to validate password
const validatePassword = (password) => {
  if (!password) {
    return "Password is required";
  }
  if (!passwordRegex.test(password)) {
    return "Password must be between 6 and 12 characters";
  }
  return "";
};

// Add event listeners for real-time validation
if (document.getElementById("firstname-input")) {
  // Signup form validation
  document.getElementById("firstname-input").addEventListener("input", (e) => {
    const firstname = e.target.value.trim();
    if (!firstname) {
      showError("firstname", "First name is required");
    } else if (firstname.length < 2) {
      showError("firstname", "First name must be at least 2 characters");
    } else if (!nameRegex.test(firstname)) {
      showError("firstname", "First name can only contain letters and spaces");
    } else {
      clearError("firstname");
    }
  });

  document
    .getElementById("repeat-password-input")
    .addEventListener("input", (e) => {
      const repeatPassword = e.target.value;
      const password = document.getElementById("password-input").value;

      if (!repeatPassword) {
        showError("repeat-password", "Please repeat your password");
      } else if (repeatPassword !== password) {
        showError("repeat-password", "Passwords do not match");
      } else {
        clearError("repeat-password");
      }
    });
}

// Email validation for both forms
document.getElementById("email-input").addEventListener("input", (e) => {
  const error = validateEmail(e.target.value.trim());
  if (error) {
    showError("email", error);
  } else {
    clearError("email");
  }
});

// Password validation for both forms
document.getElementById("password-input").addEventListener("input", (e) => {
  const error = validatePassword(e.target.value);
  if (error) {
    showError("password", error);
  } else {
    clearError("password");
  }
});

// Form submission
form.addEventListener("submit", (e) => {
  e.preventDefault();
  let isValid = true;

  // Validate all fields
  const email = document.getElementById("email-input").value.trim();
  const password = document.getElementById("password-input").value;

  const emailError = validateEmail(email);
  const passwordError = validatePassword(password);

  if (emailError) {
    showError("email", emailError);
    isValid = false;
  }
  if (passwordError) {
    showError("password", passwordError);
    isValid = false;
  }

  if (document.getElementById("firstname-input")) {
    // Additional signup form validation
    const firstname = document.getElementById("firstname-input").value.trim();
    const repeatPassword = document.getElementById(
      "repeat-password-input"
    ).value;

    if (!firstname) {
      showError("firstname", "First name is required");
      isValid = false;
    } else if (!nameRegex.test(firstname)) {
      showError("firstname", "First name can only contain letters and spaces");
      isValid = false;
    }
    if (repeatPassword !== password) {
      showError("repeat-password", "Passwords do not match");
      isValid = false;
    }
  }

  if (isValid) {
    // Determine which form is being submitted
    if (document.getElementById("firstname-input")) {
      // Signup form
      form.action = "signup.php";
    } else {
      // Login form
      form.action = "login.php";
    }
    form.method = "POST";
    form.submit();
  }
});
