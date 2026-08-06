import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-institusi-view',
  templateUrl: './institusi-view.component.html',
  standalone: false,
})
export class InstitusiViewComponent {
  @Input() selectedTab: string = 'home';
  @Input() userName: string | null = null;
  @Input() institutionInfo: any = null;
  @Input() institutionStats: any = null;
  @Input() institutionTeachers: any[] = [];
  @Input() isLoadingRoleData: boolean = false;
}
