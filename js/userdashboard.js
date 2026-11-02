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
            throw new Error(data.error);
        }
        // Updates your HTML elements with live database figures
        document.getElementById('openCount').textContent = data.counts.open;
        document.getElementById('inProgressCount').textContent = data.counts.in_progress;
        document.getElementById('resolvedCount').textContent = data.counts.resolved;

        const ticketList = document.getElementById('myTicketsList');
        const ticketListMessage = document.getElementById('ticketListMessage');
        ticketList.replaceChildren();

        if (data.tickets.length === 0) {
            ticketListMessage.textContent = 'You have not submitted any tickets yet.';
            return;
        }

        ticketListMessage.textContent = '';
        data.tickets.forEach(ticket => {
            const item = document.createElement('li');
            const title = document.createElement('strong');
            const details = document.createElement('span');

            title.textContent = ticket.title;
            details.textContent = ` - ${ticket.status} | ${ticket.created_at}`;
            item.append(title, details);
            ticketList.append(item);
        });
    })
    .catch(error => {
        console.error('Error getting ticket counts:', error);
        document.getElementById('openCount').textContent = "N/A";
        document.getElementById('inProgressCount').textContent = "N/A";
        document.getElementById('resolvedCount').textContent = "N/A";
        document.getElementById('ticketListMessage').textContent = 'Unable to load your tickets.';
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