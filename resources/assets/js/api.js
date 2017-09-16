import axios from 'axios'

const http_request = axios.create({
  baseURL: `${location.protocol}//${location.host}/laravel/api`,
  timeout: 5e3,
  headers: {
    'content-type': 'application/json'
  }
})

export default {
  async generateSessionId(survey_uuid) {
    return await http_request.get(`${survey_uuid}/session_id`).then(response =>
      response && response.data && response.data.success && response.data.success.session_id || response.data.error
    )
  },
  async saveAnswer(session_id, survey_id, question_id, question_option_id, free_text = '') {
    return await http_request.post('/save_answer', {
      session_id,
      survey_id,
      question_id,
      question_option_id,
      free_text
    }).then(response =>
      response && response.data && response.data.success || response.data.error
    )
  }
}
