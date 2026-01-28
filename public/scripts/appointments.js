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

    const appointmentsGrid = document.querySelector('.appointments-grid');
    const searchInput = document.getElementById('appointment-search');
    let searchTimeout;

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

    function renderAppointments(appointments) {
        appointmentsGrid.innerHTML = '';

        if (appointments.length === 0) {
            appointmentsGrid.innerHTML = `
                <div class="empty-state">
                    <h2 class="empty-title">Brak wizyt</h2>
                    <p class="empty-text">Nie znaleziono wizyt pasujących do Twoich kryteriów.</p>
                </div>
            `;
            return;
        }

        appointments.forEach(appt => {
            const dateObj = new Date(appt.appointment_date);
            const dateStr = dateObj.toLocaleDateString('pl-PL', { day: '2-digit', month: '2-digit', year: 'numeric' });
            const timeStr = dateObj.toLocaleTimeString('pl-PL', { hour: '2-digit', minute: '2-digit' });
            const dbDate = appt.appointment_date.split(' ')[0];

            let statusClass = 'badge-neutral';
            let statusLabel = appt.status;

            if (appt.status === 'confirmed') {
                statusClass = 'badge-success';
                statusLabel = 'Potwierdzona';
            } else if (appt.status === 'cancelled') {
                statusClass = 'badge-error';
                statusLabel = 'Anulowana';
            } else if (appt.status === 'pending') {
                statusClass = 'badge-pending';
                statusLabel = 'Oczekująca';
            } else if (appt.status === 'completed' || appt.status === 'finished') {
                statusClass = 'badge-finished';
                statusLabel = 'Zakończona';
            }

            const card = document.createElement('div');
            card.className = 'appointment-card';
            card.dataset.date = dbDate;

            let footerAction = '';
            if (appt.status === 'pending' || appt.status === 'confirmed') {
                footerAction = `<button class="btn-cancel" data-id="${appt.id}">Anuluj</button>`;
            } else if ((appt.status === 'completed' || appt.status === 'finished') && !appt.is_reviewed) {
                footerAction = `
                    <button class="btn-review" data-id="${appt.id}" 
                        data-business-name="${appt.business_name}" 
                        data-service-name="${appt.service_name}">
                        Dodaj opinię
                    </button>
                `;
            } else if (appt.is_reviewed) {
                footerAction = `
                    <span class="review-done-label">
                        <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle;">check_circle</span>
                        Opinia wystawiona
                    </span>
                `;
            }

            card.innerHTML = `
                <div class="card-header">
                    <div>
                        <h3 class="service-name">${appt.service_name}</h3>
                        <p class="business-name">${appt.business_name}</p>
                    </div>
                    <span class="price-tag">${appt.price} PLN</span>
                </div>
                <div class="card-details">
                    <div class="detail-item">
                        <span class="material-symbols-outlined">calendar_today</span>
                        <span>${dateStr}</span>
                    </div>
                    <div class="detail-item">
                        <span class="material-symbols-outlined">schedule</span>
                        <span>${timeStr}</span>
                    </div>
                </div>
                <div class="card-footer">
                    <span class="badge ${statusClass}">${statusLabel}</span>
                    ${footerAction}
                </div>
            `;

            // Attach listeners for dynamic elements
            if (footerAction.includes('btn-cancel')) {
                card.querySelector('.btn-cancel').addEventListener('click', () => handleCancel(appt.id));
            }
            if (footerAction.includes('btn-review')) {
                card.querySelector('.btn-review').addEventListener('click', (e) => handleReviewClick(e.target.dataset));
            }

            appointmentsGrid.appendChild(card);
        });
    }

    async function handleSearch() {
        const query = searchInput.value;
        try {
            const response = await fetch(`/searchAppointments?search=${encodeURIComponent(query)}`);
            const data = await response.json();
            renderAppointments(data);
        } catch (error) {
            console.error('Search error:', error);
        }
    }

    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(handleSearch, 300);
    });

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

    // Re-bindable handlers
    async function handleCancel(appointmentId) {
        if (!confirm('Czy na pewno chcesz anulować tę wizytę?')) return;
        try {
            const response = await fetch('/cancelAppointment', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ appointment_id: appointmentId })
            });

            if (response.ok) handleSearch(); // Refresh list after cancel
            else {
                const result = await response.json();
                alert('Błąd: ' + (result.error || 'Nie udało się anulować.'));
            }
        } catch (error) {
            alert('Błąd połączenia.');
        }
    }

    function handleReviewClick(data) {
        activeAppointmentId = data.id;
        reviewBusinessName.textContent = `${data.businessName} - ${data.serviceName}`;
        reviewModal.style.display = 'flex';
    }

    const closeModal = document.querySelector('.close-modal');
    const submitReviewBtn = document.getElementById('submit-review');
    const reviewBusinessName = document.getElementById('review-business-name');
    const commentInput = document.getElementById('review-comment');

    let activeAppointmentId = null;

    reviewBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            activeAppointmentId = btn.dataset.id;
            reviewBusinessName.textContent = `${btn.dataset.businessName} - ${btn.dataset.serviceName}`;
            reviewModal.style.display = 'flex';
        });
    });

    closeModal.addEventListener('click', () => {
        reviewModal.style.display = 'none';
        resetReviewForm();
    });

    window.addEventListener('click', (e) => {
        if (e.target === reviewModal) {
            reviewModal.style.display = 'none';
            resetReviewForm();
        }
    });

    submitReviewBtn.addEventListener('click', async () => {
        const ratingInput = document.querySelector('input[name="rating"]:checked');
        if (!ratingInput) {
            alert('Proszę wybrać ocenę (gwiazdki).');
            return;
        }

        const rating = ratingInput.value;
        const comment = commentInput.value;

        submitReviewBtn.disabled = true;
        submitReviewBtn.textContent = 'Trwa wysyłanie...';

        try {
            const response = await fetch('/addReview', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    appointment_id: activeAppointmentId,
                    rating: rating,
                    comment: comment
                })
            });

            const result = await response.json();

            if (response.ok) {
                // alert('Dziękujemy za opinię!');
                location.reload();
            } else {
                alert('Błąd: ' + (result.error || 'Nie udało się dodać opinii.'));
                submitReviewBtn.disabled = false;
                submitReviewBtn.textContent = 'Wyślij opinię';
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Wystąpił błąd połączenia.');
            submitReviewBtn.disabled = false;
            submitReviewBtn.textContent = 'Wyślij opinię';
        }
    });

    function resetReviewForm() {
        activeAppointmentId = null;
        commentInput.value = '';
        const checked = document.querySelector('input[name="rating"]:checked');
        if (checked) checked.checked = false;
    }
});
