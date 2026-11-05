<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'user') {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UTA IT Support Portal</title>
      <link rel="stylesheet" href="css/userdashboard.css">
    <link rel="stylesheet" href="css/theme.css">
  <script src="js/theme.js" defer></script>
    <script src="js/userdashboard.js" defer></script>
    <script src="js/userdashboard-menu.js" defer></script>
</head>
<body>
    <header class="topbar">
        <a class="brand" href="userdashboard.php" aria-label="UTA IT Support Portal Home">
            <img src="images/UTA_logomark.png" width="24" height="24" alt="">
            <span>UTA</span>
            <span><strong>IT Support Portal </strong>Student & Faculty Services</span>
        </a>
        <!--hamburger menu-->
        <div class="top-actions">
            <button id="hamburger" class="three-lines" type="button" aria-expanded="false" aria-controls="dashboardMenu" aria-label="Open menu">
                <img src="images/hamburger-menu-icon.png" width="24" height="24" alt="Menu">
            </button>
            <button class="theme-toggle" type="button" aria-pressed="false">Switch to dark mode</button>
        </div>
        <!--dropdown menu-->
        <div id="dashboardMenu" class="dropdown-menu">
            <!--notifcation button-->
            <button id="notificationsButton" class ="bell-button" type ="button" aria-expanded="false" aria-label="View notifications">
                <span class="bell-icon">🔔</span>
                <span id="notifcationsBadge" class="count-badge">0</span>
            </button>  
            <!--logout button-->
            <button id="logoutButton" class ="logout-button" type="button">Logout</button> 
        </div>
    </header>
    
    <main class="page">
        <section class ="welcome-row">
            <div>
                <p class="eyebrow"><strong>User Dashboard</strong></p>
                <h1 id="WelcomeHeading" style="font-family:Geneva, Verdana, sans-serif;">Welcome to IT Support</h1>
            </div>
            <!--Trigger for Dropdown form-->
            <div class ="dropdown">
                <button class ="ticket-trigger" onclick= "ticketForm()">
                    Create Ticket <span class="arrow" id ="dropDownArrow">▼</span>
                </button>
                <!--Dropdown form-->
                <div id="ticketDropDown" class="ticket-form">
                    <h3>Submit a Support Ticket</h3>
                    <form id="supportTicketForm" action="php/submit-ticket.php" method="POST">

                        <!--hidden line used for counting tickets-->
                        <input type ="hidden" name ="status" value="open">
                        <!--title group-->
                        <div class="form-group">
                            <label for="ticketTitle">Title</label>
                            <input type="text" id="ticketTitle" name="title" required>
                        </div>
                        
                        <!--category group-->
                        <div class="category-group">
                            <label for="ticketCategory">Category</label>
                            <select id="ticketCategory" name="category" required>
                                <option value="">Select a category</option>
                                <option value="hardware">Hardware</option>
                                <option value="software">Software</option>
                                <option value="network">Network</option>
                                <option value="account">Account</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <!--description group-->
                        <div class="form-group">
                            <label for="ticketDescription">Description</label>
                            <textarea id="ticketDescription" name="description" rows="4" placeholder="Describe your issue in detail"required style =
                            "resize: vertical; max-width: 100%; width: 100%; box-sizing: border-box;"></textarea>
                            <!--these properties keep the description box from being stretched past the borders-->
                        </div>

                        <!--priority group-->
                        <div class="form-group">
                            <label for="ticketPriority">Priority</label>
                            <select id="ticketPriority" name="priority" required>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>

                        <!--duedate group--->
                        <div class="form-group">
                            <label for="ticketDueDate">Due Date</label>
                            <input type="date" id="ticketDueDate" name="dueDate" required>
                        </div>

                        <!--submit/cancel button-->
                        <div class="form-actions">
                            <button type="submit" class="submit-button">Submit Ticket</button>
                            <button type="button" class="cancel-button" onclick="ticketForm()">Cancel</button>  
                        </div>
                    </form>
                </div>  
            </div>
        </section>
        
        <!-- Ticket Summary Section --> 
        <section class="summary-grid" aria-label="Ticket Summary">
            <article class="summary-card" data-status="open" tabindex="0">
                <span class ="summary-label">Open</span>
                <strong id="openCount" class="summary-number">0</strong>
                <span class="summary-note">Waiting for IT</span>
            </article>
            <article class="summary-card" data-status="in_progress" tabindex="0">
                <span class ="summary-label">In Progress</span>
                <strong id="inProgressCount" class="summary-number">0</strong>
                <span class="summary-note">Being worked on</span>
            </article>
            <article class="summary-card" data-status="resolved" tabindex="0">
                <span class ="summary-label">Resolved</span>
                <strong id="resolvedCount" class="summary-number">0</strong>
                <span class="summary-note">Resolved or Cancelled</span>
            </article>
        </section>
        <section class="ticket-list-section" aria-labelledby="myTicketsHeading">
            <h2 id="myTicketsHeading">My tickets</h2>
            <p id="ticketListMessage" aria-live="polite">Loading your tickets...</p>
            <ul id="myTicketsList"></ul>
        </section>

        <!--Ticket List Section -->
        <section class="ticket-list-section">
            <h2 id="ticketListHeading">Select a status to view your tickets</h2>
            <div class="ticket-background" id="ticketList"></div>
        </section>

        <div class="theme-control">
            <button type="button" id="themeToggle" class="theme-switch" role="switch" aria-checked="false" aria-label="Toggle dark mode">
                <span class="theme-icon sun">☀️</span>
                <span class="theme-icon moon">🌙</span>
                <span class="theme-thumb"></span>
            </button>
            <p class="toggle">Toggle for Light/Dark Mode</p>
        <script src="js/usertheme.js"></script>
    </main>
</body>