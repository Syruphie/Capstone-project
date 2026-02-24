<?php include 'includes/header.php'; ?>

<link rel="stylesheet" href="css/events.css">

<div class="container">
  <h2>Create Event</h2>

  <form id="createEventForm" class="event-form">
    <div class="form-group">
      <label>Event Title</label>
      <input type="text" placeholder="Enter event title" required>
    </div>

    <div class="form-group">
      <label>Date</label>
      <input type="date" required>
    </div>

    <div class="form-group">
      <label>Start Time</label>
      <input type="time" required>
    </div>

    <div class="form-group">
      <label>End Time</label>
      <input type="time" required>
    </div>

    <div class="form-group">
      <label>Description</label>
      <textarea placeholder="Event details..."></textarea>
    </div>

    <button type="submit" class="btn-primary">Create Event</button>
  </form>
</div>

<?php include 'includes/footer.php'; ?>
