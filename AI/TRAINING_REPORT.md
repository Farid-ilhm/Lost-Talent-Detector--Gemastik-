# LostTalent Detector - AI Model Training Report

This report documents the machine learning performance metrics of the hybrid student career guidance system trained on 10000 samples.

## 1. Model Summary
- **Algorithm**: Random Forest Classifier
- **Parameters**: n_estimators=200, max_depth=15, min_samples_split=4, random_state=42
- **Training Accuracy**: 99.99%
- **Testing Accuracy**: 97.70%

## 2. Feature Importance Ranking
Features that have the most influence on prediction outcomes:

| Rank | Feature Name | Importance Score |
|------|--------------|------------------|
| 1 | `riasec_s` | 0.0851 |
| 2 | `riasec_r` | 0.0727 |
| 3 | `grade_aviation` | 0.0685 |
| 4 | `grade_medical` | 0.0660 |
| 5 | `riasec_a` | 0.0654 |
| 6 | `grade_agriculture` | 0.0635 |
| 7 | `grade_fishery` | 0.0611 |
| 8 | `grade_robotics` | 0.0569 |
| 9 | `grade_culinary` | 0.0565 |
| 10 | `riasec_i` | 0.0561 |
| 11 | `grade_music` | 0.0544 |
| 12 | `riasec_e` | 0.0514 |
| 13 | `grade_science` | 0.0490 |
| 14 | `grade_math` | 0.0459 |
| 15 | `riasec_c` | 0.0383 |
| 16 | `grade_sports` | 0.0316 |
| 17 | `grade_english` | 0.0294 |
| 18 | `grade_informatics` | 0.0268 |
| 19 | `achievement_science` | 0.0093 |
| 20 | `achievement_art` | 0.0078 |
| 21 | `achievement_tech` | 0.0045 |

## 3. Classification Report per Class
```
                                 precision    recall  f1-score   support

         Bisnis & Kewirausahaan       0.91      0.78      0.84       157
         Desain Kreatif & UI/UX       1.00      1.00      1.00       166
Kesehatan & Keperawatan (Medis)       1.00      1.00      1.00       142
     Olahraga & Kesehatan Fisik       1.00      1.00      1.00       146
   Penerbangan & Kedirgantaraan       1.00      1.00      1.00       167
           Perikanan & Kelautan       1.00      1.00      1.00       136
      Pertanian & Agroteknologi       1.00      1.00      1.00       153
                    Programming       1.00      1.00      1.00       148
                        Robotik       1.00      1.00      1.00       160
                  Sains & Riset       1.00      1.00      1.00       156
       Seni Kuliner & Tata Boga       1.00      1.00      1.00       166
       Seni Musik & Pertunjukan       1.00      1.00      1.00       162
            Sosial & Pendidikan       0.79      0.91      0.85       141

                       accuracy                           0.98      2000
                      macro avg       0.98      0.98      0.98      2000
                   weighted avg       0.98      0.98      0.98      2000
```
