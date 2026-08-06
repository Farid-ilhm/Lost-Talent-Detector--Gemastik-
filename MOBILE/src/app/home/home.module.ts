import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { IonicModule } from '@ionic/angular';
import { FormsModule } from '@angular/forms';
import { HomePage } from './home.page';
import { HomePageRoutingModule } from './home-routing.module';

// Components
import { SidebarDrawerComponent } from './components/sidebar-drawer/sidebar-drawer.component';
import { GuruViewComponent } from './components/guru-view/guru-view.component';
import { InstitusiViewComponent } from './components/institusi-view/institusi-view.component';
import { StudentHomeTabComponent } from './components/student-home-tab/student-home-tab.component';
import { StudentProfileTabComponent } from './components/student-profile-tab/student-profile-tab.component';
import { StudentGradesTabComponent } from './components/student-grades-tab/student-grades-tab.component';
import { StudentAchievementsTabComponent } from './components/student-achievements-tab/student-achievements-tab.component';
import { StudentTalentTabComponent } from './components/student-talent-tab/student-talent-tab.component';
import { InfoBoardTabComponent } from './components/info-board-tab/info-board-tab.component';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    HomePageRoutingModule
  ],
  declarations: [
    HomePage,
    SidebarDrawerComponent,
    GuruViewComponent,
    InstitusiViewComponent,
    StudentHomeTabComponent,
    StudentProfileTabComponent,
    StudentGradesTabComponent,
    StudentAchievementsTabComponent,
    StudentTalentTabComponent,
    InfoBoardTabComponent,
  ]
})
export class HomePageModule {}
