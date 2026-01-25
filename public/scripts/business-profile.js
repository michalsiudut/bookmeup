document.addEventListener('DOMContentLoaded', () => {
    const calendarDaysGrid = document.getElementById('calendar-days');
    const currentMonthEl = document.getElementById('current-month');
    const timeSlots = document.querySelectorAll('.time-slot');
    const bookButton = document.querySelector('.book-submit-btn');
    const businessId = document.querySelector('.main-content').dataset.businessId;
    const serviceItems = document.querySelectorAll('.service-item');
    const selectButtons = document.querySelectorAll('.select-btn');

    let selectedServiceId = null;
    let selectedDate = null;
    let selectedTime = null;

    // Service Selection Logic
    selectButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            // Find parent service item
            const serviceItem = e.target.closest('.service-item');
            const serviceId = serviceItem.dataset.id;

            // Deselect all
            serviceItems.forEach(item => {
                item.classList.remove('selected-service');
                const btn = item.querySelector('.select-btn');
                btn.textContent = 'Wybierz';
                btn.classList.remove('active');
            });

            // Select this one
            serviceItem.classList.add('selected-service');
            e.target.textContent = 'Wybrano';
            e.target.classList.add('active');

            selectedServiceId = serviceId;
            console.log(`Selected Service ID: ${selectedServiceId}`);
            validateSelection();
        });
    });

    // Calendar Generation Logic
    const today = new Date();
    let currentRenderDate = new Date(today);

    function renderCalendar(date) {
        calendarDaysGrid.innerHTML = '';

        // Add headers
        const dayLabels = ['Pn', 'Wt', 'Śr', 'Cz', 'Pt', 'So', 'Nd'];
        dayLabels.forEach(label => {
            const div = document.createElement('div');
            div.className = 'day-label';
            div.textContent = label;
            calendarDaysGrid.appendChild(div);
        });

        const year = date.getFullYear();
        const month = date.getMonth(); // 0-indexed

        // Update month label
        const monthNames = [
            "Styczeń", "Luty", "Marzec", "Kwiecień", "Maj", "Czerwiec",
            "Lipiec", "Sierpień", "Wrzesień", "Październik", "Listopad", "Grudzień"
        ];
        currentMonthEl.textContent = `${monthNames[month]} ${year}`;

        // Get first day of month
        const firstDayOfMonth = new Date(year, month, 1);
        let startingDay = firstDayOfMonth.getDay(); // 0 (Sun) to 6 (Sat)
        // Adjust for Monday start (Mon=0, Sun=6)
        startingDay = startingDay === 0 ? 6 : startingDay - 1;

        const daysInMonth = new Date(year, month + 1, 0).getDate();

        // Previous month filler
        const prevMonthLastDay = new Date(year, month, 0).getDate();
        for (let i = 0; i < startingDay; i++) {
            const div = document.createElement('div');
            div.className = 'day-number disabled';
            div.textContent = prevMonthLastDay - startingDay + 1 + i;
            calendarDaysGrid.appendChild(div);
        }

        // Current month days
        for (let i = 1; i <= daysInMonth; i++) {
            const div = document.createElement('div');
            div.className = 'day-number';
            div.textContent = i;

            // Check if date is in past relative to today (simple check)
            const checkDate = new Date(year, month, i);
            const now = new Date();
            now.setHours(0, 0, 0, 0);

            if (checkDate < now) {
                div.classList.add('disabled');
            } else {
                div.addEventListener('click', () => {
                    document.querySelectorAll('.day-number').forEach(d => d.classList.remove('active-day'));
                    div.classList.add('active-day');

                    // Format YYYY-MM-DD (month is 0-indexed so +1)
                    const m = (month + 1).toString().padStart(2, '0');
                    const d = i.toString().padStart(2, '0');
                    selectedDate = `${year}-${m}-${d}`;
                    console.log(`Selected Date: ${selectedDate}`);
                    validateSelection();
                });
            }

            calendarDaysGrid.appendChild(div);
        }
    }

    renderCalendar(currentRenderDate);

    // Month Navigation (Simple implementation)
    const prevBtn = document.querySelector('.calendar-nav .cal-nav-btn:first-child');
    const nextBtn = document.querySelector('.calendar-nav .cal-nav-btn:last-child');

    prevBtn.addEventListener('click', () => {
        currentRenderDate.setMonth(currentRenderDate.getMonth() - 1);
        renderCalendar(currentRenderDate);
    });

    nextBtn.addEventListener('click', () => {
        currentRenderDate.setMonth(currentRenderDate.getMonth() + 1);
        renderCalendar(currentRenderDate);
    });

    // Time Slots
    timeSlots.forEach(slot => {
        slot.addEventListener('click', () => {
            timeSlots.forEach(s => s.classList.remove('selected'));
            slot.classList.add('selected');
            selectedTime = slot.textContent.trim();
            console.log(`Selected Time: ${selectedTime}`);
            validateSelection();
        });
    });

    function validateSelection() {
        if (selectedServiceId && selectedDate && selectedTime) {
            bookButton.disabled = false;
            bookButton.style.opacity = '1';
        } else {
            bookButton.disabled = true;
            bookButton.style.opacity = '0.5';
        }
    }

    // Initial check
    validateSelection();

    bookButton.addEventListener('click', async () => {
        if (!selectedServiceId || !selectedDate || !selectedTime) return;

        const originalText = bookButton.textContent;
        bookButton.textContent = 'Rezerwowanie...';
        bookButton.disabled = true;

        try {
            const response = await fetch('/bookAppointment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    business_id: businessId,
                    service_id: selectedServiceId,
                    date: selectedDate,
                    time: selectedTime
                })
            });

            const result = await response.json();

            if (response.ok) {
                alert('Pomyślnie zarezerwowano wizytę!');
                location.reload();
            } else {
                alert(`Błąd: ${result.error || 'Nie udało się zarezerwować wizyty'}`);
                bookButton.textContent = originalText;
                bookButton.disabled = false;
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Wystąpił błąd połączenia.');
            bookButton.textContent = originalText;
            bookButton.disabled = false;
        }
    });

});
