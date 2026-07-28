import pandas as pd
import numpy as np
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.metrics import classification_report
import pickle
import os

def generate_synthetic_data(num_samples=1000):
    np.random.seed(42)
    
    # 1. Generate inputs
    riasec_r = np.random.randint(20, 100, num_samples)
    riasec_i = np.random.randint(20, 100, num_samples)
    riasec_a = np.random.randint(20, 100, num_samples)
    riasec_s = np.random.randint(20, 100, num_samples)
    riasec_e = np.random.randint(20, 100, num_samples)
    riasec_c = np.random.randint(20, 100, num_samples)
    
    grade_math = np.random.randint(50, 100, num_samples)
    grade_science = np.random.randint(50, 100, num_samples)
    grade_informatics = np.random.randint(50, 100, num_samples)
    grade_english = np.random.randint(50, 100, num_samples)
    
    achievement_tech = np.random.randint(0, 4, num_samples)
    achievement_science = np.random.randint(0, 4, num_samples)
    achievement_art = np.random.randint(0, 4, num_samples)
    
    data = pd.DataFrame({
        'riasec_r': riasec_r, 'riasec_i': riasec_i, 'riasec_a': riasec_a,
        'riasec_s': riasec_s, 'riasec_e': riasec_e, 'riasec_c': riasec_c,
        'grade_math': grade_math, 'grade_science': grade_science,
        'grade_informatics': grade_informatics, 'grade_english': grade_english,
        'achievement_tech': achievement_tech, 'achievement_science': achievement_science,
        'achievement_art': achievement_art
    })
    
    # 2. Heuristics to assign target labels (classes)
    targets = []
    for idx, row in data.iterrows():
        # Score calculation for each class with some random noise
        scores = {
            'Robotik': (row['grade_math'] * 0.2 + row['grade_informatics'] * 0.3 + 
                        row['grade_science'] * 0.1 + row['riasec_r'] * 0.3 + 
                        row['riasec_i'] * 0.2 + row['achievement_tech'] * 15 + 
                        np.random.normal(0, 5)),
            
            'Programming': (row['grade_informatics'] * 0.4 + row['grade_math'] * 0.2 + 
                            row['riasec_i'] * 0.4 + row['riasec_c'] * 0.2 + 
                            row['achievement_tech'] * 12 + 
                            np.random.normal(0, 5)),
            
            'Sains & Riset': (row['grade_science'] * 0.4 + row['grade_math'] * 0.3 + 
                              row['riasec_i'] * 0.5 + row['achievement_science'] * 15 + 
                              np.random.normal(0, 5)),
            
            'Desain Kreatif & UI/UX': (row['riasec_a'] * 0.6 + row['riasec_i'] * 0.2 + 
                                       row['achievement_art'] * 15 + row['grade_english'] * 0.1 + 
                                       np.random.normal(0, 5)),
            
            'Bisnis & Kewirausahaan': (row['riasec_e'] * 0.5 + row['riasec_s'] * 0.2 + 
                                       row['riasec_c'] * 0.2 + row['grade_english'] * 0.2 + 
                                       np.random.normal(0, 5)),
            
            'Sosial & Pendidikan': (row['riasec_s'] * 0.6 + row['riasec_e'] * 0.2 + 
                                    row['grade_english'] * 0.2 + 
                                    np.random.normal(0, 5))
        }
        
        # Select key with max score
        primary_talent = max(scores, key=scores.get)
        targets.append(primary_talent)
        
    data['talent'] = targets
    return data

def main():
    print("Generating synthetic student profile data...")
    df = generate_synthetic_data(1200)
    
    # Split features and target
    X = df.drop(columns=['talent'])
    y = df['talent']
    
    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
    
    print("Training Random Forest Classifier model...")
    model = RandomForestClassifier(n_estimators=100, random_state=42, max_depth=12)
    model.fit(X_train, y_train)
    
    # Evaluation
    train_acc = model.score(X_train, y_train)
    test_acc = model.score(X_test, y_test)
    print(f"Training Accuracy: {train_acc * 100:.2f}%")
    print(f"Testing Accuracy: {test_acc * 100:.2f}%")
    
    y_pred = model.predict(X_test)
    print("\nClassification Report:")
    print(classification_report(y_test, y_pred))
    
    # Save model and feature mapping
    model_data = {
        'model': model,
        'features': list(X.columns)
    }
    
    model_path = os.path.join(os.path.dirname(__file__), 'talent_model.pkl')
    with open(model_path, 'wb') as f:
        pickle.dump(model_data, f)
        
    print(f"\nModel successfully saved to: {model_path}")

if __name__ == '__main__':
    main()
