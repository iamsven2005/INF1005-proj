# Complete Booking Flow with 5-Minute Hold System

## 🔄 Flow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│ 1. USER SELECTS DATE                                            │
│    - JavaScript: show_timings(date)                             │
│    - API: api_booking.php                                       │
│    - Returns: Available slots (excluding booked + held slots)  │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│ 2. USER SELECTS TIMESLOT                                        │
│    - Shows booking form with player count selector             │
│    - Calculates subtotal                                        │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│ 3. USER CLICKS "PROCEED TO CHECKOUT"                            │
│    - JavaScript: onCheckoutclick()                              │
│    - API: api_hold_slot.php                                     │
│    - Creates hold in BookingHolds table (expires in 5 min)     │
│    - Slot is now UNAVAILABLE to other users                    │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│ 4. CHECKOUT FORM DISPLAYED                                      │
│    - Shows 5-minute countdown timer                             │
│    - User enters billing address                                │
│    - Stripe Elements for credit card                            │
│    - Apple Pay / Google Pay buttons (if available)             │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│ 5. USER SUBMITS PAYMENT                                         │
│    - JavaScript: Stripe.confirmPayment()                        │
│    - Stripe processes payment                                   │
└─────────────────────────────────────────────────────────────────┘
                              ↓
                    ┌─────────┴─────────┐
                    │                   │
              ✅ SUCCESS           ❌ FAILURE
                    │                   │
                    ↓                   ↓
    ┌───────────────────────┐   ┌──────────────────┐
    │ 6A. PAYMENT SUCCESS   │   │ 6B. PAYMENT FAIL │
    │ - Call                │   │ - Show error     │
    │   handlePaymentSuccess│   │ - User can retry │
    │ - API: api_checkout   │   │ - Hold remains   │
    │   .php                │   │   active         │
    │ - Verify hold exists  │   └──────────────────┘
    │ - Insert into         │
    │   Bookings table      │
    │ - Delete hold         │
    │ - Return booking_id   │
    └───────────────────────┘
                    ↓
    ┌───────────────────────┐
    │ 7. SUCCESS PAGE       │
    │ - Show confirmation   │
    │ - Display booking ID  │
    │ - Send email          │
    └───────────────────────┘
```

## ⏱️ Hold Expiration Scenarios

### Scenario 1: User Completes Payment (Before 5 min)
```
Hold Created → User Pays → Payment Success → Booking Created → Hold Deleted ✅
```

### Scenario 2: Hold Expires (After 5 min)
```
Hold Created → Timer Expires → Hold Auto-Deleted → Payment Button Disabled ❌
User must go back and select slot again
```

### Scenario 3: User Abandons Payment
```
Hold Created → User Leaves Page → Hold Auto-Expires After 5 min → Slot Released
```

### Scenario 4: Payment Fails
```
Hold Created → User Pays → Payment Fails → Hold Remains → User Can Retry ✅
(Hold still active for remaining time)
```

## 🔒 Database States

### BookingHolds Table (Temporary)
| holdID | date | time | expires_at | user_id | room_id |
|--------|------|------|------------|---------|---------|
| 123 | 2024-01-15 | 14:00:00 | 2024-01-15 14:05:00 | 5 | 1 |

**Status**: Slot is HELD (unavailable to others for 5 minutes)

### After Payment Success
**BookingHolds**: Row deleted ❌
**Bookings**: New row created ✅

| bookingID | date | time | players | total | status | user_id | room_id |
|-----------|------|------|---------|-------|--------|---------|---------|
| 456 | 2024-01-15 | 14:00:00 | 4 | 100.00 | Confirmed | 5 | 1 |

**Status**: Slot is BOOKED (permanently unavailable)

## 📋 API Endpoints Summary

### 1. `api/api_booking.php`
**Purpose**: Get available time slots for a date  
**Input**: `{ date: "2024-01-15" }`  
**Output**: `{ success: true, available_slots: ["9:00 AM", "10:30 AM", ...] }`  
**Logic**: Excludes slots that are:
- Already booked (Bookings table)
- Currently held by another user (BookingHolds table)

### 2. `api/api_hold_slot.php`
**Purpose**: Hold a time slot for 5 minutes  
**Input**: `{ date, time, user_id, room_id }`  
**Output**: `{ success: true, hold_id: 123, expires_at: "...", expires_in_seconds: 300 }`  
**Logic**:
- Check if slot is already booked → Reject
- Check if slot is held by another user → Reject
- Create hold record with 5-minute expiration
- Clean up expired holds

### 3. `inc/create_payment_intent.php`
**Purpose**: Initialize Stripe payment  
**Input**: `{ amount: 10000, currency: "sgd" }`  
**Output**: `{ clientSecret: "pi_xxx", paymentIntentId: "pi_xxx" }`  
**Logic**: Creates Stripe PaymentIntent (reserves payment)

### 4. `api/api_checkout.php`
**Purpose**: Confirm booking after payment success  
**Input**: `{ date, time, user_id, room_id, pax, subtotal, billing_address, ... }`  
**Output**: `{ success: true, booking_id: 456 }`  
**Logic**:
- Verify user still has valid hold
- Double-check slot availability
- Insert into Bookings table
- Delete hold
- Send confirmation email

## 🎯 Key Features

✅ **Prevents Double Booking**: Holds ensure one user at a time  
✅ **Automatic Cleanup**: Expired holds are auto-deleted  
✅ **Fair System**: 5-minute window for all users  
✅ **Visual Timer**: Users see countdown and know time limit  
✅ **Graceful Expiration**: Clear message + ability to re-select  
✅ **No Lost Payments**: Payment only processed if hold is valid  

## 🛠️ Files Required

1. ✅ `api/api_booking.php` - Get available slots
2. ✅ `api/api_hold_slot.php` - Create hold
3. ✅ `inc/create_payment_intent.php` - Initialize payment
4. ✅ `api/api_checkout.php` - Complete booking
5. ✅ `calendar.js` - Frontend logic with timer
6. ✅ `calendar.inc.php` - Payment form HTML
7. ✅ `database_schema.sql` - Database tables

## 🚀 Testing Checklist

- [ ] User A selects slot → User B cannot select same slot
- [ ] Hold expires after 5 minutes → Slot becomes available again
- [ ] Payment succeeds → Booking created, hold deleted
- [ ] Payment fails → Hold remains, user can retry
- [ ] Multiple holds by same user → Old holds deleted
- [ ] Countdown timer displays correctly
- [ ] Timer expires → Payment button disabled
- [ ] Cleanup removes expired holds automatically
