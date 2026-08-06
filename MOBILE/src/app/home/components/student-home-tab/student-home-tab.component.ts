import { Component, Input, Output, EventEmitter } from '@angular/core';

@Component({
  selector: 'app-student-home-tab',
  templateUrl: './student-home-tab.component.html',
  standalone: false,
})
export class StudentHomeTabComponent {
  @Input() student: any = null;
  @Input() testResult: any = null;
  @Input() grades: any[] = [];
  @Input() achievements: any[] = [];
  @Input() aiAnalysis: any = null;
  @Output() selectTabEvent = new EventEmitter<string>();

  selectTab(tabName: string) {
    this.selectTabEvent.emit(tabName);
  }

  getReadinessPercentage(): number {
    let pct = 0;
    
    // 1. Hobi & Minat filled (max 20%)
    const hasHobbies = this.student && this.student.hobbies && this.student.hobbies.length > 0;
    const hasInterests = this.student && this.student.interests && this.student.interests.length > 0;
    if (hasHobbies) pct += 10;
    if (hasInterests) pct += 10;

    // 2. Nilai Rapor input (max 30%)
    if (this.grades && this.grades.length > 0) {
      pct += Math.min(30, this.grades.length * 10);
    }

    // 3. Sertifikat Prestasi uploaded (max 20%)
    if (this.achievements && this.achievements.length > 0) {
      pct += 20;
    }

    // 4. Tes RIASEC interest test completed (max 30%)
    if (this.testResult) {
      pct += 30;
    }

    return pct;
  }
}
