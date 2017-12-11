import $ from 'jquery'
import _ from 'lodash'
import {PublicSurveyStats, d3Graph} from './stats.js'

const $d3_answers_options = {
  x_column: 'version',
  y_column: 'total',
  x_axis_title: 'Versions',
  y_axis_title: 'Answers',
  graph_title: 'Answers by survey version',
  on_click_bar: d => {
    if(!('fully_answered' in d && 'not_fully_answered' in d))
      return false

    d3Graph.drawBars([{
      type: 'fully',
      total: d.fully_answered
    }, {
      type: 'partially',
      total: d.not_fully_answered
    }], {
      x_column: 'type',
      y_column: 'total',
      x_axis_title: 'Completeness',
      y_axis_title: 'Answered',
      graph_title: 'Survey version ' + d.version,
      table_version: d.version,
      func_go_back: _ => d3Graph.drawBars($d3_answers_data, $d3_answers_options)
    })

    return true
  }
}

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

$(window).on('resize', _ => {
  if(window_width !== window.innerWidth) {
    window_width = window.innerWidth
    d3Graph.reload()
  }
})

$(_ => {
  window.jQuery('[data-toggle="tooltip"]').tooltip()

  window.jQuery('.svg-answer-date').on('click', event => {
    const $th = $(event.target)
    const survey_version = $th.parents('table.table-versions:eq(0)').data('surveyVersion')
    const survey_data = $d3_dates_data[survey_version]
    window.console.log(survey_data)
    //~ d3Graph.drawLines(survey_data)
  })

  setTimeout(_ => {
    d3Graph.drawBars($d3_answers_data, $d3_answers_options),
    500
  })
})
