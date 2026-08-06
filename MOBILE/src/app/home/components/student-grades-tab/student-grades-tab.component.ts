import { Component, Input, Output, EventEmitter } from '@angular/core';

@Component({
  selector: 'app-student-grades-tab',
  templateUrl: './student-grades-tab.component.html',
  standalone: false,
})
export class StudentGradesTabComponent {
  @Input() selectedTab: string = 'grades';
  @Input() grades: any[] = [];
  @Input() isSelectingGrades: boolean = false;
  @Input() selectedGrades: Set<number> = new Set<number>();
  @Input() student: any = null;
  @Input() userRole: string | null = null;
  @Input() newGrade: any = { semester: 1, subject_name: '', score: null };

  @Output() toggleGradeSelectionModeEvent = new EventEmitter<void>();
  @Output() toggleSelectGradeEvent = new EventEmitter<number>();
  @Output() deleteSelectedGradesEvent = new EventEmitter<void>();
  @Output() deleteAllGradesEvent = new EventEmitter<void>();
  @Output() deleteGradeEvent = new EventEmitter<number>();
  @Output() submitIndependentGradeEvent = new EventEmitter<void>();

  toggleGradeSelectionMode() {
    this.toggleGradeSelectionModeEvent.emit();
  }

  toggleSelectGrade(id: number) {
    this.toggleSelectGradeEvent.emit(id);
  }

  onDeleteSelectedGrades() {
    this.deleteSelectedGradesEvent.emit();
  }

  onDeleteAllGrades() {
    this.deleteAllGradesEvent.emit();
  }

  onDeleteGrade(id: number) {
    this.deleteGradeEvent.emit(id);
  }

  onSubmitIndependentGrade() {
    this.submitIndependentGradeEvent.emit();
  }
}
