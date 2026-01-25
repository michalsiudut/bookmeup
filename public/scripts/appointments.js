document.addEventListener('DOMContentLoaded', () => {
    const appContainer = document.getElementById('appointments-app');
    const appointmentDates = JSON.parse(appContainer.dataset.dates || '[]');

    // Normalize dates to YYYY-MM-DD
    const visitDays = [...new Set(appointmentDates.map(d => d.split(' ')[0]))];

    const daysContainer = document.getElementById('calendar-days');
    const monthDisplay = document.getElementById('month-display');
    const prevBtn = document.getElementById('prev-month');
    const nextBtn = document.getElementById('next-month');

    let currentDate = new Date();

    const monthNames = [
        "Styczeń", "Luty", "Marzec", "Kwiecień", "Maj", "Czerwiec",
        "Lipiec", "Sierpień", "Wrzesień", "Październik", "Listopad", "Grudzień"
    ];

    function renderCalendar() {
        daysContainer.innerHTML = '';

        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();

        monthDisplay.textContent = `${monthNames[month]} ${year}`;

        // Headers
        ['Pn', 'Wt', 'Śr', 'Cz', 'Pt', 'So', 'Nd'].forEach(h => {
            const el = document.createElement('div');
            el.className = 'day-label';
            el.textContent = h;
            daysContainer.appendChild(el);
        });

        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);

        // Shift for Mon-Sun (0 is Sun in JS, 1 is Mon)
        let startingDay = firstDay.getDay() - 1;
        if (startingDay === -1) startingDay = 6;

        // Fill empty
        for (let i = 0; i < startingDay; i++) {
            const el = document.createElement('div');
            el.className = 'day empty';
            daysContainer.appendChild(el);
        }

        const todayStr = new Date().toISOString().split('T')[0];

        // Fill days
        for (let i = 1; i <= lastDay.getDate(); i++) {
            const el = document.createElement('div');
            el.className = 'day';
            el.textContent = i;

            const dayStr = `${year}-${(month + 1).toString().padStart(2, '0')}-${i.toString().padStart(2, '0')}`;

            if (dayStr === todayStr) el.classList.add('today');
            if (visitDays.includes(dayStr)) el.classList.add('has-visit');
            if (dayStr < todayStr) el.classList.add('past');

            el.addEventListener('click', () => {
                document.querySelectorAll('.day').forEach(d => d.classList.remove('active'));
                el.classList.add('active');
                filterListByDate(dayStr);
            });

            daysContainer.appendChild(el);
        }
    }

    function filterListByDate(dateStr) {
        const cards = document.querySelectorAll('.appointment-card');
        let hasVisible = false;

        cards.forEach(card => {
            if (card.dataset.date === dateStr) {
                card.style.display = 'flex';
                card.style.animation = 'fadeIn 0.3s ease forwards';
                hasVisible = true;
            } else {
                card.style.display = 'none';
            }
        });

        // Add a "Show all" button if filtering
        updateFilterUI(dateStr, hasVisible);
    }

    function updateFilterUI(dateStr, hasVisible) {
        let banner = document.getElementById('filter-banner');
        if (!banner) {
            banner = document.createElement('div');
            banner.id = 'filter-banner';
            banner.style.cssText = 'background: #fff; padding: 1rem; border-radius: 1rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; border: 1px solid #e5e7eb;';
            const grid = document.querySelector('.appointments-grid');
            grid.parentNode.insertBefore(banner, grid);
        }

        const formattedDate = dateStr.split('-').reverse().join('.');
        banner.innerHTML = `
            <span>Pokazuję wizyty z dnia: <strong>${formattedDate}</strong> ${!hasVisible ? '(Brak)' : ''}</span>
            <button onclick="location.reload()" style="background: #000; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 0.5rem; cursor: pointer; font-size: 0.875rem; font-weight: 700;">Pokaż wszystkie</button>
        `;
    }

    prevBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    });

    nextBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    });

    renderCalendar();

    // Cancellation Logic (Re-integrated from previous implementation)
    const cancelButtons = document.querySelectorAll('.btn-cancel');
    cancelButtons.forEach(btn => {
        btn.addEventListener('click', async () => {
            const appointmentId = btn.dataset.id;
            if (!confirm('Czy na pewno chcesz anulować tę wizytę?')) return;

            const originalText = btn.textContent;
            btn.textContent = '...';
            btn.disabled = true;

            try {
                const response = await fetch('/cancelAppointment', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ appointment_id: appointmentId })
                });

                if (response.ok) location.reload();
                else {
                    const result = await response.json();
                    alert('Błąd: ' + (result.error || 'Nie udało się anulować.'));
                    btn.textContent = originalText;
                    btn.disabled = false;
                }
            } catch (error) {
                alert('Błąd połączenia.');
                btn.textContent = originalText;
                btn.disabled = false;
            }
        });
    });
});
