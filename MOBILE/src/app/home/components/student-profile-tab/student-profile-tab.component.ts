import { Component, Input, Output, EventEmitter } from '@angular/core';

@Component({
  selector: 'app-student-profile-tab',
  templateUrl: './student-profile-tab.component.html',
  standalone: false,
})
export class StudentProfileTabComponent {
  @Input() selectedTab: string = 'profile';
  @Input() student: any = null;
  @Input() profileData: any = { hobbies: '', interests: '' };

  @Output() updateProfileEvent = new EventEmitter<void>();

  onUpdateProfile() {
    this.updateProfileEvent.emit();
  }
}
