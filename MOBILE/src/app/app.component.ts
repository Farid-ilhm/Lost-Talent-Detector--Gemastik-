import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { Platform, AlertController } from '@ionic/angular';
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

  constructor(
    private platform: Platform,
    private router: Router,
    private alertController: AlertController
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
      this.platform.backButton.subscribeWithPriority(9999, async () => {
        const url = this.router.url;

        // If user is on root pages (login, home, register), ask exit app instead of popping to login
        if (url.includes('/login') || url.includes('/home') || url === '/') {
          const alert = await this.alertController.create({
            header: 'Keluar Aplikasi',
            message: 'Apakah Anda yakin ingin keluar dari aplikasi Lost Talent Detector?',
            buttons: [
              {
                text: 'Batal',
                role: 'cancel'
              },
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
          window.history.back();
        }
      });
    });
  }
}

