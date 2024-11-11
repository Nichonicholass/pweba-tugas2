function togglePasswordVisibility(inputId, toggleId) {
    const passwordInput = document.getElementById(inputId);
    const toggleIcon = document.getElementById(toggleId);
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = "password";
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

function confirmLogout(event) {
    event.preventDefault(); // Mencegah tindakan default dari link
    const userConfirmed = confirm("Are you sure you want to logout?");
    if (userConfirmed) {
        window.location.href = event.target.href; // Arahkan ke halaman logout jika pengguna mengonfirmasi
    }
}