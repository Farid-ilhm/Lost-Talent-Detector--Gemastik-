import pandas as pd
import numpy as np
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.metrics import classification_report
import pickle
import os

def generate_synthetic_data(num_samples=1500):
    np.random.seed(42)
    classes = [
        'Robotik', 'Programming', 'Sains & Riset', 'Desain Kreatif & UI/UX',
        'Bisnis & Kewirausahaan', 'Sosial & Pendidikan', 'Seni Kuliner & Tata Boga',
        'Seni Musik & Pertunjukan', 'Olahraga & Kesehatan Fisik',
        'Kesehatan & Keperawatan (Medis)', 'Pertanian & Agroteknologi',
        'Perikanan & Kelautan', 'Penerbangan & Kedirgantaraan'
    ]
    
    rows = []
    for _ in range(num_samples):
        # Pick a target class
        target = np.random.choice(classes)
        
        # Initialize default values
        r = np.random.randint(20, 70)
        i = np.random.randint(20, 70)
        a = np.random.randint(20, 70)
        s = np.random.randint(20, 70)
        e = np.random.randint(20, 70)
        c = np.random.randint(20, 70)
        
        math = np.random.randint(50, 80)
        science = np.random.randint(50, 80)
        info = np.random.randint(50, 80)
        eng = np.random.randint(50, 80)
        music = np.random.randint(50, 80)
        culinary = np.random.randint(50, 80)
        sports = np.random.randint(50, 80)
        medical = np.random.randint(50, 80)
        agriculture = np.random.randint(50, 80)
        fishery = np.random.randint(50, 80)
        robotics = np.random.randint(50, 80)
        aviation = np.random.randint(50, 80)
        
        tech_ach = np.random.randint(0, 2)
        sci_ach = np.random.randint(0, 2)
        art_ach = np.random.randint(0, 2)
        
        # Boost features based on the chosen target class to create clear signals
        if target == 'Robotik':
            r = np.random.randint(75, 100)
            robotics = np.random.randint(85, 100)
            info = np.random.randint(70, 90)
            math = np.random.randint(75, 100)
            tech_ach = np.random.randint(1, 4)
        elif target == 'Programming':
            i = np.random.randint(75, 100)
            c = np.random.randint(70, 95)
            info = np.random.randint(85, 100)
            tech_ach = np.random.randint(1, 4)
        elif target == 'Sains & Riset':
            i = np.random.randint(80, 100)
            science = np.random.randint(85, 100)
            math = np.random.randint(80, 100)
            sci_ach = np.random.randint(1, 4)
        elif target == 'Desain Kreatif & UI/UX':
            a = np.random.randint(80, 100)
            art_ach = np.random.randint(1, 4)
            eng = np.random.randint(75, 100)
        elif target == 'Bisnis & Kewirausahaan':
            e = np.random.randint(80, 100)
            eng = np.random.randint(80, 100)
            s = np.random.randint(65, 90)
        elif target == 'Seni Kuliner & Tata Boga':
            r = np.random.randint(70, 95)
            a = np.random.randint(70, 95)
            science = np.random.randint(75, 95)
            culinary = np.random.randint(80, 100)
        elif target == 'Seni Musik & Pertunjukan':
            a = np.random.randint(85, 100)
            s = np.random.randint(70, 95)
            art_ach = np.random.randint(1, 4)
            music = np.random.randint(80, 100)
        elif target == 'Olahraga & Kesehatan Fisik':
            r = np.random.randint(85, 100)
            s = np.random.randint(65, 90)
            sports = np.random.randint(80, 100)
        elif target == 'Kesehatan & Keperawatan (Medis)':
            i = np.random.randint(75, 95)
            s = np.random.randint(75, 95)
            science = np.random.randint(80, 100)
            medical = np.random.randint(80, 100)
        elif target == 'Sosial & Pendidikan':
            s = np.random.randint(80, 100)
            e = np.random.randint(70, 95)
            eng = np.random.randint(80, 100)
        elif target == 'Pertanian & Agroteknologi':
            r = np.random.randint(75, 100)
            i = np.random.randint(70, 95)
            science = np.random.randint(80, 100)
            agriculture = np.random.randint(80, 100)
        elif target == 'Perikanan & Kelautan':
            r = np.random.randint(75, 100)
            i = np.random.randint(75, 95)
            science = np.random.randint(80, 100)
            fishery = np.random.randint(80, 100)
            sci_ach = np.random.randint(1, 4)
        elif target == 'Penerbangan & Kedirgantaraan':
            r = np.random.randint(75, 100)
            i = np.random.randint(75, 95)
            science = np.random.randint(80, 100)
            aviation = np.random.randint(85, 100)
            sci_ach = np.random.randint(1, 4)
            
        rows.append({
            'riasec_r': r, 'riasec_i': i, 'riasec_a': a,
            'riasec_s': s, 'riasec_e': e, 'riasec_c': c,
            'grade_math': math, 'grade_science': science,
            'grade_informatics': info, 'grade_english': eng,
            'grade_music': music, 'grade_culinary': culinary,
            'grade_sports': sports, 'grade_medical': medical,
            'grade_agriculture': agriculture, 'grade_fishery': fishery,
            'grade_robotics': robotics, 'grade_aviation': aviation,
            'achievement_tech': tech_ach, 'achievement_science': sci_ach,
            'achievement_art': art_ach,
            'talent': target
        })
        
    return pd.DataFrame(rows)

def main():
    print("Generating synthetic student profile data (10,000 samples)...")
    df = generate_synthetic_data(10000)
    
    # Split features and target
    X = df.drop(columns=['talent'])
    y = df['talent']
    
    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
    
    print("Training Random Forest Classifier model (200 estimators, depth 15)...")
    model = RandomForestClassifier(n_estimators=200, random_state=42, max_depth=15, min_samples_split=4)
    model.fit(X_train, y_train)
    
    # Evaluation
    train_acc = model.score(X_train, y_train)
    test_acc = model.score(X_test, y_test)
    print(f"Training Accuracy: {train_acc * 100:.2f}%")
    print(f"Testing Accuracy: {test_acc * 100:.2f}%")
    
    y_pred = model.predict(X_test)
    class_report = classification_report(y_test, y_pred)
    print("\nClassification Report:")
    print(class_report)
    
    # Calculate Feature Importance
    importances = model.feature_importances_
    indices = np.argsort(importances)[::-1]
    feature_ranking = []
    for f in range(X.shape[1]):
        feature_ranking.append((X.columns[indices[f]], importances[indices[f]]))
    
    # Write a highly detailed academic Training Report for Gemastik
    report_path = os.path.join(os.path.dirname(__file__), 'TRAINING_REPORT.md')
    with open(report_path, 'w', encoding='utf-8') as rf:
        rf.write("# LostTalent Detector - AI Model Training Report\n\n")
        rf.write(f"This report documents the machine learning performance metrics of the hybrid student career guidance system trained on {len(df)} samples.\n\n")
        rf.write("## 1. Model Summary\n")
        rf.write("- **Algorithm**: Random Forest Classifier\n")
        rf.write("- **Parameters**: n_estimators=200, max_depth=15, min_samples_split=4, random_state=42\n")
        rf.write(f"- **Training Accuracy**: {train_acc * 100:.2f}%\n")
        rf.write(f"- **Testing Accuracy**: {test_acc * 100:.2f}%\n\n")
        
        rf.write("## 2. Feature Importance Ranking\n")
        rf.write("Features that have the most influence on prediction outcomes:\n\n")
        rf.write("| Rank | Feature Name | Importance Score |\n")
        rf.write("|------|--------------|------------------|\n")
        for idx, (f_name, score) in enumerate(feature_ranking):
            rf.write(f"| {idx+1} | `{f_name}` | {score:.4f} |\n")
        rf.write("\n")
        
        rf.write("## 3. Classification Report per Class\n")
        rf.write("```\n")
        rf.write(class_report)
        rf.write("```\n")
        
    print(f"\nAcademic Training Report successfully saved to: {report_path}")
    
    # Save model and feature mapping
    model_data = {
        'model': model,
        'features': list(X.columns)
    }
    
    model_path = os.path.join(os.path.dirname(__file__), 'talent_model.pkl')
    with open(model_path, 'wb') as f:
        pickle.dump(model_data, f)
        
    print(f"Model successfully saved to: {model_path}")

if __name__ == '__main__':
    main()

