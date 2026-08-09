import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { Platform, AlertController, ToastController } from '@ionic/angular';
import { App } from '@capacitor/app';

@Component({
  selector: 'app-root',
  templateUrl: 'app.component.html',
  styleUrls: ['app.component.scss'],
  standalone: false,
})
export class AppComponent implements OnInit {
  showSplash: boolean = true;
  isExiting: boolean = false;
  private lastBackPress = 0;

  constructor(
    private platform: Platform,
    private router: Router,
    private alertController: AlertController,
    private toastController: ToastController
  ) {
    this.setupHardwareBackButton();
  }

  ngOnInit() {
    // Show premium splash screen then smoothly zoom & fade out
    setTimeout(() => {
      this.isExiting = true;
      setTimeout(() => {
        this.showSplash = false;
      }, 600);
    }, 1600);
  }

  setupHardwareBackButton() {
    this.platform.ready().then(() => {
      // Use priority 10 so higher priority (e.g. priority 100 in HomePage) overrides when on /home
      this.platform.backButton.subscribeWithPriority(10, async () => {
        const url = this.router.url;

        // If on register or subpages, navigate back to login or home
        if (url.includes('/register')) {
          this.router.navigateByUrl('/login');
          return;
        }

        if (url.includes('/riasec-test') || url.includes('/ai-result')) {
          this.router.navigateByUrl('/home');
          return;
        }

        // If on login, handle double-tap to exit
        if (url.includes('/login') || url === '/') {
          const now = Date.now();
          if (now - this.lastBackPress < 2000) {
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
            this.lastBackPress = now;
            const toast = await this.toastController.create({
              message: 'Tekan sekali lagi untuk keluar dari aplikasi',
              duration: 2000,
              color: 'dark',
              position: 'top'
            });
            await toast.present();
          }
        } else {
          window.history.back();
        }
      });
    });
  }
}

