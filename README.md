Email reminder to all users on [DFAB Reservation System](http://cmu-dfab.org/reservations/day.php?area=1)

Heroku Dyno with Scheduler runs ```$php script.php``` at fixed time daily, before opening.  
Then sends email to all users on the schedule for that day, with an itemized list of the reservations they have for that day.

Report log is then sent to monitor email account to record and verify the reminder system.

```
Dfab Reservation Daily Reminder
Hello user,
Our records indicate you have at least one appointment today.
Please review reservations listed below.
Please show up on time.
If you think you might not be able to arrive on time, please cancel the reservation so others may utilize the equipment.
Thank you for your consideration of others.
Fondly,
-The_Reservation_System


Reservations for SAMPLE today:

2016-12-06 10:00:00 AM on: e. Vacuum Former (Max 1hr)
2016-12-06 12:00:00 PM on: a. Laser 1 (Max 1 hr)
2016-12-06 12:00:00 PM on: b. Laser 2 (Max 1hr)
2016-12-06 12:00:00 PM on: c. CNC Router (Max 4hrs)
2016-12-06 12:00:00 PM on: e. Vacuum Former (Max 1hr)
2016-12-06 1:00:00 PM on: g. IRB-4400 (Max 6hrs)
2016-12-06 1:00:00 PM on: h. IRB-6640 (Max 6hrs)
2016-12-06 12:00:00 PM on: g. IRB-4400 (Max 6hrs)
2016-12-06 12:00:00 PM on: h. IRB-6640 (Max 6hrs)
2016-12-06 6:00:00 PM on: g. IRB-4400 (Max 6hrs)
2016-12-06 6:00:00 PM on: h. IRB-6640 (Max 6hrs)
2016-12-06 1:30:00 PM on: a. Laser 1 (Max 1 hr)
2016-12-06 1:30:00 PM on: c. CNC Router (Max 4hrs)
```