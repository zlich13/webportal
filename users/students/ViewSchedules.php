<?php
session_start();
error_reporting(0);

//navigation styles
$vsstyle = "active";

//connect to database
include('../../dbconnection.php');


// Redirect to logout page if no user logged in
if (empty($_SESSION['uid'])) {
    header('Location: ../../logout.php');
    exit;
}
?>
 
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ACTS Web Portal | Student Schedules</title>
  <!-- bootstrap css -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
  <!-- page css -->
  <link rel="stylesheet" href="../../css/navigation.css"/>
  <link rel="stylesheet" href="../../css/style.css"/>
  <!--icon-->
  <link rel="shortcut icon" href="../../images/actsicon.png"/>
  <!-- lineawesome icons -->
  <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">

  <!-- Include jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- calendar plugin -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/fullcalendar.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/fullcalendar.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</head>
<body>

  <?php
  //include navigationbar
  include('NavigationBar.php');

  $student = $ret['student_num'];
$sy_id = $sy_ret['sy_id'];
$sem_id = $sy_ret['sem_id'];
  ?>

  <main>
    <div id="title-div">
      <h5 class="title" id="main-title">Schedules:</h5>
    </div>

    <div class="container">
     <div id="calendar"></div>
    </div> 
        <div class="modal" id="viewScheduleModal" tabindex="-1" aria-labelledby="viewScheduleModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">View Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form method="post" id="viewForm">
                  <input type="hidden" id="viewId" name="viewId">
                  <strong><p class="form-label" id="viewRepeat"></p></strong>
                  <div class="flex" style="gap: 50px">
                  <div>
                    <p class="form-label" >Subject: <span id="viewSubject"></span></p>
                    <p class="form-label" >Instructor: <span id="viewInstructor"></span></p>
                    <p class="form-label" >Sections: <span id="viewSections"></span></p>
                    <p class="form-label" >Time: <span id="viewTime"></span></p>
                    <p class="form-label" >Location: <span id="viewLocation"></span></p>
                  </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                  </div>
                </form>
            </div>
          </div>
        </div> 
        </div> 
  </main>
<script type="text/javascript">
$(document).ready(function() {
  var dayOfWeekText;
  var calendar = $('#calendar').fullCalendar({
    header: {
      left: 'prev,next today',
      center: 'title',
      right: 'month,agendaWeek,agendaDay'
    },
    eventRender: function(event, element) {
      element.find('.fc-title').text(event.title);
      element.css('background-color', 'green');
    },
    events: [],
    error: function(xhr, status, error) {
      console.log(error);
    },
    eventClick: function(event) {
      $('#viewTime').text(event.start.format('h:mm A') + " - " + event.end.format('h:mm A'));
      $('#viewId').val(event.id);
      $('#viewRepeat').text(event.repeating);
      $('#viewSubject').text(event.subject);
      $('#viewLocation').text(event.room);
      $('#viewInstructor').text(event.faculty);
      
      var id = event.id;
      $.ajax({
        url: 'get_class.php',
        type: 'POST',
        data: { class_id: id },
        dataType: 'json',
        success: function(response) {
          if (response.status === 'success') {
            $('#viewSections').text(response.data.sections);
          } else {
          // Handle the error or display a message
            console.log('Error:', response.message);
          }
        },
        error: function(xhr, status, error) {
          // Log the specific error message returned by the server
          console.log('AJAX Error: ' + status + ' - ' + error);
          console.log(xhr.responseText);
        }
      });
      // show the modal
      var modalView = $('#viewScheduleModal');
      modalView.modal('show');
    }
  })

  const sy_id = <?php echo $sy_id ?>;
        const sem_id = <?php echo $sem_id ?>;
 const user = <?php echo $student ?>;

    $.ajax({
      url: 'get_schedule.php',
      type: 'POST',
      data: { user: user, sy_id: sy_id, sem_id: sem_id },
      success: function(data) {
      try{
        if (data) {
          var events = JSON.parse(data);
          if (events.length > 0) {
            var calendarEvents = [];
            for (var i = 0; i < events.length; i++) {
              var event = events[i];
              var calendarEvent = {
                id: event.id,
                title: event.title,
                subject: event.subject,
                start: event.start,
                end: event.end,
                repeating: event.repeating,
                room: event.room,
                faculty: event.faculty 
              };
              calendarEvents.push(calendarEvent);
            }
            calendar.fullCalendar('addEventSource', calendarEvents);
          } else {
            console.log('No events found.');
          }
        } else {
          console.log('No data returned.');
        }
      } catch (error){
        console.log('Parsing error');
      }
      },
      error: function(xhr, status, error) {
        console.log(error);
      }
    });
  });
</script>
</body>
</html>
<style type="text/css">
  .container{
    margin-top: 20px;
  }
  .fc-view{
    background: white;
    z-index: 0;
  }
  .fc-scroller{
    height: auto !important;
  }
  .fc-center{
    padding: 10px;
  }
  .flex{
    display: flex;
  }
  #title-div{
    display: flex;
    justify-content: space-between;
  } 
  #buttons{
    text-align: right;
  }
@media screen and (max-width: 860px) {
  .fc-toolbar{
    margin: 0 !important;
  }
  .container{
    padding: 0;
    margin: 0;
  }
}
</style>