import { Component, Input, Output, EventEmitter } from '@angular/core';

@Component({
  selector: 'app-student-profile-tab',
  templateUrl: './student-profile-tab.component.html',
  standalone: false,
})
export class StudentProfileTabComponent {
  @Input() selectedTab: string = 'profile';
  @Input() student: any = null;
  @Output() saveAccountProfileEvent = new EventEmitter<{ name: string; email: string }>();

  onSaveAccountProfile() {
    if (this.student && this.student.user) {
      this.saveAccountProfileEvent.emit({
        name: this.student.user.name,
        email: this.student.user.email
      });
    }
  }
}
