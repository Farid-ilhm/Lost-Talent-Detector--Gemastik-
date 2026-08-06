import { Component, Input, Output, EventEmitter } from '@angular/core';

@Component({
  selector: 'app-info-board-tab',
  templateUrl: './info-board-tab.component.html',
  standalone: false,
})
export class InfoBoardTabComponent {
  @Input() selectedTab: string = 'info';
  @Input() announcements: any[] = [];
  @Input() announcementFilter: string = 'semua';
  @Input() isLoadingAnnouncements: boolean = false;
  @Input() filteredAnnouncements: any[] = [];

  @Output() filterCategoryEvent = new EventEmitter<string>();
  @Output() loadAnnouncementsEvent = new EventEmitter<void>();

  filterCategory(category: string) {
    this.filterCategoryEvent.emit(category);
  }

  loadAnnouncements() {
    this.loadAnnouncementsEvent.emit();
  }
}
