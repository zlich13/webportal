<?php
session_start();
error_reporting(0);

//navigation styles
$msstyle = "active";

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
	<title>ACTS Web Portal | Manage Schedules</title>
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

$sy_id = $sy_ret['sy_id'];
$sem_id = $sy_ret['sem_id'];
  if (isset($_POST['saveSched'])) {
    $sched_date = isset($_POST['sched_date']) ? $_POST['sched_date'] : null;
    $sched_sub = $_POST['sched_subject'];
    $sched_sec = $_POST['sched_sections'];
    $sched_fac = $_POST['sched_faculty'];
    $sched_tfrom = date('H:i:s', strtotime($_POST['time_from']));
    $sched_tto = date('H:i:s', strtotime($_POST['time_to']));
    $sched_room = $_POST['sched_room'];
    $sched_weeks = isset($_POST['sched_weeks']) ? $_POST['sched_weeks'] : null;
    $sched_dfrom = isset($_POST['date_from']) ? $_POST['date_from'] : null;
    $sched_dto = isset($_POST['date_to']) ? $_POST['date_to'] : null;

    
    mysqli_begin_transaction($con); 

    if (isset($_POST['set_weekly'])) {
        $rdata = array(
          'dow' => implode(',', $sched_weeks),
          'start' => $sched_dfrom,
          'end' => $sched_dto
        );
        $data = json_encode($rdata);
        $sql = "INSERT INTO class_schedule (subject, faculty_id, time_from, time_to, room_id, is_repeating, repeating_data, sem_id, sy_id) VALUES (?, ?, ?, ?, ?, 1, ?, ?, ?)";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("sissisii", $sched_sub, $sched_fac, $sched_tfrom, $sched_tto, $sched_room, $data, $sem_id, $sy_id);
    } else {
      $sql = "INSERT INTO class_schedule (subject, faculty_id, time_from, time_to, room_id, is_repeating, sched_date, sem_id, sy_id) VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?)";
      $stmt = $con->prepare($sql);
      $stmt->bind_param("sissisii", $sched_sub, $sched_fac, $sched_tfrom, $sched_tto, $sched_room, $sched_date, $sem_id, $sy_id);
    }

    if ($stmt->execute()) {
        $schedule_id = $stmt->insert_id;

        $section_sql = "INSERT INTO class_schedule_sections (class_id, section_id) VALUES (?, ?)";
        $section_stmt = $con->prepare($section_sql);

      echo "<script>console.log('prepared');</script>";
        $allSectionsInserted = true; // Flag to track if all sections were inserted successfully

        foreach ($sched_sec as $section) {
            $section_stmt->bind_param("ss", $schedule_id, $section);
            if (!$section_stmt->execute()) {
                $allSectionsInserted = false;
                break; // Exit the loop if a section insertion fails
            }
        }

        if ($allSectionsInserted) {
            mysqli_commit($con); // Commit the transaction since all section insertions were successful
            echo "<script type='text/javascript'>document.location ='Schedules.php?faculty=$sched_fac';</script>";
        } else {
            mysqli_rollback($con); // Rollback the transaction
            echo "<script>alert('Error inserting sections! Try again later.');</script>";
        }
    } else {
        mysqli_rollback($con);
        echo "<script>alert('Error inserting the schedule! Try again later.');</script>";
    }
    // Close the statement and result set
    mysqli_stmt_close($stmt);
    mysqli_free_result($result);
}
  ?>

	<main>
    <div id="title-div">
      <h5 class="title" id="main-title">Schedules:</h5>
      <div class="flex" style="gap: 20px">
        <div>
          <button id="newScheduleButton" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addScheduleModal">+New Schedule</button>  
        </div>
      </div>
    </div>
    <br>
    <div>
          <select class="form-select" id="filter_sched" name = "filter_sched" required>
             <option value="" hidden>Select Faculty</option> <?php
              // Fetch faculty options from the database
              $sql = "SELECT id, prefix, fname, mname, lname FROM faculty";
              $result = $con->query($sql);
              // Generate <option> elements
              if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $fac_id = $row['id'];
                    $fac_name = $row['prefix']." ".$row['fname']." ".substr($row['mname'], 0, 1)." ".$row['lname'];
                    echo "<option value='$fac_id'>$fac_name</option>";
                }
              } ?>
          </select>  
        </div>
        <br>
    <div class="container">
     <div id="calendar"></div>
    </div>	
    <div class="modal" id="addScheduleModal" tabindex="-1" aria-labelledby="addScheduleModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <form method="post" id="schedForm">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Add Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div style="display: flex; width: 100%; gap: 20px;">
                  <div style="width: 50%">
                    <div class="mb-3" id="sched_dateDiv">
                      <label for="sched_date" class="form-label">Schedule Date</label>
                      <input type="date" id="sched_date" name="sched_date" class="form-control">
                    </div> 
                    <div class="mb-3">
                        <label for="sched_faculty" class="form-label">Faculty</label>
                        <select class="form-select" id="sched_faculty" name = "sched_faculty" required>
                          <option value="" hidden>Select Faculty</option> <?php
                          // Fetch faculty options from the database
                          $sql = "SELECT id, prefix, fname, mname, lname FROM faculty";
                          $result = $con->query($sql);
                          // Generate <option> elements
                          if ($result->num_rows > 0) {
                              while ($row = $result->fetch_assoc()) {
                                  $fac_id = $row['id'];
                                  $fac_name = $row['prefix']." ".$row['fname']." ".substr($row['mname'], 0, 1)." ".$row['lname'];
                                  echo "<option value='$fac_id'>$fac_name</option>";
                              }
                          } ?>
                        </select>  
                        <span id="availability" style="font-size: 12px; color: darkgreen;"></span>
                    </div>
                    <div class="mb-3">
                      <label for="time_from" class="form-label" >Time From</label>
                      <input type="time" class="form-control" id="time_from" name="time_from" required>
                    </div>
                     <div class="mb-3">
                      <label for="time_to" class="form-label">Time To</label>
                      <input type="time" class="form-control" id="time_to" name="time_to" required>
                    </div>
                    <div class="mb-3">
                      <label for="sched_subject" class="form-label">Subject</label>
                      <select class="form-select" id="sched_subject" name="sched_subject" required>
                      <option value="" hidden>Select Subject</option> 
                        <?php
                        $sql = "SELECT DISTINCT subject_description, has_lab FROM subjects ORDER BY subject_description ASC";
                        $result = $con->query($sql);
                        // Generate <a> elements
                        if ($result->num_rows > 0) {
                          while ($row = $result->fetch_assoc()) {
                            $subject = $row['subject_description'];
                            $lab = $row['has_lab'];
                            echo "<option value='$subject' data-lab='$lab'>$subject</option>";
                          }
                        } ?>
                      </select>
                    </div>   
                  </div>
                  <div style="width: 50%">
                    <div class="mb-3">
                      <label for="sched_sections" class="form-label">Sections</label>
                      <select class="form-select" id="sched_sections" name="sched_sections[]" multiple="multiple" required>
                      <option hidden>Select Sections</option>
                      </select>
                      <span style="font-size: 12px; color: darkgreen;" id="student_total"></span>
                    </div>
                    <div class="mb-3">
                      <label for="loc" class="form-label">Recommended Location</label>
                      <select class="form-select" id="loc" name="loc" required> 
                      </select>
                    </div>
                    <div class="mb-3">
                      <label for="sched_room" class="form-label">Available Rooms</label>
                      <select class="form-select" id="sched_room" name="sched_room" required>
                      </select>
                    </div>
                    <div class="mb-3 hide_orient">
                      <input id = "set_weekly" type="checkbox" name="set_weekly" value="1">
                      <label for="set_weekly" class="form-label">Weekly Schedule</label>
                    </div>
                  </div>
                </div>
                <div id="weekly-div" style="display: none;">
                  <hr>
                  <div style="display: flex; width: 100%; gap: 20px;">
                    <div style="width: 50%">
                      <div class="mb-3">
                          <label for="sched_weeks" class="form-label">Days of Week</label>
                          <select class="form-select" id="sched_weeks" name="sched_weeks[]" class="custom-select select2" multiple="multiple">
                            <option hidden></option>
                            <option hidden value="0">Sunday</option>
                            <option value="1">Monday</option>
                            <option value="2">Tuesday</option>
                            <option value="3">Wednesday</option>
                            <option value="4">Thursday</option>
                            <option value="5">Friday</option>
                            <option value="6">Saturday</option>
                          </select>
                        </div>
                    </div>
                    <div style="width: 50%">
                      <div class="mb-3">
                          <label for="date_from" class="form-label">Date From</label>
                          <input type="date" class="form-control" id="date_from" name="date_from">
                        </div>
                        <div class="mb-3">
                          <label for="date_to" class="form-label">Date To</label>
                          <input type="date" class="form-control" id="date_to" name="date_to">
                        </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success" name="saveSched" id="saveSched">Save</button>
                <button type="submit" class="btn btn-success" name="updateSched" id="updateSched" style="display: none;">Save Changes</button>
              </div>
            </div>
          </form>
          </div>
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
                    <p class="form-label">Subject:</p>
                    <p class="form-label">Sections:</p>
                    <p class="form-label">Time:</p>
                    <p class="form-label">Location:</p>
                  </div>
                  <div>
                    <p class="form-label" id="viewSubject"></p>
                    <p class="form-label" id="viewSections"></p>
                    <p class="form-label" id="viewTime"></p>
                    <p class="form-label" id="viewLocation"></p>
                  </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-primary" name="editSched" id="editSched" data-bs-toggle="modal" data-bs-target="#addScheduleModal">Edit</button>
                    <button type="button" class="btn btn-danger" name="deleteSched" id="deleteSched">Delete</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  </div>
                </form>
            </div>
          </div>
        </div>  
	</main>
<script type="text/javascript">
$(document).ready(function() {
  var startFormatted;

      var facultyId = null;
      var timeFrom = null;
      var timeTo = null;
      var sched_subject = null;
      var hasLab = null;
      var sections = null;
      var totalCount = 0;   
      

  const modalEl = $('#addScheduleModal');
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
    selectable: true,
    selectHelper: true,
    select: function(start) {
      startFormatted = $.fullCalendar.formatDate(start, "Y-MM-DD");
      dayOfWeekText = moment(startFormatted).format('dddd');

      modalEl.modal('show');

      $('#sched_date, #date_from').val(startFormatted);
    },
    eventClick: function(event) {
      $('#viewTime').text(event.start.format('h:mm A') + " - " + event.end.format('h:mm A'));
      $('#viewId').val(event.id);
      $('#viewRepeat').text(event.repeating)
      const id = event.id;
      $.ajax({
        url: 'get_class.php',
        type: 'POST',
        data: { class_id: id },
        dataType: 'json',
        success: function(response) {
          if (response.status === 'success') {
            $('#viewSubject').text(event.title+" - "+response.data.subject);
            $('#viewLocation').text(response.data.room);
            $('#viewSections').text(response.data.sections);

            var class_info = {
              date: response.data.sched_date,
              faculty: response.data.faculty_id,
              time_from: event.start.format('h:mm A'),
              time_to: event.end.format('h:mm A'),
              subject: response.data.subject,
              sections: response.data.sections,
              location: response.data.location,
              room: response.data.room_id,
              repeating: response.data.is_repeating,
              repeating_data: response.data.repeating_data
            }
            $('#editSched').data('class', class_info);
          } else {
            alert('Error:', response.message);
          }
        },
        error: function(xhr, status, error) {
          // Log the specific error message returned by the server
          console.log('AJAX Error: ' + status + ' - ' + error);
          console.log(xhr.responseText);
        }
      });
      var modalView = $('#viewScheduleModal');
      modalView.modal('show');

      

      $('#editSched').click(function() {
        const update_info = $(this).data('class');
        $('#updateSched').data('id',id);
        $('#updateSched').show();
        $('#saveSched').hide();
        $('#sched_faculty').val(update_info.faculty);
        $('#sched_faculty').change();
        const from = moment(update_info.time_from, "h:mm A").format("HH:mm");
        const to = moment(update_info.time_to, "h:mm A").format("HH:mm");
        $('#time_from').val(from);
        $('#time_to').val(to);
        $('#sched_subject').val(update_info.subject);
        $('#sched_subject').change();
        if (update_info.repeating) {
          $('#sched_dateDiv').hide();
          $('#weekly-div').show();
          $('#set_weekly').prop('checked', true);
          const repeating_data = JSON.parse(update_info.repeating_data);
          $('#date_from').val(repeating_data.start);
          $('#date_to').val(repeating_data.end);
          const weeks = repeating_data.dow;
          $('#sched_weeks option').each(function() {
            var weeks_options = $(this).val();
            if (weeks.includes(weeks_options)) {
              $(this).prop('selected', true);
            }
          });
        } else {
          $('#sched_date').val(update_info.date);
        } 
      });
    }
  });


  // Add an event listener for changes in the calendar input
$('#sched_date').on('change', function() {
  startFormatted = $(this).val();
  // You can also update other related values if needed
  dayOfWeekText = moment(startFormatted).format('dddd');
});
  

  // Click event handler for "+New Schedule" button
$('#newScheduleButton').on('click', function() {
  // Add an event listener for changes in the calendar input
$('#sched_date').on('change', function() {
  startFormatted = $(this).val();
  // You can also update other related values if needed
  dayOfWeekText = moment(startFormatted).format('dddd');
});
  
  // Show the modal
  modalEl.modal('show');
});

    $('#sched_faculty').change(function() {
        $('#time_from, #time_to').val('');
        var checkDate = $('#sched_date').val();
        timeFrom = null;
        timeTo = null;
        facultyId = $(this).val();
        // get availability of faculty
        $.ajax({
          url: 'get_availability.php',
          type: 'POST',
          data: { facultyId: facultyId, schedDate: checkDate },
          dataType: 'json',
          success: function(response) {
            if (response.status === 'success') {
              var data = response.data;
              if (data.morningAvailability && data.afternoonAvailability) {
                var morningAvailability = data.morningAvailability;
                var afternoonAvailability = data.afternoonAvailability;
                var formattedAfternoonAvailability = formatTo12Hour(data.afternoonAvailability);
                $('#availability').html('Faculty Available Time:<br>AM: ' + morningAvailability + '<br>PM: ' + formattedAfternoonAvailability );
              } else if (data.availability) {
                var availability = data.availability;
                $('#availability').text(availability);
              } else {
                $('#availability').text('No availability data found');
              }
            } else {
                alert(response.message);
              }
            },
          error: function(xhr, status, error) {
            console.error(xhr);
            console.error(status);
            console.error(error);
          }
        });
        getLocationAndRoom();
    }); 

    function formatTo12Hour(timeArray) {
        var formattedAfternoonAvailability = [];
        for (var i = 0; i < timeArray.length; i++) {
          var timeRange = timeArray[i];
          var times = timeRange.match(/\d{1,2}:\d{2}/g);
          if (times && times.length === 2) {
            var formattedTimeRange = '(' + moment(times[0], 'HH:mm').format('h:mm') + ' - ' + moment(times[1], 'HH:mm').format('h:mm') + ')';
            formattedAfternoonAvailability.push(formattedTimeRange);
          }
        }
        return formattedAfternoonAvailability;
    }

    // setting variable of timeFrom and timeTo
    $('#time_from').on('input', function(event) {
        timeFrom = $('#time_from').val();
        $('#time_to').attr('min', timeFrom);
        getLocationAndRoom();
    });

    $('#time_to').on('input', function(event) {
        timeTo = $('#time_to').val();
        getLocationAndRoom();
    });

    $('#sched_sections').change(function() {
        sections = $(this).val();
        console.log(sections)
        totalCount = 0;
        // get total number of students
        if (sections && sections.length > 0) {
          for (var i = 0; i < sections.length; i++) {
            var optionValue = sections[i];
            var studentCount = extractStudentCount(optionValue);
            if (!isNaN(studentCount)) {
              totalCount += studentCount;
            }
          }
        } else {
          sections = null;
        }
        $('#student_total').text('Student Total Count: ' + totalCount);
        getLocationAndRoom();
    });

    // Function to extract student count from data-count attribute
    function extractStudentCount(optionValue) {
        var option = $('#sched_sections option[value="' + optionValue + '"]');
        var studentCount = option.attr('data-count');
        if (studentCount) {
          return parseInt(studentCount);
        }
        return NaN; // Return NaN if data-count attribute is not found or not a valid integer
    }

    // function to get the recommended Location
    function getLocationAndRoom(){
      console.log("date"+startFormatted);
        if (facultyId && timeFrom && timeTo &&
          sched_subject && hasLab != null && sections &&
          startFormatted && totalCount && totalCount!== 0) {
            // All values are not null
            requestLocationAndRoom();
        }
    }

    function requestLocationAndRoom(){
        $.ajax({
          url: 'recommended_location.php',
          type: 'POST',
          data: {
            timeFrom: timeFrom,
            timeTo: timeTo,
            facultyId: facultyId,
            schedDate: startFormatted
          },
          dataType: 'json',
          success: function(response) {
            if (response.status === 'success') {
              var recommendedLocation = response.data.recommended;
              var locations = response.data.locations;
              $('#loc').empty();
              // Create and append the options
              for (var i = 0; i < locations.length; i++) {
                var location = locations[i];
                var option = $('<option>').text(location).val(location);
                if (location === recommendedLocation) {
                  option.prop('selected', true);
                  $('#loc').val(recommendedLocation);
                }
                $('#loc').append(option);
              }
              // Set selected index to 0 if recommendedLocation is null
              if (recommendedLocation === null) {
                $('#loc').prop('selectedIndex', 0);
                $('#loc').val(null); // Set selected value to null
              }
              // ignore recommended based on faculty location when subject needs laboratory
              if (hasLab == 1) {
                console.log("Ignore recommended and set to main");
                $('#loc').prop('selected', 'Main');
                $('#loc').val('Main');
              }
              // Trigger the change event
              $('#loc').trigger('change');
            } else {
              alert(response.message);
            }
          },
          error: function(xhr, status, error) {
            console.error(xhr);
            console.error(status);
            console.error(error);
          }
        });
    }

    $('#loc').change(function() {
        const loc = $(this).val(); 
        if (loc != null) {
          $.ajax({
            url: 'get_available_rooms.php',
            type: 'POST',
            data: { loc: loc, schedDate: startFormatted, timeFrom: timeFrom, timeTo: timeTo, totalCount: totalCount, hasLab:hasLab},
            dataType: 'json',
            success: function(response) {
              // Clear the select options
              $('#sched_room').empty();
              // Process the data returned from the PHP script
              for (var i = 0; i < response.length; i++) {
                var roomId = response[i].room_id;
                var roomName = response[i].room_name;
                var roomCapacity = response[i].room_capacity;
                // Create an option element with the desired format
                var option = $('<option>').text(roomName + " (" + roomCapacity + " cap.)").val(roomId);
                // Set the data-count attribute
                option.attr('data-cap', roomCapacity);
                // Append the option to the select element
                $('#sched_room').append(option);      
              }
            },
            error: function(xhr, status, error) {
              // Log the specific error message returned by the server
              console.log('AJAX Error: ' + status + ' - ' + error);
              console.log(xhr.responseText);
            }
          });
        } else {
          $('sched_room').empty();
          console.log('No Available rooms');
        }
    });

    $('#sched_subject').change(function() {
        sched_subject = $(this).val();
        sections = null;
        totalCount = 0;

        // get the sections taking that subject
        $.ajax({
          url: 'get_sections.php',
          type: 'POST',
          data: { sched_subject: sched_subject},
          dataType: 'json',
          success: function(response) {
            // Clear the select options
            $('#sched_sections').empty();
              
            // Process the data returned from the PHP script
            for (var i = 0; i < response.length; i++) {
              var sectionId = response[i].section_id;
              var studentCount = response[i].student_count;
              var acronym = response[i].acronym;
              var year = response[i].year;
              var name = response[i].name;

              // Create the option elements
              var optionText = acronym + ' ' + year + name + ' (' + studentCount + ')';
              var optionValue = sectionId;
              var option = $('<option>').text(optionText).val(optionValue);

              // Set the data-count attribute
              option.attr('data-count', studentCount);

              // Append the option to the select element
              $('#sched_sections').append(option);
            }
          },
          error: function(xhr, status, error) {
            // Handle any errors that occur during the AJAX request
            console.log('AJAX Error: ' + status + ' - ' + error);
          }
        });
        var selectedSubject = $(this).find('option:selected');
        hasLab = selectedSubject.data('lab');
        getLocationAndRoom();
    });

    modalEl.on('hidden.bs.modal', function() {
        resetValues();
        modalEl.hide();
    });

    function resetValues() {
        // Reset the form inputs when the modal is hidden
        $('#sched_date, #sched_faculty, #time_from, #time_to, #sched_subject, #date_from, #date_to, #sched_weeks').val('');
        $('#sched_dateDiv, #saveSched').css('display', 'block');
        $('#availability, #sched_sections, #student_total, #loc, #sched_room').empty();
        $('#set_weekly').prop('checked', false);
        $('#weekly-div, #updateSched').css('display', 'none');
        $('#set_weekly, #weekly-div input, #weekly-div select').prop('required', false);
    }

    $('#set_weekly').change(function() {
      if ($(this).is(':checked')) {
        $('#sched_dateDiv').hide(); // Hide the element
        $('#weekly-div').show(); // Display the element
        $('#weekly-div input, #weekly-div select').prop('required', true); 
        $('#sched_weeks option').filter(function() {
          return $(this).text() === dayOfWeekText;
        }).prop('selected', true);
      } else {
        $('#sched_dateDiv').show(); // Hide the element
        $('#weekly-div').hide(); // Hide the element
        $('#weekly-div input, #weekly-div select').prop('required', false);
      }
    }); 
  
    $('#deleteSched').click(function() {
        if (confirm('Are you sure you want to delete this Schedule?')) {
          selectedValue = $('#filter_sched').val();
          var id = $('#viewId').val();
          $.ajax({
            url: 'Schedules/Delete.php',
            type: 'POST',
            data: { sched_id: id },
            success: function(data) {
              window.location.href = 'Schedules.php?faculty=' + selectedValue;
            },
            error: function(xhr, status, error) {
              // Handle error if any
              console.log(error);
            }
          });
        }
    });
      
    $('#filter_sched').on('change', function() {
        const sy_id = <?php echo $sy_id ?>;
        const sem_id = <?php echo $sem_id ?>;
        const selectedOption = $(this).val();
        calendar.fullCalendar('removeEvents');
          $.ajax({
            url: 'Schedules/Load.php',
            type: 'POST',
            data: { option: selectedOption, sy_id: sy_id, sem_id: sem_id },
            success: function(data) {
            try {
              if (data) {
                var events = JSON.parse(data);
                if (events.length > 0) {
                  var calendarEvents = [];
                  for (var i = 0; i < events.length; i++) {
                    var event = events[i];
                    var calendarEvent = {
                      id: event.id,
                      title: event.title,
                      start: event.start,
                      end: event.end,
                      repeating: event.repeating
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
            } catch(error){
              console.log('Parsing error');
            }
            },
            error: function(xhr, status, error) {
              console.log(error);
            }
          });
    });

        var urlParams = new URLSearchParams(window.location.search);
        var selectedValue = urlParams.get('faculty');
        $('#filter_sched').val(selectedValue);
        $('#filter_sched').change();

   /*   $.ajax({
      url: 'Schedules/Update.php',
      type: 'POST',
       data: { sched_id: id },
       success: function(response) {
         if (response.status === 'success') {
            
       console.log(response.data);
     } else {
         // Handle the error or display a message
        console.log('Error:', response.message);
      }
     },
    error: function(xhr, status, error) {
         // Handle error if any
        console.log(error);
       }
     }); */
  

      
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
  input[type="checkbox"] {
  width: 20px;
  height: 20px;
  vertical-align: middle;
  margin-right: 10px;
  }
  #sched_sections {
    height: 100px; /* Set the desired height */
  }
  #sched_weeks {
    height: 125px; /* Set the desired height */
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