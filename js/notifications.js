const NOTIFICATIONS_KEY =
    "smartCampus360Notifications";

const list =
    document.getElementById(
        "notificationList"
    );

const count =
    document.getElementById(
        "count"
    );


function getNotifications() {

    try {

        const data =
            JSON.parse(
                localStorage.getItem(
                    NOTIFICATIONS_KEY
                ) || "[]"
            );

        return Array.isArray(data)
            ? data
            : [];

    } catch {

        return [];

    }

}


function saveNotifications(
    notifications
) {

    localStorage.setItem(
        NOTIFICATIONS_KEY,
        JSON.stringify(
            notifications
        )
    );

}


function formatDate(date) {

    const d =
        new Date(date);

    return d.toLocaleString();

}


function displayNotifications() {

    const notifications =
        getNotifications();

    const unread =
        notifications.filter(
            item => !item.read
        ).length;

    count.textContent =
        "Total Notifications: " +
        notifications.length +
        " | Unread: " +
        unread;

    list.innerHTML = "";

    if (
        notifications.length === 0
    ) {

        list.innerHTML = `
            <div class="empty">
                🔔 No notifications yet.
                <br><br>
                Notifications will appear when staff are assigned or a request is completed.
            </div>
        `;

        return;
    }


    notifications.forEach(
        notification => {

            const card =
                document.createElement(
                    "div"
                );

            card.className =
                notification.read
                    ? "notification"
                    : "notification unread";


            const heading =
                notification.type ===
                "staff_assigned"

                    ? "👨‍🔧 Staff Assigned"

                    : notification.type ===
                      "request_completed"

                        ? "✅ Request Completed"

                        : "📢 Request Update";


            card.innerHTML = `

                <h3>
                    ${heading}
                </h3>

                <p>
                    ${notification.message}
                </p>

                <p>
                    <strong>
                        Request ID:
                    </strong>

                    ${notification.requestId}
                </p>

                <p class="date">
                    ${formatDate(
                        notification.createdAt
                    )}
                </p>

                ${
                    notification.read

                    ? `<button disabled>
                         ✓ Read
                       </button>`

                    : `<button
                         onclick="markRead('${notification.notificationId}')">
                         Mark as Read
                       </button>`
                }

                <button
                    class="delete"
                    onclick="deleteNotification('${notification.notificationId}')">

                    Delete

                </button>

            `;

            list.appendChild(card);

        }
    );

}


function markRead(id) {

    const notifications =
        getNotifications();

    const notification =
        notifications.find(
            item =>
                item.notificationId === id
        );

    if (notification) {

        notification.read = true;

        saveNotifications(
            notifications
        );

        displayNotifications();

    }

}


function deleteNotification(id) {

    const notifications =
        getNotifications();

    const updated =
        notifications.filter(
            item =>
                item.notificationId !== id
        );

    saveNotifications(
        updated
    );

    displayNotifications();

}


document.getElementById(
    "readAll"
).addEventListener(
    "click",
    function() {

        const notifications =
            getNotifications();

        notifications.forEach(
            notification => {
                notification.read = true;
            }
        );

        saveNotifications(
            notifications
        );

        displayNotifications();

    }
);


document.getElementById(
    "refresh"
).addEventListener(
    "click",
    function() {

        displayNotifications();

    }
);


displayNotifications();