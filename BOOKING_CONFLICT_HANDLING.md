# Booking Conflict Handling Documentation

## Overview
This document describes the comprehensive booking conflict handling system implemented to prevent double-bookings, handle race conditions, and provide excellent user experience when slot conflicts occur.

## System Architecture

### 1. **Three-Layer Conflict Prevention**

#### Layer 1: Temporary Hold System (BookingHolding Table)
- **Purpose**: Reserve slots for 5 minutes while users complete payment
- **Mechanism**: When a user clicks "Checkout", a hold record is created
- **Expiration**: Automatically expires after 5 minutes
- **Benefits**: Prevents other users from attempting to book the same slot during checkout

#### Layer 2: Row-Level Locking (Database)
- **Purpose**: Prevent race conditions at database level
- **Mechanism**: Uses `FOR UPDATE` in SELECT statements during critical operations
- **Coverage**: Applied in both hold creation and final booking
- **Benefits**: Ensures atomic operations even under high concurrency

#### Layer 3: Final Validation
- **Purpose**: Double-check availability before confirming booking
- **Mechanism**: Validates hold ownership and slot availability before inserting booking
- **Coverage**: Checks performed in transaction before committing
- **Benefits**: Last line of defense against any edge cases

---

## Conflict Scenarios & Handling

### Scenario 1: Slot Already Booked
**When**: User tries to hold a slot that's already confirmed
**Detection**: Database query checks Bookings table
**Response**:
```json
{
  "success": false,
  "message": "This time slot is already booked. Please select another time.",
  "conflict_type": "booked"
}
```
**User Experience**: 
- Clear error message
- Timeslots automatically refresh to show current availability
- User can select a different slot immediately

### Scenario 2: Slot Held by Another User
**When**: User tries to hold a slot currently held by someone else
**Detection**: Checks BookingHolding table for active holds
**Response**:
```json
{
  "success": false,
  "message": "This time slot is currently being booked by another user.",
  "conflict_type": "held",
  "retry_in_seconds": 180,
  "suggested_action": "Please select a different time slot or wait 3 minute(s) and try again."
}
```
**User Experience**:
- Informed about the temporary hold
- Advised on wait time (calculated from hold expiration)
- Can choose different slot or wait and retry

### Scenario 3: Hold Expired During Checkout
**When**: User's hold expires while filling payment details
**Detection**: Payment button disabled + backend validation
**Response**:
```json
{
  "success": false,
  "message": "Your hold on this time slot has expired. Please select a time slot again.",
  "conflict_type": "hold_expired",
  "suggested_action": "return_to_calendar"
}
```
**User Experience**:
- Countdown timer shows remaining hold time
- Warning when < 1 minute remaining
- Payment button disabled if expired
- Clear instructions to reselect slot

### Scenario 4: Double Booking Attempt Prevented
**When**: Slot gets booked by another user between hold and final checkout
**Detection**: Final availability check with row-level locking
**Response**:
```json
{
  "success": false,
  "message": "This timeslot has just been booked by another user. Please select a different time.",
  "conflict_type": "double_booking_prevented",
  "suggested_action": "return_to_calendar"
}
```
**User Experience**:
- Clear explanation that slot was taken
- Payment NOT processed (conflict detected before payment)
- Automatically returned to calendar
- No charge incurred

---

## Technical Implementation

### Backend Improvements

#### 1. api_hold_slot.php
**Enhancements Added**:
- ✅ Row-level locking with `FOR UPDATE`
- ✅ Automatic hold refresh for existing user holds
- ✅ Detailed conflict type identification
- ✅ Suggested retry times for held slots
- ✅ Comprehensive error logging

**Key Code Pattern**:
```php
// Start transaction with proper isolation
$conn->begin_transaction();

// Lock the booking row to prevent race conditions
$check_booking = $conn->prepare("
    SELECT bookingID FROM Bookings 
    WHERE bookingDate = ? AND bookingTimeslot = ? AND Rooms_roomID = ? 
    AND bookingStatus IN ('Confirmed', 'Completed')
    FOR UPDATE
");
```

#### 2. api_checkout.php
**Enhancements Added**:
- ✅ Hold ownership verification before payment
- ✅ Row-level locking during final booking
- ✅ Atomic transaction for booking + hold cleanup
- ✅ Detailed conflict logging
- ✅ Proper rollback on conflicts

**Key Code Pattern**:
```php
// Verify user still has valid hold
$verify_hold = $conn->prepare("
    SELECT holdID, expires_at FROM BookingHolding 
    WHERE holdDate = ? AND holdTimeslot = ? 
    AND Rooms_roomID = ? AND Users_userID = ?
    AND expires_at > NOW()
    FOR UPDATE
");

// Only proceed if user has valid hold
if (!$user_hold) {
    $conn->rollback();
    // Return error
}
```

#### 3. api_booking.php
**Existing Logic** (already working well):
- Filters out booked slots
- Filters out held slots (except user's own)
- Cleans up expired holds
- Returns only truly available slots

### Frontend Improvements

#### 1. Enhanced Error Messages
**Implementation**: Smart conflict detection and user-friendly messaging
```javascript
if (response.conflict_type === 'booked') {
    errorMsg = "⚠️ This time slot has just been booked.\n\n" +
               "Please select another time slot.";
    shouldRefresh = true;
}
```

#### 2. Auto-Refresh Timeslots
**Feature**: Automatically refreshes slot availability every 30 seconds
**Benefits**:
- Users see when held slots become available
- No manual refresh needed
- Stops when in checkout to avoid confusion

```javascript
function startTimeslotAutoRefresh(date) {
    timeslotRefreshInterval = setInterval(function() {
        if (currentDisplayedDate && !$(".checkout-form").is(":visible")) {
            // Refresh and compare slots
        }
    }, 30000); // 30 seconds
}
```

#### 3. Hold Countdown Timer
**Feature**: Visual countdown showing remaining hold time
**Enhanced**:
- Changes color when < 1 minute remaining
- Disables payment button when expired
- Provides clear instructions

#### 4. Payment Error Handling
**Improvements**:
- Distinguishes between network errors and conflicts
- Provides specific recovery instructions
- Prevents payment if hold expired

---

## Database Schema

### BookingHolding Table
```sql
CREATE TABLE BookingHolding (
    holdID INT PRIMARY KEY AUTO_INCREMENT,
    holdDate DATE NOT NULL,
    holdTimeslot TIME NOT NULL,
    expires_at DATETIME NOT NULL,
    Rooms_roomID INT NOT NULL,
    Users_userID INT NOT NULL,
    FOREIGN KEY (Rooms_roomID) REFERENCES Rooms(roomID),
    FOREIGN KEY (Users_userID) REFERENCES Users(userID),
    INDEX idx_expires (expires_at),
    INDEX idx_slot_lookup (holdDate, holdTimeslot, Rooms_roomID)
);
```

### Bookings Table (Key Fields)
```sql
bookingStatus ENUM('Confirmed', 'Completed', 'Cancelled')
bookingDate DATE
bookingTimeslot TIME
```

---

## Flow Diagrams

### Successful Booking Flow
```
1. User selects date → Show available timeslots
2. User selects time → Show booking form
3. User clicks checkout → Hold slot (5 min)
   ↓ [Success: Hold created]
4. User fills payment details
5. User submits payment → Stripe processes
   ↓ [Payment successful]
6. Backend validates hold & availability
   ↓ [Valid: Hold exists, slot free]
7. Insert booking → Delete hold → Commit
8. Send confirmation email
9. Redirect to success page
```

### Conflict Handling Flow
```
1. User attempts hold
   ↓
2. Check #1: Is slot booked?
   ├─ YES → Return error (conflict_type: 'booked')
   └─ NO → Continue
   ↓
3. Check #2: Is slot held by another user?
   ├─ YES → Return error (conflict_type: 'held' + retry_in_seconds)
   └─ NO → Continue
   ↓
4. Create hold with FOR UPDATE lock
5. If user proceeds to payment...
   ↓
6. Check #3: Does user still have valid hold?
   ├─ NO → Return error (conflict_type: 'hold_expired')
   └─ YES → Continue
   ↓
7. Check #4: Is slot still available?
   ├─ NO → Rollback → Return error (conflict_type: 'double_booking_prevented')
   └─ YES → Complete booking
```

---

## Testing Scenarios

### Test Case 1: Concurrent Hold Attempts
**Setup**: Two users try to hold same slot simultaneously
**Expected**: First user gets hold, second user gets "held" error
**Verification**: Check BookingHolding table for single record

### Test Case 2: Hold Expiration
**Setup**: User holds slot but waits > 5 minutes
**Expected**: Hold automatically expires, slot becomes available
**Verification**: Expired holds cleaned up on next query

### Test Case 3: Race Condition Prevention
**Setup**: Two users complete checkout nearly simultaneously
**Expected**: First completes booking, second gets prevented
**Verification**: Only one booking record created, second user informed

### Test Case 4: Hold Refresh
**Setup**: User clicks checkout again while already holding slot
**Expected**: Hold time refreshed to 5 minutes
**Verification**: Check expires_at timestamp updated

---

## Monitoring & Logging

### Error Logs
All conflicts are logged with details:
```php
error_log("Hold attempt failed - slot already booked: " .
          "date=$date, time=$time, room=$room_id, user=$user_id");
```

### Success Logs
```php
error_log("Booking successful: booking_id=$booking_id, " .
          "ref=$ref, user=$user_id, date=$date, time=$time");
```

### Monitoring Queries
```sql
-- Check active holds
SELECT * FROM BookingHolding WHERE expires_at > NOW();

-- Check expired holds waiting cleanup
SELECT * FROM BookingHolding WHERE expires_at < NOW();

-- Check booking conflicts (should be 0)
SELECT b1.* FROM Bookings b1
JOIN Bookings b2 ON b1.bookingDate = b2.bookingDate 
    AND b1.bookingTimeslot = b2.bookingTimeslot
    AND b1.Rooms_roomID = b2.Rooms_roomID
    AND b1.bookingID != b2.bookingID
WHERE b1.bookingStatus = 'Confirmed';
```

---

## Performance Considerations

1. **Index Optimization**: Indexes on (holdDate, holdTimeslot, Rooms_roomID) for fast lookups
2. **Automatic Cleanup**: Expired holds cleaned on each availability check
3. **Transaction Scope**: Kept minimal to reduce lock duration
4. **Auto-Refresh Rate**: 30 seconds balances freshness vs server load

---

## Future Enhancements

1. **WebSocket Support**: Real-time slot availability updates
2. **Hold Extension**: Allow user to extend hold if needed
3. **Queue System**: Waiting list for popular time slots
4. **Analytics**: Track conflict frequency to optimize business logic
5. **Admin Dashboard**: View active holds and resolve conflicts manually

---

## Summary

The enhanced booking conflict handling system provides:
- ✅ **Zero Double-Bookings**: Multiple layers of prevention
- ✅ **Race Condition Protection**: Database-level locking
- ✅ **Excellent UX**: Clear messages and automatic recovery
- ✅ **Comprehensive Logging**: Full audit trail for debugging
- ✅ **Automatic Recovery**: Self-healing with expired hold cleanup
- ✅ **Real-time Updates**: Auto-refresh shows slot availability

This system ensures a reliable, user-friendly booking experience even under high concurrency.
