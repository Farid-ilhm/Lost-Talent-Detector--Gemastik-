import { Component, Input, Output, EventEmitter } from '@angular/core';

@Component({
  selector: 'app-student-talent-tab',
  templateUrl: './student-talent-tab.component.html',
  standalone: false,
})
export class StudentTalentTabComponent {
  @Input() selectedTab: string = 'talent';
  @Input() talentSubTab: string = 'interest';
  @Input() profileData: any = { hobbies: '', interests: '' };
  @Input() riasecTest: any = null;
  @Input() riasecAnswers: { [key: number]: string } = {};
  @Input() isAnalyzingAi: boolean = false;
  @Input() aiAnalysis: any = null;

  @Output() setTalentSubTabEvent = new EventEmitter<string>();
  @Output() updateProfileEvent = new EventEmitter<void>();
  @Output() submitRiasecTestEvent = new EventEmitter<void>();
  @Output() resetRiasecTestEvent = new EventEmitter<void>();
  @Output() runAiAnalysisEvent = new EventEmitter<void>();
  @Output() resetAiAnalysisEvent = new EventEmitter<void>();

  setTalentSubTab(subTab: string) {
    this.setTalentSubTabEvent.emit(subTab);
  }

  onUpdateProfile() {
    this.updateProfileEvent.emit();
  }

  isRiasecFormValid(): boolean {
    if (!this.riasecTest || !this.riasecTest.questions) return false;
    return this.riasecTest.questions.every((q: any) => this.riasecAnswers[q.id] && this.riasecAnswers[q.id] !== '');
  }

  onSubmitRiasecTest() {
    this.submitRiasecTestEvent.emit();
  }

  onResetRiasecTest() {
    this.resetRiasecTestEvent.emit();
  }

  onRunAiAnalysis() {
    this.runAiAnalysisEvent.emit();
  }

  onResetAiAnalysis() {
    this.resetAiAnalysisEvent.emit();
  }
}
