// ======================================================
// SmartCampus360 - ADMIN JS
// Handles:
// - Requests
// - Staff assignment
// - Staff
// - Users
// - Locations
// - Announcements
// - Feedback
// - Reports
// ======================================================


const REQUESTS_KEY = "smartCampus360Requests";
const NOTIFICATIONS_KEY = "smartCampus360Notifications";
const USERS_KEY = "smartCampus360Users";
const STAFF_KEY = "smartCampus360Staff";
const LOCATIONS_KEY = "smartCampus360Locations";
const ANNOUNCEMENTS_KEY = "smartCampus360Announcements";
const FEEDBACK_KEY = "smartCampus360Feedback";


// ======================================================
// COMMON FUNCTIONS
// ======================================================

function getData(key) {

    return JSON.parse(localStorage.getItem(key)) || [];
}


function saveData(key, data) {

    localStorage.setItem(
        key,
        JSON.stringify(data)
    );
}


function escapeHTML(value) {

    if (value === undefined || value === null) {
        return "";
    }

    return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}


// ======================================================
// NOTIFICATIONS
// ======================================================

function createAdminNotification(
    title,
    message,
    requestId
) {

    const notifications =
        getData(NOTIFICATIONS_KEY);

    notifications.push({

        id:
            "NOT-" +
            Date.now() +
            "-" +
            Math.floor(Math.random() * 1000),

        title: title,

        message: message,

        requestId: requestId,

        date: new Date().toLocaleString(),

        read: false

    });

    saveData(
        NOTIFICATIONS_KEY,
        notifications
    );
}


// ======================================================
// REQUEST FUNCTIONS
// ======================================================

function getRequests() {

    return getData(REQUESTS_KEY);
}


function saveRequests(requests) {

    saveData(
        REQUESTS_KEY,
        requests
    );
}


// ======================================================
// ASSIGN STAFF
// ======================================================

function assignStaff(
    requestId,
    staffName
) {

    const requests = getRequests();

    const request =
        requests.find(
            item =>
                item.requestId === requestId
        );


    if (!request) {

        alert("Request not found.");

        return;
    }


    if (!staffName) {

        alert("Please select a staff member.");

        return;
    }


    // Assign staff
    request.assignedStaff = staffName;

    request.status = "Assigned";


    // Save request
    saveRequests(requests);


    // ==================================================
    // STAFF ASSIGNED NOTIFICATION
    // ==================================================

    createAdminNotification(

        "👨‍🔧 Staff Assigned",

        "Staff member " +
        staffName +
        " has been assigned to your request " +
        requestId +
        ".",

        requestId

    );


    alert(
        "Staff assigned successfully!\n\n" +
        "Request ID: " +
        requestId +
        "\n" +
        "Staff: " +
        staffName
    );


    // Refresh page
    location.reload();
}


// ======================================================
// UPDATE REQUEST STATUS
// ======================================================

function updateRequestStatus(
    requestId,
    newStatus
) {

    const requests = getRequests();

    const request =
        requests.find(
            item =>
                item.requestId === requestId
        );


    if (!request) {

        alert("Request not found.");

        return;
    }


    request.status = newStatus;


    if (newStatus === "Completed") {

        request.completedAt =
            new Date().toLocaleString();


        createAdminNotification(

            "✅ Request Completed",

            "Your request " +
            requestId +
            " has been completed successfully.",

            requestId

        );
    }


    saveRequests(requests);

    location.reload();
}


// ======================================================
// DELETE REQUEST
// ======================================================

function deleteRequest(requestId) {

    if (
        !confirm(
            "Are you sure you want to delete this request?"
        )
    ) {
        return;
    }


    let requests = getRequests();

    requests =
        requests.filter(
            request =>
                request.requestId !== requestId
        );


    saveRequests(requests);

    location.reload();
}


// ======================================================
// ADMIN DASHBOARD
// ======================================================

function loadAdminStatistics() {

    const requests = getRequests();


    const total =
        requests.length;


    const pending =
        requests.filter(
            r => r.status === "Pending"
        ).length;


    const assigned =
        requests.filter(
            r => r.status === "Assigned"
        ).length;


    const progress =
        requests.filter(
            r => r.status === "In Progress"
        ).length;


    const completed =
        requests.filter(
            r => r.status === "Completed"
        ).length;


    const totalElement =
        document.getElementById(
            "totalRequests"
        );

    const pendingElement =
        document.getElementById(
            "pendingRequests"
        );

    const assignedElement =
        document.getElementById(
            "assignedRequests"
        );

    const progressElement =
        document.getElementById(
            "progressRequests"
        );

    const completedElement =
        document.getElementById(
            "completedRequests"
        );


    if (totalElement)
        totalElement.textContent = total;

    if (pendingElement)
        pendingElement.textContent = pending;

    if (assignedElement)
        assignedElement.textContent = assigned;

    if (progressElement)
        progressElement.textContent = progress;

    if (completedElement)
        completedElement.textContent = completed;
}


// ======================================================
// ADMIN REQUESTS
// ======================================================

function loadAdminRequests() {

    const container =
        document.getElementById(
            "adminRequestsList"
        );


    if (!container) {
        return;
    }


    const requests = getRequests();

    const staff =
        getData(STAFF_KEY);


    if (requests.length === 0) {

        container.innerHTML =
            `<div class="empty-state">
                <h3>No Requests Found</h3>
                <p>No student requests have been submitted yet.</p>
            </div>`;

        return;
    }


    container.innerHTML =
        requests
            .slice()
            .reverse()
            .map(request => {

                let staffOptions =
                    `<option value="">Select Staff</option>`;


                staff.forEach(person => {

                    const name =
                        person.name ||
                        person.staffName ||
                        person;


                    const selected =
                        request.assignedStaff === name
                            ? "selected"
                            : "";


                    staffOptions +=
                        `<option value="${escapeHTML(name)}" ${selected}>
                            ${escapeHTML(name)}
                        </option>`;
                });


                return `

                <div class="request-card">

                    <div class="request-header">

                        <h3>
                            ${escapeHTML(
                                request.requestId
                            )}
                        </h3>

                        <span class="status ${String(
                            request.status || ""
                        )
                            .toLowerCase()
                            .replace(/\s+/g, "-")}">
                            ${escapeHTML(
                                request.status
                            )}
                        </span>

                    </div>


                    <p>
                        <strong>Student:</strong>
                        ${escapeHTML(
                            request.studentName
                        )}
                    </p>


                    <p>
                        <strong>Email:</strong>
                        ${escapeHTML(
                            request.studentEmail
                        )}
                    </p>


                    <p>
                        <strong>Category:</strong>
                        ${escapeHTML(
                            request.category
                        )}
                    </p>


                    <p>
                        <strong>Building:</strong>
                        ${escapeHTML(
                            request.building
                        )}
                    </p>


                    <p>
                        <strong>Location:</strong>
                        ${escapeHTML(
                            request.location
                        )}
                    </p>


                    <p>
                        <strong>Priority:</strong>
                        ${escapeHTML(
                            request.priority
                        )}
                    </p>


                    <p>
                        <strong>Description:</strong>
                        ${escapeHTML(
                            request.description
                        )}
                    </p>


                    <p>
                        <strong>Assigned Staff:</strong>
                        ${escapeHTML(
                            request.assignedStaff ||
                            "Not Assigned"
                        )}
                    </p>


                    <div class="admin-actions">

                        <select
                            id="staff-${escapeHTML(
                                request.requestId
                            )}"
                        >
                            ${staffOptions}
                        </select>


                        <button
                            class="primary-btn"
                            onclick="assignSelectedStaff('${escapeHTML(
                                request.requestId
                            )}')"
                        >
                            👨‍🔧 Assign Staff
                        </button>


                        <button
                            class="danger-btn"
                            onclick="deleteRequest('${escapeHTML(
                                request.requestId
                            )}')"
                        >
                            🗑 Delete
                        </button>

                    </div>

                </div>

                `;

            })
            .join("");
}


// ======================================================
// ASSIGN SELECTED STAFF
// ======================================================

function assignSelectedStaff(
    requestId
) {

    const select =
        document.getElementById(
            "staff-" + requestId
        );


    if (!select) {

        alert("Staff selection not found.");

        return;
    }


    const staffName =
        select.value;


    assignStaff(
        requestId,
        staffName
    );
}


// ======================================================
// STAFF MANAGEMENT
// ======================================================

function getStaff() {

    return getData(STAFF_KEY);
}


function saveStaff(staff) {

    saveData(
        STAFF_KEY,
        staff
    );
}


function loadStaffList() {

    const container =
        document.getElementById(
            "staffList"
        );


    if (!container) {
        return;
    }


    const staff =
        getStaff();


    if (staff.length === 0) {

        container.innerHTML =
            `<div class="empty-state">
                <p>No staff members found.</p>
            </div>`;

        return;
    }


    container.innerHTML =
        staff
            .map((person, index) => {

                return `

                <div class="list-card">

                    <h3>
                        ${escapeHTML(
                            person.name
                        )}
                    </h3>

                    <p>
                        Email:
                        ${escapeHTML(
                            person.email
                        )}
                    </p>

                    <p>
                        Department:
                        ${escapeHTML(
                            person.department
                        )}
                    </p>

                    <button
                        class="danger-btn"
                        onclick="deleteStaff(${index})"
                    >
                        Delete
                    </button>

                </div>

                `;

            })
            .join("");
}


function addStaff(event) {

    event.preventDefault();


    const name =
        document.getElementById(
            "staffName"
        ).value.trim();


    const email =
        document.getElementById(
            "staffEmail"
        ).value.trim();


    const department =
        document.getElementById(
            "staffDepartment"
        ).value.trim();


    const staff =
        getStaff();


    staff.push({

        name: name,

        email: email,

        department: department

    });


    saveStaff(staff);


    alert(
        "Staff member added successfully!"
    );


    event.target.reset();

    loadStaffList();
}


function deleteStaff(index) {

    const staff =
        getStaff();


    staff.splice(index, 1);


    saveStaff(staff);

    loadStaffList();
}


// ======================================================
// USERS
// ======================================================

function getUsers() {

    return getData(USERS_KEY);
}


function loadUsers() {

    const container =
        document.getElementById(
            "usersList"
        );


    if (!container) {
        return;
    }


    const users =
        getUsers();


    if (users.length === 0) {

        container.innerHTML =
            `<div class="empty-state">
                <p>No registered users found.</p>
            </div>`;

        return;
    }


    container.innerHTML =
        users.map(user => {

            return `

            <div class="list-card">

                <h3>
                    ${escapeHTML(
                        user.name ||
                        user.username ||
                        "User"
                    )}
                </h3>

                <p>
                    Email:
                    ${escapeHTML(
                        user.email || ""
                    )}
                </p>

            </div>

            `;

        }).join("");
}


// ======================================================
// LOCATIONS
// ======================================================

function getLocations() {

    return getData(LOCATIONS_KEY);
}


function saveLocations(locations) {

    saveData(
        LOCATIONS_KEY,
        locations
    );
}


function loadLocations() {

    const container =
        document.getElementById(
            "locationsList"
        );


    if (!container) {
        return;
    }


    const locations =
        getLocations();


    if (locations.length === 0) {

        container.innerHTML =
            `<div class="empty-state">
                <p>No locations found.</p>
            </div>`;

        return;
    }


    container.innerHTML =
        locations.map(
            (location, index) => {

                return `

                <div class="list-card">

                    <h3>
                        ${escapeHTML(
                            location.name
                        )}
                    </h3>

                    <p>
                        ${escapeHTML(
                            location.description
                        )}
                    </p>

                    <button
                        class="danger-btn"
                        onclick="deleteLocation(${index})"
                    >
                        Delete
                    </button>

                </div>

                `;

            }
        ).join("");
}


function addLocation(event) {

    event.preventDefault();


    const name =
        document.getElementById(
            "locationName"
        ).value.trim();


    const description =
        document.getElementById(
            "locationDescription"
        ).value.trim();


    const locations =
        getLocations();


    locations.push({

        name: name,

        description: description

    });


    saveLocations(locations);


    alert(
        "Location added successfully!"
    );


    event.target.reset();

    loadLocations();
}


function deleteLocation(index) {

    const locations =
        getLocations();


    locations.splice(index, 1);


    saveLocations(locations);

    loadLocations();
}


// ======================================================
// ANNOUNCEMENTS
// ======================================================

function getAnnouncements() {

    return getData(
        ANNOUNCEMENTS_KEY
    );
}


function saveAnnouncements(
    announcements
) {

    saveData(
        ANNOUNCEMENTS_KEY,
        announcements
    );
}


function loadAnnouncements() {

    const container =
        document.getElementById(
            "announcementsList"
        );


    if (!container) {
        return;
    }


    const announcements =
        getAnnouncements();


    if (announcements.length === 0) {

        container.innerHTML =
            `<div class="empty-state">
                <p>No announcements found.</p>
            </div>`;

        return;
    }


    container.innerHTML =
        announcements
            .slice()
            .reverse()
            .map(
                announcement => {

                    return `

                    <div class="list-card">

                        <h3>
                            ${escapeHTML(
                                announcement.title
                            )}
                        </h3>

                        <p>
                            ${escapeHTML(
                                announcement.message
                            )}
                        </p>

                        <small>
                            ${escapeHTML(
                                announcement.date
                            )}
                        </small>

                    </div>

                    `;

                }
            ).join("");
}


function addAnnouncement(event) {

    event.preventDefault();


    const title =
        document.getElementById(
            "announcementTitle"
        ).value.trim();


    const message =
        document.getElementById(
            "announcementMessage"
        ).value.trim();


    const announcements =
        getAnnouncements();


    announcements.push({

        title: title,

        message: message,

        date: new Date().toLocaleString()

    });


    saveAnnouncements(
        announcements
    );


    alert(
        "Announcement published successfully!"
    );


    event.target.reset();

    loadAnnouncements();
}


// ======================================================
// FEEDBACK
// ======================================================

function getFeedback() {

    return getData(
        FEEDBACK_KEY
    );
}


function loadFeedback() {

    const container =
        document.getElementById(
            "feedbackList"
        );


    if (!container) {
        return;
    }


    const feedback =
        getFeedback();


    if (feedback.length === 0) {

        container.innerHTML =
            `<div class="empty-state">
                <p>No feedback found.</p>
            </div>`;

        return;
    }


    container.innerHTML =
        feedback
            .slice()
            .reverse()
            .map(item => {

                return `

                <div class="list-card">

                    <h3>
                        ${escapeHTML(
                            item.name ||
                            "Student"
                        )}
                    </h3>

                    <p>
                        <strong>Category:</strong>
                        ${escapeHTML(
                            item.category
                        )}
                    </p>

                    <p>
                        <strong>Rating:</strong>
                        ${escapeHTML(
                            item.rating
                        )}
                    </p>

                    <p>
                        ${escapeHTML(
                            item.message
                        )}
                    </p>

                    <small>
                        ${escapeHTML(
                            item.submittedAt ||
                            ""
                        )}
                    </small>

                </div>

                `;

            }).join("");
}


// ======================================================
// REPORTS
// ======================================================

function loadReports() {

    const requests =
        getRequests();


    const total =
        requests.length;


    const pending =
        requests.filter(
            r => r.status === "Pending"
        ).length;


    const assigned =
        requests.filter(
            r => r.status === "Assigned"
        ).length;


    const progress =
        requests.filter(
            r => r.status === "In Progress"
        ).length;


    const completed =
        requests.filter(
            r => r.status === "Completed"
        ).length;


    const totalElement =
        document.getElementById(
            "reportTotal"
        );


    const pendingElement =
        document.getElementById(
            "reportPending"
        );


    const assignedElement =
        document.getElementById(
            "reportAssigned"
        );


    const progressElement =
        document.getElementById(
            "reportProgress"
        );


    const completedElement =
        document.getElementById(
            "reportCompleted"
        );


    if (totalElement)
        totalElement.textContent = total;


    if (pendingElement)
        pendingElement.textContent = pending;


    if (assignedElement)
        assignedElement.textContent = assigned;


    if (progressElement)
        progressElement.textContent = progress;


    if (completedElement)
        completedElement.textContent = completed;


    loadCategoryReport();
}


function loadCategoryReport() {

    const container =
        document.getElementById(
            "categoryReport"
        );


    if (!container) {
        return;
    }


    const requests =
        getRequests();


    const categories = {};


    requests.forEach(request => {

        const category =
            request.category ||
            "Other";


        categories[category] =
            (categories[category] || 0) + 1;

    });


    if (
        Object.keys(categories).length === 0
    ) {

        container.innerHTML =
            `<div class="empty-state">
                <p>No request data available.</p>
            </div>`;

        return;
    }


    container.innerHTML =
        Object.entries(categories)
            .map(
                ([category, count]) => {

                    return `

                    <div class="list-card">

                        <h3>
                            ${escapeHTML(
                                category
                            )}
                        </h3>

                        <p>
                            Total Requests:
                            <strong>
                                ${count}
                            </strong>
                        </p>

                    </div>

                    `;

                }
            ).join("");
}


// ======================================================
// PAGE LOAD
// ======================================================

document.addEventListener(
    "DOMContentLoaded",
    function () {

        loadAdminStatistics();

        loadAdminRequests();

        loadStaffList();

        loadUsers();

        loadLocations();

        loadAnnouncements();

        loadFeedback();

        loadReports();


        const staffForm =
            document.getElementById(
                "staffForm"
            );

        if (staffForm) {

            staffForm.addEventListener(
                "submit",
                addStaff
            );
        }


        const locationForm =
            document.getElementById(
                "locationForm"
            );

        if (locationForm) {

            locationForm.addEventListener(
                "submit",
                addLocation
            );
        }


        const announcementForm =
            document.getElementById(
                "announcementForm"
            );

        if (announcementForm) {

            announcementForm.addEventListener(
                "submit",
                addAnnouncement
            );
        }

    }
);