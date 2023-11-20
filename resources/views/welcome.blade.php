<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Ügyfélfogadás</title>

        <!-- Styles -->
        <style>
            body {
                margin: 40px 10px;
                padding: 0;
                font-family: Arial, Helvetica Neue, Helvetica, sans-serif;
                font-size: 14px;
            }

            #calendar {
                max-width: 1100px;
                margin: 0 auto;
            }
        </style>

        <script src="{{asset('js/fullcalendar/index.global.min.js')}}"></script>
        <script src="{{asset('js/fullcalendar/locales/hu.global.min.js')}}"></script>
        <script>
    
    const zeroPad = (num, places) => String(num).padStart(places, '0')

    var calendar;
  document.addEventListener('DOMContentLoaded', function() {
    const dialog = document.getElementById("newEventDialog");
    const eventForm = document.getElementById('newEventForm');
    const newRecurrenceField = document.getElementById('newEventRecurrence');

    var calendarEl = document.getElementById('calendar');

    calendar = new FullCalendar.Calendar(calendarEl, {
      initialDate: '2023-11-01',
      initialView: 'timeGridWeek',
      locale: '{{app()->getLocale()}}',
      nowIndicator: true,
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
      },
      navLinks: true, // can click day/week names to navigate views
      editable: false,
      selectable: false,
      selectMirror: true,
      dayMaxEvents: true, // allow "more" link when too many events
      eventOverlap: false,
      eventSources: [
        {
            url: '{{url()->current()}}/api'
        }
        ],
      dateClick: function(eventObject) {
        var date = eventObject.date;
        document.getElementById('newEventDate').value = date.getFullYear() + '-' + zeroPad((date.getMonth() + 1), 2) + '-' + zeroPad(date.getDate(), 2);
        document.getElementById('newEventStart').value = zeroPad(date.getHours(), 2) +':' + zeroPad(date.getMinutes(), 2);
        var newDate = new Date(date.getTime() + 30*60000);
        document.getElementById('newEventEnd').value = zeroPad(newDate.getHours(), 2) +':' + zeroPad(newDate.getMinutes(), 2);
        if (isEven(date))
            newRecurrenceField.add(new Option('Páros heteken', 'even_weeks'));
        else
            newRecurrenceField.add(new Option('Páratlan heteken', 'odd_weeks'));
        
        dialog.showModal();
      }
    });

    calendar.render();

    document.getElementById("btn-cancel").addEventListener("click", () => {
        dialog.close();
        newRecurrenceField.remove(newRecurrenceField.length-1);
    });

    var enddateblock = document.getElementById('endDate_block');
    newRecurrenceField.addEventListener('change', () => {
        if (newRecurrenceField.value == 'no_repeat')
            enddateblock.hidden = true;
        else
            enddateblock.hidden = false;
    });

    eventForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        var errorField = document.getElementById('error_msg');
        var fd = new FormData(eventForm);
        // console.log(JSON.stringify(Object.fromEntries(fd.entries())));
        var response = await fetch('{{url()->current()}}/api', {
            method: "POST",
            body: JSON.stringify(Object.fromEntries(fd.entries())),
            headers: {
                "Content-type": "application/json; charset=UTF-8"
            }
        })
        .then(response => {
            if (response.status == 200)
            {
                dialog.close();
                errorField.innerHTML = '';
                newRecurrenceField.remove(newRecurrenceField.length-1);
                calendar.refetchEvents()
            }
            else
            {
                response.text().then(text => errorField.innerHTML = text);
            }
        });
    });
    
    /**onload end */
    });

    function isEven(date)
    {
        var start = new Date(date.getFullYear(), 0, 1);
        var weekNum = Math.ceil((((date - start) / 86400000) + start.getDay() - 1) /7);
        return weekNum %2 === 0;
    }


</script>
    </head>
    <body class="antialiased">
        <div id='calendar'></div>
        
        </div>
        <dialog id="newEventDialog">
            <div>
                <div id="error_msg" style="color: red"></div>
                <form id="newEventForm">
                    <p>
                    <label for="newEnevtClient">Ügyfél név</label>
                    <input type="text" name="client_name" id="newEnevtClient" required />
                    </p>
                    <p>
                    <label for="newEventDate">Dátum</label>
                    <input type="text" id="newEventDate" name="date" readonly />
                    </p>
                    <p>
                    <label for="newEventStart">Kezdés</label>
                    <input type="text" name="start" id="newEventStart" readonly />
                    </p>
                    <p>
                    <label for="newEventEnd">Befejezés</label>
                    <input type="text" name="end" id="newEventEnd" required />
                    </p>
                    <p>
                    <label for="newEventRecurrence">Ismétlődés</label>
                    <select name="recurrence" id="newEventRecurrence" >
                        <option value="no_repeat">Egyszeri</option>
                        <option value="every_week">Hetente</option>
                    </select>
                    </p>
                    <p hidden id="endDate_block">
                    <label for="newEventEnddate">Utolsó nap</label>
                    <input type="date" name="end_date" id="newEventEnddate" />
                    </p>
                    <p>
                    <button id="btn_save">Mentés</button>
                    <button id="btn-cancel" type="reset">Mégsem</button>
                    </p>
                </form>
            </div>
        </dialog>

    </body>
</html>
