<!-- Template from Colorlib https://colorlib.com/wp/template/calendar-04/ -->
<?php
// Get user details from session
$user_name = $_SESSION['username'] ?? '';
$user_email = $_SESSION['email'] ?? '';
$room_id = $_SESSION['room_id'] ?? '';
$min = $_SESSION['min'] ?? '';
$max = $_SESSION['max'] ?? '';
$price = $_SESSION['price'] ?? '';
?>

<div class="row">
    <div class="content w-100">
        <section class="calendar-container">
            <div class="calendar table-responsive">
                <div class="year-display text-center fs-4">
                    <span class="calendar-year"></span>
                </div>
                <div class="month-header"> 
                    <img src="images/prev.png" class="left-button fa fa-chevron-left" id="prev" alt="previous button">
                    <span class="calendar-month" id="label"></span>
                    <img src="images/next.png" class="right-button fa fa-chevron-right" id="next" alt="next button">
                </div>

                <table class="table table-bordered table-dark text-center days-header"> 
                    <td>Sun</td> 
                    <td>Mon</td> 
                    <td>Tue</td> 
                    <td>Wed</td> 
                    <td>Thu</td> 
                    <td>Fri</td> 
                    <td>Sat</td>
                </table> 
                
                <div class="frame"> 
                    <table class="dates-table w-100"> 
                        <tbody class="tbody">             
                        </tbody> 
                    </table>
                </div> 
            </div>
        </section>

        <!-- Right Panel: Contains timeslots, booking form, and checkout form -->
        <section class="right-panel">
            <section class="timeslots-container">
                <!-- JS populates here -->    
            </section>
            
            <section class="booking-container booking-form text-dark border-top my-4" style="display:none; margin-top:20px;">
                <div class="row align-items-center mb-3">
                    <div class="col-auto">
                        <img src="images/persons.png" class="logo me-2" alt="person icon">
                    </div>
                    <div class="col-auto">
                        <label class="fw-bold">Number of Players</label>
                    </div>
                </div>
                <div class="row align-items-center mb-3">
                    <div class="col-auto">
                        <button id="minus-btn" class="button">&minus;</button>
                    </div>
                    <div class="col-auto">
                        <span id="player-count" class="fw-bold fs-5">2</span>
                    </div>
                    <div class="col-auto">
                        <button id="plus-btn" class="button">&plus;</button>
                    </div>
                    <div class="col-auto text-muted">
                        (<span id="min-players"><?php echo htmlspecialchars($min, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></span> - <span id="max-players"><?php echo htmlspecialchars($max, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></span> players allowed)
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        Base Price &times; <span id="price"><?php echo htmlspecialchars($price, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></span> players
                    </div>
                    <div class="col-6 text-end">
                        $<span class="ticket-price"></span> &times; <span class="pax"></span>
                    </div>
                </div>
                <div class="row subtotal-box fw-bold fs-5 mb-3">
                    <div class="col-6">
                        Total:
                    </div>
                    <div class="col-6 text-end">
                        $<span id="subtotal">0</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <button id="checkout-btn" class="checkout-btn button w-100">
                            Proceed to Checkout
                        </button>
                    </div>
                </div>
            </section>
            <!-- Checkout Form -->
            <?php 
            include 'payment.php'
            ?>
        </section>
    </div>
</div>
