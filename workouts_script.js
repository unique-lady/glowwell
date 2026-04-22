
let timer;
let timeLeft;
let totalTime;

function openTrainer(name, steps, duration, icon) {
    document.getElementById('m-name').innerText = name;
    document.getElementById('m-icon').innerText = icon;
    document.getElementById('timerText').innerText = duration;
    
    // تقسيم الخطوات وعرضها
    const stepsArray = steps.split('|');
    document.getElementById('m-steps').innerHTML = stepsArray.map(s => `<p class='text-gray-700 font-medium'>• ${s}</p>`).join('');
    
    timeLeft = duration;
    totalTime = duration;
    updateCircle(0);

    // إظهار النافذة وإعادة ضبط الأزرار
    document.getElementById('trainerModal').classList.remove('hidden');
    document.getElementById('startBtn').classList.remove('hidden');
    document.getElementById('doneBtn').classList.add('hidden');
}

function startTimer() {
    // إخفاء زر البداية عند بدء العد
    document.getElementById('startBtn').classList.add('hidden');
    
    timer = setInterval(() => {
        timeLeft--;
        document.getElementById('timerText').innerText = timeLeft;
        
        let offset = ((totalTime - timeLeft) / totalTime) * 377;
        updateCircle(offset);

        if (timeLeft <= 0) {
            clearInterval(timer);
            // إظهار زر Well Done عند انتهاء الوقت
            document.getElementById('doneBtn').classList.remove('hidden');
            document.getElementById('timerText').innerText = "💪";
        }
    }, 1000);
}

function updateCircle(offset) {
    const circle = document.getElementById('timerCircle');
    if(circle) circle.style.strokeDashoffset = offset;
}

function closeTrainer() {
    clearInterval(timer);
    document.getElementById('trainerModal').classList.add('hidden');
}

// الدالة المسؤولة عن الحفظ والتحويل
function completeWorkout() {
    const workoutName = document.getElementById('m-name').innerText;
    const duration = totalTime; 

    // إرسال البيانات باستخدام FormData لضمان وصولها لـ PHP
    const formData = new URLSearchParams();
    formData.append('workout_name', workoutName);
    formData.append('duration', duration);

    fetch('save_workout.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    })
    .then(response => response.text())
    .then(data => {
        console.log("Response from server:", data);
        if(data.trim() === "success") {
            // التحويل لصفحة البروجرس فوراً عند النجاح
            window.location.href = 'progress.php';
        } else {
            alert("حدث خطأ في الحفظ: " + data);
        }
    })
    .catch(err => {
        console.error("Fetch error:", err);
        alert("فشل الاتصال بالسيرفر");
    });
}