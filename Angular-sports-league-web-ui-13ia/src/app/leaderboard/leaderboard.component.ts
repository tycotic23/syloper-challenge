import { Component, OnInit } from '@angular/core';
import { LeagueService } from '../services/league.service';

@Component({
  selector: 'app-leaderboard',
  templateUrl: './leaderboard.component.html',
  styleUrls: ['./leaderboard.component.scss']
})
export class LeaderboardComponent implements OnInit {

  constructor(public leagueService:LeagueService) { }

  ngOnInit(): void {
    this.leagueService.fetchData().then();
  }

}
