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

  getDashboard(): Observable<any> {
    const headers = this.authService.getAuthHeaders();
    return this.http.get(`${this.apiUrl}/student/dashboard`, { headers });
  }

  updateProfile(data: { hobbies: string[]; interests: string[]; personality: string }): Observable<any> {
    const headers = this.authService.getAuthHeaders();
    return this.http.put(`${this.apiUrl}/student/interests-hobbies`, data, { headers });
  }

  uploadAchievement(data: { title: string; category: string; level: string; rank: string; description?: string }): Observable<any> {
    const headers = this.authService.getAuthHeaders();
    return this.http.post(`${this.apiUrl}/student/achievements`, data, { headers });
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

  triggerAiAnalysis(): Observable<any> {
    const headers = this.authService.getAuthHeaders();
    return this.http.post(`${this.apiUrl}/student/analyze`, {}, { headers });
  }
}
