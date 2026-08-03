from flask import Flask, request, jsonify
import pickle
import pandas as pd
import numpy as np
import os
import sys
import json
import requests
import re

app = Flask(__name__)

# Load the model
model_path = os.path.join(os.path.dirname(__file__), 'talent_model.pkl')
model_data = None

if os.path.exists(model_path):
    try:
        with open(model_path, 'rb') as f:
            model_data = pickle.load(f)
        print("Successfully loaded Random Forest Classifier model.")
    except Exception as e:
        print(f"Error loading model: {e}", file=sys.stderr)
else:
    print(f"Warning: Model file not found at {model_path}. Please run train.py first.", file=sys.stderr)

def call_gemini_llm(api_key, context_data):
    if not api_key:
        return None

    url = f"https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={api_key}"
    headers = {"Content-Type": "application/json"}

    prompt = f"""
Anda adalah Asisten Psikolog Karir & Konsultan Bakat AI Profesional untuk platform 'Lost Talent Detector'.
Tugas Anda adalah menulis narasi analisis mendalam (analisis_mendalam) sepanjang 2-3 paragraf yang personal, memotivasi, dan inspiratif berdasarkan hasil analisis bakat pengguna berikut:

Bakat Utama: {context_data.get('primary_talent')} (Tingkat Keyakinan: {context_data.get('confidence_score')}%)
Bakat Pendukung: {json.dumps(context_data.get('supporting_talents', []), ensure_ascii=False)}
Orientasi RIASEC: {json.dumps(context_data.get('riasec', {}), ensure_ascii=False)}
Nilai Rata-rata Pelajaran: {json.dumps(context_data.get('grades', {}), ensure_ascii=False)}
Sertifikat/Prestasi: {json.dumps(context_data.get('achievements', []), ensure_ascii=False)}
Minat & Hobi: {json.dumps(context_data.get('hobbies', []) + context_data.get('interests', []), ensure_ascii=False)}

PENTING: Jangan mengubah hasil deteksi bakat (bakat utama) yang sudah ditentukan oleh sistem. Fokuslah HANYA untuk menyusun narasi psikologi karir yang mendalam dan relevan dengan profil di atas.

Kembalikan HANYA format JSON murni berikut tanpa tag ```json atau teks lainnya:
{{
  "analisis_mendalam": "Narasi 2-3 paragraf yang menginspirasi..."
}}
"""

    payload = {
        "contents": [{
            "parts": [{"text": prompt}]
        }],
        "generationConfig": {
            "responseMimeType": "application/json",
            "temperature": 0.4
        }
    }

    try:
        res = requests.post(url, headers=headers, json=payload, timeout=8)
        if res.status_code == 200:
            res_data = res.json()
            candidates = res_data.get('candidates', [])
            if candidates:
                text_content = candidates[0]['content']['parts'][0]['text']
                text_clean = text_content.strip()
                if text_clean.startswith("```json"):
                    text_clean = text_clean[7:]
                if text_clean.startswith("```"):
                    text_clean = text_clean[3:]
                if text_clean.endswith("```"):
                    text_clean = text_clean[:-3]
                return json.loads(text_clean.strip())
        else:
            print(f"Gemini API returned status {res.status_code}: {res.text}", file=sys.stderr)
    except Exception as e:
        print(f"Gemini LLM call exception: {e}", file=sys.stderr)

    return None

def call_openrouter_deepseek(api_key, context_data):
    if not api_key:
        return None

    url = "https://openrouter.ai/api/v1/chat/completions"
    headers = {
        "Authorization": f"Bearer {api_key}",
        "HTTP-Referer": "http://localhost:8000",
        "X-Title": "Lost Talent Detector",
        "Content-Type": "application/json"
    }

    prompt = f"""
Anda adalah Psikolog Karir & Konsultan Bakat AI Senior untuk platform 'Lost Talent Detector'.
Tulis narasi analisis psikologi karir (analisis_mendalam) sepanjang 2-3 paragraf yang tajam, mendalam, dan memotivasi berdasarkan data bakat berikut:

Bakat Utama: {context_data.get('primary_talent')} (Tingkat Keyakinan: {context_data.get('confidence_score')}%)
Bakat Pendukung: {json.dumps(context_data.get('supporting_talents', []), ensure_ascii=False)}
Skor RIASEC (%): {json.dumps(context_data.get('riasec', {}), ensure_ascii=False)}
Nilai Pelajaran Rapor: {json.dumps(context_data.get('grades', {}), ensure_ascii=False)}
Sertifikat Prestasi: {json.dumps(context_data.get('achievements', []), ensure_ascii=False)}
Minat & Hobi: {json.dumps(context_data.get('hobbies', []) + context_data.get('interests', []), ensure_ascii=False)}

PENTING: Jangan mengubah hasil deteksi bakat (bakat utama) yang sudah ditentukan oleh sistem. Fokuslah HANYA untuk menyusun narasi psikologi karir yang mendalam dan relevan dengan profil di atas.

Kembalikan HANYA format JSON murni berikut tanpa tag ```json atau teks lainnya:
{{
  "analisis_mendalam": "Narasi 2-3 paragraf analisis karir..."
}}
"""

    models_to_try = [
        "deepseek/deepseek-r1:free",
        "deepseek/deepseek-chat",
        "meta-llama/llama-3.3-70b-instruct:free"
    ]

    for model_name in models_to_try:
        payload = {
            "model": model_name,
            "messages": [
                {"role": "system", "content": "You are a professional career guidance AI that outputs valid JSON only."},
                {"role": "user", "content": prompt}
            ],
            "temperature": 0.3
        }

        try:
            res = requests.post(url, headers=headers, json=payload, timeout=12)
            if res.status_code == 200:
                res_data = res.json()
                choices = res_data.get('choices', [])
                if choices:
                    text_content = choices[0]['message']['content']
                    text_clean = text_content.strip()
                    if text_clean.startswith("```json"):
                        text_clean = text_clean[7:]
                    if text_clean.startswith("```"):
                        text_clean = text_clean[3:]
                    if text_clean.endswith("```"):
                        text_clean = text_clean[:-3]
                    return json.loads(text_clean.strip())
            else:
                print(f"OpenRouter ({model_name}) status {res.status_code}: {res.text}", file=sys.stderr)
        except Exception as e:
            print(f"OpenRouter ({model_name}) exception: {e}", file=sys.stderr)

    return None

def clean_text(text):
    if not text:
        return ""
    text = text.lower()
    text = re.sub(r'[^a-z0-9\s]', '', text)
    return " ".join(text.split())

def text_match(text, keywords):
    cleaned = clean_text(text)
    if not cleaned:
        return False
    for kw in keywords:
        kw_cleaned = clean_text(kw)
        if not kw_cleaned:
            continue
        if kw_cleaned in cleaned or cleaned in kw_cleaned:
            return True
    return False

# Load knowledge base
kb_path = os.path.join(os.path.dirname(__file__), 'knowledge_base.json')
knowledge_base = {"categories": []}
if os.path.exists(kb_path):
    try:
        with open(kb_path, 'r', encoding='utf-8') as f:
            knowledge_base = json.load(f)
        print(f"Successfully loaded Knowledge Base containing {len(knowledge_base.get('categories', []))} domains.")
    except Exception as e:
        print(f"Error loading knowledge base: {e}", file=sys.stderr)
else:
    print(f"Warning: Knowledge base not found at {kb_path}", file=sys.stderr)

@app.route('/predict', methods=['POST'])
def predict():
    global model_data
    if not model_data:
        if os.path.exists(model_path):
            with open(model_path, 'rb') as f:
                model_data = pickle.load(f)
        else:
            return jsonify({
                'success': False,
                'message': 'AI Model has not been trained yet. Please run train.py first.'
            }), 500

    try:
        req_data = request.get_json()
        if not req_data:
            return jsonify({'success': False, 'message': 'No JSON payload provided'}), 400

        riasec = req_data.get('riasec', {})
        grades = req_data.get('grades', {})
        achievements = req_data.get('achievements', [])
        hobbies = req_data.get('hobbies', [])
        interests = req_data.get('interests', [])

        scores = {}
        evidence_data = {}
        reasoning_data = {}

        categories = knowledge_base.get('categories', [])
        if not categories:
            return jsonify({'success': False, 'message': 'Knowledge base database is empty.'}), 500

        for cat in categories:
            cat_id = cat['id']
            cat_name = cat['name']
            aliases = cat.get('aliases', [])
            subjects = cat.get('subjects', [])
            hobbies_kb = cat.get('hobbies', [])
            interests_kb = cat.get('interests', [])
            riasec_kb = cat.get('riasec', [])

            # 1. Academic Score (Max 35)
            matched_grades = []
            for sub, score in grades.items():
                if text_match(sub, subjects):
                    matched_grades.append(float(score))
            
            if matched_grades:
                avg_grade = sum(matched_grades) / len(matched_grades)
                academic_score = (avg_grade / 100.0) * 35.0
            else:
                academic_score = 0.0

            # 2. Achievement Score (Max 20)
            matched_ach_count = 0
            achievement_points = 0.0
            for ach in achievements:
                ach_title = ach.get('title', '')
                ach_category = ach.get('category', '')
                ach_level = ach.get('level', 'sekolah')
                
                is_match = False
                if text_match(ach_title, aliases + [cat_name] + hobbies_kb + interests_kb):
                    is_match = True
                
                ach_cat_lower = ach_category.lower()
                if cat_id in ['robotik', 'programming'] and ach_cat_lower == 'teknologi':
                    is_match = True
                elif cat_id in ['sains_riset', 'kesehatan_medis', 'pertanian_hayati', 'perikanan_kelautan'] and ach_cat_lower in ['sains', 'akademik']:
                    is_match = True
                elif cat_id in ['desain_kreatif', 'seni_musik'] and ach_cat_lower in ['seni', 'desain']:
                    is_match = True
                elif cat_id == 'olahraga_fisik' and ach_cat_lower == 'olahraga':
                    is_match = True
                elif cat_id == 'bisnis_kewirausahaan' and any(k in ach_title.lower() for k in ['bisnis', 'usaha', 'wirausaha', 'marketing', 'debat', 'pitching', 'finance']):
                    is_match = True
                elif cat_id == 'sosial_pendidikan' and (ach_cat_lower == 'keagamaan' or any(k in ach_title.lower() for k in ['sosial', 'pramuka', 'pmr', 'osis', 'volunteer', 'mengajar', 'debat'])):
                    is_match = True
                elif cat_id == 'seni_kuliner' and any(k in ach_title.lower() for k in ['masak', 'kuliner', 'boga', 'makanan', 'chef', 'kue']):
                    is_match = True

                if is_match:
                    matched_ach_count += 1
                    level_points = {
                        'internasional': 20.0,
                        'nasional': 18.0,
                        'provinsi': 15.0,
                        'kabupaten': 12.0,
                        'kecamatan': 10.0,
                        'sekolah': 8.0
                    }
                    achievement_points = max(achievement_points, level_points.get(ach_level.lower(), 5.0))
            
            achievement_score = min(20.0, achievement_points)

            # 3. Interest Score (Max 20)
            matched_interests_count = 0
            for interest in interests:
                if text_match(interest, interests_kb + aliases + [cat_name]):
                    matched_interests_count += 1
            interest_score = min(20.0, matched_interests_count * 10.0)

            # 4. Hobby Score (Max 15)
            matched_hobbies_count = 0
            for hobby in hobbies:
                if text_match(hobby, hobbies_kb + aliases + [cat_name]):
                    matched_hobbies_count += 1
            hobby_score = min(15.0, matched_hobbies_count * 7.5)

            # 5. RIASEC Score (Max 10)
            matched_riasec_scores = []
            for r_type in riasec_kb:
                if r_type in riasec:
                    matched_riasec_scores.append(float(riasec[r_type]))
            
            if matched_riasec_scores:
                avg_riasec = sum(matched_riasec_scores) / len(matched_riasec_scores)
                riasec_score = (avg_riasec / 100.0) * 10.0
            else:
                riasec_score = 5.0

            # 6. Positive/Negative Evidence
            has_academic_ev = len(matched_grades) > 0 and (sum(matched_grades)/len(matched_grades)) >= 75
            has_achievement_ev = matched_ach_count > 0
            has_interest_ev = matched_interests_count > 0
            has_hobby_ev = matched_hobbies_count > 0
            has_riasec_ev = len(matched_riasec_scores) > 0 and (sum(matched_riasec_scores)/len(matched_riasec_scores)) >= 60

            pos_evidence = sum([has_academic_ev, has_achievement_ev, has_interest_ev, has_hobby_ev, has_riasec_ev])
            coverage = pos_evidence / 5.0

            # 7. Penalty
            penalty = 0.0
            raw_score = academic_score + achievement_score + interest_score + hobby_score + riasec_score
            if pos_evidence <= 1 and raw_score > 35.0:
                penalty = 15.0

            final_score = max(0.0, min(100.0, raw_score - penalty))
            
            # Academic Dampening Rule: If the student has grades, dampen scores of fields with zero academic matches.
            if len(grades) > 0 and academic_score == 0.0:
                if not (achievement_score > 0 and interest_score > 0):
                    final_score = final_score * 0.5

            scores[cat_name] = final_score
            evidence_data[cat_name] = {
                'positive_evidence': pos_evidence,
                'negative_evidence': 5 - pos_evidence,
                'coverage': coverage,
                'breakdown': {
                    'academic': has_academic_ev,
                    'achievement': has_achievement_ev,
                    'interest': has_interest_ev,
                    'hobby': has_hobby_ev,
                    'riasec': has_riasec_ev
                }
            }
            
            reasons = []
            if has_academic_ev:
                m_subs = [sub for sub in grades if text_match(sub, subjects)]
                reasons.append(f"Nilai akademik Anda sangat kuat pada pelajaran terkait ({', '.join(m_subs)}).")
            if has_achievement_ev:
                reasons.append(f"Memiliki {matched_ach_count} sertifikat prestasi di tingkat kompetisi.")
            if has_interest_ev:
                reasons.append("Minat pengembangan karir personal Anda sejalan dengan bidang ini.")
            if has_hobby_ev:
                reasons.append("Hobi sehari-hari aktif menunjang kecakapan teknis Anda.")
            if has_riasec_ev:
                reasons.append(f"Orientasi kepribadian RIASEC ({', '.join(riasec_kb)}) yang tinggi.")
            
            if penalty > 0:
                reasons.append("Peringatan: Terdapat ketidakselarasan indikator (nilai tinggi tetapi kurang didukung minat/hobi).")

            reasoning_data[cat_name] = reasons

        # Sort scores to find Top 5
        sorted_scores = sorted(scores.items(), key=lambda x: x[1], reverse=True)
        top_category_name = sorted_scores[0][0]
        top_score = sorted_scores[0][1]
        top_coverage = evidence_data[top_category_name]['coverage']

        # Confidence: 70% Final Score + 30% Evidence Coverage
        confidence_score = round(min(99.0, max(45.0, (0.7 * top_score) + (0.3 * top_coverage * 100))), 1)

        # Extraction features for RF Classifier (Backward Compatibility)
        riasec_r = float(riasec.get('Realistic', 50))
        riasec_i = float(riasec.get('Investigative', 50))
        riasec_a = float(riasec.get('Artistic', 50))
        riasec_s = float(riasec.get('Social', 50))
        riasec_e = float(riasec.get('Enterprising', 50))
        riasec_c = float(riasec.get('Conventional', 50))

        math_list = [v for k, v in grades.items() if text_match(k, ["Matematika", "Kalkulus", "Statistika", "Aljabar", "Logika"])]
        science_list = [v for k, v in grades.items() if text_match(k, ["Fisika", "Kimia", "Biologi", "Sains", "IPA"])]
        info_list = [v for k, v in grades.items() if text_match(k, ["Informatika", "Komputer", "Pemrograman", "Program", "Coding", "Algoritma", "Jaringan", "Data", "Web", "Mobile", "IT", "TIK", "RPL", "Cyber"])]
        eng_list = [v for k, v in grades.items() if text_match(k, ["Inggris", "English"])]
        music_list = [v for k, v in grades.items() if text_match(k, ["Musik", "Vokal", "Solfeggio", "Harmoni", "Instrumen"])]
        culinary_list = [v for k, v in grades.items() if text_match(k, ["Boga", "Masak", "Makanan", "Patisserie", "Gizi"])]
        sports_list = [v for k, v in grades.items() if text_match(k, ["Olahraga", "Penjas", "Atletik", "Fisik"])]
        medical_list = [v for k, v in grades.items() if text_match(k, ["Anatomi", "Farmasi", "Keperawatan", "Perawat", "Bidan"])]
        agri_list = [v for k, v in grades.items() if text_match(k, ["Tani", "Tanah", "Kebun", "Botani", "Agro", "Ternak", "Tanaman"])]
        fish_list = [v for k, v in grades.items() if text_match(k, ["Ikan", "Perikanan", "Perairan", "Kelautan", "Maritim", "Akuakultur", "Iktiologi", "Mancing"])]

        grade_math = sum(math_list)/len(math_list) if math_list else 70.0
        grade_science = sum(science_list)/len(science_list) if science_list else 70.0
        grade_informatics = sum(info_list)/len(info_list) if info_list else 70.0
        grade_english = sum(eng_list)/len(eng_list) if eng_list else 70.0
        grade_music = sum(music_list)/len(music_list) if music_list else 70.0
        grade_culinary = sum(culinary_list)/len(culinary_list) if culinary_list else 70.0
        grade_sports = sum(sports_list)/len(sports_list) if sports_list else 70.0
        grade_medical = sum(medical_list)/len(medical_list) if medical_list else 70.0
        grade_agriculture = sum(agri_list)/len(agri_list) if agri_list else 70.0
        grade_fishery = sum(fish_list)/len(fish_list) if fish_list else 70.0

        achievement_tech = sum(1 for a in achievements if a.get('category','').lower() in ['teknologi', 'komputer'])
        achievement_science = sum(1 for a in achievements if a.get('category','').lower() in ['sains', 'matematika', 'akademik'])
        achievement_art = sum(1 for a in achievements if a.get('category','').lower() in ['seni', 'olahraga', 'keagamaan'])

        features_df = pd.DataFrame([{
            'riasec_r': riasec_r, 'riasec_i': riasec_i, 'riasec_a': riasec_a,
            'riasec_s': riasec_s, 'riasec_e': riasec_e, 'riasec_c': riasec_c,
            'grade_math': grade_math, 'grade_science': grade_science,
            'grade_informatics': grade_informatics, 'grade_english': grade_english,
            'grade_music': grade_music, 'grade_culinary': grade_culinary,
            'grade_sports': grade_sports, 'grade_medical': grade_medical,
            'grade_agriculture': grade_agriculture, 'grade_fishery': grade_fishery,
            'achievement_tech': achievement_tech, 'achievement_science': achievement_science,
            'achievement_art': achievement_art
        }])

        primary_talent = top_category_name
        rf_agreed = False

        if len(sorted_scores) > 1 and (sorted_scores[0][1] - sorted_scores[1][1]) <= 5.0:
            clf = model_data['model']
            try:
                rf_pred = clf.predict(features_df)[0]
                if rf_pred in [sorted_scores[0][0], sorted_scores[1][0]]:
                    primary_talent = rf_pred
                    rf_agreed = True
            except Exception as e:
                print(f"ML validation error: {e}", file=sys.stderr)
        else:
            clf = model_data['model']
            try:
                rf_pred = clf.predict(features_df)[0]
                if rf_pred == primary_talent:
                    rf_agreed = True
            except Exception as e:
                pass

        if rf_agreed:
            confidence_score = min(99.0, confidence_score + 5.0)

        supporting_talents = []
        base_percentages = [85.0, 75.0, 65.0]
        idx = 0
        for name, score in sorted_scores:
            if name == primary_talent:
                continue
            ratio = score / max(0.001, sorted_scores[0][1])
            conf = round(min(92.0, max(40.0, base_percentages[min(idx, 2)] * ratio)), 1)
            supporting_talents.append({
                'talent': name,
                'confidence': conf
            })
            idx += 1
            if idx >= 3:
                break

        reasoning = reasoning_data[primary_talent]

        primary_kb = next((c for c in categories if c['name'] == primary_talent), None)
        careers = primary_kb['careers'] if primary_kb else []
        competitions = primary_kb['competitions'] if primary_kb else []
        extracurriculars = primary_kb.get('hobbies', [])[:3]
        targets = primary_kb['roadmap'] if primary_kb else []

        openrouter_key = req_data.get('openrouter_api_key') or os.getenv('OPENROUTER_API_KEY')
        gemini_key = req_data.get('gemini_api_key') or os.getenv('GEMINI_API_KEY')

        context_data = {
            'primary_talent': primary_talent,
            'confidence_score': confidence_score,
            'supporting_talents': supporting_talents,
            'riasec': riasec,
            'grades': grades,
            'achievements': achievements,
            'hobbies': hobbies,
            'interests': interests
        }

        llm_res = None
        model_ver = 'lost-talent-scoring-v3.0-expert'

        if openrouter_key:
            llm_res = call_openrouter_deepseek(openrouter_key, context_data)
            if llm_res:
                model_ver = 'lost-talent-hybrid-v3.0-deepseek-r1'

        if not llm_res and gemini_key:
            llm_res = call_gemini_llm(gemini_key, context_data)
            if llm_res:
                model_ver = 'lost-talent-hybrid-v3.0-gemini'

        if llm_res and llm_res.get('analisis_mendalam'):
            analisis_mendalam = llm_res.get('analisis_mendalam')
        else:
            analisis_mendalam = f"Berdasarkan analisis Hybrid Explainable AI, Anda menunjukkan potensi dominan di bidang {primary_talent} dengan tingkat keyakinan {confidence_score}%. "
            if grades:
                analisis_mendalam += "Kecakapan akademis Anda pada mata pelajaran penunjang sangat mendukung pemahaman konsep penting di bidang ini. "
            if achievements:
                analisis_mendalam += "Didukung rekam jejak prestasi, Anda memiliki daya saing yang baik. "
            if hobbies or interests:
                analisis_mendalam += f"Aktivitas hobi dan minat Anda seperti {', '.join((hobbies+interests)[:2])} secara aktif mengasah bakat alami Anda."

        res_payload = {
            'success': True,
            'primary_talent': primary_talent,
            'confidence_score': confidence_score,
            'confidence': confidence_score,
            'supporting_talents': supporting_talents,
            'secondary_talent': [t['talent'] for t in supporting_talents],
            'reasoning': reasoning,
            'career_recommendations': careers,
            'extracurricular_recommendations': extracurriculars,
            'competition_recommendations': competitions,
            'development_targets': targets,
            'analisis_mendalam': analisis_mendalam,
            'analysis': analisis_mendalam,
            'evidence': {
                'academic': round(scores[primary_talent] * 0.35, 1),
                'achievement': round(scores[primary_talent] * 0.20, 1),
                'interest': round(scores[primary_talent] * 0.20, 1),
                'hobby': round(scores[primary_talent] * 0.15, 1),
                'riasec': round(scores[primary_talent] * 0.10, 1)
            },
            'recommendation': {
                'career': careers,
                'competition': competitions,
                'certification': primary_kb.get('certifications', []) if primary_kb else [],
                'roadmap': targets
            },
            'model_version': model_ver
        }

        return jsonify(res_payload)

    except Exception as e:
        return jsonify({
            'success': False,
            'message': f"Prediction failed due to error: {str(e)}"
        }), 500

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5000, debug=True)
