<?php
$title = "Reserve this property";
ob_start();?>

<!-- create an array of all reservations start and end date -->
<?php 
        ?> <script> const reservedList = []; </script> <?php
        foreach ($reservations as $reservation) {

            ?> <script> 
                reservedList.push(["<?=($reservation['start_date']);?>", "<?=($reservation['end_date']);?>"])
                console.log(reservedList)
            </script>    
        <?php }
    ?>


<body>
    <div class="creditCardForm">
        <div class="heading">
            <h1>Reservation Payment</h1>
        </div>
        <div class="payment">
            <form id="paymentForm" action="index.php" method="post">
                <p id="available">How long do you wish to stay?</p><br>
                
                <div class="calendarContainer">
                    <input id="datepicker" placeholder="Pick your dates" value=""/>
                    <!-- <div id="datepicker" placeholder="Pick your dates" value="">

                    </div> -->
                    <!-- CALENDAR JS -->
                    <script>
                    const DateTime = easepick.DateTime;
                    const bookedDates = [
                        // '2022-07-02',
                        // ['2022-07-06', '2022-07-11'],
                        
                    ].map(d => {
                        if (d instanceof Array) {
                            const start = new DateTime(d[0], 'YYYY-MM-DD');
                            const end = new DateTime(d[1], 'YYYY-MM-DD');
    
                            return [start, end];
                        }
                        
                        return new DateTime(d, 'YYYY-MM-DD');
                    });

                    const picker = new easepick.create({
                        element: document.getElementById('datepicker'),
                    
                        css: [
                        'public/style/bookingCalendar.css',
                        ],


                        plugins: ['RangePlugin', 'LockPlugin'],
                        RangePlugin: {
                        tooltipNumber(num) {
                            return num - 1;
                        },

                        locale: {
                            one: 'night',
                            other: 'nights',
                        },
                        },

                        LockPlugin: {
                        minDate: new Date(),
                        minDays: 2,
                        inseparable: true,
                        filter(date, picked) {
                            if (picked.length === 1) {
                            const incl = date.isBefore(picked[0]) ? '[)' : '(]';
                            return !picked[0].isSame(date, 'day') && date.inArray(bookedDates, incl);
                            }
    
                            let selectedRange = document.getElementById("datepicker").value.split(" - ");
                            document.getElementById("startDate").value = selectedRange[0];
                            document.getElementById("endDate").value = selectedRange[1];
                            
                            let selectedCheckInDate = document.querySelector('#selectedCheckInDate');
                            let selectedCheckOutDate = document.querySelector('#selectedCheckOutDate');
                            selectedCheckInDate.textContent = selectedRange[0];
                            selectedCheckOutDate.textContent = selectedRange[1];
    
                            // console.log(selectedRange)
                            return date.inArray(bookedDates, '[)');
                        },
                        }
                    });
                    </script>
                    <!-- CALENDAR JS -->

                    <input type="hidden" id="startDate" name="startDate">
                    <input type="hidden" id="endDate" name="endDate">
                    
                </div>
                <div id=dateBtn onclick="dateDiff()">Click here for the total cost</div>

                <br><br>
                <div class="creditCards">
                    <img id="creditCards"src="public/images/capture.JPG" alt="creditCards">
                </div><br>
                <label for="owner">Cardholder</label>
                <input type="text" class="form-control" id="owner" name="owner" placeholder="Enter your name as shown on your credit card" pattern="^(?![\s.]+$)[A-Z\-a-z\s.]{2,}" required >
                <div class="nameError"><em>Please enter your name as shown on your credit card (only letters)</em></div><br>
                <label for="cardNumber" id="cardNum">Card #</label>
                <!-- <input type="text" class="form-control" onkeyup="formatCreditCard()" placeholder="xxxx-xxxx-xxxx-xxxx" name="card-number" id="credit-card" value="" > -->                
                
                <input type="text" class="form-control" onkeyup="formatCreditCard()" id="cardNumber" name="cardNumber" placeholder="Enter a valid credit card number" required>
                <div class="numError"><em>Please enter a correct card number</em></div><br>
                <label for="cvv">CVV</label>
                <input type="text" class="form-control" id="cvv" name="cvv" placeholder="Enter the 3 or 4 digit code on the back of your card" pattern="^[0-9]{3,4}" required>
                <div class="cvvError"><em>3 or 4 numbers only</em></div><br>
                <div class="expiry">
                    <label>Expiry</label>
                    <select name="month" id="month" required>
                        <option value="" selected disabled hidden>Select month</option>
                        <option value="01">January</option>
                        <option value="02">February </option>
                        <option value="03">March</option>
                        <option value="04">April</option>
                        <option value="05">May</option>
                        <option value="06">June</option>
                        <option value="07">July</option>
                        <option value="08">August</option>
                        <option value="09">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                    <select name="year" id="year" required>
                        <option value="" selected disabled hidden>Select year</option>
                        <option value="22"> 2022</option>
                        <option value="23"> 2023</option>
                        <option value="24"> 2024</option>
                        <option value="25"> 2025</option>
                        <option value="26"> 2026</option>
                        <option value="27"> 2027</option>
                    </select>
                </div><br><br>
                    <div class="buttons">
                        <button type="submit" class="btn btn-default" id="confirm-purchase">Confirm Payment</button>
                        <input type="hidden" value="addReservationInfo" name="action">
                        <button id="reset" type="reset">Reset the form</button>
                        <input type="hidden" name="propId" value="<?=$_REQUEST['propId']?>">
                        <input type="hidden" name="price" value="<?=$_REQUEST['price']?>">
                
                    </div>

                </form>

                <!-- <div class="confirmationPage">
                    <div class="messageDisplay">
                        <span>Request Sent</span>
                        <span>This is not a confirmed booking - at least not yet. You'll get a response within 24 hours</span>
                    </div>

                    <div class="reservationInfo">
                        <h2><?php 
                            if($propDetails[0]['post_title']==''){
                                echo $propDetails[0]['p_type'].' in '.$propDetails[0]['province_state'].', '.$propDetails[0]['city'];
                            } else {
                                echo $propDetails[0]['post_title'];
                            };?>
                        </h2>
                        <h3><?=$propDetails[0]['r_type']?> in <?= $propDetails[0]['p_type'];?></h3>
                    </div>
                </div> -->
            </form>
        </div>
    </div>



</body>
<script src="public/js/reservations.js"></script>

<?php
$content = ob_get_clean();
include('template.php');
?>

