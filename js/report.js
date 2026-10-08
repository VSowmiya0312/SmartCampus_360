// ======================================================
// SmartCampus360 - Report Request
// Creates:
// 1. Request Submitted notification
// 2. Track ID notification
// ======================================================

const REQUESTS_KEY = "smartCampus360Requests";
const NOTIFICATIONS_KEY = "smartCampus360Notifications";

// Get requests
function getRequests() {
    return JSON.parse(localStorage.getItem(REQUESTS_KEY)) || [];
}

// Save requests
function saveRequests(requests) {
    localStorage.setItem(REQUESTS_KEY, JSON.stringify(requests));
}

// Get notifications
function getNotifications() {
    return JSON.parse(localStorage.getItem(NOTIFICATIONS_KEY)) || [];
}

// Save notifications
function saveNotifications(notifications) {
    localStorage.setItem(
        NOTIFICATIONS_KEY,
        JSON.stringify(notifications)
    );
}

// Create notification
function createNotification(title, message, requestId) {

    const notifications = getNotifications();

    const notification = {
        id: "NOT-" + Date.now() + "-" + Math.floor(Math.random() * 1000),
        title: title,
        message: message,
        requestId: requestId,
        date: new Date().toLocaleString(),
        read: false
    };

    notifications.push(notification);

    saveNotifications(notifications);
}


// Generate unique request ID
function generateRequestId() {

    const requests = getRequests();

    let number;

    do {
        number = Math.floor(100000 + Math.random() * 900000);
    } while (
        requests.some(request => request.requestId === "SC-" + number)
    );

    return "SC-" + number;
}


// Submit request
function submitRequest(event) {

    event.preventDefault();

    const studentName =
        document.getElementById("studentName")?.value.trim() || "";

    const studentEmail =
        document.getElementById("studentEmail")?.value.trim() || "";

    const category =
        document.getElementById("category")?.value || "";

    const building =
        document.getElementById("building")?.value || "";

    const location =
        document.getElementById("location")?.value.trim() || "";

    const priority =
        document.getElementById("priority")?.value || "Medium";

    const description =
        document.getElementById("description")?.value.trim() || "";


    // Validation
    if (
        !studentName ||
        !studentEmail ||
        !category ||
        !building ||
        !location ||
        !description
    ) {
        alert("Please fill all required fields.");
        return;
    }


    // Generate Request ID
    const requestId = generateRequestId();


    // Create request
    const request = {

        requestId: requestId,

        studentName: studentName,

        studentEmail: studentEmail,

        category: category,

        building: building,

        location: location,

        priority: priority,

        description: description,

        status: "Pending",

        assignedStaff: "",

        createdAt: new Date().toLocaleString(),

        completedAt: ""

    };


    // Save request
    const requests = getRequests();

    requests.push(request);

    saveRequests(requests);


    // ==================================================
    // NOTIFICATION 1
    // Request Submitted
    // ==================================================

    createNotification(
        "📝 Request Submitted",
        "Your request has been submitted successfully.",
        requestId
    );


    // ==================================================
    // NOTIFICATION 2
    // Track ID Created
    // ==================================================

    createNotification(
        "🔎 Track ID Created",
        "Your tracking ID is " + requestId +
        ". Use this ID to track your request.",
        requestId
    );


    // Show success
    alert(
        "Request submitted successfully!\n\n" +
        "Your Request ID is: " + requestId +
        "\n\n" +
        "Please save this ID to track your request."
    );


    // Reset form
    event.target.reset();


    // Optional redirect
    window.location.href =
        "track.html?id=" +
        encodeURIComponent(requestId);
}


// Connect form
document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("reportForm");

    if (form) {

        form.addEventListener(
            "submit",
            submitRequest
        );
    }

});