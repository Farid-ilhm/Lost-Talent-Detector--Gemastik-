import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { AuthService } from './auth.service';

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private apiUrl = 'http://localhost:8000/api';

  constructor(
    private http: HttpClient,
    private authService: AuthService
  ) {}

  getPublicInstitutions(): Observable<any> {
    return this.http.get(`${this.apiUrl}/institutions`);
  }

  getDashboard(): Observable<any> {
    const headers = this.authService.getAuthHeaders();
    return this.http.get(`${this.apiUrl}/student/dashboard`, { headers });
  }

  updateProfile(data: { hobbies: string[]; interests: string[]; personality?: string }): Observable<any> {
    const headers = this.authService.getAuthHeaders();
    return this.http.put(`${this.apiUrl}/student/interests-hobbies`, data, { headers });
  }

  uploadAchievement(data: { title: string; category: string; level: string; rank: string; description?: string }): Observable<any> {
    const headers = this.authService.getAuthHeaders();
    return this.http.post(`${this.apiUrl}/student/achievements`, data, { headers });
  }

  deleteAchievement(achievementId: number): Observable<any> {
    const headers = this.authService.getAuthHeaders();
    return this.http.delete(`${this.apiUrl}/student/achievements/${achievementId}`, { headers });
  }

  getRiasecTest(): Observable<any> {
    const headers = this.authService.getAuthHeaders();
    return this.http.get(`${this.apiUrl}/student/test`, { headers });
  }

  submitRiasecTest(testId: number, answers: { [key: number]: number }): Observable<any> {
    const headers = this.authService.getAuthHeaders();
    const formattedAnswers = Object.keys(answers).map(qId => ({
      question_id: parseInt(qId, 10),
      value: answers[parseInt(qId, 10)]
    }));
    const body = {
      test_id: testId,
      answers: formattedAnswers
    };
    return this.http.post(`${this.apiUrl}/student/test/submit`, body, { headers });
  }

  resetRiasecTest(): Observable<any> {
    const headers = this.authService.getAuthHeaders();
    return this.http.post(`${this.apiUrl}/student/test/reset`, {}, { headers });
  }

  triggerAiAnalysis(): Observable<any> {
    const headers = this.authService.getAuthHeaders();
    return this.http.post(`${this.apiUrl}/student/analyze`, {}, { headers });
  }

  resetAiAnalysis(): Observable<any> {
    const headers = this.authService.getAuthHeaders();
    return this.http.post(`${this.apiUrl}/student/analyze/reset`, {}, { headers });
  }

  saveIndependentGrade(data: { semester: number; subject_name: string; score: number }): Observable<any> {
    const headers = this.authService.getAuthHeaders();
    return this.http.post(`${this.apiUrl}/student/grades`, data, { headers });
  }

  deleteIndependentGrade(gradeId: number): Observable<any> {
    const headers = this.authService.getAuthHeaders();
    return this.http.delete(`${this.apiUrl}/student/grades/${gradeId}`, { headers });
  }

  getTeacherData(): Observable<any> {
    const headers = this.authService.getAuthHeaders();
    return this.http.get(`${this.apiUrl}/teacher/students`, { headers });
  }

  getTeacherStudents(): Observable<any> {
    const headers = this.authService.getAuthHeaders();
    return this.http.get(`${this.apiUrl}/teacher/students`, { headers });
  }

  getInstitutionStats(): Observable<any> {
    const headers = this.authService.getAuthHeaders();
    return this.http.get(`${this.apiUrl}/institution/stats`, { headers });
  }

  bulkDeleteIndependentGrades(ids: number[], all: boolean = false): Observable<any> {
    const headers = this.authService.getAuthHeaders();
    return this.http.post(`${this.apiUrl}/student/grades/bulk-delete`, { ids, all }, { headers });
  }

  bulkDeleteAchievements(ids: number[], all: boolean = false): Observable<any> {
    const headers = this.authService.getAuthHeaders();
    return this.http.post(`${this.apiUrl}/student/achievements/bulk-delete`, { ids, all }, { headers });
  }
}
