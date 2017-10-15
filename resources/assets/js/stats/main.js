import PublicSurveyStats from './stats.js'

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
