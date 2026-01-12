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
});