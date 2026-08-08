import { Component, Input, Output, EventEmitter } from '@angular/core';

@Component({
  selector: 'app-student-achievements-tab',
  templateUrl: './student-achievements-tab.component.html',
  standalone: false,
})
export class StudentAchievementsTabComponent {
  @Input() selectedTab: string = 'achievements';
  @Input() achievements: any[] = [];
  @Input() isSelectingCerts: boolean = false;
  @Input() selectedCerts: Set<number> = new Set<number>();
  @Input() student: any = null;
  @Input() selectedFileName: string = '';
  @Input() serverUrl: string = 'https://losttalentdetector.my.id/';
  @Input() newAchievement: any = {
    title: '',
    category: 'teknologi',
    level: 'sekolah',
    rank: '',
    description: '',
    certificate: ''
  };

  @Output() toggleCertSelectionModeEvent = new EventEmitter<void>();
  @Output() toggleSelectCertEvent = new EventEmitter<number>();
  @Output() deleteSelectedCertsEvent = new EventEmitter<void>();
  @Output() deleteAllCertsEvent = new EventEmitter<void>();
  @Output() deleteAchievementEvent = new EventEmitter<number>();
  @Output() fileSelectedEvent = new EventEmitter<any>();
  @Output() submitAchievementEvent = new EventEmitter<void>();

  toggleCertSelectionMode() {
    this.toggleCertSelectionModeEvent.emit();
  }

  toggleSelectCert(id: number) {
    this.toggleSelectCertEvent.emit(id);
  }

  onDeleteSelectedCerts() {
    this.deleteSelectedCertsEvent.emit();
  }

  onDeleteAllCerts() {
    this.deleteAllCertsEvent.emit();
  }

  onDeleteAchievement(id: number) {
    this.deleteAchievementEvent.emit(id);
  }

  onFileSelected(event: any) {
    this.fileSelectedEvent.emit(event);
  }

  onSubmitAchievement() {
    this.submitAchievementEvent.emit();
  }
}
