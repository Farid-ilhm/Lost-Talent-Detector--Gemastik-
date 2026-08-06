import { Component, Input, Output, EventEmitter } from '@angular/core';

@Component({
  selector: 'app-student-profile-tab',
  templateUrl: './student-profile-tab.component.html',
  standalone: false,
})
export class StudentProfileTabComponent {
  @Input() selectedTab: string = 'profile';
  @Input() student: any = null;
  @Output() saveAccountProfileEvent = new EventEmitter<{ name: string; email: string; avatar?: string }>();

  selectedAvatarBase64: string | null = null;

  onAvatarSelected(event: any) {
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = () => {
        const base64 = reader.result as string;
        this.selectedAvatarBase64 = base64;
        if (this.student && this.student.user) {
          this.student.user.avatar_url = base64;
        }
      };
      reader.readAsDataURL(file);
    }
  }

  onSaveAccountProfile() {
    if (this.student && this.student.user) {
      this.saveAccountProfileEvent.emit({
        name: this.student.user.name,
        email: this.student.user.email,
        avatar: this.selectedAvatarBase64 || undefined
      });
    }
  }
}
