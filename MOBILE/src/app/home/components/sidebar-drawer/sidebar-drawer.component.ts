import { Component, Input, Output, EventEmitter } from '@angular/core';

@Component({
  selector: 'app-sidebar-drawer',
  templateUrl: './sidebar-drawer.component.html',
  styleUrls: ['./sidebar-drawer.component.scss'],
  standalone: false,
})
export class SidebarDrawerComponent {
  @Input() isSidebarOpen: boolean = false;
  @Input() selectedTab: string = 'home';
  @Output() closeSidebarEvent = new EventEmitter<void>();
  @Output() selectTabEvent = new EventEmitter<string>();
  @Output() logoutEvent = new EventEmitter<void>();

  closeSidebar() {
    this.closeSidebarEvent.emit();
  }

  openTabFromSidebar(tabName: string) {
    this.selectTabEvent.emit(tabName);
    this.closeSidebar();
  }

  onLogout() {
    this.logoutEvent.emit();
  }
}
