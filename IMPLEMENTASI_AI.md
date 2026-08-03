1. Struktur Folder

2. knowledge_base.json

3. Algoritma

4. Pseudocode

5. Rumus Scoring

6. Confidence Formula

7. Penalty Formula

8. Evidence Formula

9. Flow AI

10. API Request

11. API Response

12. Prompt Gemini

13. Developer Notes

Knowledge Base

↓

Normalize Input

↓

Alias Matching

↓

Keyword Matching

↓

Subject Matching

↓

Achievement Matching

↓

Interest Matching

↓

RIASEC Matching

↓

Score Calculation

↓

Evidence Calculation

↓

Penalty

↓

Confidence

↓

Random Forest

↓

Recommendation

↓

Prompt Gemini

↓

Return JSON

## PSEUDOCODE

Load Knowledge Base

Normalize Input

For setiap bidang

Hitung Academic

Hitung Achievement

Hitung Hobby

Hitung Interest

Hitung RIASEC

Hitung Evidence

Hitung Penalty

Hitung Final Score

Urutkan

Ambil Top 5

Jika Top1 dan Top2 selisih <5%

Jalankan Random Forest

Generate Recommendation

Generate Prompt

Return JSON

# IMPLEMENTASI_AI.md

# Lost Talent Detector AI v3
## Technical Implementation Specification

Versi : 1.0

---

# Tujuan

Dokumen ini berisi spesifikasi implementasi AI untuk aplikasi **Lost Talent Detector**.

Dokumen ini menjadi acuan developer dalam membangun sistem AI sehingga implementasi tetap konsisten dengan rancangan yang telah dibuat.

---

# AI Workflow

```text
User Input
      │
      ▼
Normalize Input
      │
      ▼
Feature Extraction
      │
      ▼
Knowledge Base Matching
      │
      ▼
Scoring Engine
      │
      ▼
Evidence Aggregation
      │
      ▼
Machine Learning Validation
      │
      ▼
Recommendation Engine
      │
      ▼
LLM Narrative
      │
      ▼
Return JSON
```

---

# Struktur Folder

```text
ai/

│

├── api/

│

├── feature_extraction/

│

├── knowledge_base/

│

├── scoring/

│

├── evidence/

│

├── recommendation/

│

├── machine_learning/

│

├── explainable/

│

├── prompt/

│

├── utils/

│

├── dataset/

│

├── models/

│

└── knowledge/

      knowledge_base.json
```

---

# Data Input

AI menerima data berikut.

```json
{
    "academic": [],
    "achievement": [],
    "interest": [],
    "hobby": [],
    "riasec": {}
}
```

---

# Tahap 1
## Normalize Input

Semua input diubah menjadi format standar.

Contoh

"Coding"

↓

coding

↓

programming

---

"MIG Welding"

↓

mig welding

↓

mig

↓

pengelasan

---

"Developer"

↓

developer

↓

programming

---

Tujuan

- lowercase
- menghapus simbol
- alias matching
- keyword matching

---

# Tahap 2
## Feature Extraction

Feature Extraction mengambil informasi penting dari input pengguna.

Output

```json
{
    "matched_subjects":[],
    "matched_hobbies":[],
    "matched_interests":[],
    "matched_achievements":[],
    "riasec":{}
}
```

---

# Tahap 3
## Knowledge Base Matching

Knowledge Base menjadi sumber kebenaran utama (Source of Truth).

Setiap bidang memiliki:

- alias
- subject
- hobby
- interest
- achievement
- certification
- career
- competition
- roadmap
- riasec

Contoh

```json
{
"id":"programming",

"name":"Programming",

"aliases":[
"coding",
"developer",
"software engineer"
],

"subjects":[
"Informatika",
"Algoritma",
"Basis Data"
],

"hobbies":[
"Coding",
"Membuat Website"
],

"interests":[
"Backend",
"Frontend"
],

"riasec":[
"Investigative",
"Conventional"
]
}
```

---

# Tahap 4
## Scoring Engine

Komponen penilaian

| Komponen | Bobot |
|----------|-------|
| Academic | 35 |
| Achievement | 20 |
| Interest | 20 |
| Hobby | 15 |
| RIASEC | 10 |

---

Rumus

```
Final Score

=

Academic

+

Achievement

+

Interest

+

Hobby

+

RIASEC

-

Penalty
```

---

# Tahap 5
## Evidence Aggregation

Hitung jumlah evidence.

Contoh

Programming

Academic

✔

Achievement

✔

Interest

✔

Hobby

✔

RIASEC

✔

Positive Evidence

5

Negative Evidence

0

---

Programming

Academic

✔

Achievement

✖

Interest

✖

Hobby

✖

RIASEC

✖

Positive

1

Negative

4

---

Coverage

```
Coverage

=

Positive Evidence

/

Total Evidence
```

---

# Tahap 6
## Penalty

Jika hanya satu indikator yang tinggi tetapi indikator lain bertentangan maka sistem memberikan penalti.

Contoh

Nilai Informatika

98

Tetapi

Minat

Welder

Prestasi

Welding

Hobi

Mengelas

↓

Programming mendapat penalti.

---

# Tahap 7
## Confidence

Confidence dihitung dari

- Final Score
- Evidence Coverage
- ML Agreement

Semakin banyak evidence maka confidence semakin tinggi.

---

# Tahap 8
## Machine Learning Validation

Model

Random Forest

Random Forest hanya digunakan apabila

```
Selisih Top1

dan

Top2

≤5%
```

Jika selisih besar maka hasil langsung menggunakan Scoring Engine.

---

# Tahap 9
## Recommendation Engine

Menghasilkan

- Career
- Competition
- Organization
- Certification
- Roadmap

berdasarkan bidang dengan skor tertinggi.

---

# Tahap 10
## LLM

LLM tidak boleh menentukan bakat.

LLM hanya membuat narasi.

Input

```json
{
    "talent":"Programming",

    "confidence":95,

    "reason":[

    ],

    "recommendation":{

    }

}
```

Output

Narasi penjelasan.

---

# API

POST

```
/api/ai/analyze
```

Request

```json
{
    "academic":[],
    "achievement":[],
    "interest":[],
    "hobby":[],
    "riasec":{}
}
```

---

Response

```json
{
    "primary_talent":"Programming",

    "secondary_talent":[

    ],

    "confidence":95,

    "scores":{

    },

    "evidence":{

    },

    "recommendation":{

    },

    "analysis":""
}
```

---

# Pseudocode

```
Load Knowledge Base

↓

Normalize Input

↓

Extract Features

↓

Match Knowledge Base

↓

Calculate Academic Score

↓

Calculate Achievement Score

↓

Calculate Interest Score

↓

Calculate Hobby Score

↓

Calculate RIASEC Score

↓

Aggregate Evidence

↓

Apply Penalty

↓

Calculate Final Score

↓

Sort Descending

↓

Take Top 5

↓

IF Top1-Top2 <= 5%

Run Random Forest

ENDIF

↓

Generate Recommendation

↓

Generate Explainable AI

↓

Generate LLM Narrative

↓

Return JSON
```

---

# Developer Rules

1. Knowledge Base menjadi sumber kebenaran utama.
2. Machine Learning hanya sebagai validator.
3. LLM tidak boleh mengubah hasil AI.
4. Semua rekomendasi harus memiliki alasan (Explainable AI).
5. Penambahan bidang baru cukup melalui `knowledge_base.json`.
6. Hindari hardcode nama bidang di dalam kode.
7. Seluruh proses harus bersifat modular agar mudah dikembangkan.

---

# Target Output

AI harus mampu memberikan:

- Primary Talent
- Secondary Talent
- Confidence Score
- Evidence Summary
- Explainable Reasoning
- Career Recommendation
- Competition Recommendation
- Certification Recommendation
- Learning Roadmap

Semua hasil harus konsisten dengan data pengguna dan dapat dijelaskan secara transparan.