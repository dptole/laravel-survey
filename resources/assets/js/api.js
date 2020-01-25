import axios from 'axios'

const http_request = axios.create({
  baseURL: `${location.protocol}//${location.host}/laravel/api`,
  timeout: 30e3,
  headers: {
    'content-type': 'application/json'
  }
})

export default {
  generateSessionId: (survey_uuid, extra_info = {}) =>
    http_request.post(`${survey_uuid}/session_id`, extra_info).then(response =>
      response && response.data && response.data.success && response.data.success.session_id || response.data.error
    ),

  saveAnswer: (answers_session_id, survey_id, question_id, question_option_id, free_text = '') =>
    http_request.post('/save_answer', {
      answers_session_id,
      survey_id,
      question_id,
      question_option_id,
      free_text
    }).then(response =>
      response && response.data && response.data.success || response.data.error
    ),

  fetchCountryInfo: (answers_session_id, ip) =>
    http_request.post('/fetch_country_info', {
      answers_session_id,
      ip
    }).then(response =>
      response && response.data && response.data.success || response.data.error
    )
}
