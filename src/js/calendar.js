/* Template from Colorlib */
(function($) {

    "use strict";

    const months = [ 
        "January","February","March","April","May","June",
        "July","August","September","October","November","December"
    ];

    $(document).ready(function(){
        var date = new Date();
        // calendar
        $(".right-button").click({date: date}, next_month);
        $(".left-button").click({date: date}, prev_month);

        // booking
        $("#minus-btn").click(onMinusclick)
        $("#plus-btn").click(onPlusClick)

        // checkout
        $("#checkout-btn").click({date:date}, onCheckoutclick);

        init_calendar(date);
    });

    function init_calendar(date) {
        $(".tbody").empty();
        $(".timeslots-container").empty();
        $(".booking-form").hide();

        var calendar_days = $(".tbody");
        var month = date.getMonth();
        var year = date.getFullYear();
        var day_count = days_in_month(month, year);
        var row = $("<tr class='table-row'></tr>");

        $(".calendar-year").text(year);
        $(".calendar-month").text(months[month]);


        var today = new Date();
        today.setHours(0,0,0,0);

        var first_day = new Date(year, month, 1).getDay();

        var isCurrentMonth = (today.getMonth() === month && today.getFullYear() === year);
        var isFutureMonth  = (year > today.getFullYear()) ||
                             (year === today.getFullYear() && month > today.getMonth());

        for (var i = 0; i < 42; i++) {
            var day = i - first_day + 1;
            var cell;

            if (i % 7 === 0) {
                calendar_days.append(row);
                row = $("<tr class='table-row'></tr>");
            }

            if (day < 1 || day > day_count) {
                cell = $("<td class='table-date nil'></td>");
                cell.prop("disabled", true);
                row.append(cell);
                continue;
            }

            var full_date = new Date(year, month, day);
            var disabled = full_date < today;

            cell = $("<td class='table-date' id='" + day + "'>" + day + "</td>");

            if (disabled) {
                cell.addClass("disabled-td");
            }

            if ($(".active-date").length === 0 && !disabled) {
                if (isCurrentMonth && day === today.getDate()) {
                    cell.addClass("active-date");
                    show_timings(full_date);
                }
                else if (isFutureMonth && day === 1) {
                    cell.addClass("active-date");
                    show_timings(full_date);
                }
            }

            cell.click({full_date: full_date, disabled: disabled}, function(e) {
                if (e.data.disabled) return;
                $(".active-date").removeClass("active-date");
                $(this).addClass("active-date");
                show_timings(e.data.full_date);
            });

            row.append(cell);
        }

    }

    function days_in_month(month, year) {
        return new Date(year, month + 1, 0).getDate();
    }

    function next_month(event) {
        $("#dialog").hide(250);
        var date = event.data.date;
        date.setMonth(date.getMonth() + 1);
        init_calendar(date);
    }

    function prev_month(event) {
        $("#dialog").hide(250);
        var date = event.data.date;
        date.setMonth(date.getMonth() - 1);
        init_calendar(date);
    }

    function show_timings(date) {
        var formattedDate = formatDate(date);
        $(".timeslots-container").empty();
        $(".booking-form").hide();
        $(".checkout-form").hide(); // Also hide checkout

        bookingPost(formattedDate).done(function(response) {
            if (!response.success) {
                $(".timeslots-container").append(
                    $("<div class='error-card'><div class='event-name'>No Available Slots.</div></div>")
                );
                return;
            }

            var available_slots = response.available_slots;
            
            $(".timeslots-container").append(
                $("<div class='row mb-2 text-dark align-items-center'><div class='col-auto'><img src='images/clock.png' class='logo me-2' alt='clock icon'></div><div class='col-auto'><p class='m-0 fw-bold'>Available Slots</p></div></div>")
            );

            // timeslots wrapper
            let wrapper = $("<div class='timeslot-wrapper'></div>");
            $(".timeslots-container").append(wrapper);

            for (var i = 0; i < available_slots.length; i++) {
                let slot = available_slots[i];

                var timeslot_card = $("<div class='event-card timeslot'></div>");
                var timeslot_name = $("<div class='event-name'>" + slot + "</div>");

                timeslot_card.click({card:timeslot_card, name:timeslot_name},timeslot_click);
                
                timeslot_card.append(timeslot_name);

                wrapper.append(timeslot_card);
            }
        });

        $(".timeslots-container").show(250);
    }

    function bookingPost(date) {
        return $.ajax({
            type: 'POST',
            url: 'api/api_booking.php',
            data: { date: date },
            dataType: 'json'
        });
    }

    /* ---------------------------- */
    /*         BOOKING FORM         */
    /* ---------------------------- */

    var selectedTime;
    var price = $("#price").text();
    var min_players = $("#min-players").text();
    var max_players = $("#max-players").text();
    var default_pax = min_players;
    // Global booking data to be used after payment
    var bookingData = {};

    function timeslot_click(event) {
        $(".timeslot").removeClass("active-timeslot");
        event.data.card.addClass("active-timeslot");

        $(".event-name").removeClass("active-name");
        event.data.name.addClass("active-name");

        $("#player-count").text(default_pax);

        selectedTime = event.data.card.find(".event-name").text();
        
        $(".booking-form").show(250);
        updateSubtotal();
    }

    function onMinusclick() {
        var pax = parseInt($("#player-count").text());
        if (pax > min_players) {
            pax = pax - 1
        }
        else {
            pax = min_players
        }
        $("#player-count").text(pax);
        updateSubtotal(pax, price);
    }

    function onPlusClick() {
        var pax = parseInt($("#player-count").text());
        if (pax < max_players) {
            pax = pax + 1
        }
        else {
            pax = max_players
        }
        $("#player-count").text(pax);
        updateSubtotal(pax, price);
    }

    function updateSubtotal() {
        var subtotal = 0;
        var pax = parseInt($("#player-count").text());
        if (pax >= min_players && pax <= max_players) {
            var subtotal = pax * price;
        }
        $(".pax").text(pax);
        $(".ticket-price").text(price);
        $("#subtotal").text(subtotal);
    }

    function onCheckoutclick(event) {
        // Get booking details
        var room_name = $("#room_name").text(); // doesn't matter if DOM is edited, backend does not use this value

        var selectedDay = $(".active-date").attr("id");
        var month = months.indexOf($(".calendar-month").text());
        var year = event.data.date.getFullYear();
        var selectedDate = new Date(year, month, selectedDay);
        var formattedDate = formatDate(selectedDate);

        var time = moment(selectedTime, "hh:mm A").format("HH:mm:ss");
        var pax = parseInt($("#player-count").text());
        var subtotal = parseFloat($("#subtotal").text());

        // First, try to hold the slot
        $.ajax({
            type: 'POST',
            url: 'api/api_hold_slot.php',
            data: {
                date: formattedDate,
                time: time
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    console.log("Slot held successfully for 5 minutes");
                    
                    // Store booking data globally
                    bookingData = {
                        date: formattedDate,
                        time: time,
                        pax: pax,
                        subtotal: subtotal,
                        hold_id: response.hold_id
                    };

                    // Populate checkout form display
                    $("#checkout-room").text(room_name);
                    $("#checkout-date").text(formattedDate);
                    $("#checkout-time").text(selectedTime);
                    $("#checkout-players").text(pax);
                    $("#checkout-total").text(subtotal);

                    // Show checkout form
                    $(".timeslots-container").hide(250);
                    $(".booking-container").hide(250);
                    $(".checkout-form").show(250, function() {
                        // Initialize Stripe payment
                        if (typeof initializePayment === 'function') {
                            initializePayment();
                        }
                        
                        // Start countdown timer
                        startHoldTimer(response.expires_in_seconds);
                    }); 
                } else {
                    alert(response.message || "Unable to hold this time slot. Please try another slot.");
                    window.parent.location.reload();
                }
            },
            error: function(xhr, status, error) {
                console.error("Error holding slot:", error);
                alert("Unable to hold this time slot. Please try again.");
                window.parent.location.reload();
            }
        });
    }

    // Countdown timer for hold expiration
    var holdTimerInterval;
    
    function startHoldTimer(seconds) {
        // Clear any existing timer
        if (holdTimerInterval) {
            clearInterval(holdTimerInterval);
        }

        var timeRemaining = seconds;
        var minutes = Math.floor(timeRemaining / 60);
        var sec = timeRemaining % 60;
        
        // Create timer display if it doesn't exist
        if ($('#hold-timer').length === 0) {
            $('.checkout-form .section-title').after(
                '<div id="hold-timer" class="alert alert-warning mt-3" role="alert">' +
                '<strong>&#128337 Time remaining: <span id="timer-display">'+ minutes + ':' + (sec < 10 ? '0' : '') + sec +'</span></strong><br>' +
                'Please complete payment before time expires.' +
                '</div>'
            );
        }

        // Update timer every second
        holdTimerInterval = setInterval(function() {
            timeRemaining--;
            
            var minutes = Math.floor(timeRemaining / 60);
            var seconds = timeRemaining % 60;
            $('#timer-display').text(minutes + ':' + (seconds < 10 ? '0' : '') + seconds);

            // Change color when less than 1 minute remaining
            if (timeRemaining <= 60) {
                $('#hold-timer').removeClass('alert-warning').addClass('alert-danger');
            }

            // Timer expired
            if (timeRemaining <= 0) {
                clearInterval(holdTimerInterval);
                $('#hold-timer').html(
                    '<strong>&#9200 Time expired!</strong><br>' +
                    'Your hold on this time slot has expired. Please select the slot again.'
                );
                
                // Disable payment button
                $('#payment-submit-button').prop('disabled', true);
                
                // Show alert
                setTimeout(function() {
                    alert("Your hold on this time slot has expired. Please go back and select the slot again.");
                    
                    fetch("api/api_generate_token.php")
                    .then(response => {
                        if (!response.ok) throw new Error("Unable to get token");
                        location.reload();
                    })
                    .catch(err => console.log(err));
                }, 1000);
            }
        }, 1000);
    }

    // This function is called from the Stripe payment form after successful payment
    window.handlePaymentSuccess = function() {
        console.log("Payment successful, saving booking...");
        
        // Get billing details
        const billingData = {
            billing_address: document.getElementById('billing-address').value,
            billing_city: document.getElementById('billing-city').value,
            billing_postal: document.getElementById('billing-postal').value,
            billing_country: document.getElementById('billing-country').value
        };

        // Combine booking data with billing data
        const finalData = { ...bookingData, ...billingData };

        // Now save to database with status "Confirmed"
        $.ajax({
            type: 'POST',
            url: 'api/api_checkout.php',
            data: bookingData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    console.log("Booking saved successfully");
                    // Redirect to success page or show confirmation
                    window.location.href = 'booking_success.php?booking_ref=' + response.booking_ref;
                } else {
                    alert("Error saving booking: " + response.message);
                    window.parent.location.reload();
                }
            },
            error: function(xhr, status, error) {
                console.error("Error saving booking:", error);
                alert("Payment processed but booking save failed. Please contact support.");
                window.parent.location.reload(true);
            }
        });
    };

    function formatDate(date) {
        var formattedDate = date.getFullYear() + '-' + 
                            String(date.getMonth() + 1).padStart(2, '0') + '-' + 
                            String(date.getDate()).padStart(2, '0');
        return formattedDate;
    }

})(jQuery);