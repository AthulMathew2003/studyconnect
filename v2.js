// live validation during reseting and forgot password

document.addEventListener("DOMContentLoaded", function () {
  const passwordInput = document.getElementById("password-input");
  const confirmPasswordInput = document.getElementById(
    "confirm-password-input"
  );
  const passwordError = document.getElementById("password-error");
  const confirmPasswordError = document.getElementById(
    "confirm-password-error"
  );

  function validatePassword() {
    const password = passwordInput.value;

    // Check password length
    if (password.length === 0) {
      passwordError.textContent = "Password is required";
      return false;
    } else if (password.length < 6) {
      passwordError.textContent = "Password must be at least 6 characters";
      return false;
    } else if (password.length > 12) {
      passwordError.textContent = "Password must not exceed 12 characters";
      return false;
    } else {
      passwordError.textContent = "";
      return true;
    }
  }

  function validateConfirmPassword() {
    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;

    // Check if passwords match
    if (confirmPassword.length === 0) {
      confirmPasswordError.textContent = "Confirm password is required";
      return false;
    } else if (password !== confirmPassword) {
      confirmPasswordError.textContent = "Passwords do not match";
      return false;
    } else {
      confirmPasswordError.textContent = "";
      return true;
    }
  }

  // Add event listeners for real-time validation
  passwordInput.addEventListener("input", function () {
    validatePassword();
    if (confirmPasswordInput.value !== "") {
      validateConfirmPassword();
    }
  });

  confirmPasswordInput.addEventListener("input", validateConfirmPassword);

  // Validate form on submit
  document.getElementById("form").addEventListener("submit", function (e) {
    const isPasswordValid = validatePassword();
    const isConfirmPasswordValid = validateConfirmPassword();

    if (!isPasswordValid || !isConfirmPasswordValid) {
      e.preventDefault();
    }
  });
});
