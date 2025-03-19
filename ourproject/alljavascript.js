
if (document.querySelector('#login-form')) {
    const loginForm = document.querySelector('#login-form');
    loginForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const email = document.querySelector('#em').value;
        const password = document.querySelector('#pass').value;
        const username = document.querySelector('#user').value;

        if (!username || !email || !password) {
            alert('Please fill in all fields: username, email, and password.');
        } else {
            window.location.href = "practice.html";
        }
    });
}

if (document.querySelector('#signup-form')) {
    const signupForm = document.querySelector('#signup-form');
    signupForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const username = document.querySelector('#username').value;
        const email = document.querySelector('#email').value;
        const password = document.querySelector('#password').value;
        const confirmPassword = document.querySelector('#pass').value;

        if (!username || !email || !password || !confirmPassword) {
            alert('All fields are required.');
        } else if (password.length < 6) {
            alert('Password must be at least 6 characters long.');
        } else if (password !== confirmPassword) {
            alert('Passwords do not match.');
        } else {
            window.location.href = 'practice.html'; 
        }
    });
}

