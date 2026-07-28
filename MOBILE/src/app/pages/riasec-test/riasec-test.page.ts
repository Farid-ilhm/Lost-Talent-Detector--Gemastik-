import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { ApiService } from '../../services/api.service';
import { AlertController, ToastController } from '@ionic/angular';

@Component({
  selector: 'app-riasec-test',
  templateUrl: './riasec-test.page.html',
  standalone: false,
})
export class RiasecTestPage implements OnInit {
  test: any = null;
  answers: { [key: number]: string } = {};

  constructor(
    private apiService: ApiService,
    private router: Router,
    private alertController: AlertController,
    private toastController: ToastController
  ) {}

  ngOnInit() {
    this.apiService.getRiasecTest().subscribe({
      next: (res) => {
        if (res.success && res.active_test) {
          this.test = res.active_test;
          // Initialize answers
          this.test.questions.forEach((q: any) => {
            this.answers[q.id] = '';
          });
        } else {
          this.showNoActiveTestAlert();
        }
      },
      error: () => {
        this.showErrorToast();
      }
    });
  }

  isFormValid(): boolean {
    if (!this.test) return false;
    // Check if every question has an answer (is not empty string)
    return this.test.questions.every((q: any) => this.answers[q.id] !== '');
  }

  onSubmitTest() {
    if (!this.isFormValid()) return;

    // Convert string values to numbers
    const finalAnswers: { [key: number]: number } = {};
    for (const key in this.answers) {
      finalAnswers[key] = parseInt(this.answers[key], 10);
    }

    this.apiService.submitRiasecTest(this.test.id, finalAnswers).subscribe({
      next: async (res) => {
        const alert = await this.alertController.create({
          header: 'Hasil Terkirim',
          message: 'Jawaban tes RIASEC Anda berhasil disimpan.',
          buttons: [
            {
              text: 'OK',
              handler: () => {
                this.router.navigateByUrl('/home');
              }
            }
          ]
        });
        await alert.present();
      },
      error: async (err) => {
        const alert = await this.alertController.create({
          header: 'Submit Gagal',
          message: 'Terjadi kesalahan saat mengirim jawaban.',
          buttons: ['OK']
        });
        await alert.present();
      }
    });
  }

  async showNoActiveTestAlert() {
    const alert = await this.alertController.create({
      header: 'Info',
      message: 'Tidak ada kuesioner RIASEC aktif saat ini.',
      buttons: [
        {
          text: 'Kembali',
          handler: () => {
            this.router.navigateByUrl('/home');
          }
        }
      ]
    });
    await alert.present();
  }

  async showErrorToast() {
    const toast = await this.toastController.create({
      message: 'Gagal memuat tes. Coba lagi nanti.',
      duration: 3000,
      color: 'danger'
    });
    await toast.present();
  }
}
