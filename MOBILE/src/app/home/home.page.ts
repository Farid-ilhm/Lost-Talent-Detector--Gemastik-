import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../services/auth.service';
import { ApiService } from '../services/api.service';
import { AlertController, ToastController } from '@ionic/angular';

@Component({
  selector: 'app-home',
  templateUrl: 'home.page.html',
  styleUrls: ['home.page.scss'],
  standalone: false,
})
export class HomePage {
  student: any = null;
  grades: any[] = [];
  achievements: any[] = [];
  testResult: any = null;
  aiAnalysis: any = null;

  // Form binds
  profileData = {
    hobbies: '',
    interests: '',
    personality: ''
  };

  newAchievement = {
    title: '',
    category: 'teknologi',
    level: 'sekolah',
    rank: '',
    description: ''
  };

  constructor(
    private authService: AuthService,
    private apiService: ApiService,
    private router: Router,
    private alertController: AlertController,
    private toastController: ToastController
  ) {}

  ionViewWillEnter() {
    if (!this.authService.isAuthenticated()) {
      this.router.navigateByUrl('/login');
      return;
    }
    this.loadDashboardData();
  }

  loadDashboardData() {
    this.apiService.getDashboard().subscribe({
      next: (res) => {
        if (res.success) {
          this.student = res.student;
          this.grades = res.grades || [];
          this.achievements = res.achievements || [];
          this.testResult = res.test_result;
          this.aiAnalysis = res.ai_analysis;

          // Fill inputs
          this.profileData.hobbies = this.student.hobbies ? this.student.hobbies.join(', ') : '';
          this.profileData.interests = this.student.interests ? this.student.interests.join(', ') : '';
          this.profileData.personality = this.student.personality || '';
        }
      },
      error: async (err) => {
        const toast = await this.toastController.create({
          message: 'Gagal memuat data dashboard.',
          duration: 3000,
          color: 'danger'
        });
        await toast.present();
      }
    });
  }

  async onUpdateProfile() {
    const data = {
      hobbies: this.profileData.hobbies.split(',').map(s => s.trim()).filter(s => s.length > 0),
      interests: this.profileData.interests.split(',').map(s => s.trim()).filter(s => s.length > 0),
      personality: this.profileData.personality
    };

    this.apiService.updateProfile(data).subscribe({
      next: async (res) => {
        const toast = await this.toastController.create({
          message: 'Profil minat berhasil diperbarui.',
          duration: 2000,
          color: 'success'
        });
        await toast.present();
        this.loadDashboardData();
      },
      error: async (err) => {
        const alert = await this.alertController.create({
          header: 'Gagal Update',
          message: 'Gagal memperbarui profil minat.',
          buttons: ['OK']
        });
        await alert.present();
      }
    });
  }

  async onSubmitAchievement() {
    this.apiService.uploadAchievement(this.newAchievement).subscribe({
      next: async (res) => {
        const toast = await this.toastController.create({
          message: 'Sertifikat prestasi berhasil diajukan.',
          duration: 2000,
          color: 'success'
        });
        await toast.present();
        
        // Reset form
        this.newAchievement = {
          title: '',
          category: 'teknologi',
          level: 'sekolah',
          rank: '',
          description: ''
        };

        this.loadDashboardData();
      },
      error: async (err) => {
        const alert = await this.alertController.create({
          header: 'Gagal Mengajukan',
          message: 'Periksa formulir dan coba lagi.',
          buttons: ['OK']
        });
        await alert.present();
      }
    });
  }

  async onTriggerAi() {
    this.apiService.triggerAiAnalysis().subscribe({
      next: async (res) => {
        const toast = await this.toastController.create({
          message: 'Analisis AI berhasil diperbarui!',
          duration: 2000,
          color: 'success'
        });
        await toast.present();
        this.loadDashboardData();
      },
      error: async (err) => {
        const alert = await this.alertController.create({
          header: 'Gagal Analisis AI',
          message: 'Gagal memicu analisis bakat AI.',
          buttons: ['OK']
        });
        await alert.present();
      }
    });
  }

  onLogout() {
    this.authService.logout().subscribe({
      next: () => {
        this.router.navigateByUrl('/login');
      },
      error: () => {
        this.authService.clearStorage();
        this.router.navigateByUrl('/login');
      }
    });
  }
}
