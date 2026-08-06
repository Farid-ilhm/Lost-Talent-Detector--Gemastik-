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
  @Output() deleteAvatarEvent = new EventEmitter<void>();

  selectedAvatarBase64: string | null = null;

  onAvatarSelected(event: any) {
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = () => {
        const img = new Image();
        img.src = reader.result as string;
        img.onload = () => {
          // Resize using canvas to save massive bandwidth and disk space
          const canvas = document.createElement('canvas');
          const MAX_WIDTH = 300;
          const MAX_HEIGHT = 300;
          let width = img.width;
          let height = img.height;

          if (width > height) {
            if (width > MAX_WIDTH) {
              height *= MAX_WIDTH / width;
              width = MAX_WIDTH;
            }
          } else {
            if (height > MAX_HEIGHT) {
              width *= MAX_HEIGHT / height;
              height = MAX_HEIGHT;
            }
          }

          canvas.width = width;
          canvas.height = height;
          const ctx = canvas.getContext('2d');
          if (ctx) {
            ctx.drawImage(img, 0, 0, width, height);
            const resizedBase64 = canvas.toDataURL('image/jpeg', 0.7); // 70% quality
            this.selectedAvatarBase64 = resizedBase64;
            if (this.student && this.student.user) {
              this.student.user.avatar_url = resizedBase64;
            }
          }
        };
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

  onDeleteAvatar() {
    this.deleteAvatarEvent.emit();
  }
}
