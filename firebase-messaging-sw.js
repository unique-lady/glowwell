// استيراد مكتبات Firebase للعمل في الخلفية
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js');

// تهيئة Firebase داخل ملف الخدمة
firebase.initializeApp({
    apiKey: "AIzaSyCI4EA4ZdYMeMNwtOfyFIHrk2bHbdKHYcs",
    projectId: "glowwell-ac819",
    messagingSenderId: "658820604328",
    appId: "1:658820604328:web:c680a36e6af611e2b4fd9d"
});

const messaging = firebase.messaging();

// هذا الكود هو المسؤول عن إظهار الإشعار عند وصوله
messaging.onBackgroundMessage((payload) => {
  console.log('[firebase-messaging-sw.js] Received background message ', payload);
  const notificationTitle = payload.notification.title;
  const notificationOptions = {
    body: payload.notification.body,
    icon: '/logo.png' // تأكدي أن مسار الأيقونة صحيح أو احذفي هذا السطر
  };
  self.registration.showNotification(notificationTitle, notificationOptions);
});