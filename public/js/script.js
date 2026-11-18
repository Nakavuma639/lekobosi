//Mobile menu
const toggle = document.querySelector(".menu-toggle");
const nav = document.querySelector(".header nav");
if (toggle && nav){
    toggle.addEventListener("click", function(){
        nav.classList.toggle("open");
    })
}
// Registration form validarion
const registerForm = document.querySelector(".registerForm");

if (registerForm) {
    registerForm.addEventListener("submit", function (event) {
        const password = registerForm.querySelector('[name="password"]').value;
        const confirmPassword = registerForm.querySelector('[name="confirm_password"]').value;

        if (password.length < 8) {
            event.preventDefault();
            alert("Password must contain at least 8 characters.");
        } else if (password !== confirmPassword) {
            event.preventDefault();
            alert("Passwords do not match.");
        }
    });
}
//booking form validation
const bookingForm = document.querySelector(".bookingForm");

if (bookingForm) {
    bookingForm.addEventListener("submit", function (event) {
        const eventDate = bookingForm.querySelector('[name="date"]').value;
        const guests = bookingForm.querySelector('[name="guests"]').value;

        if (!eventDate || new Date(eventDate) < new Date().setHours(0, 0, 0, 0)) {
            event.preventDefault();
            alert("Please select a valid future event date.");
        } else if (Number(guests) < 1) {
            event.preventDefault();
            alert("Please enter at least one guest.");
        }
    });
}
//contact form validation
const contactForm = document.querySelector(".contactForm");

if (contactForm) {
    contactForm.addEventListener("submit", function (event) {
        const email = contactForm.querySelector('[name="email"]').value;
        const message = contactForm.querySelector('[name="message"]').value;

        if (!email.includes("@") || !email.includes(".")) {
            event.preventDefault();
            alert("Please enter a valid email address.");
        } else if (message.trim() === "") {
            event.preventDefault();
            alert("Please enter your message.");
        }
    });
}
//login validation
const loginForm = document.querySelector(".login-form");

if (loginForm) {
    loginForm.addEventListener("submit", function (event) {
        const email = loginForm.querySelector('[name="email"]').value;
        const password = loginForm.querySelector('[name="password"]').value;

        if (email.trim() === "" || password.trim() === "") {
            event.preventDefault();
            alert("Please enter your email and password.");
        }
    });
}