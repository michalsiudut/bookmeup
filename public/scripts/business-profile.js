document.addEventListener('DOMContentLoaded', () => {
    const calendarDaysGrid = document.getElementById('calendar-days');
    const currentMonthEl = document.getElementById('current-month');
    const bookButton = document.querySelector('.book-submit-btn');
    const businessId = document.querySelector('.main-content').dataset.businessId;
    const serviceItems = document.querySelectorAll('.service-item');
    const selectButtons = document.querySelectorAll('.select-btn');

    const timeSlotsGrid = document.querySelector('.time-slots-grid'); // Add selection of grid container

    let selectedServiceId = null;
    let selectedDate = null;
    let selectedTime = null;
    let bookedSlots = [];

    // Config for selected service
    let serviceConfig = {
        startHour: '09:00',
        endHour: '17:00',
        duration: 60
    };

    // Service Selection Logic
    selectButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            // Find parent service item
            const serviceItem = e.target.closest('.service-item');
            const serviceId = serviceItem.dataset.id;

            // Toggle Deselection
            if (selectedServiceId === serviceId) {
                serviceItem.classList.remove('selected-service');
                e.target.textContent = 'Wybierz';
                e.target.classList.remove('active');

                selectedServiceId = null;
                console.log('Service deselected');

                renderTimeSlots();
                validateSelection();
                return;
            }

            // Update config
            serviceConfig.startHour = serviceItem.dataset.startHour;
            serviceConfig.endHour = serviceItem.dataset.endHour;
            serviceConfig.duration = parseInt(serviceItem.dataset.duration);

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
            console.log(`Selected Service ID: ${selectedServiceId}, Config:`, serviceConfig);

            // Re-render time slots based on new config
            renderTimeSlots();
            validateSelection();
        });
    });

    function renderTimeSlots() {
        timeSlotsGrid.innerHTML = '';
        selectedTime = null; // Reset selection on re-render

        // If no service selected (though currently we only call this when service IS selected), but just in case logic changes
        if (!selectedServiceId) {
            timeSlotsGrid.innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: var(--gray-500);">Wybierz usługę, aby zobaczyć dostępne terminy.</p>';
            return;
        }

        const startParts = serviceConfig.startHour.split(':');
        const endParts = serviceConfig.endHour.split(':');

        let startTotalMinutes = parseInt(startParts[0]) * 60 + parseInt(startParts[1]);
        let endTotalMinutes = parseInt(endParts[0]) * 60 + parseInt(endParts[1]);
        const duration = serviceConfig.duration;

        // Simple loop to generate slots
        for (let time = startTotalMinutes; time + duration <= endTotalMinutes; time += duration) {
            const hours = Math.floor(time / 60);
            const minutes = time % 60;
            const timeString = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;

            // Skip booked slots
            if (bookedSlots.includes(timeString)) continue;

            const btn = document.createElement('button');
            btn.className = 'time-slot';
            btn.textContent = timeString;

            btn.addEventListener('click', () => {
                document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
                btn.classList.add('selected');
                selectedTime = timeString;
                validateSelection();
            });

            timeSlotsGrid.appendChild(btn);
        }

        if (timeSlotsGrid.children.length === 0) {
            timeSlotsGrid.innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: var(--gray-500);">Brak dostępnych terminów dla tej konfiguracji.</p>';
        }
    }

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
                div.addEventListener('click', async () => {
                    document.querySelectorAll('.day-number').forEach(d => d.classList.remove('active-day'));
                    div.classList.add('active-day');

                    // Format YYYY-MM-DD (month is 0-indexed so +1)
                    const m = (month + 1).toString().padStart(2, '0');
                    const d = i.toString().padStart(2, '0');
                    selectedDate = `${year}-${m}-${d}`;
                    console.log(`Selected Date: ${selectedDate}`);

                    // Fetch Booked Slots
                    try {
                        const response = await fetch(`/getBookedSlots?business_id=${businessId}&date=${selectedDate}`);
                        bookedSlots = await response.json();
                        console.log('Booked Slots:', bookedSlots);
                        renderTimeSlots();
                    } catch (error) {
                        console.error('Error fetching booked slots:', error);
                        bookedSlots = [];
                        renderTimeSlots();
                    }

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

    // Time Slots logic managed in renderTimeSlots now to update dynamically

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

    // Reviews Accordion Logic
    const reviewsToggle = document.getElementById('reviews-toggle-btn');
    if (reviewsToggle) {
        reviewsToggle.addEventListener('click', () => {
            const accordion = reviewsToggle.closest('.reviews-accordion');
            accordion.classList.toggle('active');
        });
    }
});
