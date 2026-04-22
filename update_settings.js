function updateSetting(column, isChecked) {
    let value = isChecked ? 1 : 0;

    // إرسال البيانات لملف المعالجة PHP
    fetch('update_settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `column=${encodeURIComponent(column)}&value=${value}`
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        if(data.success) {
            console.log(`${column} updated successfully!`);
        } else {
            console.error("Failed to update database.");
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}