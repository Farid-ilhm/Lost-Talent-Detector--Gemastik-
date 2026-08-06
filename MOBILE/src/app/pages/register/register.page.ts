import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../../services/auth.service';
import { ApiService } from '../../services/api.service';
import { AlertController } from '@ionic/angular';

@Component({
  selector: 'app-register',
  templateUrl: './register.page.html',
  standalone: false,
})
export class RegisterPage {
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


  constructor(
    private authService: AuthService,
    private apiService: ApiService,
    private router: Router,
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
                  this.router.navigateByUrl('/login');
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

  async promptOtpVerification(email: string) {
    const alert = await this.alertController.create({
      header: 'Verifikasi Kode OTP',
      subHeader: `Kode OTP 6-digit telah dikirim ke ${email}`,
      inputs: [
        {
          name: 'otp_code',
          type: 'text',
          placeholder: 'Masukkan 6-digit OTP',
          attributes: {
            maxlength: 6,
            inputmode: 'numeric'
          }
        }
      ],
      buttons: [
        {
          text: 'Kirim Ulang Email',
          role: 'cancel',
          handler: () => {
            this.authService.resendOtp({ email }).subscribe({
              next: async () => {
                const toastAlert = await this.alertController.create({
                  header: 'OTP Dikirim Ulang',
                  message: 'Silakan periksa kotak masuk email Anda.',
                  buttons: ['OK']
                });
                await toastAlert.present();
                this.promptOtpVerification(email);
              }
            });
          }
        },
        {
          text: 'Verifikasi',
          handler: (data) => {
            if (!data.otp_code || data.otp_code.length !== 6) {
              return false; // don't close alert
            }
            this.authService.verifyOtp({ email, otp_code: data.otp_code }).subscribe({
              next: async (res: any) => {
                const successAlert = await this.alertController.create({
                  header: 'Verifikasi Berhasil!',
                  message: 'Akun Anda telah aktif. Mengalihkan ke aplikasi...',
                  buttons: [
                    {
                      text: 'OK',
                      handler: () => {
                        this.router.navigateByUrl('/home');
                      }
                    }
                  ]
                });
                await successAlert.present();
              },
              error: async (err: any) => {
                let errMessage = 'Kode OTP tidak valid atau telah expired.';
                if (err.error && err.error.message) {
                  errMessage = err.error.message;
                }
                const failAlert = await this.alertController.create({
                  header: 'Verifikasi Gagal',
                  message: errMessage,
                  buttons: [
                    {
                      text: 'Coba Lagi',
                      handler: () => {
                        this.promptOtpVerification(email);
                      }
                    }
                  ]
                });
                await failAlert.present();
              }
            });
            return true;
          }
        }
      ]
    });

    await alert.present();
  }
}
