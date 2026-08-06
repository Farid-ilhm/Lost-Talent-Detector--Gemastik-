import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-guru-view',
  templateUrl: './guru-view.component.html',
  standalone: false,
})
export class GuruViewComponent {
  @Input() selectedTab: string = 'home';
  @Input() userName: string | null = null;
  @Input() teacherInfo: any = null;
  @Input() teacherStudents: any[] = [];
  @Input() isLoadingRoleData: boolean = false;
}
