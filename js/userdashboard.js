function ticketForm() {
    const form = document.getElementById("ticketDropDown");
    
    // Toggles the visibility class on and off 
    form.classList.toggle("show");
}

function updateDashboardCounters() {
    fetch('php/get-ticket-counts.php')
    .then(response => {
        if (!response.ok) {
            throw new Error("HTTP error " + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            console.error("Database error:", data.error);
            return;
        }
        // Updates your HTML elements with live database figures
        document.getElementById('openCount').textContent = data.open;
        document.getElementById('inProgressCount').textContent = data.in_progress;
        document.getElementById('resolvedCount').textContent = data.resolved;
    })
    .catch(error => {
        console.error('Error getting ticket counts:', error);
        // If it fails, fallback to 0 instead of a blank dash
        document.getElementById('openCount').textContent = "0";
    });
}

document.addEventListener('DOMContentLoaded', () => {
    updateDashboardCounters();

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status') === 'success') {
        alert('Ticket submitted successfully!');
        // Clean up the URL so the alert doesn't keep popping up on manual refresh
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});