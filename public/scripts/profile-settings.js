document.addEventListener('DOMContentLoaded', () => {
    const emailNotif = document.getElementById('email_notifications');
    const smsNotif = document.getElementById('sms_notifications');

    function update() {
        const data = {
            email_notifications: emailNotif.checked,
            sms_notifications: smsNotif.checked
        };

        fetch('/updateSettings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    console.log('Zapisano!');
                } else {
                    console.error('Błąd zapisu');
                }
            })
            .catch(err => console.error('Błąd sieci:', err));
    }

    emailNotif.addEventListener('change', update);
    smsNotif.addEventListener('change', update);

    // Cancel Appointment Logic
    const cancelButtons = document.querySelectorAll('.btn-cancel-appointment');

    cancelButtons.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const appointmentId = btn.dataset.id;

            if (!confirm('Czy na pewno chcesz anulować tę rezerwację?')) {
                return;
            }

            const originalText = btn.textContent;
            btn.textContent = '...';
            btn.disabled = true;

            try {
                const response = await fetch('/cancelAppointment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ appointment_id: appointmentId })
                });

                const result = await response.json();

                if (response.ok) {
                    // alert('Rezerwacja anulowana.');
                    location.reload();
                } else {
                    alert('Błąd: ' + (result.error || 'Nie udało się anulować.'));
                    btn.textContent = originalText;
                    btn.disabled = false;
                }
            } catch (error) {
                console.error(error);
                alert('Błąd połączenia.');
                btn.textContent = originalText;
                btn.disabled = false;
            }
        });
    });
});