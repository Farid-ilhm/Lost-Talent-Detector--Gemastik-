import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../../services/auth.service';
import { AlertController } from '@ionic/angular';

@Component({
  selector: 'app-login',
  templateUrl: './login.page.html',
  standalone: false,
})
export class LoginPage implements OnInit {
  credentials = {
    email: '',
    password: ''
  };

  showPassword = false;


  constructor(
    private authService: AuthService,
    private router: Router,
    private alertController: AlertController
  ) {}

  ngOnInit() {
    if (this.authService.isAuthenticated()) {
      this.router.navigateByUrl('/home');
    }
  }

  async onLogin() {
    this.authService.login(this.credentials).subscribe({
      next: (res) => {
        this.router.navigateByUrl('/home');
      },
      error: async (err) => {
        const msg = err.error?.message || 'Email atau password salah.';
        const alert = await this.alertController.create({
          header: 'Login Gagal',
          message: msg,
          buttons: ['OK']
        });
        await alert.present();
      }
    });
  }
}
