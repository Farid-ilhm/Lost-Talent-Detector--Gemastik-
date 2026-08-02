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
}
