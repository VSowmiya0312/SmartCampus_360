// ================= REGISTER =================

const registerForm = document.getElementById("registerForm");

if (registerForm) {

    registerForm.addEventListener("submit", function (event) {

        event.preventDefault();

        const name =
            document.getElementById("registerName").value.trim();

        const email =
            document.getElementById("registerEmail").value.trim();

        const mobile =
            document.getElementById("registerMobile").value.trim();

        const role =
            document.getElementById("registerRole").value;

        const password =
            document.getElementById("registerPassword").value;

        const confirmPassword =
            document.getElementById("confirmPassword").value;

        const message =
            document.getElementById("registerMessage");


        if (password !== confirmPassword) {

            message.textContent =
                "Passwords do not match.";

            message.style.color = "red";

            return;
        }


        if (!/^[0-9]{10}$/.test(mobile)) {

            message.textContent =
                "Enter a valid 10-digit mobile number.";

            message.style.color = "red";

            return;
        }


        const users =
            JSON.parse(localStorage.getItem("smartCampusUsers")) || [];


        const existingUser =
            users.find(user => user.email === email);


        if (existingUser) {

            message.textContent =
                "An account with this email already exists.";

            message.style.color = "red";

            return;
        }


        const newUser = {

            id: Date.now(),

            name: name,

            email: email,

            mobile: mobile,

            role: role,

            password: password

        };


        users.push(newUser);


        localStorage.setItem(
            "smartCampusUsers",
            JSON.stringify(users)
        );


        message.textContent =
            "Account created successfully! Redirecting to login...";

        message.style.color = "green";


        registerForm.reset();


        setTimeout(function () {

            window.location.href = "login.html";

        }, 1500);

    });

}


// ================= LOGIN =================

const loginForm = document.getElementById("loginForm");

if (loginForm) {

    loginForm.addEventListener("submit", function (event) {

        event.preventDefault();


        const email =
            document.getElementById("loginEmail").value.trim();

        const password =
            document.getElementById("loginPassword").value;

        const role =
            document.getElementById("loginRole").value;

        const message =
            document.getElementById("loginMessage");


        // Demo administrator login

        if (
            email === "admin@smartcampus.com" &&
            password === "admin123" &&
            role === "admin"
        ) {

            localStorage.setItem(
                "smartCampusCurrentUser",
                JSON.stringify({
                    name: "System Administrator",
                    email: email,
                    role: "admin"
                })
            );


            message.textContent =
                "Admin login successful.";

            message.style.color = "green";


            setTimeout(function () {

                window.location.href =
                    "admin/dashboard.html";

            }, 800);


            return;
        }


        const users =
            JSON.parse(
                localStorage.getItem("smartCampusUsers")
            ) || [];


        const user = users.find(function (item) {

            return (
                item.email === email &&
                item.password === password &&
                item.role === role
            );

        });


        if (!user) {

            message.textContent =
                "Invalid email, password or user type.";

            message.style.color = "red";

            return;
        }


        localStorage.setItem(
            "smartCampusCurrentUser",
            JSON.stringify(user)
        );


        message.textContent =
            "Login successful.";

        message.style.color = "green";


        setTimeout(function () {

            if (
                role === "student" ||
                role === "faculty"
            ) {

                window.location.href =
                    "student/dashboard.html";

            } else if (role === "maintenance") {

                window.location.href =
                    "staff/dashboard.html";

            }

        }, 800);

    });

}