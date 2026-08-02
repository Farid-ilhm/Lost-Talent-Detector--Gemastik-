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

  // Active tab state for mobile navigation
  selectedTab: string = 'home';
  talentSubTab: string = 'test';

  // RIASEC & AI unified state
  riasecTest: any = null;
  riasecAnswers: { [key: number]: string } = {};
  isAnalyzingAi: boolean = false;

  // Form binds
  profileData = {
    hobbies: '',
    interests: ''
  };

  newAchievement = {
    title: '',
    category: 'teknologi',
    level: 'sekolah',
    rank: '',
    description: ''
  };

  userRole: string | null = null;
  userName: string | null = null;
  
  // Teacher & Institution read-only state
  teacherInfo: any = null;
  teacherStudents: any[] = [];
  institutionInfo: any = null;
  institutionStats: any = null;
  isLoadingRoleData: boolean = false;

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
    this.userRole = this.authService.getUserRole();
    this.userName = this.authService.getUserName();

    if (this.isStudentRole()) {
      this.loadDashboardData();
    } else if (this.userRole === 'guru') {
      this.loadTeacherData();
    } else if (this.userRole === 'institusi') {
      this.loadInstitutionData();
    }
  }

  isStudentRole(): boolean {
    return !this.userRole || ['siswa', 'mahasiswa', 'umum'].includes(this.userRole);
  }

  selectTab(tabName: string) {
    this.selectedTab = tabName;
    if (tabName === 'talent' && !this.riasecTest) {
      this.loadRiasecTest();
    }
  }

  loadTeacherData() {
    this.isLoadingRoleData = true;
    this.apiService.getTeacherStudents().subscribe({
      next: (res: any) => {
        this.isLoadingRoleData = false;
        if (res.success) {
          this.teacherInfo = res.teacher;
          this.teacherStudents = res.students || [];
        }
      },
      error: () => {
        this.isLoadingRoleData = false;
      }
    });
  }

  loadInstitutionData() {
    this.isLoadingRoleData = true;
    this.apiService.getInstitutionStats().subscribe({
      next: (res: any) => {
        this.isLoadingRoleData = false;
        if (res.success) {
          this.institutionInfo = res.data?.institution;
          this.institutionStats = res.data?.stats;
        }
      },
      error: () => {
        this.isLoadingRoleData = false;
      }
    });
  }

  loadDashboardData() {
    this.apiService.getDashboard().subscribe({
      next: (res: any) => {
        if (res.success) {
          const data = res.data || res;
          this.student = data.student;
          this.grades = data.grades || [];
          this.achievements = data.achievements || [];
          this.testResult = data.test_result;
          this.aiAnalysis = data.ai_analysis;

          // Fill inputs
          if (this.student) {
            this.profileData.hobbies = this.student.hobbies ? this.student.hobbies.join(', ') : '';
            this.profileData.interests = this.student.interests ? this.student.interests.join(', ') : '';
          }
        }
      },
      error: async (err: any) => {
        const toast = await this.toastController.create({
          message: 'Gagal memuat data dashboard.',
          duration: 3000,
          color: 'danger'
        });
        await toast.present();
      }
    });
  }

  loadRiasecTest() {
    this.apiService.getRiasecTest().subscribe({
      next: (res: any) => {
        const activeTest = res.test || res.active_test;
        if (res.success && activeTest) {
          this.riasecTest = activeTest;
          this.riasecTest.questions.forEach((q: any) => {
            if (!this.riasecAnswers[q.id]) {
              this.riasecAnswers[q.id] = '';
            }
          });
        }
      }
    });
  }

  isRiasecFormValid(): boolean {
    if (!this.riasecTest || !this.riasecTest.questions) return false;
    return this.riasecTest.questions.every((q: any) => this.riasecAnswers[q.id] && this.riasecAnswers[q.id] !== '');
  }

  onSubmitRiasecTest() {
    if (!this.isRiasecFormValid()) return;
    const finalAnswers: { [key: number]: number } = {};
    for (const key in this.riasecAnswers) {
      finalAnswers[key] = parseInt(this.riasecAnswers[key], 10);
    }

    this.apiService.submitRiasecTest(this.riasecTest.id, finalAnswers).subscribe({
      next: async (res: any) => {
        const alert = await this.alertController.create({
          header: 'Hasil Terkirim',
          message: 'Jawaban tes RIASEC Anda berhasil disimpan. Anda kini dapat melihat Laporan AI.',
          buttons: ['OK']
        });
        await alert.present();
        this.talentSubTab = 'ai';
        this.loadDashboardData();
      },
      error: async (err: any) => {
        let msg = 'Terjadi kesalahan saat mengirim jawaban.';
        if (err.error && err.error.message) msg = err.error.message;
        const alert = await this.alertController.create({
          header: 'Submit Gagal',
          message: msg,
          buttons: ['OK']
        });
        await alert.present();
      }
    });
  }

  async onRunAiAnalysis() {
    this.isAnalyzingAi = true;
    this.apiService.triggerAiAnalysis().subscribe({
      next: async (res: any) => {
        this.isAnalyzingAi = false;
        if (res.success) {
          const toast = await this.toastController.create({
            message: 'Analisis AI berhasil diperbarui!',
            duration: 2000,
            color: 'success'
          });
          await toast.present();
          this.loadDashboardData();
        } else {
          const toast = await this.toastController.create({
            message: res.message || 'Gagal memicu analisis.',
            duration: 2000,
            color: 'warning'
          });
          await toast.present();
        }
      },
      error: async () => {
        this.isAnalyzingAi = false;
        const toast = await this.toastController.create({
          message: 'Gagal memproses analisis bakat AI.',
          duration: 2000,
          color: 'danger'
        });
        await toast.present();
      }
    });
  }

  async onUpdateProfile() {
    const data = {
      hobbies: this.profileData.hobbies.split(',').map(s => s.trim()).filter(s => s.length > 0),
      interests: this.profileData.interests.split(',').map(s => s.trim()).filter(s => s.length > 0)
    };

    this.apiService.updateProfile(data).subscribe({
      next: async (res: any) => {
        const toast = await this.toastController.create({
          message: 'Profil minat berhasil diperbarui.',
          duration: 2000,
          color: 'success'
        });
        await toast.present();
        this.loadDashboardData();
      },
      error: async (err: any) => {
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
      next: async (res: any) => {
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
      error: async (err: any) => {
        const alert = await this.alertController.create({
          header: 'Gagal Mengajukan',
          message: 'Periksa formulir dan coba lagi.',
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
