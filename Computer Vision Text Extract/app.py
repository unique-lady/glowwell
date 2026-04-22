# -*- coding: utf-8 -*-
"""
مشروع تعرف على الوجبات – يعتمد فقط على مفتاح Google Vision API.
استخراج النص (OCR) + تعرف على الوجبات مع المغذيات.
"""
from flask import Flask, request, render_template, send_file
import os
import json
import requests
import base64
import re

app = Flask(__name__)

# مفتاح Google Vision API (نفس المفتاح لـ OCR والتعرف على الوجبات)
API_KEY = 'AIzaSyDzGSBIG3dX6oyKVgsUmAzH0s597EWPAQg'

# تحميل قاعدة المغذيات من مشروع الوجبات
def load_nutrition_db():
    path = os.path.join(os.path.dirname(__file__), 'data', 'nutrition-db.json')
    if os.path.exists(path):
        with open(path, 'r', encoding='utf-8') as f:
            data = json.load(f)
            return data.get('foods', data) if isinstance(data, dict) else data
    return []

NUTRITION_DB = load_nutrition_db()

# خريطة تسميات Vision إلى معرفات الأصناف — فقط تطابق واضح (بدون اختراع بيانات)
# لا نربط أبداً: soup، food، meal، dish، plate، أو أدوات/أواني
LABEL_TO_FOOD_ID = {
    'chicken': 'chicken', 'poultry': 'chicken', 'broiler': 'chicken', 'kabsa': 'chicken',
    'beef': 'beef', 'meat': 'beef', 'steak': 'beef', 'ribs': 'beef', 'lamb': 'beef', 'mutton': 'beef',
    'fish': 'fish', 'seafood': 'fish', 'salmon': 'fish', 'tuna': 'fish', 'shrimp': 'fish', 'prawn': 'fish',
    'rice': 'rice', 'grain': 'rice', 'cereal': 'rice', 'basmati': 'basmati', 'jasmine': 'rice',
    'bread': 'bread', 'toast': 'bread', 'bun': 'bread', 'bagel': 'bread', 'pita': 'bread',
    'pasta': 'pasta', 'noodle': 'pasta', 'spaghetti': 'pasta', 'macaroni': 'pasta',
    'egg': 'egg', 'eggs': 'egg', 'omelette': 'egg',
    'cheese': 'cheese', 'cheddar': 'cheese', 'mozzarella': 'cheese',
    'yogurt': 'yogurt', 'dairy': 'yogurt',
    'salad': 'salad', 'vegetable': 'salad', 'lettuce': 'salad', 'greens': 'salad',
    'tomato': 'tomato', 'tomatoes': 'tomato',
    'cucumber': 'cucumber', 'cucumbers': 'cucumber',
    'onion': 'onion', 'onions': 'onion',
    'broccoli': 'broccoli', 'cauliflower': 'cauliflower', 'carrot': 'carrot', 'carrots': 'carrot',
    'potato': 'potato', 'potatoes': 'potato',
    'fries': 'potato', 'frenchfries': 'potato', 'friedpotatoes': 'potato', 'frenchfry': 'potato',
    'chip': 'potato', 'chips': 'potato',
    'burger': 'beef', 'hamburger': 'beef', 'cheeseburger': 'beef', 'fastfood': 'beef',
    'sandwich': 'bread', 'hotdog': 'beef',
    'lentil': 'lentils', 'lentils': 'lentils', 'legume': 'lentils',
    'beans': 'beans', 'bean': 'beans',
    'hummus': 'hummus', 'chickpea': 'hummus',
    'falafel': 'falafel',
    'apple': 'apple', 'apples': 'apple', 'banana': 'banana', 'bananas': 'banana',
    'orange': 'orange', 'oranges': 'orange', 'grape': 'grape', 'grapes': 'grape',
    'strawberry': 'strawberry', 'strawberries': 'strawberry',
    'watermelon': 'watermelon', 'lemon': 'lemon', 'lemons': 'lemon',
    'lime': 'lime', 'limes': 'lime', 'grapefruit': 'grapefruit',
    'peach': 'peach', 'peaches': 'peach', 'pear': 'pear', 'pears': 'pear',
    'plum': 'plum', 'plums': 'plum', 'apricot': 'apricot', 'apricots': 'apricot',
    'cherry': 'cherry', 'cherries': 'cherry', 'mango': 'mango', 'mangoes': 'mango',
    'pineapple': 'pineapple', 'pineapples': 'pineapple', 'kiwi': 'kiwi', 'kiwis': 'kiwi',
    'pomegranate': 'pomegranate', 'pomegranates': 'pomegranate',
    'fig': 'fig', 'figs': 'fig', 'date': 'date', 'dates': 'date',
    'avocado': 'avocado', 'avocados': 'avocado', 'coconut': 'coconut', 'coconuts': 'coconut',
    'blueberry': 'blueberry', 'blueberries': 'blueberry', 'raspberry': 'raspberry', 'raspberries': 'raspberry',
    'blackberry': 'blackberry', 'blackberries': 'blackberry', 'cranberry': 'cranberry', 'cranberries': 'cranberry',
    'papaya': 'papaya', 'melon': 'melon', 'melons': 'melon', 'cantaloupe': 'melon', 'honeydew': 'melon',
    'nectarine': 'nectarine', 'nectarines': 'nectarine', 'tangerine': 'tangerine', 'tangerines': 'tangerine',
    'mandarin': 'tangerine', 'clementine': 'tangerine', 'guava': 'guava', 'guavas': 'guava',
    'pizza': 'bread', 'donut': 'bread', 'cake': 'bread',
    'friedfood': 'potato',
    'noodlespasta': 'pasta', 'vegetablefruit': 'salad',
    'soup': 'soup', 'bisque': 'soup', 'tarhana': 'soup', 'ezogelinsoup': 'soup', 'tourin': 'soup',
    'sauce': 'sauce', 'dippingsauce': 'sauce', 'raita': 'raita',
    'zucchini': 'zucchini', 'pepper': 'pepper', 'bellpepper': 'pepper',
    'whiterice': 'rice', 'cookedrice': 'rice', 'glutinousrice': 'rice',
    'lettuce': 'lettuce', 'spinach': 'spinach', 'kale': 'kale', 'cabbage': 'cabbage',
    'eggplant': 'eggplant', 'eggplants': 'eggplant', 'aubergine': 'eggplant',
    'garlic': 'garlic', 'ginger': 'ginger', 'celery': 'celery', 'asparagus': 'asparagus',
    'greenbeans': 'green_beans', 'greenbean': 'green_beans', 'peas': 'peas', 'pea': 'peas',
    'corn': 'corn', 'sweetpotato': 'sweet_potato', 'sweetpotatoes': 'sweet_potato',
    'pumpkin': 'pumpkin', 'pumpkins': 'pumpkin', 'squash': 'pumpkin',
    'beet': 'beet', 'beets': 'beet', 'beetroot': 'beet', 'radish': 'radish', 'radishes': 'radish',
    'mushroom': 'mushroom', 'mushrooms': 'mushroom', 'okra': 'okra',
    'artichoke': 'artichoke', 'artichokes': 'artichoke', 'leek': 'leek', 'leeks': 'leek',
    'turnip': 'turnip', 'turnips': 'turnip', 'arugula': 'arugula', 'rocket': 'arugula',
}

# تسميات عامة — لا نربطها بأصناف (لتجنب ظهور "جبن" عند صورة شوربة لأن Vision يعيد "Dairy product")
NON_FOOD_LABELS = {
    'food', 'meal', 'dish', 'plate', 'recipe', 'condiment', 'ingredient',
    'serveware', 'tableware', 'dishware', 'bowl', 'spoon', 'kitchen utensil',
    'cuisine', 'breakfast', 'brunch', 'produce', 'finger food', 'vegetable', 'fruit',
    'side dish', 'sidedish', 'staple food', 'cooking', 'lunch', 'dinner', 'future',
    'dairy', 'dairy product', 'dairyproduct', 'dessert', 'mixture', 'comfort food',
}


def find_food_by_keyword(keyword, db):
    """ يبحث في قاعدة المغذيات عن صنف يطابق الكلمة. لا يعيد أي صنف افتراضي. """
    k = (keyword or '').lower()
    for f in db:
        if (f.get('nameEn') and k in f['nameEn'].lower()) or (f.get('nameAr') and keyword in f.get('nameAr', '')):
            return f
    return None


def _is_non_food_label(description):
    """تسميات عامة أو أدوات — لا نعاملها كصنف طعام."""
    if not description:
        return True
    low = (description or '').lower().strip()
    key = low.replace(' ', '')
    if key in NON_FOOD_LABELS or low in NON_FOOD_LABELS:
        return True
    for nf in NON_FOOD_LABELS:
        if nf in key or nf in low:
            return True
    return False


def map_vision_label_to_food(description, db):
    if _is_non_food_label(description):
        return None
    label = (description or '').lower().strip()
    key = label.replace(' ', '')
    if key in LABEL_TO_FOOD_ID:
        return LABEL_TO_FOOD_ID[key]
    for label_key, food_id in LABEL_TO_FOOD_ID.items():
        if label_key in key or label_key in label:
            return food_id
    for f in db:
        name_en = (f.get('nameEn') or '').lower()
        fid = f.get('id', '')
        if name_en and (name_en in key or fid in key or name_en in label):
            return fid
    found = find_food_by_keyword(description, db)
    return found['id'] if found else None


def labels_to_food_items(labels, db):
    if not labels or not db:
        return []
    all_desc = [ (l.get('description') or '').lower() for l in labels ]
    is_burger = any(
        'hamburger' in d or 'cheeseburger' in d or 'burger' in d or 'sandwich' in d
        for d in all_desc
    )
    if is_burger:
        has_fries = any(
            'fries' in d or 'french fry' in d or 'fried potato' in d or 'potato' in d
            for d in all_desc
        )
        components = [
            ('beef', 110), ('bread', 85), ('cheese', 25), ('salad', 25), ('tomato', 15)
        ]
        if has_fries:
            components.append(('potato', 100))
        out = []
        for fid, grams in components:
            food = next((f for f in db if f.get('id') == fid), None)
            if food:
                out.append({
                    'foodId': food['id'],
                    'nameAr': food.get('nameAr', ''),
                    'nameEn': food.get('nameEn', ''),
                    'estimatedGrams': grams,
                    'score': 0.9,
                })
        return out

    seen = set()
    by_id = {}
    for label in labels[:10]:
        desc = (label.get('description') or '').strip()
        if not desc or desc.lower() in seen:
            continue
        food_id = map_vision_label_to_food(desc, db)
        food = next((f for f in db if f.get('id') == food_id), None)
        if not food:
            continue
        seen.add(desc.lower())
        score = label.get('score', 0.8)
        grams = round(80 + score * 120)
        if food_id in by_id:
            prev = by_id[food_id]
            prev['estimatedGrams'] = min(prev['estimatedGrams'] + round(grams * 0.3), 250)
        else:
            by_id[food_id] = {
                'foodId': food['id'],
                'nameAr': food.get('nameAr', ''),
                'nameEn': food.get('nameEn', ''),
                'estimatedGrams': grams,
                'score': score,
            }
    return list(by_id.values())


def compute_nutrition(item, db):
    food = next((f for f in db if f.get('id') == item['foodId']), None)
    if not food:
        return item
    g = item.get('estimatedGrams', 100) / 100.0
    item['calories'] = round(food.get('caloriesPer100g', 0) * g)
    item['protein'] = round(food.get('proteinPer100g', 0) * g, 1)
    item['carbs'] = round(food.get('carbsPer100g', 0) * g, 1)
    item['fat'] = round(food.get('fatPer100g', 0) * g, 1)
    return item


# --- استخراج النص (OCR) ---
def extract_text_from_image(api_key, image_file):
    image_file.seek(0)
    base64_image = base64.b64encode(image_file.read()).decode('utf-8')
    payload = {
        "requests": [{
            "image": {"content": base64_image},
            "features": [{"type": "TEXT_DETECTION"}]
        }]
    }
    url = f'https://vision.googleapis.com/v1/images:annotate?key={api_key}'
    r = requests.post(url, headers={'Content-Type': 'application/json'}, data=json.dumps(payload), timeout=30)
    data = r.json()
    try:
        ann = data.get('responses', [{}])[0].get('textAnnotations', [])
        return ann[0].get('description', '') if ann else ''
    except (IndexError, KeyError):
        return ''


def format_extracted_text(text):
    text = re.sub(r'\n+', '\n', text)
    text = re.sub(r'\\u[0-9a-fA-F]{4}', '', text)
    text = re.sub(r'\s{2,}', ' ', text).strip()
    return text


# --- تعرف على الوجبات: Labels + Web Detection للحصول على تسميات أوضح (نوع الطعام) ---
def recognize_meals(api_key, image_file):
    image_file.seek(0)
    base64_image = base64.b64encode(image_file.read()).decode('utf-8')
    payload = {
        "requests": [{
            "image": {"content": base64_image},
            "features": [
                {"type": "LABEL_DETECTION", "maxResults": 50},
                {"type": "WEB_DETECTION", "maxResults": 30}
            ]
        }]
    }
    url = f'https://vision.googleapis.com/v1/images:annotate?key={api_key}'
    r = requests.post(url, headers={'Content-Type': 'application/json'}, data=json.dumps(payload), timeout=30)
    data = r.json()
    if 'error' in data:
        return None, data['error'].get('message', str(data['error'])), []
    resp = data.get('responses', [{}])[0]
    raw = resp.get('labelAnnotations', [])

    # دمج تسميات الويب (أكثر تحديداً أحياناً: بيض، توست، موز، إلخ)
    seen_lower = set()
    def add_name(name):
        if not name or not isinstance(name, str):
            return
        n = name.strip()
        if len(n) < 2:
            return
        key = n.lower()
        if key in seen_lower:
            return
        seen_lower.add(key)
        return n

    raw_names = []
    # أفضل التخمينات من الويب أولاً (غالباً أوضح: "Fried egg", "Toast with banana")
    web = resp.get('webDetection') or {}
    for bg in web.get('bestGuessLabels', []):
        label = bg.get('label') if isinstance(bg, dict) else bg
        n = add_name(label)
        if n:
            raw_names.append(n)
    for ent in web.get('webEntities', [])[:25]:
        n = add_name(ent.get('description'))
        if n:
            raw_names.append(n)
    # ثم تسميات الصورة العادية
    for l in raw:
        n = add_name(l.get('description'))
        if n:
            raw_names.append(n)

    generic = {'food', 'meal', 'dish', 'cuisine', 'plate'}
    labels = [
        l for l in raw
        if (l.get('score') or 0) >= 0.6
        and (l.get('description') or '').lower() not in generic
    ][:8]
    # محاولة مطابقة تسميات الويب أيضاً إن لم تكفِ التسميات العادية
    if not labels and raw_names:
        labels = [{'description': name, 'score': 0.7} for name in raw_names[:10]]
    if not labels:
        return [], None, raw_names or [l.get('description', '') for l in raw[:12] if l.get('description')]
    items = labels_to_food_items(labels, NUTRITION_DB)
    if not items and labels:
        for l in labels[:6]:
            food_id = map_vision_label_to_food(l.get('description') if isinstance(l, dict) else l, NUTRITION_DB)
            food = next((f for f in NUTRITION_DB if f.get('id') == food_id), None)
            if food:
                items.append({
                    'foodId': food['id'],
                    'nameAr': food.get('nameAr', ''),
                    'nameEn': food.get('nameEn', ''),
                    'estimatedGrams': round(80 + (l.get('score') or 0.5) * 120),
                    'score': l.get('score', 0.5),
                })
    for it in items:
        compute_nutrition(it, NUTRITION_DB)
    return items, None, raw_names[:35]


@app.route('/', methods=['GET', 'POST'])
def index():
    if request.method == 'POST':
        file = request.files.get('image')
        mode = request.form.get('mode', 'meals')
        if not file:
            return render_template('index.html', meal_error='لم تُرفع صورة.')
        # قراءة الصورة مرة واحدة للعرض والمقارنة
        file.seek(0)
        image_bytes = file.read()
        image_base64 = base64.b64encode(image_bytes).decode('utf-8')
        image_mimetype = (file.content_type or 'image/jpeg').split(';')[0].strip() or 'image/jpeg'
        from io import BytesIO
        file_io = BytesIO(image_bytes)

        if mode == 'text':
            text = extract_text_from_image(API_KEY, file_io)
            return render_template(
                'index.html',
                text=format_extracted_text(text),
                image_base64=image_base64,
                image_mimetype=image_mimetype,
                mode='text',
            )
        items, err, raw_labels = recognize_meals(API_KEY, file_io)
        if err:
            return render_template('index.html', meal_error=err, image_base64=image_base64, image_mimetype=image_mimetype)
        # دائماً نمرر التسميات الخام للمقارنة مع المخرجات
        total_cal = sum(i.get('calories', 0) for i in items) if items else 0
        total_protein = sum(i.get('protein', 0) for i in items) if items else 0
        total_carbs = sum(i.get('carbs', 0) for i in items) if items else 0
        total_fat = sum(i.get('fat', 0) for i in items) if items else 0
        return render_template(
            'index.html',
            meals=items or [],
            raw_vision_labels=raw_labels or [],
            total_calories=total_cal,
            total_protein=round(total_protein, 1),
            total_carbs=round(total_carbs, 1),
            total_fat=round(total_fat, 1),
            image_base64=image_base64,
            image_mimetype=image_mimetype,
            mode='meals',
            meal_error=None if items else 'لم تُطابق التسميات أي صنف في قاعدة المغذيات.',
        )
    return render_template('index.html')


@app.route('/download')
def download_file():
    text = request.args.get('text', '')
    from io import BytesIO
    buf = BytesIO(text.encode('utf-8'))
    buf.seek(0)
    return send_file(buf, as_attachment=True, download_name='extracted_text.txt', mimetype='text/plain')


if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5001, debug=True)
