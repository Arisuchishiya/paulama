// Notification handling
document.addEventListener('DOMContentLoaded', function() {
    // Handle notification fade-out
    const notifications = document.querySelectorAll('.feedback');
    notifications.forEach(notification => {
        setTimeout(() => {
            notification.classList.add('fade-out');
            // Remove the element after fade-out animation completes
            setTimeout(() => {
                notification.remove();
            }, 500);
        }, 2000);
    });
});

// Function to show a new notification
function showNotification(message, type = 'success') {
    const container = document.querySelector('.container');
    const notification = document.createElement('div');
    notification.className = `feedback ${type}`;
    notification.textContent = message;
    
    // Insert at the beginning of the container
    container.insertBefore(notification, container.firstChild);
    
    // Fade out after 2 seconds
    setTimeout(() => {
        notification.classList.add('fade-out');
        // Remove the element after fade-out animation completes
        setTimeout(() => {
            notification.remove();
        }, 500);
    }, 2000);
} 