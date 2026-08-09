import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-root',
  templateUrl: 'app.component.html',
  styleUrls: ['app.component.scss'],
  standalone: false,
})
export class AppComponent implements OnInit {
  showSplash: boolean = true;
  isExiting: boolean = false;

  constructor() {}

  ngOnInit() {
    // Show premium splash screen then smoothly zoom & fade out
    setTimeout(() => {
      this.isExiting = true;
      setTimeout(() => {
        this.showSplash = false;
      }, 600);
    }, 1600);
  }
}

