import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { tap } from 'rxjs/operators';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  // Configurable base URL. Change to your computer's IP address (e.g., http://192.168.1.5:8000/api) 
  // if testing on a physical mobile device or emulator.
  private apiUrl = 'http://localhost:8000/api';

  constructor(private http: HttpClient) {}

  login(credentials: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/login`, credentials).pipe(
      tap((res: any) => {
        const token = res && (res.access_token || res.token);
        if (token) {
          localStorage.setItem('auth_token', token);
          if (res.user) {
            localStorage.setItem('user_role', res.user.role);
            localStorage.setItem('user_name', res.user.name);
          }
        }
      })
    );
  }

  register(userData: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/register`, userData);
  }

  verifyOtp(data: { email: string; otp_code: string }): Observable<any> {
    return this.http.post(`${this.apiUrl}/verify-otp`, data).pipe(
      tap((res: any) => {
        const token = res && (res.access_token || res.token);
        if (token) {
          localStorage.setItem('auth_token', token);
          if (res.user) {
            localStorage.setItem('user_role', res.user.role);
            localStorage.setItem('user_name', res.user.name);
          }
        }
      })
    );
  }

  resendOtp(data: { email: string }): Observable<any> {
    return this.http.post(`${this.apiUrl}/resend-otp`, data);
  }

  logout(): Observable<any> {
    const headers = this.getAuthHeaders();
    return this.http.post(`${this.apiUrl}/logout`, {}, { headers }).pipe(
      tap(() => {
        this.clearStorage();
      })
    );
  }

  getToken(): string | null {
    return localStorage.getItem('auth_token');
  }

  getUserRole(): string | null {
    return localStorage.getItem('user_role');
  }

  getUserName(): string | null {
    return localStorage.getItem('user_name');
  }

  isAuthenticated(): boolean {
    return !!this.getToken();
  }

  clearStorage() {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user_role');
    localStorage.removeItem('user_name');
  }

  getAuthHeaders(): HttpHeaders {
    const token = this.getToken();
    return new HttpHeaders({
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    });
  }
}
