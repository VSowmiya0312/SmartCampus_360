// ======================================================
// SmartCampus360 - STAFF JS
// Handles staff requests and completion notifications
// ======================================================

const STAFF_REQUESTS_KEY =
    "smartCampus360Requests";

const STAFF_NOTIFICATIONS_KEY =
    "smartCampus360Notifications";


// ======================================================
// GET REQUESTS
// ======================================================

function getStaffRequests() {

    return JSON.parse(
        localStorage.getItem(
            STAFF_REQUESTS_KEY
        )
    ) || [];
}


// ======================================================
// SAVE REQUESTS
// ======================================================

function saveStaffRequests(requests) {

    localStorage.setItem(
        STAFF_REQUESTS_KEY,
        JSON.stringify(requests)
    );
}


// ======================================================
// GET NOTIFICATIONS
// ======================================================

function getStaffNotifications() {

    return JSON.parse(
        localStorage.getItem(
            STAFF_NOTIFICATIONS_KEY
        )
    ) || [];
}


// ======================================================
// SAVE NOTIFICATIONS
// ======================================================

function saveStaffNotifications(
    notifications
) {

    localStorage.setItem(
        STAFF_NOTIFICATIONS_KEY,
        JSON.stringify(
            notifications
        )
    );
}


// ======================================================
// CREATE NOTIFICATION
// ======================================================

function addStaffNotification(
    title,
    message,
    requestId
) {

    const notifications =
        getStaffNotifications();


    notifications.push({

        id:
            "NOT-" +
            Date.now() +
            "-" +
            Math.floor(
                Math.random() * 1000
            ),

        title: title,

        message: message,

        requestId: requestId,

        date:
            new Date().toLocaleString(),

        read: false

    });


    saveStaffNotifications(
        notifications
    );
}


// ======================================================
// GET ASSIGNED REQUESTS
// ======================================================

function getAssignedRequests() {

    const requests =
        getStaffRequests();


    return requests.filter(
        request =>
            request.assignedStaff &&
            request.assignedStaff.trim() !== ""
    );
}


// ======================================================
// STAFF DASHBOARD STATISTICS
// ======================================================

function loadStaffStatistics() {

    const requests =
        getAssignedRequests();


    const total =
        requests.length;


    const pending =
        requests.filter(
            r =>
                r.status === "Assigned" ||
                r.status === "Pending"
        ).length;


    const progress =
        requests.filter(
            r =>
                r.status === "In Progress"
        ).length;


    const completed =
        requests.filter(
            r =>
                r.status === "Completed"
        ).length;


    const totalElement =
        document.getElementById(
            "totalAssigned"
        );


    const pendingElement =
        document.getElementById(
            "pendingAssigned"
        );


    const progressElement =
        document.getElementById(
            "progressAssigned"
        );


    const completedElement =
        document.getElementById(
            "completedAssigned"
        );


    if (totalElement)
        totalElement.textContent = total;


    if (pendingElement)
        pendingElement.textContent = pending;


    if (progressElement)
        progressElement.textContent = progress;


    if (completedElement)
        completedElement.textContent = completed;
}


// ======================================================
// LOAD ASSIGNED REQUESTS
// ======================================================

function loadAssignedRequests() {

    const container =
        document.getElementById(
            "assignedRequestsList"
        );


    if (!container) {
        return;
    }


    const requests =
        getAssignedRequests();


    if (requests.length === 0) {

        container.innerHTML =
            `<div class="empty-state">
                <h3>No Assigned Requests</h3>
                <p>No requests have been assigned yet.</p>
            </div>`;

        return;
    }


    container.innerHTML =
        requests
            .slice()
            .reverse()
            .map(request => {

                return `

                <div class="request-card">

                    <div class="request-header">

                        <h3>
                            ${escapeStaffHTML(
                                request.requestId
                            )}
                        </h3>

                        <span class="status">
                            ${escapeStaffHTML(
                                request.status
                            )}
                        </span>

                    </div>


                    <p>
                        <strong>Student:</strong>
                        ${escapeStaffHTML(
                            request.studentName
                        )}
                    </p>


                    <p>
                        <strong>Category:</strong>
                        ${escapeStaffHTML(
                            request.category
                        )}
                    </p>


                    <p>
                        <strong>Building:</strong>
                        ${escapeStaffHTML(
                            request.building
                        )}
                    </p>


                    <p>
                        <strong>Location:</strong>
                        ${escapeStaffHTML(
                            request.location
                        )}
                    </p>


                    <p>
                        <strong>Priority:</strong>
                        ${escapeStaffHTML(
                            request.priority
                        )}
                    </p>


                    <p>
                        <strong>Description:</strong>
                        ${escapeStaffHTML(
                            request.description
                        )}
                    </p>


                    <p>
                        <strong>Assigned Staff:</strong>
                        ${escapeStaffHTML(
                            request.assignedStaff
                        )}
                    </p>


                    <a
                        class="primary-btn"
                        href="update-request.html?id=${encodeURIComponent(
                            request.requestId
                        )}"
                    >
                        Update Request
                    </a>

                </div>

                `;

            })
            .join("");
}


// ======================================================
// FIND REQUEST
// ======================================================

function findStaffRequest(
    requestId
) {

    const requests =
        getStaffRequests();


    return requests.find(
        request =>
            request.requestId === requestId
    );
}


// ======================================================
// UPDATE STATUS
// ======================================================

function updateStaffRequestStatus(
    requestId,
    newStatus,
    staffNote = ""
) {

    const requests =
        getStaffRequests();


    const request =
        requests.find(
            item =>
                item.requestId === requestId
        );


    if (!request) {

        alert("Request not found.");

        return false;
    }


    request.status =
        newStatus;


    request.staffNote =
        staffNote;


    // ==================================================
    // COMPLETED
    // ==================================================

    if (
        newStatus === "Completed"
    ) {

        request.completedAt =
            new Date().toLocaleString();


        // ONE completion notification
        addStaffNotification(

            "✅ Request Completed",

            "Your request " +
            requestId +
            " has been completed successfully.",

            requestId

        );

    }


    saveStaffRequests(
        requests
    );


    return true;
}


// ======================================================
// LOAD UPDATE REQUEST PAGE
// ======================================================

function loadStaffUpdateRequest() {

    const form =
        document.getElementById(
            "staffUpdateForm"
        );


    if (!form) {
        return;
    }


    const params =
        new URLSearchParams(
            window.location.search
        );


    const requestId =
        params.get("id");


    if (!requestId) {

        alert(
            "Request ID is missing."
        );

        return;
    }


    const request =
        findStaffRequest(
            requestId
        );


    if (!request) {

        alert(
            "Request not found."
        );

        return;
    }


    const idField =
        document.getElementById(
            "updateRequestId"
        );


    const statusField =
        document.getElementById(
            "updateStatus"
        );


    const noteField =
        document.getElementById(
            "staffNote"
        );


    if (idField)
        idField.value =
            request.requestId;


    if (statusField)
        statusField.value =
            request.status;


    if (noteField)
        noteField.value =
            request.staffNote || "";


    form.addEventListener(
        "submit",
        function (event) {

            submitStaffUpdate(
                event,
                requestId
            );

        }
    );
}


// ======================================================
// SUBMIT STAFF UPDATE
// ======================================================

function submitStaffUpdate(
    event,
    requestId
) {

    event.preventDefault();


    const statusField =
        document.getElementById(
            "updateStatus"
        );


    const noteField =
        document.getElementById(
            "staffNote"
        );


    const newStatus =
        statusField.value;


    const staffNote =
        noteField.value.trim();


    const success =
        updateStaffRequestStatus(
            requestId,
            newStatus,
            staffNote
        );


    if (!success) {
        return;
    }


    alert(
        "Request updated successfully!"
    );


    window.location.href =
        "assigned-requests.html";
}


// ======================================================
// WORK HISTORY
// ======================================================

function loadStaffWorkHistory() {

    const container =
        document.getElementById(
            "workHistoryList"
        );


    if (!container) {
        return;
    }


    const requests =
        getStaffRequests()
            .filter(
                request =>
                    request.status ===
                    "Completed"
            );


    if (requests.length === 0) {

        container.innerHTML =
            `<div class="empty-state">
                <p>No completed requests yet.</p>
            </div>`;

        return;
    }


    container.innerHTML =
        requests
            .slice()
            .reverse()
            .map(request => {

                return `

                <div class="request-card">

                    <h3>
                        ${escapeStaffHTML(
                            request.requestId
                        )}
                    </h3>

                    <p>
                        <strong>Student:</strong>
                        ${escapeStaffHTML(
                            request.studentName
                        )}
                    </p>

                    <p>
                        <strong>Category:</strong>
                        ${escapeStaffHTML(
                            request.category
                        )}
                    </p>

                    <p>
                        <strong>Staff:</strong>
                        ${escapeStaffHTML(
                            request.assignedStaff
                        )}
                    </p>

                    <p>
                        <strong>Completed:</strong>
                        ${escapeStaffHTML(
                            request.completedAt
                        )}
                    </p>

                    <p>
                        <strong>Staff Note:</strong>
                        ${escapeStaffHTML(
                            request.staffNote ||
                            "No note"
                        )}
                    </p>

                </div>

                `;

            })
            .join("");
}


// ======================================================
// ESCAPE HTML
// ======================================================

function escapeStaffHTML(
    value
) {

    if (
        value === undefined ||
        value === null
    ) {
        return "";
    }


    return String(value)
        .replace(
            /&/g,
            "&amp;"
        )
        .replace(
            /</g,
            "&lt;"
        )
        .replace(
            />/g,
            "&gt;"
        )
        .replace(
            /"/g,
            "&quot;"
        )
        .replace(
            /'/g,
            "&#039;"
        );
}


// ======================================================
// PAGE LOAD
// ======================================================

document.addEventListener(
    "DOMContentLoaded",
    function () {

        loadStaffStatistics();

        loadAssignedRequests();

        loadStaffUpdateRequest();

        loadStaffWorkHistory();

    }
);