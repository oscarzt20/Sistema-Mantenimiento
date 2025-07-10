document.addEventListener('DOMContentLoaded', () => {
    // Get references to HTML elements for file upload
    const fileUploadInput = document.getElementById('file-upload');
    const selectFileButton = document.getElementById('select-file-button');
    const fileUploadText = document.getElementById('file-upload-text');

    // --- File Upload Functionality ---
    // Event listener for the "Seleccionar archivo" button to trigger the hidden file input
    selectFileButton.addEventListener('click', () => {
        fileUploadInput.click(); // Programmatically click the hidden file input
    });

    // Event listener for when a file is selected
    fileUploadInput.addEventListener('change', () => {
        if (fileUploadInput.files.length > 0) {
            // Update the text to show the selected file's name
            fileUploadText.textContent = `Archivo seleccionado: ${fileUploadInput.files[0].name}`;
        } else {
            // Reset the text if no file is selected (e.g., user cancels the dialog)
            fileUploadText.textContent = 'Arrastre y suelte una imagen aquí o';
        }
    });

    // Optional: Drag and drop functionality (basic indication)
    const dropArea = fileUploadInput.closest('div'); // Get the parent div for drag-and-drop

    // Add visual feedback when a file is dragged over the drop area
    dropArea.addEventListener('dragover', (event) => {
        event.preventDefault(); // Prevent default drag behavior
        dropArea.classList.add('border-blue-500', 'bg-blue-50'); // Add styling for visual feedback
    });

    // Remove visual feedback when a dragged file leaves the drop area
    dropArea.addEventListener('dragleave', () => {
        dropArea.classList.remove('border-blue-500', 'bg-blue-50'); // Remove styling
    });

    // Handle the file drop event
    dropArea.addEventListener('drop', (event) => {
        event.preventDefault(); // Prevent default drop behavior (e.g., opening file in browser)
        dropArea.classList.remove('border-blue-500', 'bg-blue-50'); // Remove styling

        const files = event.dataTransfer.files; // Get the files that were dropped
        if (files.length > 0) {
            fileUploadInput.files = files; // Assign dropped files to the hidden file input
            fileUploadText.textContent = `Archivo seleccionado: ${files[0].name}`; // Update text
        }
    });

    // --- Notification Dropdown Functionality (from original HTML) ---
    // This function will eventually fetch real notifications
    function fetchNotifications() {
        // For now, it's a placeholder. In a real application, you'd make an AJAX call here.
        const notifications = [
            // Example data, replace with fetched data from your database
            // { id: 1, message: "Mantenimiento programado para Laptop HP el 2023-10-05", read: false },
            // { id: 2, message: "Reparación de pantalla realizada en Laptop HP el 2023-10-02", read: true }
        ];

        const notificationDropdown = document.getElementById('dropdown');
        const notificationBadge = document.getElementById('notification-badge');
        const noNotifications = document.getElementById('noNotifications');

        // Clear existing notifications before adding new ones
        notificationDropdown.innerHTML = '';

        if (notifications.length === 0) {
            // If no notifications, display the "No hay notificaciones" message
            notificationDropdown.appendChild(noNotifications);
            noNotifications.style.display = 'block';
            notificationBadge.textContent = '0'; // Set badge to 0
        } else {
            noNotifications.style.display = 'none'; // Hide the "No notifications" message
            let unreadCount = 0;
            notifications.forEach(notification => {
                const notificationItem = document.createElement('div');
                notificationItem.classList.add('notification-item');
                if (!notification.read) {
                    notificationItem.classList.add('unread');
                    unreadCount++; // Increment unread count
                }
                notificationItem.textContent = notification.message;
                notificationDropdown.appendChild(notificationItem);
            });
            notificationBadge.textContent = unreadCount; // Update the notification badge
        }
    }

    // Function to toggle the visibility of the notification dropdown
    function toggleDropdown() {
        document.getElementById("dropdown").classList.toggle("show");
    }

    // Attach the toggleDropdown function to the global window object so it can be called from HTML
    window.toggleDropdown = toggleDropdown;

    // Close the dropdown if the user clicks outside of it
    window.onclick = function (event) {
        if (!event.target.matches('.notification-btn')) {
            var dropdowns = document.getElementsByClassName("notification-dropdown");
            for (var i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (openDropdown.classList.contains('show')) {
                    openDropdown.classList.remove('show');
                }
            }
        }
    }

    // Fetch notifications when the DOM content is fully loaded
    fetchNotifications();

    // JavaScript for delete confirmation modal
    const deleteTeamButtonModal = document.getElementById('delete-team-button-modal');
    const deleteConfirmationModal = document.getElementById('deleteConfirmationModal');
    const cancelDeleteButton = document.getElementById('cancelDeleteButton');

    if (deleteTeamButtonModal) { // Ensure the button exists before adding listener
        deleteTeamButtonModal.addEventListener('click', function () {
            deleteConfirmationModal.classList.remove('hidden');
            deleteConfirmationModal.classList.add('flex'); // Make it a flex container to center
        });
    }

    if (cancelDeleteButton) { // Ensure the button exists before adding listener
        cancelDeleteButton.addEventListener('click', function () {
            deleteConfirmationModal.classList.add('hidden');
            deleteConfirmationModal.classList.remove('flex');
        });
    }
});
