/**
 * تعرف على الوجبات - ترجمة عربي / إنجليزي
 */
window.GW_SCAN_I18N = {
  ar: {
    title: 'تعرف على ',
    title_accent: 'الوجبات',
    subtitle: 'ارفع صورة أو التقط من الكاميرا لمعرفة المحتوى والسعرات والمغذيات',
    image_source: 'مصدر الصورة',
    upload_file: 'رفع من الملف',
    from_camera: 'التصوير من الكاميرا',
    capture_btn: 'التقاط صورة',
    preview: 'معاينة',
    analysis_type: 'نوع التحليل',
    mode_meals: 'تعرف على الوجبات (سعرات ومغذيات)',
    mode_ocr: 'استخراج النص (OCR)',
    analyze_btn: 'تحليل الصورة',
    image_and_content: 'الصورة ومحتواها',
    image_alt: 'الصورة',
    vision_detected: 'ما اكتشفه Vision:',
    mode_ocr_label: 'وضع التحليل: استخراج النص (OCR)',
    output_title: 'المخرجات (السعرات والمغذيات)',
    matched_items: 'الأصناف المطابقة مع قاعدة المغذيات:',
    no_matched: 'لا توجد أصناف مطابقة من قاعدة المغذيات.',
    download_text: 'تحميل النص',
    no_text_extracted: 'لم يُستخرج أي نص.',
    api_note: 'Google Vision API — يوجد حد مجاني شهري (حوالي 1000 طلب)، وبعدها الخدمة مدفوعة.',
    total: 'المجموع',
    protein: 'بروتين',
    carbs: 'كربوهيدرات',
    carbs_short: 'كارب',
    fat: 'دهون',
    cal_unit: 'سعرة',
    gram_unit: 'غ',
    camera_loading: 'جاري تشغيل الكاميرا...',
    camera_error: 'تعذّر الوصول للكاميرا. تحقق من الصلاحيات أو استخدم «رفع من الملف».',
    browser_no_camera: 'المتصفح لا يدعم الكاميرا. استخدم «رفع من الملف».',
    camera_start_error: 'لم يتمكن من تشغيل الكاميرا. تحقق من السماح للموقع بالكاميرا أو استخدم «رفع من الملف».',
    choose_file_first: 'اختر صورة من الملف أولاً.',
    capture_first: 'اضغط «التقاط صورة» أولاً ثم «تحليل الصورة».',
    analyzing: 'جاري التحليل...',
    submit_error: 'حدث خطأ أثناء الإرسال.'
  },
  en: {
    title: 'Recognize ',
    title_accent: 'Meals',
    subtitle: 'Upload an image or capture from camera to see content, calories and nutrients',
    image_source: 'Image source',
    upload_file: 'Upload from file',
    from_camera: 'Capture from camera',
    capture_btn: 'Capture image',
    preview: 'Preview',
    analysis_type: 'Analysis type',
    mode_meals: 'Recognize meals (calories & nutrients)',
    mode_ocr: 'Extract text (OCR)',
    analyze_btn: 'Analyze image',
    image_and_content: 'Image and content',
    image_alt: 'Image',
    vision_detected: 'What Vision detected:',
    mode_ocr_label: 'Analysis mode: Extract text (OCR)',
    output_title: 'Output (calories & nutrients)',
    matched_items: 'Items matched with nutrition database:',
    no_matched: 'No items matched from nutrition database.',
    download_text: 'Download text',
    no_text_extracted: 'No text was extracted.',
    api_note: 'Google Vision API — free tier (about 1000 requests/month), then paid.',
    total: 'Total',
    protein: 'Protein',
    carbs: 'Carbs',
    carbs_short: 'Carbs',
    fat: 'Fat',
    cal_unit: 'cal',
    gram_unit: 'g',
    camera_loading: 'Starting camera...',
    camera_error: 'Could not access camera. Check permissions or use "Upload from file".',
    browser_no_camera: 'Browser does not support camera. Use "Upload from file".',
    camera_start_error: 'Could not start camera. Allow camera for this site or use "Upload from file".',
    choose_file_first: 'Please choose an image file first.',
    capture_first: 'Tap "Capture image" first, then "Analyze image".',
    analyzing: 'Analyzing...',
    submit_error: 'An error occurred while submitting.'
  }
};

window.GW_SCAN_LANG = localStorage.getItem('glowwell_scan_lang') || 'ar';

window.GW_SCAN_T = function(key) {
  var lang = window.GW_SCAN_LANG;
  return (window.GW_SCAN_I18N[lang] && window.GW_SCAN_I18N[lang][key]) || key;
};

window.GW_SCAN_APPLY = function() {
  var lang = window.GW_SCAN_LANG;
  var t = window.GW_SCAN_I18N[lang];
  if (!t) return;
  document.querySelectorAll('[data-i18n]').forEach(function(el) {
    var k = el.getAttribute('data-i18n');
    if (t[k] !== undefined) el.textContent = t[k];
  });
  document.querySelectorAll('[data-i18n-html]').forEach(function(el) {
    var k = el.getAttribute('data-i18n-html');
    if (t[k] !== undefined) el.innerHTML = t[k];
  });
  document.querySelectorAll('.meal-meta-i18n').forEach(function(el) {
    var g = el.getAttribute('data-grams'), c = el.getAttribute('data-cal'), p = el.getAttribute('data-protein'), cb = el.getAttribute('data-carbs'), f = el.getAttribute('data-fat');
    if (g !== null && c !== null) el.textContent = '~' + g + ' ' + (t.gram_unit || 'g') + ' · ' + c + ' ' + (t.cal_unit || 'cal') + ' · ' + (t.protein || '') + ' ' + p + (t.gram_unit || 'g') + ' · ' + (t.carbs_short || 'Carbs') + ' ' + cb + (t.gram_unit || 'g') + ' · ' + (t.fat || '') + ' ' + f + (t.gram_unit || 'g');
  });
  var resultImg = document.getElementById('resultImgAlt');
  if (resultImg && t.image_alt) resultImg.alt = t.image_alt;
  document.documentElement.lang = lang === 'ar' ? 'ar' : 'en';
  document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
  document.title = (lang === 'ar' ? t.title + ' - ' + t.title_accent : t.title + ' ' + t.title_accent) || document.title;
  if (window.GW_SCAN_UPDATE_SWITCHER) window.GW_SCAN_UPDATE_SWITCHER();
};

window.GW_SCAN_SET_LANG = function(lang) {
  window.GW_SCAN_LANG = lang;
  localStorage.setItem('glowwell_scan_lang', lang);
  window.GW_SCAN_APPLY();
};
