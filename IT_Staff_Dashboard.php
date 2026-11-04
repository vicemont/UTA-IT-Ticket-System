<?php

$tickets = [
    [
        "issue" => "Ticket 1",
        "createdBy" => "Name",
        "priority" => "low"
    ],
    [
        "issue" => "Ticket 2",
        "createdBy" => "Name",
        "priority" => "medium"
    ],
     [
        "issue" => "Ticket 3",
        "createdBy" => "Name",
        "priority" => "high"
    ]
];

?>
<DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>IT Ticket System</title>

    <link rel="stylesheet" href="ITstaffstyles.css">
    <script src="staff.js"></script>
</head>

<body>


<header>
    <h1>UTA IT Support</h1>
</header>
<!-- Tab links -->
<div class="tab">
  <button class="tablinks" onclick="openCity(event, 'Open')">Open Tickets</button>
  <button class="tablinks" onclick="openCity(event, 'Closed')">Closed Tickets</button>
  <button class="tablinks" onclick="openCity(event, 'Search')">Search</button>
</div>

<!-- Tab content -->
<div id="Open" class="tabcontent">
  <h3>Open Tickets</h3>
  <div class="cards">

    <h2>Open Tickets</h2>

        <?php foreach ($tickets as $ticket): ?>

        <div class="card">

            <h3>
                <?php echo $ticket["issue"]; ?>
            </h3>

            <p>
                Created By:
                <?php echo $ticket["createdBy"]; ?>
            </p>

           

            <p>
                <?php echo $ticket["priority"]; ?>
            </p>

           

        </div>

        <?php endforeach; ?>

    </div>

</div>

<div id="Closed" class="tabcontent">
  <h3>Closed Tickets</h3>
  
</div>

<div id="Search" class="tabcontent">
  <h3>Search</h3>
 
</div>



</body>
</html>