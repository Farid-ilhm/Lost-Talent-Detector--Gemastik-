import { Component, ViewChild, ElementRef, OnDestroy } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../../services/auth.service';
import { ApiService } from '../../services/api.service';
import { AlertController, NavController } from '@ionic/angular';

@Component({
  selector: 'app-register',
  templateUrl: './register.page.html',
  standalone: false,
})
export class RegisterPage implements OnDestroy {
  userData = {
    role: 'siswa',
    name: '',
    email: '',
    phone: '',
    npsn: '',
    address: '',
    institution_id: '',
    nisn: '',
    classroom: '',
    major: '',
    nim: '',
    semester: '',
    password: '',
    password_confirmation: ''
  };

  institutions: any[] = [];

  showPassword = false;
  showConfirmPassword = false;

  showOtpModal = false;
  otpCode = '';
  otpEmail = '';
  isVerifyingOtp = false;
  isResendingOtp = false;
  resendCountdown = 0;
  countdownInterval: any;

  @ViewChild('otpInput', { static: false }) otpInput!: ElementRef;


  constructor(
    private authService: AuthService,
    private apiService: ApiService,
    private router: Router,
    private navCtrl: NavController,
    private alertController: AlertController
  ) {}

  ionViewWillEnter() {
    this.loadInstitutions();
  }

  loadInstitutions() {
    this.apiService.getPublicInstitutions().subscribe({
      next: (res: any) => {
        if (res.success) {
          this.institutions = res.data || [];
        }
      },
      error: () => {
        this.institutions = [];
      }
    });
  }

  async onRegister() {
    if (this.userData.password !== this.userData.password_confirmation) {
      const alert = await this.alertController.create({
        header: 'Registrasi Gagal',
        message: 'Konfirmasi password tidak cocok.',
        buttons: ['OK']
      });
      await alert.present();
      return;
    }

    this.authService.register(this.userData).subscribe({
      next: async (res: any) => {
        if (res.requires_otp) {
          await this.promptOtpVerification(this.userData.email);
        } else {
          const alert = await this.alertController.create({
            header: 'Registrasi Berhasil',
            message: 'Akun Anda berhasil didaftarkan. Silakan login.',
            buttons: [
              {
                text: 'OK',
                handler: () => {
                  this.navCtrl.navigateRoot('/login', { replaceUrl: true });
                }
              }
            ]
          });
          await alert.present();
        }
      },
      error: async (err: any) => {
        let msg = 'Gagal mendaftarkan akun. Periksa data kembali.';
        if (err.error && err.error.errors) {
          const firstErrKey = Object.keys(err.error.errors)[0];
          msg = err.error.errors[firstErrKey][0];
        } else if (err.error && err.error.message) {
          msg = err.error.message;
        }
        const alert = await this.alertController.create({
          header: 'Registrasi Gagal',
          message: msg,
          buttons: ['OK']
        });
        await alert.present();
      }
    });
  }

  ngOnDestroy() {
    this.clearCountdown();
  }

  startCountdown() {
    this.resendCountdown = 60;
    this.clearCountdown();
    this.countdownInterval = setInterval(() => {
      if (this.resendCountdown > 0) {
        this.resendCountdown--;
      } else {
        this.clearCountdown();
      }
    }, 1000);
  }

  clearCountdown() {
    if (this.countdownInterval) {
      clearInterval(this.countdownInterval);
      this.countdownInterval = null;
    }
  }

  focusOtpInput() {
    setTimeout(() => {
      if (this.otpInput && this.otpInput.nativeElement) {
        this.otpInput.nativeElement.focus();
      }
    }, 200);
  }

  onOtpChange(value: any) {
    if (value) {
      const stringValue = String(value);
      this.otpCode = stringValue.replace(/[^0-9]/g, '');
      if (this.otpCode.length === 6) {
        this.verifyOtp();
      }
    }
  }

  promptOtpVerification(email: string) {
    this.otpEmail = email;
    this.otpCode = '';
    this.showOtpModal = true;
    this.startCountdown();
    this.focusOtpInput();
  }

  verifyOtp() {
    if (this.otpCode.length !== 6 || this.isVerifyingOtp) {
      return;
    }
    this.isVerifyingOtp = true;
    this.authService.verifyOtp({ email: this.otpEmail, otp_code: this.otpCode }).subscribe({
      next: async (res: any) => {
        this.isVerifyingOtp = false;
        this.showOtpModal = false;
        this.clearCountdown();
        
        const successAlert = await this.alertController.create({
          header: 'Verifikasi Berhasil!',
          message: 'Akun Anda telah aktif. Mengalihkan ke aplikasi...',
          buttons: [
            {
              text: 'OK',
              handler: () => {
                this.navCtrl.navigateRoot('/home', { replaceUrl: true });
              }
            }
          ]
        });
        await successAlert.present();
      },
      error: async (err: any) => {
        this.isVerifyingOtp = false;
        let errMessage = 'Kode OTP tidak valid atau telah expired.';
        if (err.error && err.error.message) {
          errMessage = err.error.message;
        }
        const failAlert = await this.alertController.create({
          header: 'Verifikasi Gagal',
          message: errMessage,
          buttons: ['Coba Lagi']
        });
        await failAlert.present();
      }
    });
  }

  resendOtp() {
    if (this.isResendingOtp || this.resendCountdown > 0) {
      return;
    }
    this.isResendingOtp = true;
    this.authService.resendOtp({ email: this.otpEmail }).subscribe({
      next: async () => {
        this.isResendingOtp = false;
        this.otpCode = '';
        this.startCountdown();
        const toastAlert = await this.alertController.create({
          header: 'OTP Dikirim Ulang',
          message: 'Silakan periksa kotak masuk email Anda.',
          buttons: ['OK']
        });
        await toastAlert.present();
        this.focusOtpInput();
      },
      error: async (err: any) => {
        this.isResendingOtp = false;
        let errMessage = 'Gagal mengirim ulang OTP. Silakan coba lagi.';
        if (err.error && err.error.message) {
          errMessage = err.error.message;
        }
        const failAlert = await this.alertController.create({
          header: 'Gagal Kirim Ulang',
          message: errMessage,
          buttons: ['OK']
        });
        await failAlert.present();
      }
    });
  }

  closeOtpModal() {
    this.showOtpModal = false;
    this.clearCountdown();
  }
}
