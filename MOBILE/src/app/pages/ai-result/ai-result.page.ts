import { Component, OnInit } from '@angular/core';
import { ApiService } from '../../services/api.service';
import { ToastController, LoadingController } from '@ionic/angular';

@Component({
  selector: 'app-ai-result',
  templateUrl: './ai-result.page.html',
  standalone: false,
})
export class AiResultPage implements OnInit {
  aiAnalysis: any = null;

  constructor(
    private apiService: ApiService,
    private toastController: ToastController,
    private loadingController: LoadingController
  ) {}

  ngOnInit() {
    this.fetchAiResult();
  }

  fetchAiResult() {
    this.apiService.getDashboard().subscribe({
      next: (res) => {
        if (res.success) {
          const data = res.data || res;
          this.aiAnalysis = data.ai_analysis;
        }
      },
      error: () => {
        this.showToast('Gagal memuat hasil analisis AI.', 'danger');
      }
    });
  }

  async onRunAnalysis() {
    const loading = await this.loadingController.create({
      message: 'Sedang menganalisis bakat dengan AI...',
    });
    await loading.present();

    this.apiService.triggerAiAnalysis().subscribe({
      next: (res) => {
        loading.dismiss();
        if (res.success) {
          this.showToast('Analisis AI berhasil diperbarui!', 'success');
          this.fetchAiResult();
        } else {
          this.showToast(res.message || 'Gagal memicu analisis.', 'warning');
        }
      },
      error: () => {
        loading.dismiss();
        this.showToast('Gagal memproses prediksi bakat AI.', 'danger');
      }
    });
  }

  async showToast(msg: string, color: string) {
    const toast = await this.toastController.create({
      message: msg,
      duration: 2000,
      color: color,
      position: 'top'
    });
    await toast.present();
  }
}
