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
    // Show splash briefly then fade out
    setTimeout(() => {
      this.isExiting = true;
      setTimeout(() => {
        this.showSplash = false;
      }, 350);
    }, 1200);
  }
}

