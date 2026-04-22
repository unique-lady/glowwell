document.querySelectorAll('.log-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        let challengeId = this.getAttribute('data-id');
        
        fetch('update_progress.php', {
            method: 'POST',
            body: 'challenge_id=' + challengeId,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        })
        .then(res => res.text())
        .then(data => {
            if(data === "success") {
                alert("Great job! Your progress has been updated.");
                location.reload();
            } else {
                alert("You have already logged your progress for today!");
            }
        });
    });
});