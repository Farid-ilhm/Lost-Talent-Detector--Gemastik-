from flask import Flask, request, jsonify
import pickle
import pandas as pd
import numpy as np
import os
import sys

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

@app.route('/predict', methods=['POST'])
def predict():
    global model_data
    if not model_data:
        # If model is not loaded, reload it dynamically
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

        # Extract features from input payload
        riasec = req_data.get('riasec', {})
        grades = req_data.get('grades', {})
        achievements = req_data.get('achievements', [])
        hobbies = req_data.get('hobbies', [])
        interests = req_data.get('interests', [])

        # Map to feature names expected by the model
        riasec_r = float(riasec.get('Realistic', 50))
        riasec_i = float(riasec.get('Investigative', 50))
        riasec_a = float(riasec.get('Artistic', 50))
        riasec_s = float(riasec.get('Social', 50))
        riasec_e = float(riasec.get('Enterprising', 50))
        riasec_c = float(riasec.get('Conventional', 50))

        # Grades maps (with defaults)
        grade_math = float(grades.get('Matematika', 70))
        grade_science = float(grades.get('Fisika', 70)) # Treat physics as science
        grade_informatics = float(grades.get('Informatika', 70))
        grade_english = float(grades.get('Bahasa Inggris', 70))

        # Achievement counts
        achievement_tech = 0
        achievement_science = 0
        achievement_art = 0

        for ach in achievements:
            cat = ach.get('category', '').lower()
            if cat in ['teknologi', 'komputer']:
                achievement_tech += 1
            elif cat in ['sains', 'matematika', 'akademik']:
                achievement_science += 1
            elif cat in ['seni', 'olahraga', 'keagamaan']:
                achievement_art += 1

        # Build feature DataFrame
        features = pd.DataFrame([{
            'riasec_r': riasec_r, 'riasec_i': riasec_i, 'riasec_a': riasec_a,
            'riasec_s': riasec_s, 'riasec_e': riasec_e, 'riasec_c': riasec_c,
            'grade_math': grade_math, 'grade_science': grade_science,
            'grade_informatics': grade_informatics, 'grade_english': grade_english,
            'achievement_tech': achievement_tech, 'achievement_science': achievement_science,
            'achievement_art': achievement_art
        }])

        # Perform ML prediction
        clf = model_data['model']
        classes = clf.classes_
        probabilities = clf.predict_proba(features)[0]

        # Sort classes by probability
        pred_dict = {classes[i]: float(probabilities[i]) for i in range(len(classes))}
        sorted_preds = sorted(pred_dict.items(), key=lambda item: item[1], reverse=True)

        primary_talent = sorted_preds[0][0]
        confidence_score = round(sorted_preds[0][1] * 100, 2)

        # Supporting talents
        supporting_talents = []
        for i in range(1, min(4, len(sorted_preds))):
            supporting_talents.append({
                'talent': sorted_preds[i][0],
                'confidence': round(sorted_preds[i][1] * 100, 2)
            })

        # Dynamic reasoning generator (explainable AI)
        reasoning = []
        if grade_informatics > 85 and primary_talent in ['Programming', 'Robotik']:
            reasoning.append(f"Nilai akademik Informatika sangat tinggi ({grade_informatics:.1f}) mendukung kecakapan teknologi.")
        if grade_math > 85:
            reasoning.append(f"Logika berpikir didukung nilai Matematika yang kuat ({grade_math:.1f}).")
        
        # RIASEC reason
        dominant_riasec = max(riasec, key=riasec.get) if riasec else 'Investigative'
        reasoning.append(f"Orientasi minat dominan pada tipe kepribadian {dominant_riasec} ({riasec.get(dominant_riasec, 50)}%).")
        
        if achievement_tech > 0 and primary_talent in ['Robotik', 'Programming']:
            reasoning.append(f"Memiliki {achievement_tech} sertifikat prestasi sah di bidang teknologi.")
        if achievement_science > 0 and primary_talent == 'Sains & Riset':
            reasoning.append(f"Ditopang {achievement_science} pencapaian prestasi akademik bidang sains/penelitian.")
        
        # Hobbies / Interests contribution
        relevant_hobbies = [h for h in hobbies if any(keyword in h.lower() for keyword in ['code', 'robot', 'desain', 'bisnis', 'gambar'])]
        if relevant_hobbies:
            reasoning.append(f"Minat personal diperkuat oleh hobi aktif: {', '.join(relevant_hobbies[:2])}.")

        # Recommendations mappings
        careers = []
        extracurriculars = []
        competitions = []
        targets = []

        if primary_talent == 'Robotik':
            careers = ['Robotics Engineer', 'Embedded Systems Developer', 'IoT Architect', 'Automation Specialist']
            extracurriculars = ['Klub Robotika', 'Coding Club', 'Karya Ilmiah Remaja']
            competitions = ['Lomba Robotika Nasional (BARON)', 'GEMASTIK - Pengembangan Perangkat Lunak']
            targets = ['Mempelajari mikrokontroler Arduino/Raspberry Pi', 'Mengembangkan portofolio Internet of Things (IoT)']
        elif primary_talent == 'Programming':
            careers = ['Software Engineer', 'Backend Developer', 'Data Scientist', 'System Analyst']
            extracurriculars = ['Coding Club', 'Karya Ilmiah Remaja']
            competitions = ['GEMASTIK - Pemrograman (Competitive Programming)', 'Hackathon Indonesia AI Innovation']
            targets = ['Mempelajari algoritma dan struktur data lanjutan', 'Membangun aplikasi open-source di GitHub']
        elif primary_talent == 'Sains & Riset':
            careers = ['Research Scientist', 'Data Analyst', 'Academic Professor', 'Laboratory Researcher']
            extracurriculars = ['Karya Ilmiah Remaja', 'Olimpiade Club']
            competitions = ['Olimpiade Sains Nasional (OSN) - Informatika', 'GEMASTIK - Karya Tulis Ilmiah TIK']
            targets = ['Mempelajari metode penulisan karya ilmiah', 'Membaca jurnal sains bereputasi secara rutin']
        elif primary_talent == 'Desain Kreatif & UI/UX':
            careers = ['UI/UX Designer', 'Product Designer', 'Creative Director', 'Graphic Designer']
            extracurriculars = ['Klub Desain & Fotografi', 'Pramuka (Pubdok)']
            competitions = ['GEMASTIK - Desain Pengalaman Pengguna (UI/UX Design)', 'Festival dan Lomba Seni Siswa Nasional (FLS2N)']
            targets = ['Mempelajari software desain Figma/Adobe XD', 'Membuat portofolio case study di Behance']
        elif primary_talent == 'Bisnis & Kewirausahaan':
            careers = ['Business Development', 'Product Manager', 'Entrepreneur/Founder', 'Financial Analyst']
            extracurriculars = ['Koperasi Siswa', 'Debate Club']
            competitions = ['National Business Plan Competition (NBPC)', 'Lomba Debat Nasional']
            targets = ['Menyusun proposal model bisnis kanvas (BMC)', 'Mempelajari dasar analisis keuangan & pemasaran']
        else: # Sosial & Pendidikan
            careers = ['Teacher / Educator', 'Public Relations Specialist', 'Human Resources Manager', 'Social Worker']
            extracurriculars = ['Pramuka', 'Palang Merah Remaja (PMR)', 'OSIS']
            competitions = ['Lomba Debat Bahasa Indonesia', 'Kompetisi Pengabdian Sosial']
            targets = ['Melatih kemampuan public speaking', 'Mengikuti program volunterisme kemanusiaan']

        return jsonify({
            'success': True,
            'primary_talent': primary_talent,
            'confidence_score': confidence_score,
            'supporting_talents': supporting_talents,
            'reasoning': reasoning,
            'career_recommendations': careers,
            'extracurricular_recommendations': extracurriculars,
            'competition_recommendations': competitions,
            'development_targets': targets,
            'model_version': 'lost-talent-rf-v1.0'
        })

    except Exception as e:
        return jsonify({
            'success': False,
            'message': f"Prediction failed due to error: {str(e)}"
        }), 500

if __name__ == '__main__':
    # Default Flask port is 5000
    app.run(host='127.0.0.1', port=5000, debug=True)
