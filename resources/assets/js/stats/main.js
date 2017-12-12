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
      graph_title: 'All answers',
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
    window.console.log('new-user', data)
  })

$(window).on('resize', _ => {
  if(window_width !== window.innerWidth) {
    window_width = window.innerWidth
    d3Graph.reload()
  }
})

$(_ => {
  window.jQuery('[data-toggle="tooltip"]').tooltip()

  window.jQuery('table.table-versions').each((index, table) => {
    const $table = $(table)
    const $svg_answer_completeness = $table.find('.svg-answer-completeness')
    const $svg_answer_date = $table.find('.svg-answer-date')
    const d3_border_color = d3.scale.category10()()

    $svg_answer_completeness.css('border', '2px dashed ' + d3_border_color)
    $svg_answer_date.css('border', '2px dashed ' + d3_border_color)

    $svg_answer_completeness.on('click', event => {
      const survey_version = $table.data('surveyVersion')
      const d = $d3_answers_data.find(answers =>
        answers.version === survey_version
      )

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
        graph_title: 'All answers',
        table_version: d.version,
        func_go_back: _ => d3Graph.drawBars($d3_answers_data, $d3_answers_options)
      })
    })

    $svg_answer_date.on('click', event => {
      const survey_version = $table.data('surveyVersion')
      const d3_dates_data = $d3_dates_data[survey_version]
      const survey_data = [].concat(d3_dates_data).map(d => {
        d.date = new Date(d.date)
        return d
      })

      d3Graph.drawLines(survey_data, {
        x_column: 'date',
        y_column: 'answers',
        y_axis_title: 'Answered',
        graph_title: 'Answers by date',
        func_go_back: _ => d3Graph.drawBars($d3_answers_data, $d3_answers_options)
      })
    })
  })

  setTimeout(_ => {
    d3Graph.drawBars($d3_answers_data, $d3_answers_options),
    500
  })
})
