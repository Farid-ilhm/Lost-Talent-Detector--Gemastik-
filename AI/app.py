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

from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity

# Repositories for recommendations
CAREER_REPOSITORY = [
    {"name": "Robotics Engineer", "talent": "Robotik", "tags": "robotika robot arduino mechanical embedded sensor automation iot"},
    {"name": "Embedded Systems Developer", "talent": "Robotik", "tags": "embedded iot microcontoller arduino raspberry c++ firmware hardware"},
    {"name": "IoT Architect", "talent": "Robotik", "tags": "iot internet of things sensor cloud smart home device network hardware"},
    {"name": "Automation Specialist", "talent": "Robotik", "tags": "plc automation industrial control mechanical robot robotics"},
    {"name": "Software Engineer", "talent": "Programming", "tags": "software engineering code coding programming python java web backend developer architect"},
    {"name": "Backend Developer", "talent": "Programming", "tags": "backend web server api database programming sql node.js python golang php"},
    {"name": "Data Scientist", "talent": "Programming", "tags": "data science analyst machine learning artificial intelligence python statistics sql math r"},
    {"name": "System Analyst", "talent": "Programming", "tags": "system analyst software design requirements flow charts business architecture"},
    {"name": "Research Scientist", "talent": "Sains & Riset", "tags": "scientist research research lab physics chemistry biology academic paper journal write study"},
    {"name": "Data Analyst", "talent": "Sains & Riset", "tags": "data analyst excel tableau statistics visualization sql research insight trends"},
    {"name": "Academic Professor", "talent": "Sains & Riset", "tags": "professor academic teacher researcher write paper teaching lecture university education"},
    {"name": "Laboratory Researcher", "talent": "Sains & Riset", "tags": "laboratory experiment lab chemistry biochemistry physics analysis sample science research"},
    {"name": "UI/UX Designer", "talent": "Desain Kreatif & UI/UX", "tags": "ui ux designer design figma wireframe prototype visual web layout app graphic user experience"},
    {"name": "Product Designer", "talent": "Desain Kreatif & UI/UX", "tags": "product design industrial design prototyping user research figma packaging aesthetic"},
    {"name": "Creative Director", "talent": "Desain Kreatif & UI/UX", "tags": "creative director advertising agency layout design photography visual art branding marketing"},
    {"name": "Graphic Designer", "talent": "Desain Kreatif & UI/UX", "tags": "graphic design illustrator photoshop art vector logo branding canvas poster"},
    {"name": "Business Development", "talent": "Bisnis & Kewirausahaan", "tags": "business development startup pitch deck b2b marketing sale strategy negotiation startup manager"},
    {"name": "Product Manager", "talent": "Bisnis & Kewirausahaan", "tags": "product manager roadmap user stories backlog business requirements project agile scrum"},
    {"name": "Entrepreneur/Founder", "talent": "Bisnis & Kewirausahaan", "tags": "entrepreneur founder startup business plan funding capital pitching ceo finance canvas bmc"},
    {"name": "Financial Analyst", "talent": "Bisnis & Kewirausahaan", "tags": "financial analyst accounting invest money market excel stocks business valuation audit tax"},
    {"name": "Teacher / Educator", "talent": "Sosial & Pendidikan", "tags": "teacher education classroom student teaching school curriculum learning public speaking"},
    {"name": "Public Relations Specialist", "talent": "Sosial & Pendidikan", "tags": "public relations communication media press release event organizer branding talk speaker"},
    {"name": "Human Resources Manager", "talent": "Sosial & Pendidikan", "tags": "human resources hr manager hiring recruit training employee psychology relation community"},
    {"name": "Social Worker", "talent": "Sosial & Pendidikan", "tags": "social work charity NGO community service public relations support mental help helper"},
    {"name": "Chef / Koki Profesional", "talent": "Seni Kuliner & Tata Boga", "tags": "chef koki boga kuliner masak food restaurant kitchen pastry baking"},
    {"name": "Pastry Chef", "talent": "Seni Kuliner & Tata Boga", "tags": "pastry chef cake baking bakery bread dessert kitchen boga"},
    {"name": "Food Stylist", "talent": "Seni Kuliner & Tata Boga", "tags": "food stylist photography presentation aesthetic culinary art cook boga"},
    {"name": "Penyanyi / Vokalis Profesional", "talent": "Seni Musik & Pertunjukan", "tags": "penyanyi vokal menyanyi vokalis choir band musik song singer pertunjukan"},
    {"name": "Komponis / Music Producer", "talent": "Seni Musik & Pertunjukan", "tags": "komponis music producer arranger audio recording song write instrument melodi harmoni"},
    {"name": "Guru Musik / Vokal", "talent": "Seni Musik & Pertunjukan", "tags": "guru musik vokal melodi harmoni instrument piano gitar biola bernyanyi"},
    {"name": "Atlet Profesional", "talent": "Olahraga & Kesehatan Fisik", "tags": "atlet olahraga fisik lari bola futsal badminton renang basket silat karate penjas"},
    {"name": "Pelatih Olahraga / Coach", "talent": "Olahraga & Kesehatan Fisik", "tags": "pelatih coach olahraga fisik training fitness team strategy penjas"},
    {"name": "Perawat Profesional", "talent": "Kesehatan & Keperawatan (Medis)", "tags": "perawat nurse medis dokter rumah sakit obat rawat anatomi bidan farmasi"},
    {"name": "Asisten Apoteker", "talent": "Kesehatan & Keperawatan (Medis)", "tags": "apoteker farmasi obat resep kimia medis klinik lab"}
]

COMPETITION_REPOSITORY = [
    {"name": "GEMASTIK - Pengembangan Perangkat Lunak", "talent": "Robotik", "tags": "software gemastik iot hardware app development code prototype"},
    {"name": "Lomba Robotika Nasional (BARON)", "talent": "Robotik", "tags": "robot robotika baron arduino mechanical electrical race line follower obstacle avoidance"},
    {"name": "GEMASTIK - Pemrograman (Competitive Programming)", "talent": "Programming", "tags": "gemastik programming algorithm competitive programming codeforces hackerrank structure c++"},
    {"name": "Hackathon Indonesia AI Innovation", "talent": "Programming", "tags": "hackathon ai artificial intelligence app prototype startup code development"},
    {"name": "Olimpiade Sains Nasional (OSN) - Informatika", "talent": "Sains & Riset", "tags": "osn sains informatika olimpiade computer science math algorithm competitive"},
    {"name": "GEMASTIK - Karya Tulis Ilmiah TIK", "talent": "Sains & Riset", "tags": "gemastik karya tulis ilmiah paper research research report TIK write study journal"},
    {"name": "GEMASTIK - Desain Pengalaman Pengguna (UI/UX Design)", "talent": "Desain Kreatif & UI/UX", "tags": "gemastik ui ux designer wireframe layout prototype figma app design user experience research"},
    {"name": "Festival dan Lomba Seni Siswa Nasional (FLS2N)", "talent": "Desain Kreatif & UI/UX", "tags": "fls2n art seni lukis poster photography design graphic craft creativity"},
    {"name": "National Business Plan Competition (NBPC)", "talent": "Bisnis & Kewirausahaan", "tags": "business plan pitch deck NBPC startup entrepreneur pitching canvas marketing plan financial plan"},
    {"name": "Lomba Debat Nasional", "talent": "Bisnis & Kewirausahaan", "tags": "debat debate english critical thinking speech public speaking business model"},
    {"name": "Lomba Debat Bahasa Indonesia", "talent": "Sosial & Pendidikan", "tags": "debat debate indonesia speech public speaking discussion arguments"},
    {"name": "Kompetisi Pengabdian Sosial", "talent": "Sosial & Pendidikan", "tags": "social NGO community service charity volunteer help society education public service"},
    {"name": "Lomba Masak Nasional", "talent": "Seni Kuliner & Tata Boga", "tags": "masak boga kuliner makanan cooking salon culinaire chef koki"},
    {"name": "Salon Culinaire Indonesia", "talent": "Seni Kuliner & Tata Boga", "tags": "salon culinaire boga kuliner chef cooking competition food pastry"},
    {"name": "Festival dan Lomba Seni Siswa Nasional (FLS2N) - Menyanyi", "talent": "Seni Musik & Pertunjukan", "tags": "fls2n seni menyanyi vokal vokalist lagu choir musik pertunjukan"},
    {"name": "Got Talent Competition", "talent": "Seni Musik & Pertunjukan", "tags": "got talent menyanyi musik dance pertunjukan bakat show musik"},
    {"name": "Olimpiade Olahraga Siswa Nasional (O2SN)", "talent": "Olahraga & Kesehatan Fisik", "tags": "o2sn olahraga fisik lari renang silat karate badminton futsal atlet"},
    {"name": "Pekan Olahraga Mahasiswa (POMNAS)", "talent": "Olahraga & Kesehatan Fisik", "tags": "pomnas olahraga mahasiswa atlet tanding futsal basket badminton silat"},
    {"name": "Lomba Kompetensi Siswa (LKS) Health & Social Care", "talent": "Kesehatan & Keperawatan (Medis)", "tags": "lks care health nurse medis perawat sosial rumah sakit"},
    {"name": "Karya Tulis Ilmiah Kesehatan", "talent": "Kesehatan & Keperawatan (Medis)", "tags": "karya tulis ilmiah riset medis kesehatan obat farmasi penyakit"}
]

EXTRACURRICULAR_REPOSITORY = [
    {"name": "Klub Robotika", "talent": "Robotik", "tags": "robot robotika klub mechanical hardware arduino"},
    {"name": "Coding Club", "talent": "Robotik", "tags": "coding club programming software code computer"},
    {"name": "Karya Ilmiah Remaja", "talent": "Robotik", "tags": "kir karya ilmiah riset research science study"},
    {"name": "Coding Club", "talent": "Programming", "tags": "coding club programming software code computer dev"},
    {"name": "Karya Ilmiah Remaja", "talent": "Programming", "tags": "kir karya ilmiah riset research science study TIK"},
    {"name": "Karya Ilmiah Remaja", "talent": "Sains & Riset", "tags": "kir karya ilmiah riset research science study lab physics biology chemistry"},
    {"name": "Olimpiade Club", "talent": "Sains & Riset", "tags": "olimpiade club kompetisi sains math biology physics astronomy chemistry"},
    {"name": "Klub Desain & Fotografi", "talent": "Desain Kreatif & UI/UX", "tags": "desain fotografi photoshop illustrator figma art visual craft"},
    {"name": "Pramuka (Pubdok)", "talent": "Desain Kreatif & UI/UX", "tags": "pramuka pubdok dokumentasi publikasi foto video media design"},
    {"name": "Koperasi Siswa", "talent": "Bisnis & Kewirausahaan", "tags": "koperasi siswa bisnis jualan retail financial money marketing"},
    {"name": "Debate Club", "talent": "Bisnis & Kewirausahaan", "tags": "debat debate club public speaking english discussion arguments model"},
    {"name": "Pramuka", "talent": "Sosial & Pendidikan", "tags": "pramuka scout leader community volunteer organization"},
    {"name": "Palang Merah Remaja (PMR)", "talent": "Sosial & Pendidikan", "tags": "pmr red cross medicine health volunteer helper community"},
    {"name": "OSIS", "talent": "Sosial & Pendidikan", "tags": "osis organization school leader event communication student relations"},
    {"name": "Klub Memasak / Tata Boga", "talent": "Seni Kuliner & Tata Boga", "tags": "masak boga kuliner chef kue makanan kitchen culinary"},
    {"name": "Kewirausahaan Kuliner", "talent": "Seni Kuliner & Tata Boga", "tags": "bisnis kuliner makanan warung cafe startup boga"},
    {"name": "Paduan Suara / Choir", "talent": "Seni Musik & Pertunjukan", "tags": "choir paduan suara vokal menyanyi musik konser pertunjukan"},
    {"name": "Klub Band / Musik", "talent": "Seni Musik & Pertunjukan", "tags": "band musik lagu gitar piano drum melodi harmoni klub"},
    {"name": "Klub Futsal / Sepakbola", "talent": "Olahraga & Kesehatan Fisik", "tags": "futsal sepakbola bola lari olahraga fisik penjas"},
    {"name": "Klub Basket / Badminton", "talent": "Olahraga & Kesehatan Fisik", "tags": "basket badminton raket olahraga fisik penjas ganda tunggal"},
    {"name": "Palang Merah Remaja (PMR)", "talent": "Kesehatan & Keperawatan (Medis)", "tags": "pmr red cross kesehatan medis pertolongan pertama obat rawat"},
    {"name": "Klub Sains Keperawatan", "talent": "Kesehatan & Keperawatan (Medis)", "tags": "sains keperawatan medis perawat obat biologi anatomi"}
]

TARGET_REPOSITORY = [
    {"name": "Mempelajari mikrokontroler Arduino/Raspberry Pi", "talent": "Robotik", "tags": "arduino raspberry pi mikrokontroler hardware rakit sensor iot"},
    {"name": "Mengembangkan portofolio Internet of Things (IoT)", "talent": "Robotik", "tags": "iot internet of things cloud web sensor app portofolio"},
    {"name": "Mempelajari algoritma dan struktur data lanjutan", "talent": "Programming", "tags": "algoritma struktur data advanced competitive programming codeforces hackerrank logic"},
    {"name": "Membangun aplikasi open-source di GitHub", "talent": "Programming", "tags": "github open source git code app software project sharing repository"},
    {"name": "Mempelajari metode penulisan karya ilmiah", "talent": "Sains & Riset", "tags": "karya ilmiah penulisan paper journal academic research method"},
    {"name": "Membaca jurnal sains bereputasi secara rutin", "talent": "Sains & Riset", "tags": "jurnal sains membaca research paper biology physics chemistry library"},
    {"name": "Mempelajari software desain Figma/Adobe XD", "talent": "Desain Kreatif & UI/UX", "tags": "figma adobe xd ui ux design layout app web graphics wireframe prototype"},
    {"name": "Membuat portofolio case study di Behance", "talent": "Desain Kreatif & UI/UX", "tags": "behance dribbble portfolio case study ui ux design graphic visual"},
    {"name": "Menyusun proposal model bisnis kanvas (BMC)", "talent": "Bisnis & Kewirausahaan", "tags": "bmc business model canvas business plan strategy startup pitch deck presentation"},
    {"name": "Mempelajari dasar analisis keuangan & pemasaran", "talent": "Bisnis & Kewirausahaan", "tags": "keuangan pemasaran finance marketing analysis strategy startup sales excel study"},
    {"name": "Melatih kemampuan public speaking", "talent": "Sosial & Pendidikan", "tags": "public speaking bicara depan umum presentasi communication speech debate talk"},
    {"name": "Mengikuti program volunterisme kemanusiaan", "talent": "Sosial & Pendidikan", "tags": "volunteer relawan kemanusiaan sosial charity NGO community service help"},
    {"name": "Mempelajari teknik sanitasi & kebersihan pangan", "talent": "Seni Kuliner & Tata Boga", "tags": "sanitasi higienis boga kuliner dapur pangan gizi masak"},
    {"name": "Mengembangkan resep kreasi boga orisinal", "talent": "Seni Kuliner & Tata Boga", "tags": "resep masak boga makanan pastry kuliner rasa orisinal"},
    {"name": "Mempelajari teori harmoni dan aransemen musik", "talent": "Seni Musik & Pertunjukan", "tags": "teori harmoni aransemen melodi partitur lagu musik piano vokal"},
    {"name": "Melatih teknik vokal/instrumen secara konsisten", "talent": "Seni Musik & Pertunjukan", "tags": "teknik vokal bernyanyi instrument latihan piano gitar konsisten musik"},
    {"name": "Meningkatkan ketahanan fisik dan teknik olahraga", "talent": "Olahraga & Kesehatan Fisik", "tags": "ketahanan fisik stamina gym lari teknik futsal basket badminton olahraga"},
    {"name": "Mempelajari dasar fisiologi & nutrisi olahraga", "talent": "Olahraga & Kesehatan Fisik", "tags": "fisiologi anatomi nutrisi makan diet kalori suplemen olahraga atlet"},
    {"name": "Mempelajari teknik pertolongan pertama & keperawatan", "talent": "Kesehatan & Keperawatan (Medis)", "tags": "pertolongan pertama p3k medis perawat dokter bidan luka obat rawat"},
    {"name": "Mengikuti seminar kesehatan dan magang klinis", "talent": "Kesehatan & Keperawatan (Medis)", "tags": "seminar kesehatan magang rumah sakit puskesmas klinik perawat medis apoteker"}
]

def recommend_items(query, item_repository, predicted_talent, limit=4):
    relevant_items = [item for item in item_repository if item['talent'] == predicted_talent]
    if not relevant_items:
        # Check if this is a custom dynamic interest not in standard 10 categories
        standards = ['Robotik', 'Programming', 'Sains & Riset', 'Desain Kreatif & UI/UX', 'Bisnis & Kewirausahaan', 'Sosial & Pendidikan', 'Seni Kuliner & Tata Boga', 'Seni Musik & Pertunjukan', 'Olahraga & Kesehatan Fisik', 'Kesehatan & Keperawatan (Medis)']
        if predicted_talent not in standards:
            if item_repository == CAREER_REPOSITORY:
                return [
                    f"{predicted_talent} Professional",
                    f"Spesialis {predicted_talent}",
                    f"Konsultan {predicted_talent}",
                    f"Pendidik / Praktisi {predicted_talent}"
                ][:limit]
            elif item_repository == COMPETITION_REPOSITORY:
                return [
                    f"Kompetisi Nasional {predicted_talent}",
                    f"Lomba Inovasi Mahasiswa {predicted_talent}",
                    f"Festival / Pameran {predicted_talent}"
                ][:limit]
            elif item_repository == EXTRACURRICULAR_REPOSITORY:
                return [
                    f"Klub / Komunitas {predicted_talent}",
                    f"Karya Tulis Ilmiah Bidang {predicted_talent}"
                ][:limit]
            elif item_repository == TARGET_REPOSITORY:
                return [
                    f"Meningkatkan keahlian praktis di bidang {predicted_talent}",
                    f"Membangun portofolio karya dan proyek {predicted_talent}"
                ][:limit]
        relevant_items = item_repository
        
    documents = [item['tags'] for item in relevant_items]
    
    try:
        vectorizer = TfidfVectorizer(stop_words='english')
        tfidf_matrix = vectorizer.fit_transform(documents)
        query_vector = vectorizer.transform([query])
        
        similarity_scores = cosine_similarity(query_vector, tfidf_matrix).flatten()
        ranked_indices = similarity_scores.argsort()[::-1]
        
        recommendations = []
        for idx in ranked_indices:
            recommendations.append(relevant_items[idx]['name'])
            if len(recommendations) >= limit:
                break
        return recommendations
    except Exception as e:
        return [item['name'] for item in relevant_items[:limit]]

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

        # Check if there is a highly specific interest that doesn't fit standard categories
        has_custom_interest = False
        custom_interest_name = ''
        if len(interests) > 0:
            primary_interest = interests[0].strip()
            if len(primary_interest) > 2:
                custom_interest_name = primary_interest.title()
                has_custom_interest = True
                
                lower_interest = primary_interest.lower()
                standards = ['robot', 'coding', 'program', 'sains', 'riset', 'desain', 'ui', 'ux', 'bisnis', 'usaha', 'sosial', 'didik', 'masak', 'boga', 'kuliner', 'chef', 'koki', 'musik', 'vokal', 'olahraga', 'atlet', 'medis', 'dokter', 'perawat', 'tani', 'kebun', 'tanah', 'botani', 'agro', 'ternak', 'hama', 'tanaman']
                for s in standards:
                    if s in lower_interest:
                        has_custom_interest = False
                        break

        # Map to feature names expected by the model
        riasec_r = float(riasec.get('Realistic', 50))
        riasec_i = float(riasec.get('Investigative', 50))
        riasec_a = float(riasec.get('Artistic', 50))
        riasec_s = float(riasec.get('Social', 50))
        riasec_e = float(riasec.get('Enterprising', 50))
        riasec_c = float(riasec.get('Conventional', 50))

        # Grades maps (with defaults)
        grade_math = float(grades.get('Matematika', 70))
        grade_science = float(grades.get('Fisika', 70))
        grade_informatics = float(grades.get('Informatika', 70))
        grade_english = float(grades.get('Bahasa Inggris', 70))

        # Extract general domain grades dynamically
        grade_music = 70.0
        grade_culinary = 70.0
        grade_sports = 70.0
        grade_medical = 70.0
        grade_agriculture = 70.0

        music_list = []
        culinary_list = []
        sports_list = []
        medical_list = []
        agriculture_list = []

        for sub, score in grades.items():
            sub_lower = sub.lower()
            if any(k in sub_lower for k in ['musik', 'vokal', 'solfeggio', 'harmoni', 'diksi', 'instrumen']):
                music_list.append(float(score))
            if any(k in sub_lower for k in ['boga', 'masak', 'makanan', 'patisserie', 'gizi']):
                culinary_list.append(float(score))
            if any(k in sub_lower for k in ['olahraga', 'penjas', 'atletik', 'fisik', 'kesehatan rekreasi']):
                sports_list.append(float(score))
            if any(k in sub_lower for k in ['anatomi', 'farmasi', 'klinis', 'perawat', 'bidan', 'gigi', 'patologi']):
                medical_list.append(float(score))
            if any(k in sub_lower for k in ['tani', 'tanah', 'kebun', 'botani', 'agro', 'ternak', 'hama', 'klimatologi']):
                agriculture_list.append(float(score))

        if music_list: grade_music = sum(music_list) / len(music_list)
        if culinary_list: grade_culinary = sum(culinary_list) / len(culinary_list)
        if sports_list: grade_sports = sum(sports_list) / len(sports_list)
        if medical_list: grade_medical = sum(medical_list) / len(medical_list)
        if agriculture_list: grade_agriculture = sum(agriculture_list) / len(agriculture_list)

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
            'grade_music': grade_music, 'grade_culinary': grade_culinary,
            'grade_sports': grade_sports, 'grade_medical': grade_medical,
            'grade_agriculture': grade_agriculture,
            'achievement_tech': achievement_tech, 'achievement_science': achievement_science,
            'achievement_art': achievement_art
        }])

        # Perform ML prediction
        clf = model_data['model']
        classes = clf.classes_
        probabilities = clf.predict_proba(features)[0]

        # Sort classes by probability
        pred_dict = {classes[i]: float(probabilities[i]) for i in range(len(classes))}

        # Hybrid Expert System: Boost probability if student has outstanding grades in a specific domain
        if grade_agriculture > 85:
            pred_dict['Pertanian & Ilmu Hayati'] = pred_dict.get('Pertanian & Ilmu Hayati', 0.0) + 0.40
        if grade_music > 85:
            pred_dict['Seni Musik & Pertunjukan'] = pred_dict.get('Seni Musik & Pertunjukan', 0.0) + 0.40
        if grade_culinary > 85:
            pred_dict['Seni Kuliner & Tata Boga'] = pred_dict.get('Seni Kuliner & Tata Boga', 0.0) + 0.40
        if grade_sports > 85:
            pred_dict['Olahraga & Kesehatan Fisik'] = pred_dict.get('Olahraga & Kesehatan Fisik', 0.0) + 0.40
        if grade_medical > 85:
            pred_dict['Kesehatan & Keperawatan (Medis)'] = pred_dict.get('Kesehatan & Keperawatan (Medis)', 0.0) + 0.40

        # Normalize probabilities (optional, but sorting works fine even without normalising)
        sorted_preds = sorted(pred_dict.items(), key=lambda item: item[1], reverse=True)

        if has_custom_interest:
            primary_talent = custom_interest_name
            confidence_score = 99.0
            supporting_talents = [
                {'talent': sorted_preds[0][0], 'confidence': round(sorted_preds[0][1] * 100, 2)},
                {'talent': sorted_preds[1][0], 'confidence': round(sorted_preds[1][1] * 100, 2)},
                {'talent': sorted_preds[2][0], 'confidence': round(sorted_preds[2][1] * 100, 2)}
            ]
        else:
            primary_talent = sorted_preds[0][0]
            confidence_score = round(sorted_preds[0][1] * 100, 2)
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
        if has_custom_interest:
            reasoning.append(f"Memiliki bakat kustom terarah dan fokus eksplorasi mandiri di bidang {primary_talent}.")

        # Recommendations mappings
        # Construct dynamic context query for TF-IDF ranking
        query_parts = [primary_talent]
        query_parts.extend(hobbies)
        query_parts.extend(interests)
        query_parts.extend(list(grades.keys()))
        query = " ".join(query_parts).lower()

        careers = recommend_items(query, CAREER_REPOSITORY, primary_talent, limit=4)
        extracurriculars = recommend_items(query, EXTRACURRICULAR_REPOSITORY, primary_talent, limit=3)
        competitions = recommend_items(query, COMPETITION_REPOSITORY, primary_talent, limit=2)
        targets = recommend_items(query, TARGET_REPOSITORY, primary_talent, limit=2)

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
