const REQUESTS_KEY = "smartCampus360Requests";
const NOTIFICATIONS_KEY = "smartCampus360Notifications";

let currentRequest = null;

const trackForm =
    document.getElementById("trackForm");

const requestInput =
    document.getElementById("requestId");

const requestBox =
    document.getElementById("requestBox");

const searchMessage =
    document.getElementById("searchMessage");

const actionMessage =
    document.getElementById("actionMessage");

const staffSelect =
    document.getElementById("staffSelect");


function getRequests() {

    try {

        const data = JSON.parse(
            localStorage.getItem(REQUESTS_KEY) || "[]"
        );

        return Array.isArray(data) ? data : [];

    } catch {

        return [];

    }
}


function saveRequests(requests) {

    localStorage.setItem(
        REQUESTS_KEY,
        JSON.stringify(requests)
    );

}


function getNotifications() {

    try {

        const data = JSON.parse(
            localStorage.getItem(NOTIFICATIONS_KEY) || "[]"
        );

        return Array.isArray(data) ? data : [];

    } catch {

        return [];

    }
}


function saveNotifications(notifications) {

    localStorage.setItem(
        NOTIFICATIONS_KEY,
        JSON.stringify(notifications)
    );

}


function addNotification(
    request,
    message,
    type
) {

    const notifications =
        getNotifications();

    notifications.unshift({

        notificationId:
            "NT-" +
            Date.now() +
            "-" +
            Math.random()
                .toString(36)
                .substring(2, 8),

        requestId:
            request.requestId,

        studentName:
            request.studentName,

        studentEmail:
            request.studentEmail,

        message:
            message,

        type:
            type,

        createdAt:
            new Date().toISOString(),

        read: false

    });

    saveNotifications(notifications);

}


function showMessage(
    element,
    text,
    type
) {

    element.textContent = text;

    element.className =
        "message " + type;

}


function displayRequest(request) {

    document.getElementById(
        "displayId"
    ).textContent =
        request.requestId;

    document.getElementById(
        "displayStudent"
    ).textContent =
        request.studentName;

    document.getElementById(
        "displayEmail"
    ).textContent =
        request.studentEmail;

    document.getElementById(
        "displayCategory"
    ).textContent =
        request.category;

    document.getElementById(
        "displayBuilding"
    ).textContent =
        request.building;

    document.getElementById(
        "displayLocation"
    ).textContent =
        request.location;

    document.getElementById(
        "displayPriority"
    ).textContent =
        request.priority;

    document.getElementById(
        "displayDescription"
    ).textContent =
        request.description;

    document.getElementById(
        "displayStaff"
    ).textContent =
        request.assignedStaff ||
        "Not Assigned";

    document.getElementById(
        "displayStatus"
    ).textContent =
        request.status;

    staffSelect.value =
        request.assignedStaff || "";

    requestBox.hidden = false;

}


function findRequest(requestId) {

    const requests =
        getRequests();

    return requests.find(
        request =>
            request.requestId.toLowerCase() ===
            requestId.trim().toLowerCase()
    );

}


trackForm.addEventListener(
    "submit",
    function(event) {

        event.preventDefault();

        searchMessage.textContent = "";
        actionMessage.textContent = "";

        const enteredId =
            requestInput.value.trim();

        if (!enteredId) {

            showMessage(
                searchMessage,
                "Please enter your Request ID.",
                "error"
            );

            return;
        }

        const request =
            findRequest(enteredId);

        if (!request) {

            requestBox.hidden = true;

            showMessage(
                searchMessage,
                "Request not found. Please enter the Request ID generated after submitting the request.",
                "error"
            );

            return;
        }

        currentRequest = request;

        displayRequest(request);

        showMessage(
            searchMessage,
            "✅ Request found successfully.",
            "success"
        );

    }
);


document.getElementById(
    "assignButton"
).addEventListener(
    "click",
    function() {

        if (!currentRequest) {

            showMessage(
                actionMessage,
                "Please search for a request first.",
                "error"
            );

            return;
        }

        const staff =
            staffSelect.value;

        if (!staff) {

            showMessage(
                actionMessage,
                "Please select a staff member.",
                "error"
            );

            return;
        }

        if (
            currentRequest.status ===
            "Completed"
        ) {

            showMessage(
                actionMessage,
                "This request is already completed.",
                "error"
            );

            return;
        }

        const requests =
            getRequests();

        const index =
            requests.findIndex(
                request =>
                    request.requestId ===
                    currentRequest.requestId
            );

        if (index === -1) {

            showMessage(
                actionMessage,
                "Request could not be found.",
                "error"
            );

            return;
        }

        const oldStaff =
            requests[index].assignedStaff;

        if (oldStaff === staff) {

            showMessage(
                actionMessage,
                staff +
                " is already assigned to this request.",
                "error"
            );

            return;
        }

        requests[index].assignedStaff =
            staff;

        requests[index].status =
            "Assigned";

        saveRequests(requests);

        currentRequest =
            requests[index];

        addNotification(
            currentRequest,

            "Your request " +
            currentRequest.requestId +
            " has been assigned to staff member " +
            staff +
            ".",

            "staff_assigned"
        );

        displayRequest(
            currentRequest
        );

        showMessage(
            actionMessage,
            "✅ " +
            staff +
            " has been assigned. Notification created successfully.",
            "success"
        );

    }
);


document.getElementById(
    "completeButton"
).addEventListener(
    "click",
    function() {

        if (!currentRequest) {

            showMessage(
                actionMessage,
                "Please search for a request first.",
                "error"
            );

            return;
        }

        if (
            currentRequest.status ===
            "Completed"
        ) {

            showMessage(
                actionMessage,
                "This request is already completed.",
                "error"
            );

            return;
        }

        const requests =
            getRequests();

        const index =
            requests.findIndex(
                request =>
                    request.requestId ===
                    currentRequest.requestId
            );

        if (index === -1) {

            showMessage(
                actionMessage,
                "Request could not be found.",
                "error"
            );

            return;
        }

        requests[index].status =
            "Completed";

        requests[index].completedAt =
            new Date().toISOString();

        saveRequests(requests);

        currentRequest =
            requests[index];

        addNotification(
            currentRequest,

            "Your request " +
            currentRequest.requestId +
            " has been completed successfully.",

            "request_completed"
        );

        displayRequest(
            currentRequest
        );

        showMessage(
            actionMessage,
            "✅ Request completed. Completion notification created successfully.",
            "success"
        );

    }
);


// If track.html?id=SC-XXXXX is opened.
const urlId =
    new URLSearchParams(
        window.location.search
    ).get("id");

if (urlId) {

    requestInput.value =
        urlId;

    trackForm.dispatchEvent(
        new Event("submit")
    );

}