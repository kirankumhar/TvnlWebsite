document.addEventListener("DOMContentLoaded", function () {
  const input = document.getElementById("domain_path");
  const prefix = "https://";

  // Ensure prefix on page load
  if (!input.value.startsWith(prefix)) {
    input.value = prefix;
  }

  input.addEventListener("input", function () {
    // Always keep prefix
    if (!input.value.startsWith(prefix)) {
      input.value = prefix;
    }

    // Extract user-entered part
    let domainPart = input.value.slice(prefix.length);

    // Allow only lowercase letters and dots
    domainPart = domainPart.toLowerCase().replace(/[^a-z.]/g, "");

    input.value = prefix + domainPart;
  });

  // Prevent cursor from going before prefix
  input.addEventListener("keydown", function (e) {
    if (input.selectionStart < prefix.length) {
      input.setSelectionRange(prefix.length, prefix.length);
    }
  });

  input.addEventListener("click", function () {
    if (input.selectionStart < prefix.length) {
      input.setSelectionRange(prefix.length, prefix.length);
    }
  });
});

document.addEventListener('DOMContentLoaded', function () {

    const passwordInput = document.getElementById('UserPassword');
    const confirmInput  = document.getElementById('confirmPassword');
    const mismatchText  = document.getElementById('passwordMismatch');
    const criteriaBox   = document.getElementById('passwordCriteria');
    const toggleBtn     = document.getElementById('togglePassword');
    const form          = passwordInput.closest('form');
    const submitBtn     = form.querySelector('button[type="submit"]');

    function validatePassword() {
        const val = passwordInput.value;

        const checks = {
            len:      val.length >= 8,
            upper:    (val.match(/[A-Z]/g) || []).length === 1,
            lower:    (val.match(/[a-z]/g) || []).length >= 1,
            number:   (val.match(/[0-9]/g) || []).length >= 1,
            special:  (val.match(/[^A-Za-z0-9]/g) || []).length === 1
        };

        Object.keys(checks).forEach(id => toggle(id, checks[id]));

        const passwordOk = Object.values(checks).every(Boolean);

        criteriaBox.style.display = passwordOk ? 'block' : 'block';

        return passwordOk;
    }

    function validateConfirm() {
        const match = passwordInput.value !== '' &&
                      passwordInput.value === confirmInput.value;

        mismatchText.style.display = match ? 'none' : 'block';

        return match;
    }

    function toggle(id, ok) {
        const el = document.getElementById(id);
        if (!el) return;

        el.classList.remove('text-dark', 'text-danger', 'text-success');
        el.classList.add(ok ? 'text-success' : 'text-danger');
    }

    function toggleSubmit() {
        const isPasswordOk = validatePassword();
        const isMatchOk    = validateConfirm();

        submitBtn.disabled = !(isPasswordOk && isMatchOk);
    }

    passwordInput.addEventListener('keyup', toggleSubmit);
    confirmInput.addEventListener('keyup', toggleSubmit);

    // 👁 Eye toggle
    toggleBtn.addEventListener('click', function () {
        const icon = this.querySelector('i');

        passwordInput.type =
            passwordInput.type === 'password' ? 'text' : 'password';

        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
    });

    // initial state
    submitBtn.disabled = true;

});

const prefixInput = document.getElementById('emailPrefix');
const finalEmail = document.getElementById('finalEmail');
const domain = '@cgstranchizone.gov.in';

prefixInput.addEventListener('input', function () {
    // allow only lowercase letters & numbers
    this.value = this.value
        .toLowerCase()
        .replace(/[^a-z0-9]/g, '');

    // build final email
    finalEmail.value = this.value + domain;
});

