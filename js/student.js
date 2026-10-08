const STUDENT_REQUESTS_KEY =
    "smartCampus360Requests";

const STUDENT_NOTIFICATIONS_KEY =
    "smartCampus360Notifications";

const STUDENT_FEEDBACK_KEY =
    "smartCampus360Feedback";


/* =========================================
   GET REQUESTS
========================================= */

function getStudentRequests() {

    return JSON.parse(
        localStorage.getItem(
            STUDENT_REQUESTS_KEY
        )
    ) || [];
}


/* =========================================
   GET NOTIFICATIONS
========================================= */

function getStudentNotifications() {

    return JSON.parse(
        localStorage.getItem(
            STUDENT_NOTIFICATIONS_KEY
        )
    ) || [];
}


/* =========================================
   SAVE NOTIFICATIONS
========================================= */

function saveStudentNotifications(
    notifications
) {

    localStorage.setItem(
        STUDENT_NOTIFICATIONS_KEY,
        JSON.stringify(notifications)
    );
}


/* =========================================
   STUDENT STATISTICS
========================================= */

function getStudentStatistics() {

    const requests =
        getStudentRequests();

    return {

        total:
            requests.length,

        pending:
            requests.filter(
                request =>
                    request.status === "Pending"
            ).length,

        assigned:
            requests.filter(
                request =>
                    request.status === "Assigned"
            ).length,

        inProgress:
            requests.filter(
                request =>
                    request.status === "In Progress"
            ).length,

        completed:
            requests.filter(
                request =>
                    request.status === "Completed"
            ).length

    };
}


/* =========================================
   LOAD STUDENT DASHBOARD
========================================= */

function loadStudentStatistics() {

    const stats =
        getStudentStatistics();


    const total =
        document.getElementById(
            "totalRequests"
        );

    const pending =
        document.getElementById(
            "pendingRequests"
        );

    const assigned =
        document.getElementById(
            "assignedRequests"
        );

    const progress =
        document.getElementById(
            "inProgressRequests"
        );

    const completed =
        document.getElementById(
            "completedRequests"
        );


    if (total) {

        total.textContent =
            stats.total;
    }

    if (pending) {

        pending.textContent =
            stats.pending;
    }

    if (assigned) {

        assigned.textContent =
            stats.assigned;
    }

    if (progress) {

        progress.textContent =
            stats.inProgress;
    }

    if (completed) {

        completed.textContent =
            stats.completed;
    }
}


/* =========================================
   LOAD MY REQUESTS
========================================= */

function loadMyRequests(
    containerId = "requestsContainer"
) {

    const container =
        document.getElementById(
            containerId
        );

    if (!container) {
        return;
    }

    const requests =
        getStudentRequests();


    if (requests.length === 0) {

        container.innerHTML = `

            <div class="empty">

                <div class="empty-icon">
                    📭
                </div>

                <h2>
                    No Requests Found
                </h2>

                <p>
                    You have not submitted any campus issue yet.
                </p>

                <a
                    href="../report.html"
                    class="btn"
                >
                    📝 Report an Issue
                </a>

            </div>

        `;

        return;
    }


    container.innerHTML = "";


    requests
        .slice()
        .reverse()
        .forEach(request => {

            let statusClass =
                "pending";

            if (
                request.status ===
                "Assigned"
            ) {

                statusClass =
                    "assigned";
            }

            if (
                request.status ===
                "In Progress"
            ) {

                statusClass =
                    "progress";
            }

            if (
                request.status ===
                "Completed"
            ) {

                statusClass =
                    "completed";
            }


            const card =
                document.createElement(
                    "div"
                );

            card.className =
                "request-card";


            card.innerHTML = `

                <h2>
                    🎫 ${request.requestId}
                </h2>

                <div class="details">

                    <div class="detail">

                        <strong>
                            Category
                        </strong>

                        ${request.category || "Not Specified"}

                    </div>


                    <div class="detail">

                        <strong>
                            Building
                        </strong>

                        ${request.building || "Not Specified"}

                    </div>


                    <div class="detail">

                        <strong>
                            Location
                        </strong>

                        ${request.location || "Not Specified"}

                    </div>


                    <div class="detail">

                        <strong>
                            Priority
                        </strong>

                        ${request.priority || "Normal"}

                    </div>


                    <div class="detail">

                        <strong>
                            Assigned Staff
                        </strong>

                        ${request.assignedStaff || "Not Assigned"}

                    </div>


                    <div class="detail">

                        <strong>
                            Submitted
                        </strong>

                        ${request.createdAt || "Not Available"}

                    </div>

                </div>


                <p>

                    <strong>
                        Description:
                    </strong>

                    ${request.description || "No description"}

                </p>


                <span
                    class="status ${statusClass}"
                >

                    Status:
                    ${request.status || "Pending"}

                </span>


                <br>


                <a
                    href="../track.html?id=${encodeURIComponent(request.requestId)}"
                    class="btn"
                >
                    🔍 Track Request
                </a>

            `;


            container.appendChild(
                card
            );

        });
}


/* =========================================
   UNREAD NOTIFICATION COUNT
========================================= */

function getUnreadNotificationCount() {

    const notifications =
        getStudentNotifications();

    return notifications.filter(
        notification =>
            !notification.read
    ).length;
}


/* =========================================
   DISPLAY NOTIFICATION COUNT
========================================= */

function loadNotificationCount() {

    const count =
        getUnreadNotificationCount();


    const elements =
        document.querySelectorAll(
            ".notification-count"
        );


    elements.forEach(
        element => {

            element.textContent =
                count;

            if (count === 0) {

                element.style.display =
                    "none";

            } else {

                element.style.display =
                    "inline-block";
            }

        }
    );


    const unreadElement =
        document.getElementById(
            "unreadCount"
        );

    if (unreadElement) {

        unreadElement.textContent =
            count;
    }
}


/* =========================================
   LOAD STUDENT NOTIFICATIONS
========================================= */

function loadStudentNotifications(
    containerId =
        "notificationsContainer"
) {

    const container =
        document.getElementById(
            containerId
        );

    if (!container) {
        return;
    }


    const notifications =
        getStudentNotifications();


    if (notifications.length === 0) {

        container.innerHTML = `

            <div class="empty">

                <div class="empty-icon">
                    🔕
                </div>

                <h2>
                    No Notifications
                </h2>

                <p>
                    You don't have any notifications yet.
                </p>

            </div>

        `;

        loadNotificationCount();

        return;
    }


    container.innerHTML = "";


    notifications
        .slice()
        .reverse()
        .forEach(
            notification => {

                const card =
                    document.createElement(
                        "div"
                    );


                card.className =
                    notification.read
                    ? "notification"
                    : "notification unread";


                card.innerHTML = `

                    <h3>
                        🔔 SmartCampus360 Update
                    </h3>

                    <p>
                        ${notification.message || ""}
                    </p>

                    <small>
                        ${notification.time || ""}
                    </small>

                    <br><br>

                    ${
                        !notification.read
                        ?
                        `
                        <button
                            onclick="
                                markStudentNotificationRead(
                                    '${notification.id}'
                                )
                            "
                        >
                            ✓ Mark as Read
                        </button>
                        `
                        :
                        `
                        <strong>
                            ✓ Read
                        </strong>
                        `
                    }

                    <button
                        onclick="
                            deleteStudentNotification(
                                '${notification.id}'
                            )
                        "
                    >
                        🗑 Delete
                    </button>

                `;


                container.appendChild(
                    card
                );

            }
        );


    loadNotificationCount();
}


/* =========================================
   MARK NOTIFICATION AS READ
========================================= */

function markStudentNotificationRead(
    notificationId
) {

    const notifications =
        getStudentNotifications();


    const notification =
        notifications.find(
            item =>
                String(item.id) ===
                String(notificationId)
        );


    if (notification) {

        notification.read =
            true;
    }


    saveStudentNotifications(
        notifications
    );


    loadStudentNotifications();
}


/* =========================================
   MARK ALL AS READ
========================================= */

function markAllStudentNotificationsRead() {

    const notifications =
        getStudentNotifications();


    notifications.forEach(
        notification => {

            notification.read =
                true;

        }
    );


    saveStudentNotifications(
        notifications
    );


    loadStudentNotifications();
}


/* =========================================
   DELETE NOTIFICATION
========================================= */

function deleteStudentNotification(
    notificationId
) {

    let notifications =
        getStudentNotifications();


    notifications =
        notifications.filter(
            notification =>
                String(notification.id) !==
                String(notificationId)
        );


    saveStudentNotifications(
        notifications
    );


    loadStudentNotifications();
}


/* =========================================
   DELETE ALL NOTIFICATIONS
========================================= */

function deleteAllStudentNotifications() {

    if (
        !confirm(
            "Delete all notifications?"
        )
    ) {

        return;
    }


    localStorage.removeItem(
        STUDENT_NOTIFICATIONS_KEY
    );


    loadStudentNotifications();
}


/* =========================================
   SAVE FEEDBACK
========================================= */

function saveStudentFeedback(
    name,
    email,
    category,
    rating,
    message
) {

    const feedbackList =
        JSON.parse(
            localStorage.getItem(
                STUDENT_FEEDBACK_KEY
            )
        ) || [];


    feedbackList.push({

        id:
            "FB" +
            Date.now(),

        name:
            name,

        email:
            email,

        category:
            category,

        rating:
            rating,

        message:
            message,

        submittedAt:
            new Date().toLocaleString()

    });


    localStorage.setItem(
        STUDENT_FEEDBACK_KEY,
        JSON.stringify(
            feedbackList
        )
    );


    return true;
}


/* =========================================
   TRACK REQUEST
========================================= */

function findStudentRequest(
    requestId
) {

    const requests =
        getStudentRequests();


    return requests.find(
        request =>
            request.requestId ===
            requestId
    );
}


/* =========================================
   INITIAL LOAD
========================================= */

document.addEventListener(
    "DOMContentLoaded",
    function() {

        loadStudentStatistics();

        loadNotificationCount();

        loadMyRequests();

        loadStudentNotifications();

    }
);