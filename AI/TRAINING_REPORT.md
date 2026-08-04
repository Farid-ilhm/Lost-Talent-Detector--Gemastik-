# LostTalent Detector - AI Model Training Report

This report documents the machine learning performance metrics of the hybrid student career guidance system trained on 10000 samples.

## 1. Model Summary
- **Algorithm**: Random Forest Classifier
- **Parameters**: n_estimators=200, max_depth=15, min_samples_split=4, random_state=42
- **Training Accuracy**: 100.00%
- **Testing Accuracy**: 100.00%

## 2. Feature Importance Ranking
Features that have the most influence on prediction outcomes:

| Rank | Feature Name | Importance Score |
|------|--------------|------------------|
| 1 | `riasec_r` | 0.0648 |
| 2 | `grade_sports` | 0.0599 |
| 3 | `grade_robotics` | 0.0548 |
| 4 | `riasec_i` | 0.0536 |
| 5 | `grade_agriculture` | 0.0527 |
| 6 | `riasec_a` | 0.0521 |
| 7 | `riasec_s` | 0.0521 |
| 8 | `grade_design` | 0.0511 |
| 9 | `grade_social` | 0.0511 |
| 10 | `grade_aviation` | 0.0498 |
| 11 | `grade_fishery` | 0.0497 |
| 12 | `grade_math` | 0.0496 |
| 13 | `grade_culinary` | 0.0491 |
| 14 | `grade_medical` | 0.0482 |
| 15 | `riasec_c` | 0.0433 |
| 16 | `grade_science` | 0.0401 |
| 17 | `grade_music` | 0.0381 |
| 18 | `grade_business` | 0.0359 |
| 19 | `riasec_e` | 0.0314 |
| 20 | `grade_english` | 0.0292 |
| 21 | `grade_informatics` | 0.0209 |
| 22 | `achievement_science` | 0.0143 |
| 23 | `achievement_tech` | 0.0047 |
| 24 | `achievement_art` | 0.0033 |

## 3. Classification Report per Class
```
                                 precision    recall  f1-score   support

         Bisnis & Kewirausahaan       1.00      1.00      1.00       153
         Desain Kreatif & UI/UX       1.00      1.00      1.00       153
Kesehatan & Keperawatan (Medis)       1.00      1.00      1.00       139
     Olahraga & Kesehatan Fisik       1.00      1.00      1.00       126
   Penerbangan & Kedirgantaraan       1.00      1.00      1.00       176
           Perikanan & Kelautan       1.00      1.00      1.00       148
      Pertanian & Agroteknologi       1.00      1.00      1.00       152
                    Programming       1.00      1.00      1.00       154
                        Robotik       1.00      1.00      1.00       172
                  Sains & Riset       1.00      1.00      1.00       175
       Seni Kuliner & Tata Boga       1.00      1.00      1.00       150
       Seni Musik & Pertunjukan       1.00      1.00      1.00       167
            Sosial & Pendidikan       1.00      1.00      1.00       135

                       accuracy                           1.00      2000
                      macro avg       1.00      1.00      1.00      2000
                   weighted avg       1.00      1.00      1.00      2000
```
