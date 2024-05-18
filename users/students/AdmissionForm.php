<?php
session_start();
error_reporting(0);

//navigation styles
$afstyle = "active";

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
    <title>ACTS Web Portal | Admission</title>
    <!-- bootstrap css -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <!-- page css -->
    <link rel="stylesheet" href="../../css/navigation.css"/>
    <link rel="stylesheet" href="../../css/style.css"/>
    <!--icon-->
    <link rel="shortcut icon" href="../../images/actsicon.png"/>
    <!-- lineawesome icons -->
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <!-- phone number -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <!-- jquery -->
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
  </head>
  <body>
    <?php
    //include navigationbar
    include('NavigationBar.php'); 

    if(!is_null($ret['app_id'])){
      if ($ret['app_status'] != 3) {
        //if not rejected redirect to form2
        echo "<script> window.location.href = 'AdmissionForm2.php';</script>";
      }
    }
    ?>
    <main>
      <!-- admission form submit -->
      <?php
      if(isset($_POST['submit'])){
        $firstname = $_POST['firstname'];
        $midname = $_POST['midname'] ?? null;
        $lastname = $_POST['lastname'];
        $category = $_POST['category'];
        $year = $_POST['year'];
        $course = $_POST['course'];
        $birth = $_POST['birth'];
        $gen = $_POST['gen'];
        $s_status = $_POST['s_status'];
        $number = preg_replace('/\s+/', '', $_POST['number']); // Remove spaces from phone number
        $nationality = $_POST['nationality'];
        $address = $_POST['address'];
        $mother = $_POST['mother'];
        $mother_phone = preg_replace('/\s+/', '', $_POST['mother_phone']);
        $mother_occu = $_POST['mother_occu'] ?? null;
        $father = $_POST['father'];
        $father_phone = preg_replace('/\s+/', '', $_POST['father_phone']);
        $father_occu = $_POST['father_occu'] ?? null;
        $guardian = $_POST['guardian'];
        $guardian_phone = preg_replace('/\s+/', '', $_POST['guardian_phone']);
        $relation = $_POST['relation'];
        $elem = $_POST['elem'];
        $elem_year = $_POST['elem_year'];
        $junior = $_POST['junior'];
        $junior_year = $_POST['junior_year'];
        $senior = $_POST['senior'] ?? null;
        $strand = $_POST['strand'] ?? null;
        $senior_year = $_POST['senior_year'] ?? null;
        $college = $_POST['college'] ?? null;
        $old_course = $_POST['old_course'] ?? null;
        $college_year = $_POST['college_year'] ?? null;
        $agreement=$_POST['h_agree'];
        $prom_date=$_POST['prom_date'];
        $sign = $_POST['signature'];

        // make phone number null if user didnt enter number other than country code
        if (strlen($mother_phone)<5) {
          $mother_phone = null;
        }
        if (strlen($father_phone)<5) {
          $father_phone = null;
        }

        $tmpName = $_FILES['card']['tmp_name'];
        $card = base64_encode(file_get_contents(addslashes($tmpName)));

        //check signature if empty
        if (empty($sign)) {
          echo "<script>alert('Please provide a signature.');</script>";
        } else {
          //if previous application is rejected update data
          if ($ret['app_status'] == 3) {
              $sql = "UPDATE applications SET category=?, year=?, course=?, fname=?, mname=?, lname=?, birth=?, gen=?, s_status=?, phone=?, nationality=?, address=?, mother=?, mother_phone=?, mother_occu=?, father=?, father_phone=?, father_occu=?, guardian=?, guardian_phone=?, relation=?, elem=?, elem_year=?, junior=?, junior_year=?, senior=?, strand=?, senior_year=?, college=?, old_course=?, college_year=?, card_tor=?, agreement=?, prom_date=?, signature=?, date_applied=current_timestamp(), app_status=1, app_remarks='', app_remarks_date=null, app_process_by=null WHERE user_id=$uid";
              $stmt = $con->prepare($sql);
              $stmt->bind_param("iiissssissssssssssssssisissississss", $category, $year, $course, $firstname, $midname, $lastname, $birth, $gen, $s_status, $number, $nationality, $address, $mother, $mother_phone, $mother_occu, $father, $father_phone, $father_occu, $guardian, $guardian_phone, $relation, $elem, $elem_year, $junior, $junior_year, $senior, $strand, $senior_year, $college, $old_course, $college_year, $card, $agreement, $prom_date, $sign);
          } else {
              $sql = "INSERT INTO applications (user_id, category, year, course, fname, mname, lname, birth, gen, s_status, phone, nationality, address, mother, mother_phone, mother_occu, father, father_phone, father_occu, guardian, guardian_phone, relation, elem, elem_year, junior, junior_year, senior, strand, senior_year, college, old_course, college_year, card_tor, agreement, prom_date, signature, date_applied) VALUES ($uid, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, current_timestamp())";
              $stmt = $con->prepare($sql);
              $stmt->bind_param("iiissssissssssssssssssisissississss", $category, $year, $course, $firstname, $midname, $lastname, $birth, $gen, $s_status, $number, $nationality, $address, $mother, $mother_phone, $mother_occu, $father, $father_phone, $father_occu, $guardian, $guardian_phone, $relation, $elem, $elem_year, $junior, $junior_year, $senior, $strand, $senior_year, $college, $old_course, $college_year, $card, $agreement, $prom_date, $sign);
          }
          if ($stmt->execute()) {
              echo "<script type='text/javascript'> document.location ='StudentDashboard.php'; </script>";
          } else {
              echo "<script>alert('Error! try again later.');</script>";
          }
          $stmt->close();
        }
      } 
      ?>
      
      <div>
        <h5 class="title" id="main-title">Admission Form:</h5>  
      </div>
        <!-- fillup admission form -->
        <div class="container" id = "fill_div">
          <form method="post" id="admission-form" enctype="multipart/form-data">
            <div class = "details">
                <span class="details_title">PERSONAL DETAILS</span>
              <div class="fields">
                <div class="input-fields">
                  <label for="firstname">First Name</label>
                  <input id = "firstname" type="text" name="firstname" value="<?php echo isset($_POST['firstname']) ? $_POST['firstname'] : ''; ?>"  required>
                </div>

                <div class="input-fields">
                  <label for="midname">Middle Name</label>
                  <input id = "midname" type="text" name="midname" value="<?php echo isset($_POST['midname']) ? $_POST['midname'] : ''; ?>">
                </div>

                <div class="input-fields">
                  <label for="lastname">Last Name</label>
                  <input id = "lastname" type="text" name="lastname" value="<?php echo isset($_POST['lastname']) ? $_POST['lastname'] : ''; ?>"required>
                </div>

                <div class="input-fields">
                  <label for="category">Category</label>
                  <select name='category' id="category" value="<?php echo isset($_POST['category']) ? $_POST['category'] : ''; ?>" required>
                    <option value="" hidden="" selected>-Select-</option>
                    <option value="1">New Student</option>
                    <option value="2">Transferee</option>
                  </select>
                </div>

                <div class="input-fields">
                  <label for="year">Grade/Year</label>
                  <select name='year' id="year" value="<?php echo isset($_POST['year']) ? $_POST['year'] : ''; ?>" disabled required>
                    <option value="" hidden="" selected>-Select-</option>
                    <option value="11">Grade 11</option>
                    <option value="12" class = "forTf" hidden>Grade 12</option>
                    <option value="1">First Year</option>
                    <option value="2" class = "forTf" hidden>Second Year</option>
                    <option value="3" class = "forTf" hidden>Third Year</option>
                    <option value="4" class = "forTf" hidden>Fourth Year</option>
                  </select>
                </div>

                <div class="input-fields">
                  <label for="course">Strand/Course</label>
                  <select name='course' id="course" value="<?php echo isset($_POST['course']) ? $_POST['course'] : ''; ?>" disabled required>
                    <option value="" hidden="" selected>-Select-</option>
                      <!-- get strands from database -->
                      <?php 
                      $query=mysqli_query($con,"select * from course_strand where type = 1");
                      while($row=mysqli_fetch_array($query)){ ?>    
                      <option class = "strandOps" value="<?php echo $row['id'];?>" hidden> <?php echo $row['acronym'] ." - ". $row['name'] ;?></option>
                      <?php } 
                      // get courses from database
                      $query=mysqli_query($con,"select * from course_strand where type = 2");
                      while($row=mysqli_fetch_array($query)){ ?>    
                      <option class = "courseOps" value="<?php echo $row['id'];?>" hidden><?php echo $row['acronym'] ." - ". $row['name'] ;?></option>
                      <?php } ?>  
                  </select>
                </div>

                <div class="input-fields">
                  <label for="birth">Birthdate</label>
                  <input type="date" id = "birth" name="birth" min="<?php echo date('Y-m-d', strtotime('-100 year')); ?>" max="<?php echo date('Y-m-d', strtotime('-12 years')); ?>" value="<?php echo isset($_POST['birth']) ? $_POST['birth'] : ''; ?>" required>
                </div>

                <div class="input-fields">
                  <label for="gen">Gender</label>
                  <select name='gen' id="gen" value="<?php echo isset($_POST['gen']) ? $_POST['gen'] : ''; ?>" required>
                    <option value="" hidden="" selected>-Select-</option>
                    <option value="1"  >Male</option>
                    <option value="2"  >Female</option>
                  </select>
                </div>

                <div class="input-fields">
                  <label for="s_status">Status</label>
                  <select name='s_status' id="s_status" value="<?php echo isset($_POST['status']) ? $_POST['status'] : ''; ?>" required>
                    <option value="" hidden="" selected>-Select-</option>
                    <option value="Single">Single</option>
                    <option value="Married">Married</option>
                    <option value="Divorced">Divorced</option>
                    <option value="Separated">Separated</option>
                    <option value="Widowed">Widowed</option>
                  </select>
                </div>

                <div class="input-fields">
                  <label for="email">Email</label>
                  <input id = "email" type="text" name="email" value="<?php echo $ret['email'] ?>" disabled>
                </div>

                <div class="input-fields">
                  <label for="number" class="form-label">Phone Number</label>
                  <input id = "number" name="number" class = "tel-input" type="tel"  pattern="^\+\d{5,}$" title="Please enter a valid phone number starting with + sign, without spaces." value="<?php echo isset($_POST['number']) ? $_POST['number'] : ''; ?>" required>
                </div>

                <div class="input-fields">
                  <label for="nationality">Nationality</label>
                  <input id = "nationality" type="text" name="nationality" placeholder = "ex. Filipino" value="<?php echo isset($_POST['nationality']) ? $_POST['nationality'] : ''; ?>" required>
                </div>
                
                <div class="input-fields" id="stretch-add">
                  <label for="address">Complete Address <small>(House#, Street, Purok/Zone, Barangay/Village, Town/Municipality, Province/City, ZIP Code)</small></label>
                  <input id = "address" type="text" name="address" value="<?php echo isset($_POST['address']) ? $_POST['address'] : ''; ?>"  required>
                </div>
              </div>
            </div>

            <div class = "details">
              <span class="details_title">FAMILY DETAILS</span>
              <label for="mother">Mother's Information</label>
              <div class="fields">
                <div class="input-fields">
                  <input id = "mother" type="text" name="mother" placeholder = "Full Name" value="<?php echo isset($_POST['mother']) ? $_POST['mother'] : ''; ?>" required>
                </div>
                <div class="input-fields">
                  <input id = "mother_phone" class = "tel-input" name="mother_phone" type="tel" value="<?php echo isset($_POST['mother_phone']) ? $_POST['mother_phone'] : ''; ?>" required>
                </div>
                <div class="input-fields">
                  <input id = "mother_occu" type="text" name="mother_occu" placeholder = "Occupation" value="<?php echo isset($_POST['mother_occu']) ? $_POST['mother_occu'] : ''; ?>">
                </div>
              </div>
              
                <label for="father">Father's Information</label>

              <div class="fields">
                <div class="input-fields">
                  <input id = "father" type="text" name="father" placeholder = "Full Name" value="<?php echo isset($_POST['father']) ? $_POST['father'] : ''; ?>" required>
                </div>
                <div class="input-fields">
                  <input id = "father_phone" type="tel" class = "tel-input" name="father_phone" value="<?php echo isset($_POST['father_phone']) ? $_POST['father_phone'] : ''; ?>">
                </div>
                <div class="input-fields">
                  <input id = "father_occu" type="text" name="father_occu" placeholder = "Occupation" value="<?php echo isset($_POST['father_occu']) ? $_POST['father_occu'] : ''; ?>">
                </div>
              </div>

              <label for="guardian">Guardian/Person to contact in case of emergency</label>

              <div class="fields">
                <div class="input-fields">
                  <input id = "guardian" type="text" name="guardian" placeholder = "Full name" value="<?php echo isset($_POST['guardian']) ? $_POST['mother_phone'] : ''; ?>" required>
                </div>
                <div class="input-fields">
                  <input id = "guardian_phone" type="tel"  class = "tel-input" name="guardian_phone" pattern="^\+\d{5,}$" title="Please enter a valid phone number starting with + sign, without spaces." value="<?php echo isset($_POST['guardian_phone']) ? $_POST['guardian_phone'] : ''; ?>" required>
                </div>
                <div class="input-fields">
                  <input id = "relation" type="text" name="relation" placeholder="Relationship ex.(Mother/Father/Aunt/Grandmother)" value="<?php echo isset($_POST['relation']) ? $_POST['relation'] : ''; ?>" >
                </div>
              </div>

              <div class="sets" id="checkbox-container">
                <div class="input-fields">
                  <input id = "set-mother" type="checkbox" name="set-mother" value="<?php echo isset($_POST['set-mother']) ? $_POST['set-mother'] : ''; ?>">
                  <label for="set-mother">Set mother as my guardian.</label>
                </div>
                <div class="input-fields">
                  <input id = "set-father" type="checkbox" name="set-father" value="<?php echo isset($_POST['set-father']) ? $_POST['set-father'] : ''; ?>">
                  <label for="set-father">Set father as my guardian.</label>
                </div>
                <div class="input-fields" id="set"></div>
              </div>
            </div>

            <div class = "details">
              <span class="details_title">EDUCATIONAL BACKGROUND</span>

              <label for="elem">Elementary</label>

              <div class="fields">
                <div class="input-fields" id="stretch">
                  <input id = "elem" type="text" name="elem" placeholder = "School Name" value="<?php echo isset($_POST['elem']) ? $_POST['elem'] : ''; ?>" required>
                </div>
                <div class="input-fields" >
                  <input id = "elem_year" type="number" name="elem_year"placeholder = "Year Graduated" value="<?php echo isset($_POST['elem_year']) ? $_POST['elem_year'] : ''; ?>"  required>
                </div>
              </div>

              <label for="junior">Junior High</label>

              <div class="fields">
                <div class="input-fields" id="stretch">
                  <input id = "junior" type="text" name="junior" placeholder = "School Name" value="<?php echo isset($_POST['junior']) ? $_POST['junior'] : ''; ?>" required>
                </div>
                <div class="input-fields">
                  <input id = "junior_year" type="number" name="junior_year" placeholder = "Year Graduated" value="<?php echo isset($_POST['junior_year']) ? $_POST['junior_year'] : ''; ?>" required>
                </div>
              </div>

              <label for="senior">Senior High</label>

              <div class="fields">
                <div class="input-fields">
                  <input class = "senior_inputs" id = "senior" type="text" name="senior" placeholder = "School Name" value="<?php echo isset($_POST['senior']) ? $_POST['senior'] : ''; ?>" required disabled>
                </div>
                <div class="input-fields">
                  <input class = "senior_inputs" id = "strand" type="text" name="strand" placeholder = "Strand/Track" value="<?php echo isset($_POST['strand']) ? $_POST['strand'] : ''; ?>" required disabled>
                </div>
                <div class="input-fields">
                  <input class = "senior_inputs" id = "senior_year" type="number" name="senior_year" placeholder = "Year Graduated" value="<?php echo isset($_POST['senior_year']) ? $_POST['senior_year'] : ''; ?>" required disabled>
                </div>
              </div>

              <label for="college">College</label>

              <div class="fields">
                <div class="input-fields">
                  <input class="college_inputs" id = "college" type="text" name="college" placeholder = "Previous School/University Name" value="<?php echo isset($_POST['college']) ? $_POST['college'] : ''; ?>" required disabled>
                </div>
                <div class="input-fields">
                  <input id = "old_course" type="text" name="old_course" placeholder = "Previous Course" class = "college_inputs" value="<?php echo isset($_POST['old_course']) ? $_POST['old_course'] : ''; ?>" required disabled>
                </div>
                <div class="input-fields">
                  <input class = "college_inputs" id = "college_year" type="number" name="college_year" placeholder = "Year Last Attended" value="<?php echo isset($_POST['college_year']) ? $_POST['college_year'] : ''; ?>" required disabled>
                </div>
              </div>
            </div>
            <div class="details">
              <span id="card_label" class="details_title">UPLOAD</span>
              <div class="fields">
                <div class="input-fields">
                  <input id = "card" type="file" name="card" onchange="checkImage(event)" value="<?php echo isset($_POST['card']) ? $_POST['card'] : ''; ?>" required>
                </div>
              </div>
            </div>
            <div class="details">
              <span class="details_title declaration">DECLARATION AND AGREEMENT</span>
                  <p class="center"><i></i></p>
                  <p class="center"><i  id="agreement"></i>&ensp;<input id = "prom_date" type="date" name="prom_date" min="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d', strtotime('+1 month')); ?>" required style="display:none"></p>
                  <input type="text" name="h_agree" id="h_agree" value="<?php echo isset($_POST['h_agree']) ? $_POST['h_agree'] : ''; ?>" hidden>
            </div>
            <div class="details">
              <span class="details_title">Write OR Upload signature</span>
              <div class="fields">
                <div class="input-fields">
                  <canvas id="signature-pad" required></canvas>
                   <input type="hidden" name="signature" id="signature" value="<?php echo isset($_POST['signature']) ? $_POST['signature'] : ''; ?>">
                    <div>
                      <label for="file-upload" class="custom-file-upload">UPLOAD IMAGE OF SIGNATURE</label>
                      <input id="file-upload" value="<?php echo isset($_POST['file_upload']) ? $_POST['file_upload'] : ''; ?>" onchange="previewImage(event)" type="file" style="display: none;" />
                      <button id="clear-button" class="btn btn-danger" type="button">CLEAR</button>
                    </div>
                </div> 
              </div>
            </div>
            <div class="title flex">
              <small style="color: darkred;">Note: Check all your information carefully before submitting.</small>
              <button class="btn btn-success" id="submit" type="submit" name="submit">SUBMIT</button>
            </div>
          </form>
        </div>
    </main>
    <script type="text/javascript">
      //$('#prom_date').hide(); //hide this field initially

      //phone number format
      function initializeTelephoneInputByClass(classSelector) {
        let phoneInputFields = document.querySelectorAll(classSelector);
         // Load the intlTelInput library.
        const intlTelInput = window.intlTelInput;
        phoneInputFields.forEach((phoneInputField) => {
          // Configure the intlTelInput library with initialCountry, preferredCountries, and utilsScript.
          const phoneInput = intlTelInput(phoneInputField, {
            initialCountry: "ph",
            preferredCountries: ["ph", "hk", "us"],
            utilsScript:
              "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
          });
          // Update the phone input field value with the selected country's dial code.
          const updatePhoneFieldValue = () => {
            const { dialCode } = phoneInput.getSelectedCountryData();
            const formattedDialCode = dialCode ? '+' + dialCode + '' : '';
            phoneInputField.value = formattedDialCode;
          };
          updatePhoneFieldValue();
          // Add an event listener to the phone input field for when the selected
          // country changes, and update the phone input field value accordingly.
          phoneInputField.addEventListener("countrychange", updatePhoneFieldValue);
        });
      }
      initializeTelephoneInputByClass('.tel-input');

      const courseSelect = document.getElementById("course");
      const yearSelect = document.getElementById("year");
      const categorySelect = document.getElementById("category");
      const forTfElements = document.querySelectorAll(".forTf");
      const strandOps = document.querySelectorAll(".strandOps");
      const courseOps = document.querySelectorAll(".courseOps");
      const seniorInputs = document.querySelectorAll('.senior_inputs');
      const collegeInputs = document.querySelectorAll('.college_inputs');
      const senior_field = document.getElementById('senior');
      const strand_field = document.getElementById('strand');
      const senior_year_field = document.getElementById('senior_year');
      const card_label = document.querySelector('#card_label');
      const agreement = document.querySelector('#agreement');
      const h_agree = document.querySelector('#h_agree');
      const prom_date = document.querySelector('#prom_date');
      courseSelect.selectedIndex = 0;

      //toggle year options based on category
      categorySelect.addEventListener("change", () => {
        const cindex = categorySelect.selectedIndex;
        forTfElements.forEach(element => {
          if (cindex === 2) {
            element.removeAttribute("hidden");
          } else {
            element.setAttribute("hidden", true);
          }
        });
        yearSelect.selectedIndex = 0;
        courseSelect.disabled = true;
        yearSelect.disabled = false;
      });

      //toggle course options based on year
      yearSelect.addEventListener("change", () => {
        const yindex = yearSelect.selectedIndex;
        const cindex = categorySelect.selectedIndex;

        function showCourseOps() {
          courseSelect.disabled = false;
          if (yindex < 3) {
            strandOps.forEach(strand => strand.removeAttribute("hidden"));
            courseOps.forEach(course => course.setAttribute("hidden", true));
          } else {
            strandOps.forEach(strand => strand.setAttribute("hidden", true));
            courseOps.forEach(course => course.removeAttribute("hidden"));
          }
        }

        //toggle enable/disable fields based on category and year
        function enableEdInputs() {
          seniorInputs.forEach(input => input.disabled = true);
          senior_field.placeholder = "School Name";
          strand_field.placeholder = "Strand/Track";
          senior_year_field.placeholder = "Year Graduated";
          collegeInputs.forEach(input => input.disabled = true);

          switch (cindex) {
            case 2:
              seniorInputs.forEach(input => input.disabled = false);
              if (yindex < 3) {
                senior_field.placeholder = "Previous School Name";
                strand_field.placeholder = "Previous Strand/Track";
                senior_year_field.placeholder = "Year Last Attended";
              } else {
                collegeInputs.forEach(input => input.disabled = false);
              }
              break;
            default:
              if (yindex === 3) {
                seniorInputs.forEach(input => input.disabled = false);
              }
          }
        }
        showCourseOps();
        enableEdInputs();

        //customize the agreement based on category and year
        let card="", add_agreement="";
        if (cindex == 1) {
            if (yindex == 1) {
              card = "Grade 10 Report Card";
              add_agreement = card+", Form 137, JHS Completion Certificate";
            } else {
              card = "Grade 12 Report Card";
              add_agreement = card;
            }
        } else {
          if (yindex == 1 || yindex == 2) {
            card = "Report Card Issued by the Last School Attended";
            add_agreement = card+", JHS Completion Certificate";
          } else {
            card = "Copy of Grades or Official Transcript of Records (TOR)";
            add_agreement = card+", Honorable Dismissal";
          }
        }
        card_label.textContent = "UPLOAD "+card;
        agreement.textContent = "I hereby state that the facts mentioned above are true to the best of my knowledge and belief. I also hereby promise to pass the original copies of my PSA Birth Certificate, Good Moral Character Certificate, "+add_agreement+", and recent 2x2 pictures on or before ";
        const promDateInput = document.getElementById("prom_date");
        promDateInput.style.display = "inline-block";
        //$('#prom_date').show();
        h_agree.value=add_agreement;
      });

      const mother_check = document.getElementById("set-mother");
      const father_check = document.getElementById("set-father");
      const guardian_field = document.getElementById("guardian");
      const guardian_phone_field = document.getElementById("guardian_phone");
      const relation_field = document.getElementById("relation");
      const mother_field = document.getElementById("mother");
      const mother_phone_field = document.getElementById("mother_phone");
      const father_field = document.getElementById("father");
      const father_phone_field = document.getElementById("father_phone");

      //auto set the fields for mother/father as guardian
      function handleCheckboxClick() {
        if (this === mother_check) {
          father_check.checked = false;
          if (this.checked) {
            guardian_field.value = mother_field.value;
            guardian_phone_field.value = mother_phone_field.value;
            relation_field.value = "Mother";
          } else {
            guardian_field.value = "";
            guardian_phone_field.value = "";
            relation_field.value = "";
          }
        } else if (this === father_check) {
          mother_check.checked = false;
          if (this.checked) {
            guardian_field.value = father_field.value;
            guardian_phone_field.value = father_phone_field.value;
            relation_field.value = "Father";
          } else {
            guardian_field.value = "";
            guardian_phone_field.value = "";
            relation_field.value = "";
          }
        }
      }
      mother_check.addEventListener("click", handleCheckboxClick);
      father_check.addEventListener("click", handleCheckboxClick);

      // convert the signature as image data on submit button
      const submitBtn = document.getElementById('submit');

      submitBtn.addEventListener('click', function() {
        const canvas = document.getElementById('signature-pad');
        const ctx = canvas.getContext('2d');
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const pixels = imageData.data;

        // Check if the pixel data is all zeros
        let isEmpty = true;
        for (let i = 0; i < pixels.length; i++) {
          if (pixels[i] !== 0) {
            isEmpty = false;
            break;
          }
        }
        if (!isEmpty) {
          const dataURL = canvas.toDataURL('image/png;base64');
          const base64Signature = dataURL.split(',')[1];
          const signatureInput = document.getElementById('signature');
          signatureInput.value = base64Signature;
        }
      });

      // preview the uploaded signature image
      function previewImage(event) {
        const file = event.target.files[0];
        const canvas = document.getElementById('signature-pad');

        if (!file || !file.type.match(/image\/(png|jpe?g|jfif)/)) {
          alert("Only PNG, JPG, JPEG, and JFIF files are allowed!");
          $('#file-upload').val("");
          return;
        }
        if (file.size > 2 * 1024 * 1024) {
          alert("Image size exceeds 2MB limit!");
          document.getElementById('file-upload').value = '';
          return;
        }

        const reader = new FileReader();
        reader.onload = () => {
          const img = new Image();
          img.onload = () => {
            const context = canvas.getContext('2d');
            context.clearRect(0, 0, canvas.width, canvas.height); // clear the canvas first

            const canvasAspectRatio = canvas.width / canvas.height;
            const imageAspectRatio = img.width / img.height;
            let drawWidth, drawHeight;

            // Determine the dimensions to draw the image to maintain aspect ratio
            if (canvasAspectRatio > imageAspectRatio) {
              drawWidth = img.width * (canvas.height / img.height);
              drawHeight = canvas.height;
            } else {
              drawWidth = canvas.width;
              drawHeight = img.height * (canvas.width / img.width);
            }
            
            // Center the image on the canvas
            const x = (canvas.width - drawWidth) / 2;
            const y = (canvas.height - drawHeight) / 2;
            
            // Draw the image on the canvas
            context.drawImage(img, x, y, drawWidth, drawHeight);
          };
          img.src = reader.result;
        };
        reader.readAsDataURL(file);
      }

      //declaring canvas
      const canvas = document.getElementById('signature-pad');
      const context = canvas.getContext('2d');
      const clearButton = document.getElementById('clear-button');
      let isDrawing = false;
      let lastX, lastY;

      function startDrawing(e) {
        isDrawing = true;
        [lastX, lastY] = getXY(e);
      }

      function draw(e) {
        if (!isDrawing) return;
        const [x, y] = getXY(e);
        context.beginPath();
        context.moveTo(lastX, lastY);
        context.lineTo(x, y);
        context.stroke();
        [lastX, lastY] = [x, y];
      }

      function stopDrawing() {
        isDrawing = false;
      }

      function clearCanvas() {
        context.clearRect(0, 0, canvas.width, canvas.height);
      }

      // calculates the coordinates of the mouse/touch 
      function getXY(e) {
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        let x, y;
        
        // Check if the event was triggered by a touch screen
        if (e.changedTouches) {
          x = e.changedTouches[0].clientX - rect.left;
          y = e.changedTouches[0].clientY - rect.top;
        } else {
          x = e.clientX - rect.left;
          y = e.clientY - rect.top;
        }
        
        return [x * scaleX, y * scaleY];
      }

      canvas.addEventListener('mousedown', startDrawing);
      canvas.addEventListener('mousemove', draw);
      canvas.addEventListener('mouseup', stopDrawing);
      canvas.addEventListener('touchstart', startDrawing);
      canvas.addEventListener('touchmove', draw);
      canvas.addEventListener('touchend', stopDrawing);
      clearButton.addEventListener('click', clearCanvas);

      window.addEventListener('load', function() {
        const image = new Image();
        image.onload = function() {
          const aspectRatio = image.width / image.height;
          let canvasWidth = canvas.width;
          let canvasHeight = canvasWidth / aspectRatio;

          // recalculate the canvas width and height to fit within the canvas element
          if (canvasHeight > canvas.height) {
            canvasHeight = canvas.height;
            canvasWidth = canvasHeight * aspectRatio;
          }

          const x = (canvas.width - canvasWidth) / 2;
          const y = (canvas.height - canvasHeight) / 2;

          // Draw the image onto the canvas with the calculated dimensions and position
          context.drawImage(image, x, y, canvasWidth, canvasHeight);
        };
      });

      //validate size and type of image uploaded
      function checkImage(event) {
        const input = document.getElementById('card');
        const file = event.target.files[0];
        const reader = new FileReader();
        if (!file || !file.type.match(/image\/(png|jpe?g|jfif)/)) {
          alert("Only PNG, JPG, JPEG, and JFIF files are allowed!");
          input.value = '';
          return;
        }
        if (file.size > 2 * 1024 * 1024) {
          alert("Image size exceeds 2MB limit!");
          input.value = '';
          return;
        }
      }
    </script>
  </body>
</html>
<style type="text/css">

  .center{
    text-align: center;
  }

  form img{
    border-bottom: 1px solid black;
  }

  .right{
    text-align: right;
  }

  #card{
    height: 40px;
    padding: 5px;
    background: white;
  }

  #fill_div *{
    font-family: Calibri;
  }

  .details_title{
    display: block;
    font-weight: bold;
    background: #d4edda;
    color:  #155724;
    padding: 6px;
    margin: 20px 0;
  }

  .declaration{
    background: #f8d7da;
    color: #721c24;
  }

  .flex{
      display: flex;
      justify-content: space-between;
  }

  .custom-file-upload {
    display: inline-block;
    padding: 8px;
    background-color: #0a58ca;
    color: white;
    border-radius: 5px;
    cursor: pointer;
    font-weight: normal;
  }

  .custom-file-upload:hover {
    background-color: #0d6efd;;
  }

  canvas {
    border: 1px solid grey;
    cursor: crosshair;
    touch-action: none;

  }

  #stretch-add{
    width: 100%;
  }

  #stretch{
    width: calc(100% * 2/3 - 10px);
  }

  .details{
    margin-bottom: 20px;
  }

  .sets{
    display: flex;
  }

  .sets .input-fields{
    flex-direction: row;
    width: 100%;
  }

  .sets input{
    margin: 0;
    margin-right: 10px;
    height: 20px;
  }

  .sets label:hover, .sets input:hover{
    cursor: pointer;
  }

  .sets label:hover{
    text-decoration: underline !important;
  }

  table {
    width: auto;
    border-collapse: collapse;
  }

  table th{
    text-align: left;
    width: 25%;
  }

  table td{
    padding-left: 50px;
  }

  .tel-input{
    margin: 5px 0 !important; 
    width: 100%;
  }

  @media(max-width: 500px){
    .input-fields, #stretch, #birth{
      width: 100%;
    }
    *{
      font-size: 12px;
    }
  }
</style>