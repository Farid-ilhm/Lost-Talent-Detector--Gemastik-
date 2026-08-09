import { Component } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { Location } from '@angular/common';
import { AuthService } from '../services/auth.service';
import { ApiService } from '../services/api.service';
import { AlertController, ToastController, NavController, Platform } from '@ionic/angular';
import { App } from '@capacitor/app';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-home',
  templateUrl: 'home.page.html',
  styleUrls: ['home.page.scss'],
  standalone: false,
})
export class HomePage {
  student: any = null;
  serverUrl: string = 'https://losttalentdetector.my.id/';
  grades: any[] = [];
  achievements: any[] = [];
  testResult: any = null;
  aiAnalysis: any = null;
  teachers: any[] = [];
  isLoadingDashboard: boolean = false;
  dashboardError: string | null = null;

  // Announcements state
  announcements: any[] = [];
  announcementFilter: string = 'semua';
  isLoadingAnnouncements: boolean = false;

  // Selection states for bulk deletion
  isSelectingGrades: boolean = false;
  selectedGrades: Set<number> = new Set<number>();
  isSelectingCerts: boolean = false;
  selectedCerts: Set<number> = new Set<number>();

  // Sidebar & Back button state
  isSidebarOpen: boolean = false;
  private backButtonSubscription?: Subscription;
  private lastBackPressOnHome = 0;

  toggleSidebar() {
    this.isSidebarOpen = !this.isSidebarOpen;
  }

  closeSidebar() {
    this.isSidebarOpen = false;
  }

  openTabFromSidebar(tabName: string) {
    this.selectTab(tabName);
    this.closeSidebar();
  }

  getTabTitle(): string {
    switch (this.selectedTab) {
      case 'home': return 'Beranda';
      case 'profile': return 'Profil Pengguna';
      case 'info': return 'Papan Informasi';
      case 'grades': return 'Nilai Akademik';
      case 'achievements': return 'Sertifikat Prestasi';
      case 'talent': return 'Tes & Analisis AI';
      default: return 'Beranda';
    }
  }

  // Active tab state for mobile navigation
  selectedTab: string = 'home';
  talentSubTab: string = 'interest';

  // RIASEC & AI unified state
  riasecTest: any = null;
  riasecAnswers: { [key: number]: string } = {};
  isAnalyzingAi: boolean = false;

  // Form binds
  profileData = {
    hobbies: '',
    interests: ''
  };

  selectedFileName: string = '';

  newAchievement = {
    title: '',
    category: 'teknologi',
    level: 'sekolah',
    rank: '',
    description: '',
    certificate: ''
  };

  newGrade = {
    semester: 1,
    subject_name: '',
    score: null as number | null
  };

  userRole: string | null = null;
  userName: string | null = null;
  
  // Teacher & Institution read-only state
  teacherInfo: any = null;
  teacherStudents: any[] = [];
  institutionInfo: any = null;
  institutionStats: any = null;
  institutionTeachers: any[] = [];
  isLoadingRoleData: boolean = false;

  constructor(
    private authService: AuthService,
    private apiService: ApiService,
    private router: Router,
    private route: ActivatedRoute,
    private location: Location,
    private navCtrl: NavController,
    private platform: Platform,
    private alertController: AlertController,
    private toastController: ToastController
  ) {
    // Force all toasts in home page to appear at the top to avoid overlapping bottom nav menus
    const originalCreate = this.toastController.create.bind(this.toastController);
    this.toastController.create = (opts: any) => {
      opts.position = 'top';
      return originalCreate(opts);
    };
  }

  ionViewWillEnter() {
    if (!this.authService.isAuthenticated()) {
      this.navCtrl.navigateRoot('/login', { replaceUrl: true });
      return;
    }
    this.userRole = this.authService.getUserRole();
    this.userName = this.authService.getUserName();
    this.registerBackButtonHandler();

    // Sync active tab from query param if available
    const urlTab = this.route.snapshot.queryParamMap.get('tab');
    if (urlTab && ['home', 'profile', 'info', 'grades', 'achievements', 'talent'].includes(urlTab)) {
      this.selectedTab = urlTab;
    }

    if (this.isStudentRole()) {
      this.loadDashboardData();
    } else if (this.userRole === 'guru') {
      this.loadTeacherData();
      this.loadAnnouncements();
    } else if (this.userRole === 'institusi') {
      this.loadInstitutionData();
      this.loadAnnouncements();
    }
  }

  ionViewWillLeave() {
    if (this.backButtonSubscription) {
      this.backButtonSubscription.unsubscribe();
    }
  }

  registerBackButtonHandler() {
    if (this.backButtonSubscription) {
      this.backButtonSubscription.unsubscribe();
    }
    this.backButtonSubscription = this.platform.backButton.subscribeWithPriority(100, async () => {
      // 1. Close sidebar if open
      if (this.isSidebarOpen) {
        this.closeSidebar();
        return;
      }

      // 2. Return to Beranda tab if currently in another menu tab
      if (this.selectedTab !== 'home') {
        this.selectTab('home');
        return;
      }

      // 3. Already on Beranda tab: double tap to exit app
      const now = Date.now();
      if (now - this.lastBackPressOnHome < 2000) {
        const alert = await this.alertController.create({
          header: 'Keluar Aplikasi',
          message: 'Apakah Anda yakin ingin keluar dari aplikasi Lost Talent Detector?',
          buttons: [
            { text: 'Batal', role: 'cancel' },
            {
              text: 'Keluar',
              handler: () => {
                App.exitApp();
              }
            }
          ]
        });
        await alert.present();
      } else {
        this.lastBackPressOnHome = now;
        const toast = await this.toastController.create({
          message: 'Tekan sekali lagi untuk keluar dari aplikasi',
          duration: 2000,
          color: 'dark',
          position: 'top'
        });
        await toast.present();
      }
    });
  }

  isStudentRole(): boolean {
    return !this.userRole || ['siswa', 'mahasiswa', 'umum'].includes(this.userRole);
  }

  selectTab(tabName: string) {
    this.selectedTab = tabName;
    this.location.go(`/home?tab=${tabName}`);

    if (tabName === 'talent' && !this.riasecTest) {
      this.loadRiasecTest();
    }
    if (tabName === 'info' && this.announcements.length === 0) {
      this.loadAnnouncements();
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
          this.institutionTeachers = res.data?.teachers || [];
        }
      },
      error: () => {
        this.isLoadingRoleData = false;
      }
    });
  }

  loadDashboardData() {
    this.isLoadingDashboard = true;
    this.dashboardError = null;
    this.apiService.getDashboard().subscribe({
      next: (res: any) => {
        this.isLoadingDashboard = false;
        if (res.success) {
          const data = res.data || res;
          this.student = data.student;
          this.grades = data.grades || [];
          this.achievements = data.achievements || [];
          this.testResult = data.test_result;
          this.aiAnalysis = data.ai_analysis;
          this.teachers = data.teachers || [];

          // Fill inputs
          if (this.student) {
            this.profileData.hobbies = this.student.hobbies ? this.student.hobbies.join(', ') : '';
            this.profileData.interests = this.student.interests ? this.student.interests.join(', ') : '';
          }
          this.loadAnnouncements();
        } else {
          this.dashboardError = res.message || 'Gagal memuat data dashboard.';
        }
      },
      error: async (err: any) => {
        this.isLoadingDashboard = false;
        this.dashboardError = err.error?.message || 'Gagal terhubung ke backend server API (https://losttalentdetector.my.id).';
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

  loadAnnouncements() {
    this.isLoadingAnnouncements = true;
    this.apiService.getAnnouncements().subscribe({
      next: (res: any) => {
        this.isLoadingAnnouncements = false;
        if (res.success) {
          this.announcements = res.announcements || res.data || [];
        }
      },
      error: () => {
        this.isLoadingAnnouncements = false;
      }
    });
  }

  getFilteredAnnouncements() {
    if (this.announcementFilter === 'semua') {
      return this.announcements;
    }
    return this.announcements.filter(a => a.category === this.announcementFilter);
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
            duration: 2500,
            color: 'success',
            icon: 'sparkles',
            position: 'bottom',
            mode: 'ios'
          });
          await toast.present();
          this.loadDashboardData();
        } else {
          const toast = await this.toastController.create({
            message: res.message || 'Gagal memicu analisis.',
            duration: 2500,
            color: 'warning',
            icon: 'alert-circle',
            position: 'bottom',
            mode: 'ios'
          });
          await toast.present();
        }
      },
      error: async () => {
        this.isAnalyzingAi = false;
        const toast = await this.toastController.create({
          message: 'Gagal memproses analisis bakat AI.',
          duration: 2500,
          color: 'danger',
          icon: 'close-circle',
          position: 'bottom',
          mode: 'ios'
        });
        await toast.present();
      }
    });
  }

  async onResetRiasecTest() {
    const alert = await this.alertController.create({
      header: 'Konfirmasi Reset',
      message: 'Apakah Anda yakin ingin meriset jawaban tes minat RIASEC? Semua skor tes sebelumnya akan dihapus.',
      buttons: [
        { text: 'Batal', role: 'cancel' },
        {
          text: 'Ya, Reset',
          role: 'destructive',
          handler: () => {
            this.apiService.resetRiasecTest().subscribe({
              next: async (res: any) => {
                const toast = await this.toastController.create({
                  message: 'Hasil tes RIASEC berhasil di-reset.',
                  duration: 2000,
                  color: 'success'
                });
                await toast.present();
                this.testResult = null;
                this.riasecAnswers = {};
                this.loadDashboardData();
              },
              error: async () => {
                const toast = await this.toastController.create({
                  message: 'Gagal meriset tes RIASEC.',
                  duration: 2000,
                  color: 'danger'
                });
                await toast.present();
              }
            });
          }
        }
      ]
    });
    await alert.present();
  }

  async onResetAiAnalysis() {
    const alert = await this.alertController.create({
      header: 'Konfirmasi Reset',
      message: 'Apakah Anda yakin ingin meriset laporan analisis AI? Hasil analisis saat ini akan dihapus.',
      buttons: [
        { text: 'Batal', role: 'cancel' },
        {
          text: 'Ya, Reset',
          role: 'destructive',
          handler: () => {
            this.apiService.resetAiAnalysis().subscribe({
              next: async (res: any) => {
                const toast = await this.toastController.create({
                  message: 'Laporan analisis AI berhasil di-reset.',
                  duration: 2000,
                  color: 'success'
                });
                await toast.present();
                this.aiAnalysis = null;
                this.loadDashboardData();
              },
              error: async () => {
                const toast = await this.toastController.create({
                  message: 'Gagal meriset laporan AI.',
                  duration: 2000,
                  color: 'danger'
                });
                await toast.present();
              }
            });
          }
        }
      ]
    });
    await alert.present();
  }

  async onSaveAccountProfile(event: { name: string; email: string; avatar?: string }) {
    this.apiService.updateAccountProfile(event).subscribe({
      next: async (res: any) => {
        const toast = await this.toastController.create({
          message: 'Profil nama & email berhasil diperbarui.',
          duration: 2000,
          color: 'success'
        });
        await toast.present();
        this.loadDashboardData();
      },
      error: async (err: any) => {
        const alert = await this.alertController.create({
          header: 'Gagal Update',
          message: err.error?.message || 'Gagal memperbarui profil akun.',
          buttons: ['OK']
        });
        await alert.present();
      }
    });
  }

  async onDeleteAvatar() {
    const alert = await this.alertController.create({
      header: 'Hapus Foto Profil',
      message: 'Apakah Anda yakin ingin menghapus foto profil Anda?',
      buttons: [
        {
          text: 'Batal',
          role: 'cancel'
        },
        {
          text: 'Hapus',
          handler: () => {
            this.apiService.deleteAvatar().subscribe({
              next: async (res: any) => {
                const toast = await this.toastController.create({
                  message: 'Foto profil berhasil dihapus.',
                  duration: 2000,
                  color: 'success'
                });
                await toast.present();
                this.loadDashboardData();
              },
              error: async (err: any) => {
                const errorAlert = await this.alertController.create({
                  header: 'Gagal Hapus',
                  message: err.error?.message || 'Gagal menghapus foto profil.',
                  buttons: ['OK']
                });
                await errorAlert.present();
              }
            });
          }
        }
      ]
    });
    await alert.present();
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
        if (this.talentSubTab === 'interest') {
          this.talentSubTab = 'test';
        }
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

  onFileSelected(event: any) {
    this.onFileChange(event);
  }

  onFileChange(event: any) {
    const file = event.target.files[0];
    if (file) {
      this.selectedFileName = file.name;
      const reader = new FileReader();
      reader.onload = () => {
        this.newAchievement.certificate = reader.result as string;
      };
      reader.readAsDataURL(file);
    } else {
      this.selectedFileName = '';
    }
  }

  async onSubmitAchievement() {
    this.apiService.uploadAchievement(this.newAchievement).subscribe({
      next: async (res: any) => {
        const isAutoAcc = res.achievement && res.achievement.is_verified;
        const msg = isAutoAcc ? 'Sertifikat prestasi berhasil disimpan (Auto-Verified).' : 'Sertifikat prestasi berhasil diajukan untuk verifikasi Guru.';
        
        const toast = await this.toastController.create({
          message: msg,
          duration: 3000,
          color: 'success',
          icon: 'checkmark-circle',
          position: 'bottom',
          mode: 'ios'
        });
        await toast.present();
        
        // Reset form
        this.selectedFileName = '';
        this.newAchievement = {
          title: '',
          category: 'teknologi',
          level: 'sekolah',
          rank: '',
          description: '',
          certificate: ''
        };

        // Reset file input element
        const fileInput = document.querySelector('input[type="file"]') as HTMLInputElement;
        if (fileInput) {
          fileInput.value = '';
        }

        this.loadDashboardData();
      },
      error: async (err: any) => {
        const alert = await this.alertController.create({
          header: 'Gagal Mengajukan',
          message: 'Periksa formulir dan pastikan bukti sertifikat sudah terpilih.',
          buttons: ['OK']
        });
        await alert.present();
      }
    });
  }

  onSubmitIndependentGrade() {
    if (!this.newGrade.subject_name || this.newGrade.score === null) return;
    const payload = {
      semester: this.newGrade.semester,
      subject_name: this.newGrade.subject_name,
      score: this.newGrade.score
    };
    this.apiService.saveIndependentGrade(payload).subscribe({
      next: async (res: any) => {
        const toast = await this.toastController.create({
          message: 'Nilai akademik mandiri berhasil disimpan.',
          duration: 2000,
          color: 'success'
        });
        await toast.present();
        // Reset form
        this.newGrade = {
          semester: 1,
          subject_name: '',
          score: null
        };
        this.loadDashboardData();
      },
      error: async (err: any) => {
        let msg = 'Gagal menyimpan nilai.';
        if (err.error && err.error.message) msg = err.error.message;
        const alert = await this.alertController.create({
          header: 'Gagal',
          message: msg,
          buttons: ['OK']
        });
        await alert.present();
      }
    });
  }

  async onDeleteGrade(gradeId: number) {
    const alert = await this.alertController.create({
      header: 'Hapus Nilai',
      message: 'Apakah Anda yakin ingin menghapus nilai ini?',
      buttons: [
        {
          text: 'Batal',
          role: 'cancel'
        },
        {
          text: 'Hapus',
          handler: () => {
            this.apiService.deleteIndependentGrade(gradeId).subscribe({
              next: async (res: any) => {
                const toast = await this.toastController.create({
                  message: 'Nilai berhasil dihapus.',
                  duration: 2000,
                  color: 'success'
                });
                await toast.present();
                this.loadDashboardData();
              },
              error: async (err: any) => {
                let msg = 'Gagal menghapus nilai.';
                if (err.error && err.error.message) msg = err.error.message;
                const toast = await this.toastController.create({
                  message: msg,
                  duration: 2000,
                  color: 'danger'
                });
                await toast.present();
              }
            });
          }
        }
      ]
    });
    await alert.present();
  }

  async onDeleteAchievement(achId: number) {
    const alert = await this.alertController.create({
      header: 'Hapus Sertifikat',
      message: 'Apakah Anda yakin ingin menghapus sertifikat prestasi ini?',
      buttons: [
        {
          text: 'Batal',
          role: 'cancel'
        },
        {
          text: 'Hapus',
          handler: () => {
            this.apiService.deleteAchievement(achId).subscribe({
              next: async (res: any) => {
                const toast = await this.toastController.create({
                  message: 'Sertifikat prestasi berhasil dihapus.',
                  duration: 2000,
                  color: 'success'
                });
                await toast.present();
                this.loadDashboardData();
              },
              error: async (err: any) => {
                const toast = await this.toastController.create({
                  message: 'Gagal menghapus sertifikat.',
                  duration: 2000,
                  color: 'danger'
                });
                await toast.present();
              }
            });
          }
        }
      ]
    });
    await alert.present();
  }

  toggleGradeSelectionMode() {
    this.isSelectingGrades = !this.isSelectingGrades;
    if (!this.isSelectingGrades) {
      this.selectedGrades.clear();
    }
  }

  toggleSelectGrade(id: number) {
    if (this.selectedGrades.has(id)) {
      this.selectedGrades.delete(id);
    } else {
      this.selectedGrades.add(id);
    }
  }

  async onDeleteSelectedGrades() {
    const ids = Array.from(this.selectedGrades);
    const alert = await this.alertController.create({
      header: 'Hapus Beberapa Nilai',
      message: `Apakah Anda yakin ingin menghapus ${ids.length} nilai terpilih?`,
      buttons: [
        { text: 'Batal', role: 'cancel' },
        {
          text: 'Hapus',
          role: 'destructive',
          handler: () => {
            this.apiService.bulkDeleteIndependentGrades(ids, false).subscribe({
              next: async (res: any) => {
                const toast = await this.toastController.create({
                  message: 'Nilai terpilih berhasil dihapus.',
                  duration: 2000,
                  color: 'success'
                });
                await toast.present();
                this.isSelectingGrades = false;
                this.selectedGrades.clear();
                this.loadDashboardData();
              },
              error: async (err: any) => {
                const toast = await this.toastController.create({
                  message: 'Gagal menghapus nilai terpilih.',
                  duration: 2000,
                  color: 'danger'
                });
                await toast.present();
              }
            });
          }
        }
      ]
    });
    await alert.present();
  }

  async onDeleteAllGrades() {
    const alert = await this.alertController.create({
      header: 'Hapus Semua Nilai',
      message: 'Apakah Anda yakin ingin menghapus semua nilai akademik Anda?',
      buttons: [
        { text: 'Batal', role: 'cancel' },
        {
          text: 'Hapus Semua',
          role: 'destructive',
          handler: () => {
            this.apiService.bulkDeleteIndependentGrades([], true).subscribe({
              next: async (res: any) => {
                const toast = await this.toastController.create({
                  message: 'Semua nilai akademik berhasil dihapus.',
                  duration: 2000,
                  color: 'success'
                });
                await toast.present();
                this.isSelectingGrades = false;
                this.selectedGrades.clear();
                this.loadDashboardData();
              },
              error: async (err: any) => {
                const toast = await this.toastController.create({
                  message: 'Gagal menghapus semua nilai.',
                  duration: 2000,
                  color: 'danger'
                });
                await toast.present();
              }
            });
          }
        }
      ]
    });
    await alert.present();
  }

  toggleCertSelectionMode() {
    this.isSelectingCerts = !this.isSelectingCerts;
    if (!this.isSelectingCerts) {
      this.selectedCerts.clear();
    }
  }

  toggleSelectCert(id: number) {
    if (this.selectedCerts.has(id)) {
      this.selectedCerts.delete(id);
    } else {
      this.selectedCerts.add(id);
    }
  }

  async onDeleteSelectedCerts() {
    const ids = Array.from(this.selectedCerts);
    const alert = await this.alertController.create({
      header: 'Hapus Beberapa Sertifikat',
      message: `Apakah Anda yakin ingin menghapus ${ids.length} sertifikat terpilih?`,
      buttons: [
        { text: 'Batal', role: 'cancel' },
        {
          text: 'Hapus',
          role: 'destructive',
          handler: () => {
            this.apiService.bulkDeleteAchievements(ids, false).subscribe({
              next: async (res: any) => {
                const toast = await this.toastController.create({
                  message: 'Sertifikat terpilih berhasil dihapus.',
                  duration: 2000,
                  color: 'success'
                });
                await toast.present();
                this.isSelectingCerts = false;
                this.selectedCerts.clear();
                this.loadDashboardData();
              },
              error: async (err: any) => {
                const toast = await this.toastController.create({
                  message: 'Gagal menghapus sertifikat terpilih.',
                  duration: 2000,
                  color: 'danger'
                });
                await toast.present();
              }
            });
          }
        }
      ]
    });
    await alert.present();
  }

  async onDeleteAllCerts() {
    const alert = await this.alertController.create({
      header: 'Hapus Semua Sertifikat',
      message: 'Apakah Anda yakin ingin menghapus semua sertifikat prestasi Anda?',
      buttons: [
        { text: 'Batal', role: 'cancel' },
        {
          text: 'Hapus Semua',
          role: 'destructive',
          handler: () => {
            this.apiService.bulkDeleteAchievements([], true).subscribe({
              next: async (res: any) => {
                const toast = await this.toastController.create({
                  message: 'Semua sertifikat prestasi berhasil dihapus.',
                  duration: 2000,
                  color: 'success'
                });
                await toast.present();
                this.isSelectingCerts = false;
                this.selectedCerts.clear();
                this.loadDashboardData();
              },
              error: async (err: any) => {
                const toast = await this.toastController.create({
                  message: 'Gagal menghapus semua sertifikat.',
                  duration: 2000,
                  color: 'danger'
                });
                await toast.present();
              }
            });
          }
        }
      ]
    });
    await alert.present();
  }

  async onLogout() {
    const alert = await this.alertController.create({
      header: 'Konfirmasi Keluar',
      message: 'Apakah Anda yakin ingin keluar dari sistem?',
      buttons: [
        {
          text: 'Batal',
          role: 'cancel'
        },
        {
          text: 'Keluar',
          handler: () => {
            this.authService.logout().subscribe({
              next: () => {
                this.navCtrl.navigateRoot('/login', { replaceUrl: true, animated: true, animationDirection: 'back' });
              },
              error: () => {
                this.authService.clearStorage();
                this.navCtrl.navigateRoot('/login', { replaceUrl: true, animated: true, animationDirection: 'back' });
              }
            });
          }
        }
      ]
    });
    await alert.present();
  }

  formatWhatsApp(phone: string): string {
    if (!phone) return '';
    let clean = phone.replace(/\D/g, '');
    if (clean.startsWith('0')) {
      clean = '62' + clean.slice(1);
    }
    return clean;
  }
}
