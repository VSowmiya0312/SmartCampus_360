/* =====================================================
   SMARTCAMPUS360 - MAIN.JS
   Request + Track + Staff + Notification System
   ===================================================== */

const REQUEST_KEY = "smartCampusRequests";
const NOTIFICATION_KEY = "smartCampusNotifications";
const LAST_ID_KEY = "smartCampusLastID";


/* =====================================================
   REQUEST STORAGE
   ===================================================== */

function getRequests() {

    const data = localStorage.getItem(REQUEST_KEY);

    if (!data) {
        return [];
    }

    try {

        const requests = JSON.parse(data);

        if (Array.isArray(requests)) {
            return requests;
        }

        return [];

    } catch (error) {

        console.error("Error reading requests:", error);

        return [];

    }
}


/* =====================================================
   SAVE REQUESTS
   ===================================================== */

function saveRequests(requests) {

    localStorage.setItem(
        REQUEST_KEY,
        JSON.stringify(requests)
    );

}


/* =====================================================
   NOTIFICATIONS
   ===================================================== */

function getNotifications() {

    const data =
        localStorage.getItem(NOTIFICATION_KEY);

    if (!data) {
        return [];
    }

    try {

        const notifications = JSON.parse(data);

        if (Array.isArray(notifications)) {
            return notifications;
        }

        return [];

    } catch (error) {

        console.error(
            "Error reading notifications:",
            error
        );

        return [];

    }

}


/* =====================================================
   SAVE NOTIFICATIONS
   ===================================================== */

function saveNotifications(notifications) {

    localStorage.setItem(
        NOTIFICATION_KEY,
        JSON.stringify(notifications)
    );

}


/* =====================================================
   CREATE NOTIFICATION
   ===================================================== */

function createNotification(message, type = "info") {

    const notifications =
        getNotifications();

    const notification = {

        id:
            Date.now() +
            Math.floor(Math.random() * 1000),

        message: message,

        type: type,

        date:
            new Date().toLocaleString(),

        read: false

    };

    notifications.unshift(notification);

    saveNotifications(notifications);

}


/* =====================================================
   GENERATE UNIQUE TRACK ID
   ===================================================== */

function generateTrackID() {

    let lastID =
        parseInt(
            localStorage.getItem(LAST_ID_KEY),
            10
        );


    if (
        isNaN(lastID) ||
        lastID < 1000
    ) {

        lastID = 1000;

    }


    let newID;

    let exists = true;


    while (exists) {

        lastID++;

        newID = "SCR" + lastID;

        exists =
            getRequests().some(
                function(request) {

                    return String(request.id)
                        .toUpperCase() ===
                        newID.toUpperCase();

                }
            );

    }


    localStorage.setItem(
        LAST_ID_KEY,
        lastID
    );


    return newID;

}


/* =====================================================
   SUBMIT NEW REQUEST
   ===================================================== */

function submitRequest(data) {

    const requests =
        getRequests();


    const trackID =
        generateTrackID();


    const request = {

        id: trackID,

        studentName:
            String(data.studentName || "")
            .trim(),

        studentEmail:
            String(data.studentEmail || "")
            .trim(),

        category:
            String(data.category || "")
            .trim(),

        location:
            String(data.location || "")
            .trim(),

        description:
            String(data.description || "")
            .trim(),

        status:
            "Submitted",

        assignedStaff:
            "Not Assigned",

        createdAt:
            new Date().toLocaleString(),

        updatedAt:
            new Date().toLocaleString()

    };


    requests.push(request);

    saveRequests(requests);


    /* -------------------------------------------------
       NOTIFICATION 1
       Request submitted
       ------------------------------------------------- */

    createNotification(

        "Your request has been submitted successfully. Track ID: " +
        trackID,

        "success"

    );


    /* -------------------------------------------------
       NOTIFICATION 2
       Track ID generated
       ------------------------------------------------- */

    createNotification(

        "Track ID " +
        trackID +
        " has been created. You can use this ID to track your request.",

        "track"

    );


    return request;

}


/* =====================================================
   FIND REQUEST BY TRACK ID
   ===================================================== */

function getRequestByID(trackID) {

    if (
        trackID === null ||
        trackID === undefined
    ) {

        return null;

    }


    const searchID =
        String(trackID)
        .trim()
        .toUpperCase();


    if (searchID === "") {

        return null;

    }


    const requests =
        getRequests();


    const request =
        requests.find(
            function(item) {

                return String(item.id)
                    .trim()
                    .toUpperCase() ===
                    searchID;

            }
        );


    return request || null;

}


/* =====================================================
   ASSIGN STAFF
   ===================================================== */

function assignStaff(trackID, staff) {

    const requests =
        getRequests();


    const searchID =
        String(trackID)
        .trim()
        .toUpperCase();


    const request =
        requests.find(
            function(item) {

                return String(item.id)
                    .trim()
                    .toUpperCase() ===
                    searchID;

            }
        );


    if (!request) {

        return false;

    }


    request.assignedStaff =
        staff;

    request.status =
        "Staff Assigned";

    request.updatedAt =
        new Date().toLocaleString();


    saveRequests(requests);


    /* -------------------------------------------------
       STAFF ASSIGNMENT NOTIFICATION
       ------------------------------------------------- */

    createNotification(

        "Staff " +
        staff +
        " has been assigned to request " +
        request.id +
        ".",

        "assignment"

    );


    return true;

}


/* =====================================================
   UPDATE REQUEST STATUS
   ===================================================== */

function updateRequestStatus(trackID, status) {

    const requests =
        getRequests();


    const searchID =
        String(trackID)
        .trim()
        .toUpperCase();


    const request =
        requests.find(
            function(item) {

                return String(item.id)
                    .trim()
                    .toUpperCase() ===
                    searchID;

            }
        );


    if (!request) {

        return false;

    }


    request.status =
        status;

    request.updatedAt =
        new Date().toLocaleString();


    saveRequests(requests);


    /* -------------------------------------------------
       IN PROGRESS NOTIFICATION
       ------------------------------------------------- */

    if (status === "In Progress") {

        createNotification(

            "Request " +
            request.id +
            " is now In Progress.",

            "progress"

        );

    }


    /* -------------------------------------------------
       COMPLETED NOTIFICATION
       ------------------------------------------------- */

    if (status === "Completed") {

        createNotification(

            "Request " +
            request.id +
            " has been completed successfully.",

            "success"

        );

    }


    return true;

}


/* =====================================================
   MARK NOTIFICATION AS READ
   ===================================================== */

function markNotificationRead(id) {

    const notifications =
        getNotifications();


    notifications.forEach(
        function(notification) {

            if (
                String(notification.id) ===
                String(id)
            ) {

                notification.read = true;

            }

        }
    );


    saveNotifications(
        notifications
    );

}


/* =====================================================
   DELETE NOTIFICATION
   ===================================================== */

function deleteNotification(id) {

    let notifications =
        getNotifications();


    notifications =
        notifications.filter(
            function(notification) {

                return String(notification.id) !==
                    String(id);

            }
        );


    saveNotifications(
        notifications
    );

}


/* =====================================================
   CLEAR ALL NOTIFICATIONS
   ===================================================== */

function clearNotifications() {

    localStorage.removeItem(
        NOTIFICATION_KEY
    );

}


/* =====================================================
   DEBUG / TEST FUNCTION
   ===================================================== */

function showAllRequests() {

    console.log(
        "SmartCampus360 Requests:",
        getRequests()
    );

}