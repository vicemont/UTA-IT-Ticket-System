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

//Loads ticket columns for user
function loadTickets(status, label) {
    const list = document.getElementById('ticketList');
    document.getElementById('ticketListHeading').textContent = label + ' Tickets';
    list.textContent = 'Loading...';

    fetch('php/get-tickets.php?status=' + encodeURIComponent(status))
        .then(res => {
            if (!res.ok) throw new Error('HTTP error ' + res.status);
            return res.json();
        })
        .then(tickets => {
            list.textContent = '';
            if (tickets.length === 0) {
                list.textContent = 'No tickets here.';
                return;
            }
            tickets.forEach(t => {
                const card = document.createElement('div');
                card.className = 'ticket-item';

                const title = document.createElement('strong');
                title.textContent = t.title;

                const meta = document.createElement('span');
                meta.className = 'ticket-meta';
                meta.textContent = 'Category: ' + t.category +
                                ' · Priority: ' + t.priority +
                                ' · Status: ' + t.status.replace('_', ' ');

                const desc = document.createElement('p');
                desc.className = 'ticket-description';
                desc.textContent = t.description;

                card.append(title, meta, desc);

                // no cancel button on resolved/closed tickets
                // no cancel button on resolved/closed tickets
                if (status !== 'resolved') {
                    const actions = document.createElement('div');
                    actions.className = 'ticket-actions';

                    const errorBox = document.createElement('p');
                    errorBox.className = 'ticket-error';

                    const cancelBtn = document.createElement('button');
                    cancelBtn.type = 'button';
                    cancelBtn.className = 'cancel-ticket-button';
                    cancelBtn.textContent = 'Cancel Ticket';

                    const confirmRow = document.createElement('div');
                    confirmRow.className = 'confirm-row';
                    confirmRow.hidden = true;

                    const question = document.createElement('span');
                    question.textContent = 'Cancel this ticket?';

                    const yesBtn = document.createElement('button');
                    yesBtn.type = 'button';
                    yesBtn.className = 'cancel-ticket-button';
                    yesBtn.textContent = 'Yes, cancel';

                    const noBtn = document.createElement('button');
                    noBtn.type = 'button';
                    noBtn.className = 'keep-ticket-button';
                    noBtn.textContent = 'Keep ticket';

                    confirmRow.append(question, yesBtn, noBtn);

                    cancelBtn.addEventListener('click', () => {
                        cancelBtn.hidden = true;
                        confirmRow.hidden = false;
                        errorBox.textContent = '';
                    });

                    noBtn.addEventListener('click', () => {
                        confirmRow.hidden = true;
                        cancelBtn.hidden = false;
                    });

                    yesBtn.addEventListener('click', () => {
                        yesBtn.disabled = true;
                        cancelTicket(t.id, status, label, card, errorBox);
                    });

                    actions.append(cancelBtn, confirmRow, errorBox);
                    card.appendChild(actions);
                }
                list.appendChild(card);
            });
        })
        .catch(err => {
            console.error(err);
            list.textContent = 'Could not load tickets.';
        });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.summary-card').forEach(card => {
        const label = card.querySelector('.summary-label').textContent;
        const go = () => loadTickets(card.dataset.status, label);
        card.addEventListener('click', go);
        card.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); go(); }
        });
    });
});

//For ticket cancellation
function cancelTicket(id, status, label, card, errorBox) {
    const body = new FormData();
    body.append('id', id);

    fetch('php/cancel-ticket.php', { method: 'POST', body })
        .then(res => res.json().then(data => ({ res, data })))
        .then(({ res, data }) => {
            if (!res.ok) {
                errorBox.textContent = data.error || 'Could not cancel ticket.';
                return;
            }
            loadTickets(status, label);   // refresh the list
            updateDashboardCounters();    // refresh the numbers
        })
        .catch(() => {
            errorBox.textContent = 'Could not cancel ticket.';
        });
}