import $ from 'jquery'
import _ from 'lodash'
import {PublicSurveyStats, d3Graph} from './stats.js'

const public_survey_stats = new PublicSurveyStats
let window_width = 0

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

setTimeout(d3Graph.drawBars, 1000)
$(window).on('resize', _ => {
  if(window_width !== window.innerWidth) {
    window_width = window.innerWidth
    d3Graph.reload()
  }
})

$(_ =>
  window.jQuery('[data-toggle="tooltip"]').tooltip()
)
