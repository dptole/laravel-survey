import $ from 'jquery'
import _ from 'lodash'
import {PublicSurveyStats, d3Graph} from './stats.js'

const public_survey_stats = new PublicSurveyStats

public_survey_stats
  .websocket.config({
    broadcaster: 'pusher',
    key: '21d452bbca84d41d5945',
    cluster: 'us2',
    encrypted: true
  })
  .channel('public-survey')
  .on('new-user', function(data) {
    console.log('new-user', data)
  })

d3Graph.drawBars()
$(window).on('resize', d3Graph.drawBars)
