import $ from 'jquery'
import API from '../api.js'
import {PublicSurveyStats, d3Graph} from './stats.js'

const public_survey_stats = new PublicSurveyStats
let window_width = 0

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
      func_go_back: d3BackRoot
    })

    return true
  }
}

function websocketQuestionAnswered(data) {
  const $table = window.jQuery('#lar-tab-user-answers-' + (data.user.session_id) + ' table')
  const $first_tr = $table.find('tbody > :eq(1)')
  const started_from_zero = !!$first_tr.find('> td').attr('colspan')
  
  if(started_from_zero)
    $first_tr.remove()
  
  const $question = window.jQuery('<tr>')
  const $answer = window.jQuery('<tr>')
  const answer_check = data.user.question_option.type === 'check'
  const glyphicon_css_class = 'glyphicon ' + (answer_check ? 'glyphicon-ok' : 'glyphicon-pencil')
  const answer_text = answer_check ? data.user.question_option.description : data.user.answer.free_text
  const $question_line = window.jQuery('<th>').text(`Question ${data.user.question.order}`)
  
  if(started_from_zero)
    $question_line.attr('width', '30%')
  
  $question.append($question_line)
  $question.append(
    window.jQuery('<td>').text(data.user.question.description)
  )
  
  $answer.append(
    window.jQuery('<th>').append(
      answer_check ? 'Selected answer' : 'Typed answer'
    )
  )

  $answer.append(
    window.jQuery('<td>').append(
      window.jQuery('<span>').addClass(glyphicon_css_class),
      ' ',
      answer_text
    )
  )
  
  $table.find('tbody').append($question, $answer)
}

function d3BackRoot() {
  $('.table-users-info').addClass('hide')
  d3Graph.drawBars($d3_data.answers, $d3_answers_options)
}

function renderCountryInfo(answer_session_uuid, country_info) {
  const $lar_country_info = window.jQuery('.lar-country-info-box[data-answer-session-uuid="' + answer_session_uuid + '"]')
  const $table = $lar_country_info.find('.lar-has-country-info')
  const $fetch_box = $lar_country_info.find('.lar-hasnt-country-info')
  
  if($lar_country_info.length < 1 || !$fetch_box.is(':visible'))
    return false
  
  const $tbody = window.jQuery('<tbody>')
  for(const prop in country_info)
    $tbody.append(
      window.jQuery('<tr>').append(
        window.jQuery('<th>').text(prop),
        window.jQuery('<td>').text(country_info[prop])
      )
    )
  
  $table.append($tbody)
  
  $table.removeClass('hide')
  $fetch_box.addClass('hide')
}

public_survey_stats
  .websocket.config({
    broadcaster: 'pusher',
    key: '21d452bbca84d41d5945',
    cluster: 'us2',
    encrypted: true
  })
  .channel('public-survey-' + $survey_uuid)
    .on('new-user', data => window.jQuery('.lar-refresh-survey').removeClass('hide'))
    .on('user-done-survey', data => window.jQuery('.lar-refresh-survey').removeClass('hide'))
    .on('user-answer', data => websocketQuestionAnswered(data))

$(window).on('resize', _ => {
  if(window_width !== window.innerWidth) {
    window_width = window.innerWidth
    d3Graph.reload()
  }
})

$(_ => {
  let scroll_top = document.documentElement.scrollTop
  window.jQuery('[data-toggle="tooltip"]').tooltip()

  window.jQuery('.lar-user-answer td').on('click', event => {
    const $tr = window.jQuery(event.target).parent()
    const uuid = $tr.data('tableUserInfo')

    if(!uuid)
      return false

    scroll_top = document.documentElement.scrollTop
    $tr.parents('table:eq(0)').addClass('hide')
    const $table_user_info = window.jQuery('.table-user-info-' + uuid)
    $table_user_info.removeClass('hide')
    if($table_user_info.offset())
      document.documentElement.scrollTop =  $table_user_info.offset().top
  })

  window.jQuery('.lar-fetch-country-info').on('click', event => {
    const $button = window.jQuery(event.target)
    const survey_version = $button.data('surveyVersion')
    const $country_info_box = $button.parents('.lar-country-info-box:eq(0)')
    const $loader = $country_info_box.find('.lar-loading-country-info')
    
    if($country_info_box.length < 1)
      return false
    
    if($loader.is(':visible'))
      return false
    
    if($country_info[survey_version] && $country_info[survey_version][event.target.dataset.answerSessionUuid]) {
      renderCountryInfo(
        event.target.dataset.answerSessionUuid,
        $country_info[survey_version][event.target.dataset.answerSessionUuid]
      )
      return false
    }
    
    $button.addClass('hide')
    $loader.removeClass('hide')
    
    setTimeout(_ => {
      API.fetchCountryInfo(
        event.target.dataset.answerSessionId,
        event.target.dataset.answerSessionIp
      ).then(response => {
        if(!$country_info[survey_version])
          $country_info[survey_version] = {}
        $country_info[survey_version][event.target.dataset.answerSessionUuid] = response
        
        renderCountryInfo(
          event.target.dataset.answerSessionUuid,
          $country_info[survey_version][event.target.dataset.answerSessionUuid]
        )
      }).catch(error => {
        $button.removeClass('hide')
        $loader.addClass('hide')
      })
    }, 1000)
  })

  window.jQuery('.lar-user-info-return').on('click', event => {
    const $button = window.jQuery(event.target)
    const survey_version = $(event.target).data('surveyVersion')

    if(!survey_version)
      return false

    window.jQuery('.table-version-' + survey_version).removeClass('hide')
    $button.parents('.table-users-info').addClass('hide')
    document.documentElement.scrollTop =  scroll_top
  })

  window.jQuery('table.table-versions').each((index, table) => {
    const $table = window.jQuery(table)
    const $svg_answer_completeness = $table.find('.svg-answer-completeness')
    const $svg_answer_date = $table.find('.svg-answer-date')
    const $svg_browser_date = $table.find('.svg-answer-browser')
    const $svg_platform_date = $table.find('.svg-answer-platform')
    const $svg_country = $table.find('.svg-answer-country')
    const d3_border_color = d3.scale.category10()();

    [$svg_country, $svg_answer_completeness, $svg_answer_date, $svg_browser_date, $svg_platform_date].forEach($dom =>
      $dom.css('border', '2px dashed ' + d3_border_color)
    )

    $svg_country.on('click', event => {
      const survey_version = $table.data('surveyVersion')
      const countries = $country_info[survey_version] || {}
      const data = Object.keys(countries)
        .map(k =>
          ({
            answer_session_uuid: k,
            c: countries[k].Coordinates,
            country: countries[k].Country + '/' + countries[k]['State / Region']
          })
        )
        .filter(c =>
          !/0, +0/.test(c.c)
        )
        .map(c => {
          const coords = c.c.match(/^([^,]+),(.+)$/) && [parseFloat(RegExp.$1.trim()), parseFloat(RegExp.$2.trim())]
          if(coords) {
            coords.answer_session_uuid = c.answer_session_uuid
            coords.country = c.country
            return coords
          }
        })
        .filter(id => id)

      d3Graph.drawMap(data, {
        x_column: '1',
        y_column: '0',
        mouseover_column: 'country',
        func_go_back: d3BackRoot
      })
    })

    $svg_answer_completeness.on('click', event => {
      const survey_version = $table.data('surveyVersion')
      const d = $d3_data.answers.find(answers =>
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
        func_go_back: d3BackRoot
      })
    })

    $svg_browser_date.on('click', event => {
      const survey_version = $table.data('surveyVersion')
      const d = Object.keys($d3_data.browsers[survey_version]).map(browser => {
        return {
          type: browser,
          total: $d3_data.browsers[survey_version][browser]
        }
      })

      d3Graph.drawHorizontalBars(d, {
        x_column: 'total',
        y_column: 'type',
        x_axis_title: 'Answers',
        graph_title: 'Answers by browser',
        table_version: survey_version,
        func_go_back: d3BackRoot
      })
    })

    $svg_platform_date.on('click', event => {
      const survey_version = $table.data('surveyVersion')
      const d = Object.keys($d3_data.platforms[survey_version]).map(platform => {
        return {
          type: platform,
          total: $d3_data.platforms[survey_version][platform]
        }
      })

      d3Graph.drawHorizontalBars(d, {
        x_column: 'total',
        y_column: 'type',
        x_axis_title: 'Answers',
        graph_title: 'Answers by platform',
        table_version: survey_version,
        func_go_back: d3BackRoot
      })
    })

    $svg_answer_date.on('click', event => {
      const survey_version = $table.data('surveyVersion')
      const d3_dates_data = $d3_data.dates[survey_version]
      const survey_data = [].concat(d3_dates_data).map(d => {
        d.date = new Date(d.date)
        return d
      })

      d3Graph.drawLines(survey_data, {
        x_column: 'date',
        y_column: 'answers',
        y_axis_title: 'Answered',
        graph_title: 'Answers by date',
        func_go_back: d3BackRoot
      })
    })
  })

  setTimeout(_ => {
    d3Graph.drawBars($d3_data.answers, $d3_answers_options),
    500
  })
})
