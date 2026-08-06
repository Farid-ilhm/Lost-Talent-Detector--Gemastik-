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
}
