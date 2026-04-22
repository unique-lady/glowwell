/**
 * تعرف على الوجبات - رفع صورة / كاميرا وإرسال النموذج
 */
(function() {
    'use strict';

    var $form = $('#mainForm');
    var $srcFile = $('#srcFile');
    var $srcCamera = $('#srcCamera');
    var $fileSource = $('#fileSource');
    var $cameraSource = $('#cameraSource');
    var $fileInput = $('#fileInput');
    var $cameraFileInput = $('#cameraFileInput');
    var $video = $('#cameraVideo')[0];
    var $captureBtn = $('#captureBtn');
    var $capturePreview = $('#capturePreview')[0];
    var stream = null;
    /** صورة مأخوذة من الكاميرا (blob) - نستخدمها عند الإرسال إذا كان input.files غير قابل للكتابة */
    var capturedBlob = null;

    function showCameraError(msg) {
        var box = document.getElementById('cameraSource');
        var existing = box.querySelector('.camera-error-msg');
        if (existing) existing.remove();
        var p = document.createElement('p');
        p.className = 'camera-error-msg text-danger small mt-2';
        p.textContent = msg || (window.GW_SCAN_T ? window.GW_SCAN_T('camera_error') : 'تعذّر الوصول للكاميرا. تحقق من الصلاحيات أو استخدم «رفع من الملف».');
        box.appendChild(p);
    }

    function clearCameraError() {
        var el = document.querySelector('.camera-error-msg');
        if (el) el.remove();
    }

    function switchSource() {
        var isFile = $srcFile.is(':checked');
        $fileSource.toggleClass('d-block', isFile).toggleClass('d-none', !isFile);
        $cameraSource.toggleClass('d-block', !isFile).toggleClass('d-none', isFile);
        if (isFile) {
            stopCamera();
            capturedBlob = null;
            clearCameraError();
        } else {
            $captureBtn.prop('disabled', true).text(window.GW_SCAN_T ? window.GW_SCAN_T('camera_loading') : 'جاري تشغيل الكاميرا...');
            startCamera();
        }
    }

    function startCamera() {
        if (stream) return;
        clearCameraError();
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            showCameraError(window.GW_SCAN_T ? window.GW_SCAN_T('browser_no_camera') : 'المتصفح لا يدعم الكاميرا. استخدم «رفع من الملف».');
            return;
        }
        var constraints = { video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } }, audio: false };
        function onVideoReady() {
            $video.removeEventListener('loadedmetadata', onVideoReady);
            $video.removeEventListener('playing', onVideoReady);
            updateCaptureButtonState();
        }
        $video.addEventListener('loadedmetadata', onVideoReady);
        $video.addEventListener('playing', onVideoReady);
        navigator.mediaDevices.getUserMedia(constraints)
            .then(function(s) {
                stream = s;
                $video.srcObject = s;
                return $video.play ? $video.play() : Promise.resolve();
            })
            .catch(function(err) {
                return navigator.mediaDevices.getUserMedia({ video: true, audio: false });
            })
            .then(function(s) {
                if (s && !stream) {
                    stream = s;
                    $video.srcObject = s;
                }
                if (stream && $video.srcObject) {
                    if ($video.play) $video.play().catch(function() {});
                }
                updateCaptureButtonState();
            })
            .catch(function(err) {
                showCameraError(window.GW_SCAN_T ? window.GW_SCAN_T('camera_start_error') : 'لم يتمكن من تشغيل الكاميرا. تحقق من السماح للموقع بالكاميرا أو استخدم «رفع من الملف».');
                updateCaptureButtonState();
            });
    }

    function updateCaptureButtonState() {
        var ready = stream && $video.videoWidth > 0;
        $captureBtn.prop('disabled', !ready);
        $captureBtn.text(ready ? (window.GW_SCAN_T ? window.GW_SCAN_T('capture_btn') : 'التقاط صورة') : (window.GW_SCAN_T ? window.GW_SCAN_T('camera_loading') : 'جاري تشغيل الكاميرا...'));
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(function(t) { t.stop(); });
            stream = null;
            $video.srcObject = null;
        }
    }

    function captureFromCamera() {
        if (!stream || !$video.videoWidth) return;
        var canvas = document.createElement('canvas');
        canvas.width = $video.videoWidth;
        canvas.height = $video.videoHeight;
        canvas.getContext('2d').drawImage($video, 0, 0);
        canvas.toBlob(function(blob) {
            if (!blob) return;
            capturedBlob = blob;
            var file = new File([blob], 'capture.jpg', { type: 'image/jpeg' });
            try {
                var dt = new DataTransfer();
                dt.items.add(file);
                $cameraFileInput[0].files = dt.files;
            } catch (e) {
                // بعض المتصفحات لا تسمح بتعيين files - سنعتمد على capturedBlob عند الإرسال
            }
            if ($capturePreview.src) URL.revokeObjectURL($capturePreview.src);
            $capturePreview.src = URL.createObjectURL(blob);
            $capturePreview.classList.add('has-image');
        }, 'image/jpeg', 0.92);
    }

    $srcFile.on('change', switchSource);
    $srcCamera.on('change', switchSource);
    $captureBtn.on('click', captureFromCamera);

    $form.on('submit', function(e) {
        var isFile = $srcFile.is(':checked');
        if (isFile) {
            if (!$fileInput[0].files.length) {
                e.preventDefault();
                alert(window.GW_SCAN_T ? window.GW_SCAN_T('choose_file_first') : 'اختر صورة من الملف أولاً.');
                return;
            }
            $cameraFileInput.removeAttr('name');
            $fileInput.attr('name', 'image');
        } else {
            var hasFile = $cameraFileInput[0].files && $cameraFileInput[0].files.length > 0;
            if (!hasFile && !capturedBlob) {
                e.preventDefault();
                alert(window.GW_SCAN_T ? window.GW_SCAN_T('capture_first') : 'اضغط «التقاط صورة» أولاً ثم «تحليل الصورة».');
                return;
            }
            $fileInput.removeAttr('name');
            $cameraFileInput.attr('name', 'image');
            if (hasFile) {
                capturedBlob = null;
                return;
            }
            if (capturedBlob) {
                e.preventDefault();
                var formData = new FormData($form[0]);
                formData.delete('image');
                formData.append('image', capturedBlob, 'capture.jpg');
                $captureBtn.prop('disabled', true).text(window.GW_SCAN_T ? window.GW_SCAN_T('analyzing') : 'جاري التحليل...');
                var submitUrl = ($form.attr('action') && $form.attr('action').trim()) ? $form.attr('action') : window.location.href;
                fetch(submitUrl, { method: 'POST', body: formData })
                    .then(function(res) { return res.text(); })
                    .then(function(html) {
                        document.open();
                        document.write(html);
                        document.close();
                    })
                    .catch(function() {
                        alert(window.GW_SCAN_T ? window.GW_SCAN_T('submit_error') : 'حدث خطأ أثناء الإرسال.');
                        $captureBtn.prop('disabled', false).text(window.GW_SCAN_T ? window.GW_SCAN_T('capture_btn') : 'التقاط صورة');
                    });
            }
        }
    });

    if ($srcCamera.is(':checked')) {
        startCamera();
    }
})();
